<?php
require_once __DIR__ . '/../model/ProductModel.php';
require_once __DIR__ . '/../model/GeminiService.php';

class SetupController {

    private $productModel;

    private $slots = [
        'cpu'         => ['label' => 'Processador',        'icon' => 'fa-microchip',    'categoria' => 'Processadores',              'obrigatorio' => true],
        'motherboard' => ['label' => 'Placa-Mãe',           'icon' => 'fa-server',       'categoria' => 'Placas-Mãe',                  'obrigatorio' => true],
        'ram'         => ['label' => 'Memória RAM',         'icon' => 'fa-memory',       'categoria' => 'Memórias RAM',                'obrigatorio' => true],
        'gpu'         => ['label' => 'Placa de Vídeo',      'icon' => 'fa-tv',           'categoria' => 'Placas de Vídeo',             'obrigatorio' => false],
        'storage'     => ['label' => 'Armazenamento',       'icon' => 'fa-hdd',          'categoria' => 'Armazenamento (SSD/NVMe)',    'obrigatorio' => true],
        'psu'         => ['label' => 'Fonte',               'icon' => 'fa-bolt',         'categoria' => 'Fontes de Alimentação',       'obrigatorio' => true],
        'cooler'      => ['label' => 'Cooler',               'icon' => 'fa-wind',         'categoria' => 'Refrigeração e Coolers',      'obrigatorio' => false],
        'gabinete'    => ['label' => 'Gabinete',            'icon' => 'fa-box',          'categoria' => 'Gabinetes',                   'obrigatorio' => true],
    ];

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $this->productModel = new ProductModel();
    }

    public function router() {
        $action = $_GET['action'] ?? 'view';

        if ($action === 'ajax_ai') {
            $this->ajaxAi();
        } else {
            $this->view();
        }
    }

    /**
     * Carrega os produtos de cada slot, já com specs decodificadas e preço final.
     * Usado tanto pela tela quanto pela IA.
     */
    private function carregarProdutosPorSlot(): array {
        $produtosPorSlot = [];

        foreach ($this->slots as $key => $slot) {
            $linhas = $this->productModel->getProductsByCategoria($slot['categoria']);

            foreach ($linhas as &$linha) {
                $specs = json_decode($linha['especificacoes'] ?? '{}', true);
                $linha['especificacoes'] = is_array($specs) ? $specs : [];
                $linha['preco'] = floatval($linha['preco']);

                $desconto = floatval($linha['desconto'] ?? 0);
                $linha['desconto'] = $desconto;
                $linha['preco_final'] = round($linha['preco'] - ($linha['preco'] * ($desconto / 100)), 2);
            }
            unset($linha);

            $produtosPorSlot[$key] = $linhas;
        }

        return $produtosPorSlot;
    }

    public function view() {
        $produtosPorSlot = $this->carregarProdutosPorSlot();
        $slots = $this->slots;
        $title = 'Montador de Setup | Voidbyte Shop';

        require_once __DIR__ . '/../views/setup.php';
    }

    /**
     * IA: monta o setup a partir do pedido da pessoa.
     * Teste: montar-setup.php?action=ajax_ai&pedido=PC para programar com orçamento de 6000 reais
     */
    public function ajaxAi() {
        header('Content-Type: application/json; charset=utf-8');

        $pedido = trim($_REQUEST['pedido'] ?? '');
        if (mb_strlen($pedido) < 5 || mb_strlen($pedido) > 500) {
            http_response_code(400);
            echo json_encode(['erro' => 'Descreva o setup que você quer (entre 5 e 500 caracteres).']);
            exit;
        }

        $produtosPorSlot = $this->carregarProdutosPorSlot();

        // Catálogo enxuto que vai para a IA + tabela de preços para validar a resposta
        $catalogo = [];
        $precos   = [];
        foreach ($produtosPorSlot as $slot => $lista) {
            $catalogo[$slot] = [];
            foreach ($lista as $p) {
                $id = (int) $p['id'];
                $catalogo[$slot][] = [
                    'id'    => $id,
                    'nome'  => $p['nome'],
                    'preco' => $p['preco_final'],
                    'specs' => $p['especificacoes'],
                ];
                $precos[$slot][$id] = $p['preco_final'];
            }
        }

        // Schema: um id (ou null) por slot + uma explicação curta
        $propsPecas = [];
        foreach (array_keys($this->slots) as $slot) {
            $propsPecas[$slot] = ['type' => 'INTEGER', 'nullable' => true];
        }
        $schema = [
            'type' => 'OBJECT',
            'properties' => [
                'pecas'      => ['type' => 'OBJECT', 'properties' => $propsPecas, 'required' => array_keys($this->slots)],
                'explicacao' => ['type' => 'STRING'],
            ],
            'required' => ['pecas', 'explicacao'],
        ];

        $instrucao = <<<TXT
Você é o assistente de montagem de PCs da loja Voidbyte Shop.
Escolha UMA peça por categoria, usando SOMENTE ids que existem no catálogo recebido.
Regras:
- Respeite o orçamento do cliente: a soma dos preços das peças escolhidas não pode ultrapassar o valor pedido. Se ele não informar orçamento, escolha um bom custo-benefício.
- Garanta compatibilidade: socket da CPU igual ao da placa-mãe; tipo de memória RAM igual ao aceito pela placa-mãe; formato da placa-mãe cabe no gabinete; fonte com potência maior que o consumo da CPU + GPU + 150W, com folga de 20%; cooler compatível com o socket.
- Use null quando não houver peça adequada no catálogo, ou quando a categoria for opcional (placa de vídeo, cooler) e o uso do cliente não exigir.
- Adapte as escolhas ao uso descrito (jogos, programação, edição de vídeo, escritório...).
- Não calcule totais: o sistema soma os preços.
- Em "explicacao", escreva em português do Brasil, em até 4 frases, o porquê das escolhas e avise se algo ficou de fora por falta de peças no catálogo.
TXT;

        $prompt = "Pedido do cliente: {$pedido}\n\nCatálogo (JSON, por categoria):\n"
                . json_encode($catalogo, JSON_UNESCAPED_UNICODE);

        try {
            $gemini = new GeminiService();
            $saida  = $gemini->gerarJson($instrucao, $prompt, $schema);
        } catch (Exception $e) {
            error_log('[Voidbyte IA] ' . $e->getMessage());
            http_response_code(502);
            echo json_encode([
                'erro'  => 'A IA não conseguiu responder agora. Tente novamente em instantes.',
                'debug' => $e->getMessage(), // REMOVER no passo de proteções
            ]);
            exit;
        }

        // Validação: a IA só pode devolver ids que existem no slot correspondente
        $pecas  = [];
        $avisos = [];
        $total  = 0;

        foreach ($this->slots as $slot => $info) {
            $id = $saida['pecas'][$slot] ?? null;

            if ($id !== null && isset($precos[$slot][(int) $id])) {
                $pecas[$slot] = (int) $id;
                $total += $precos[$slot][(int) $id];
            } else {
                $pecas[$slot] = null;
                if ($id !== null) {
                    $avisos[] = "A IA sugeriu uma peça inválida para {$info['label']}; ela foi ignorada.";
                } elseif ($info['obrigatorio']) {
                    $avisos[] = "Nenhuma peça adequada encontrada para {$info['label']}.";
                }
            }
        }

        echo json_encode([
            'pecas'      => $pecas,
            'total'      => round($total, 2),
            'explicacao' => $saida['explicacao'] ?? '',
            'avisos'     => $avisos,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>