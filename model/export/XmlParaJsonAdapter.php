<?php
require_once __DIR__ . '/ExportadorJson.php';
require_once __DIR__ . '/ExportadorXml.php';

/**
 * ADAPTER (Object Adapter) — estrutura da Aula 10, slide 21.
 *
 *   Cliente  ──▶  ExportadorJson (Alvo)
 *                        ▲
 *                        │ implements
 *                 XmlParaJsonAdapter ──adapta──▶ ExportadorXml (Adaptado)
 *
 * O Adapter IMPLEMENTA a interface que o cliente espera (ExportadorJson)
 * e CONTÉM uma instância do adaptado (composição, não herança múltipla).
 * Toda chamada a exportarJson() é traduzida internamente para
 * exportarXml() e o resultado é convertido de XML para JSON.
 */
class XmlParaJsonAdapter implements ExportadorJson {

    /** @var ExportadorXml O objeto adaptado */
    private $adaptado;

    /** Guarda o último XML produzido, útil para baixar os dois formatos */
    private $ultimoXml = '';

    public function __construct(ExportadorXml $exportadorXml) {
        $this->adaptado = $exportadorXml;
    }

    /**
     * Interface esperada pelo cliente. Por dentro delega ao adaptado.
     */
    public function exportarJson(array $dados): string {
        // 1) Chama a interface incompatível do adaptado
        $this->ultimoXml = $this->adaptado->exportarXml($dados);

        // 2) Converte XML -> estrutura PHP
        $xmlObj = simplexml_load_string(
            $this->ultimoXml,
            'SimpleXMLElement',
            LIBXML_NOCDATA // faz o conteúdo em CDATA virar string normal
        );

        if ($xmlObj === false) {
            throw new Exception('Falha ao converter o XML gerado pelo exportador.');
        }

        // 3) Entrega no formato que o sistema entende
        $array = json_decode(json_encode($xmlObj), true);

        return json_encode(
            $array,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /** Devolve o XML intermediário (o "antes" da adaptação) */
    public function getUltimoXml(): string {
        return $this->ultimoXml;
    }
}
?>
