<?php
require_once __DIR__ . '/../model/ProductModel.php';

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
        $this->view();
    }

    public function view() {
        $produtosPorSlot = [];

        foreach ($this->slots as $key => $slot) {
            $linhas = $this->productModel->getProductsByCategoria($slot['categoria']);

            // Decodifica as especificações aqui para o JS não precisar fazer JSON.parse duplo
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

        $slots = $this->slots;
        $title = 'Montador de Setup | Voidbyte Shop';

        require_once __DIR__ . '/../views/setup.php';
    }
}
?>