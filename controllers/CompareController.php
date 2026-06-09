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
        // Busca todos os produtos para preencher as caixas de seleção
        $produtos = $this->productModel->getAllProducts();
        $title = 'Matriz de Comparação | Voidbyte Shop';
        require_once __DIR__ . '/../views/comparador.php';
    }

    public function ajaxCompare() {
        $idA = intval($_GET['id_a'] ?? 0);
        $idB = intval($_GET['id_b'] ?? 0);

        $prodA = $this->productModel->getProductById($idA);
        $prodB = $this->productModel->getProductById($idB);

        // Decodifica o JSON do banco para transformar em Array do PHP
        if($prodA) $prodA['especificacoes'] = json_decode($prodA['especificacoes'] ?? '{}', true);
        if($prodB) $prodB['especificacoes'] = json_decode($prodB['especificacoes'] ?? '{}', true);

        header('Content-Type: application/json');
        echo json_encode(['produtoA' => $prodA, 'produtoB' => $prodB]);
        exit;
    }
}
?>