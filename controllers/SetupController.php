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

        require_once __DIR__