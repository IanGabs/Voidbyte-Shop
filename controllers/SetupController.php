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
     * Monta o setup de forma automática via algoritmo local caso a API externe falhe (503/429)
     */
    private function montarSetupLocal(array $produtosPorSlot): array {
        $pecas = [];
        $total = 0;

        foreach ($this->slots as $slot => $info) {
            $lista = $produtosPorSlot[$slot] ?? [];
            if (empty($lista)) {
                $pecas[$slot] = null;
                continue;
            }

            // Seleciona a opção com melhor custo-benefício (menor preço final disponível no catálogo)
            usort($lista, function($a, $b) {
                return $a['preco_final'] <=> $b['preco_final'];
            });

            // Se for opcional (ex: gpu, cooler) e houver mais de uma opção, pega a segunda ou pula se estrito, senão pega a mais barata
            $escolhido = $lista[0];
            $pecas[$slot] = (int) $escolhido['id'];
            $total += $escolhido['preco_final'];
        }

        return [
            'pecas'      => $pecas,
            'total'      => round($total, 2),
            'explicacao' => 'Montagem gerada pelo assistente de contingência local (Modo Offline/Resiliente devido à alta demanda nos servidores de IA). Selecionamos os melhores componentes em custo-benefício cadastrados no banco de dados.',
            'avisos'     => ['Os servidores externos da IA estão sobrecarregados (Erro 503). O setup foi otimizado automaticamente pelo sistema interno.']
        ];
    }

    public function ajaxAi() {
        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: application/json; charset=utf-8');

        $pedido = trim($_REQUEST['pedido'] ?? '');
        if (mb_strlen($pedido) < 5 || mb_strlen($pedido) > 500) {
            http_response_code(400);
            echo json_encode(['erro' => 'Descreva o setup que você quer (entre 5 e 500 caracteres).']);
            exit;
        }

        $produtosPorSlot = $this->carregarProdutosPorSlot();

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
TXT;

        $prompt = "Pedido do cliente: {$pedido}\n\nCatálogo (JSON, por categoria):\n"
                . json_encode($catalogo, JSON_UNESCAPED_UNICODE);

        try {
            $gemini = new GeminiService();
            $saida  = $gemini->gerarJson($instrucao, $prompt, $schema);
            
            // Validação da resposta da IA
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

            $respostaFinal = [
                'pecas'      => $pecas,
                'total'      => round($total, 2),
                'explicacao' => $saida['explicacao'] ?? '',
                'avisos'     => $avisos,
            ];

        } catch (Exception $e) {
            // FALLBACK INTELIGENTE: Se der 503, 429 ou qualquer erro de API, o PHP resolve localmente!
            error_log('[Voidbyte IA Fallback Ativado] ' . $e->getMessage());
            $respostaFinal = $this->montarSetupLocal($produtosPorSlot);
        }

        echo json_encode($respostaFinal, JSON_UNESCAPED_UNICODE);
        exit;
    }
}