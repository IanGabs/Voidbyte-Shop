<?php
require_once __DIR__ . '/../model/ProductModel.php';

class HomeController {
    private $productModel;

    public function __construct() {
        // Inicia a sessão se ela ainda não existir, para saber se o cliente está logado
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->productModel = new ProductModel();
    }

    public function router() {
        $action = $_GET['action'] ?? 'home';

        switch ($action) {
            case 'home':
                $this->home();
                break;
            // Futuramente colocaremos rotas como 'produtos', 'detalhes', etc.
            default:
                $this->home();
                break;
        }
    }

    public function home() {
        // Busca todos os produtos reais que o Admin cadastrou no banco de dados
        $produtos = $this->productModel->getAllProducts();
        
        // Renderiza a View principal do cliente
        require_once __DIR__ . '/../views/home.php';
    }
}
?>