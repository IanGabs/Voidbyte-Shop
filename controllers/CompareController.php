<?php
require_once __DIR__ . '/../model/ProductModel.php';

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

    /**
     * Aceita de 2 a 3 ids: comparar.php?action=ajax_compare&ids=4,7,9
     */
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
            // Especificações viram array para o front montar a tabela
            $specs = json_decode($p['especificacoes'] ?? '{}', true);
            $p['especificacoes'] = is_array($specs) ? $specs : [];

            // Preço já calculado com desconto, para não repetir a conta no JS
            $desconto = floatval($p['desconto'] ?? 0);
            $p['preco'] = floatval($p['preco']);
            $p['preco_final'] = round($p['preco'] - ($p['preco'] * ($desconto / 100)), 2);
            $p['desconto'] = $desconto;
        }
        unset($p);

        echo json_encode(['produtos' => $produtos]);
        exit;
    }
}
?>