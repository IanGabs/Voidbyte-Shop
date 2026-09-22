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

    public function detalhes() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id > 0) {
            require_once 'model/ProductModel.php';
            $productModel = new ProductModel();
            
            // Alterado de getProdutoById para getProductById
            $produto = $productModel->getProductById($id);

            if ($produto) {
                require_once 'views/detalhes.php';
                return;
            }
        }
        
        header("Location: produtos.php");
        exit;
    }
}
?>