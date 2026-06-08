<?php
require_once __DIR__ . '/../model/ProductModel.php';

class ProductController {
    private $productModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->productModel = new ProductModel();
    }

    public function router() {
        $action = $_GET['action'] ?? 'catalog';

        switch ($action) {
            case 'catalog':
            default:
                $this->showCatalog();
                break;
        }
    }

    public function showCatalog() {
        // Busca os produtos no banco de dados
        $produtos = $this->productModel->getAllProducts();
        
        // Define o título da página para a aba do navegador
        $title = 'Catálogo de Produtos | Voidbyte Shop';
        
        // Chama a View
        require_once __DIR__ . '/../views/produtos.php';
    }
}
?>