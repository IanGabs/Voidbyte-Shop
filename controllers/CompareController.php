<?php
require_once __DIR__ . '/../model/ProductModel.php';
require_once __DIR__ . '/../model/GeminiService.php';

class CompareController {
    private $productModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->productModel = new ProductModel();
    }

    public function router() {
        $action = $_GET['action'] ?? 'view';
        if ($action === 'ajax_compare') {
            $this->ajaxCompare();
        } elseif ($action === 'ajax_ai_compare') {
            $this->ajaxAiCompare();
        } else {
            $this->view();
        }
    }

    public function view() {
        $produtos   = $this->productModel->getAllProducts();
        $categorias = $this->productModel->getCategorias();
        $title = 'Matriz de Comparação | Voidbyte Shop';
        require_once __DIR__ . '/../views/comparador.php';
    }

    public function ajaxCompare() {
        header('Content-Type: application/json; charset=utf-8');

        $idsRaw = $_GET['ids'] ?? '';
        $ids = array_slice(array_filter(array_map('intval', explode(',', $idsRaw))), 0, 3);

        if (count($ids) < 2) {
            echo json_encode(['erro' => 'Selecione pelo menos dois produtos.']);
            exit;
        }

        $produtos = $this->productModel->getProductsByIds($ids);

        foreach ($produtos as &$p) {
            $specs = json_decode($p['especificacoes'] ?? '{}', true);
            $p['especificacoes'] = is_array($specs) ? $specs : [];

            $desconto = floatval($p['desconto'] ?? 0);
            $p['preco'] = floatval($p['preco']);
            $p['preco_final'] = round($p['preco'] - ($p['preco'] * ($desconto / 100)), 2);
            $p['desconto'] = $desconto;
        }
        unset($p);

        echo json_encode(['produtos' => $produtos]);
        exit;
    }

    /**
     * Fallback local inteligente: filtra por palavras-chave parciais (ex: "mouse", "teclado", "ssd")
     */
    private function compararLocal(array $todosProdutos, string $pedido): array {
        $ids = [];
        $pedidoLower = mb_strtolower($pedido);

        // Extrai palavras-chave relevantes do pedido (removendo artigos comuns)
        $palavras = array_filter(explode(' ', $pedidoLower), function($palavra) {
            return mb_strlen($palavra) > 2 && !in_array($palavra, ['para', 'com', 'dos', 'das', 'uma', 'que', 'qual']);
        });

        foreach ($todosProdutos as $p) {
            $catLower = mb_strtolower($p['categoria']);
            $nomeLower = mb_strtolower($p['nome']);
            
            foreach ($palavras as $palavra) {
                // Se a palavra (ex: "mouse") estiver presente na categoria ou no nome do produto
                if (str_contains($catLower, $palavra) || str_contains($nomeLower, $palavra)) {
                    $ids[] = (int) $p['id'];
                    break;
                }
            }
        }

        // Se encontrou itens correspondentes, pega até 3 únicos
        if (count($ids) >= 2) {
            $ids = array_slice(array_unique($ids), 0, 3);
        } else {
            // Fallback total se nenhuma palavra combinar: pega os 2 ou 3 primeiros do catálogo
            $ids = array_slice(array_column($todosProdutos, 'id'), 0, 3);
        }

        return [
            'ids'        => $ids,
            'explicacao' => 'Seleção gerada pelo assistente inteligente local. Comparamos os melhores itens encontrados no catálogo com base no seu pedido.'
        ];
    }

    public function ajaxAiCompare() {
        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: application/json; charset=utf-8');

        $pedido = trim($_REQUEST['pedido'] ?? '');
        if (mb_strlen($pedido) < 3 || mb_strlen($pedido) > 300) {
            http_response_code(400);
            echo json_encode(['erro' => 'Descreva o que deseja comparar (entre 3 e 300 caracteres).']);
            exit;
        }

        $todosProdutos = $this->productModel->getAllProducts();
        $catalogo = [];
        $idsValidos = [];

        foreach ($todosProdutos as $p) {
            $id = (int) $p['id'];
            $idsValidos[] = $id;
            $catalogo[] = [
                'id'        => $id,
                'nome'      => $p['nome'],
                'categoria' => $p['categoria'],
                'preco'     => round($p['preco'] - ($p['preco'] * (floatval($p['desconto'] ?? 0) / 100)), 2),
                'specs'     => json_decode($p['especificacoes'] ?? '{}', true) ?: []
            ];
        }

        $schema = [
            'type' => 'OBJECT',
            'properties' => [
                'ids_produtos' => [
                    'type' => 'ARRAY',
                    'items' => ['type' => 'INTEGER'],
                    'description' => 'De 2 a 3 IDs de produtos do catálogo que melhor atendem ao pedido.'
                ],
                'explicacao' => [
                    'type' => 'STRING',
                    'description' => 'Breve explicação em português do porquê estes produtos foram escolhidos para comparação.'
                ]
            ],
            'required' => ['ids_produtos', 'explicacao']
        ];

        $instrucao = "Você é o assistente de comparação de hardware da Voidbyte Shop. "
                   . "Com base no pedido do cliente e no catálogo fornecido, escolha de 2 a 3 IDs de produtos relevantes para serem comparados lado a lado.";

        $prompt = "Pedido do cliente: {$pedido}\n\nCatálogo (JSON):\n" . json_encode($catalogo, JSON_UNESCAPED_UNICODE);

        try {
            $gemini = new GeminiService();
            $saida = $gemini->gerarJson($instrucao, $prompt, $schema);
            
            $idsSugeridos = array_slice(array_unique(array_map('intval', $saida['ids_produtos'] ?? [])), 0, 3);
            $idsFinais = array_values(array_intersect($idsSugeridos, $idsValidos));

            if (count($idsFinais) < 2) {
                $idsFinais = array_slice($idsValidos, 0, 2);
            }

            $respostaFinal = [
                'ids'        => $idsFinais,
                'explicacao' => $saida['explicacao'] ?? 'Aqui estão os melhores itens encontrados para comparação.'
            ];

        } catch (Exception $e) {
            // Fallback inteligente ativado automaticamente por causa da instabilidade da API
            $respostaFinal = $this->compararLocal($todosProdutos, $pedido);
        }

        echo json_encode($respostaFinal, JSON_UNESCAPED_UNICODE);
        exit;
    }
}