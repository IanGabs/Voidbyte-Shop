<?php
class GeminiService {
    private $apiKey;
    private $models;
    private $timeout;

    // Códigos em que vale tentar de novo (sobrecarga ou limite momentâneo)
    private const RETENTAVEIS = [429, 500, 503, 504];
    private const TENTATIVAS_POR_MODELO = 2;

    public function __construct() {
        $cfg = require __DIR__ . '/../config/gemini.php';
        $this->apiKey  = $cfg['api_key'];
        $this->timeout = $cfg['timeout'] ?? 30;

        // Lista de modelos na ordem de preferência
        $this->models = [$cfg['model']];
        if (!empty($cfg['fallback_model'])) {
            $this->models[] = $cfg['fallback_model'];
        }
    }

    /**
     * Envia um prompt e devolve a resposta já decodificada como array,
     * obrigando o Gemini a seguir o $schema informado.
     */
    public function gerarJson(string $instrucao, string $prompt, array $schema): array {
        set_time_limit(120);

        $payload = [
            'systemInstruction' => ['parts' => [['text' => $instrucao]]],
            'contents' => [[
                'role'  => 'user',
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => [
                'temperature'      => 0.2,
                'responseMimeType' => 'application/json',
                'responseSchema'   => $schema,
            ],
        ];

        $ultimoErro = 'Erro desconhecido.';

        foreach ($this->models as $model) {
            for ($tentativa = 1; $tentativa <= self::TENTATIVAS_POR_MODELO; $tentativa++) {
                [$status, $dados, $erroCurl] = $this->chamar($model, $payload);

                // Sucesso
                if ($status === 200) {
                    $texto = $this->extrairTexto($dados);

                    // Remove cercas de markdown (```json ... ```) caso venham
                    $limpo = trim(preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($texto)));
                    $saida = json_decode($limpo, true);

                    if (!is_array($saida)) {
                        $motivo = $dados['candidates'][0]['finishReason'] ?? 'desconhecido';
                        $trecho = mb_substr($texto, 0, 200);
                        throw new Exception("[$model] JSON inválido (finishReason: $motivo). Texto recebido: \"$trecho\"");
                    }
                    return $saida;
                }

                // Falha de conexão (sem resposta do servidor)
                if ($status === 0) {
                    $ultimoErro = "[$model] Falha de conexão com o Gemini: $erroCurl";
                } else {
                    $ultimoErro = "[$model] Erro do Gemini (HTTP $status): " . ($dados['error']['message'] ?? 'sem mensagem');
                }

                // Erros que não adianta repetir (chave inválida, pedido malformado...)
                if ($status !== 0 && !in_array($status, self::RETENTAVEIS, true)) {
                    // Modelo inexistente: tenta o próximo da lista; os demais, para aqui
                    if ($status === 404) {
                        break;
                    }
                    throw new Exception($ultimoErro);
                }

                // Espera um pouco antes de tentar de novo
                if ($tentativa < self::TENTATIVAS_POR_MODELO) {
                    sleep(2);
                }
            }
        }

        throw new Exception($ultimoErro);
    }

    /**
     * Junta o texto de todas as partes da resposta, ignorando as partes
     * de "raciocínio" que alguns modelos enviam antes da resposta final.
     */
    private function extrairTexto(array $dados): string {
        $partes = $dados['candidates'][0]['content']['parts'] ?? [];
        $texto = '';
        foreach ($partes as $parte) {
            if (!empty($parte['thought'])) {
                continue;
            }
            $texto .= $parte['text'] ?? '';
        }
        return $texto;
    }

    /**
     * Faz uma única chamada HTTP. Retorna [status, dados, erroCurl].
     * status = 0 quando nem houve resposta do servidor.
     */
    private function chamar(string $model, array $payload): array {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        $resposta = curl_exec($ch);
        $erroCurl = curl_error($ch);
        $status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($resposta === false) {
            return [0, null, $erroCurl];
        }

        return [$status, json_decode($resposta, true) ?? [], ''];
    }
}