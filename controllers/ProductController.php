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
                require_once __DIR__ . '/../model/CommentModel.php';
                $commentModel = new CommentModel();

                // Ações da seção de comentários
                $acao = $_POST['action'] ?? $_GET['action'] ?? '';

                if ($acao === 'login_comentar') {
                    $this->irParaLoginDeComentario($id);
                }

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if ($acao === 'comentar') {
                        $this->salvarComentario($id, $commentModel);
                    } elseif ($acao === 'excluir_comentario') {
                        $this->excluirComentario($id, $commentModel);
                    }
                }

                $comentarios = $commentModel->getByProduct($id);

                require_once 'views/detalhes.php';
                return;
            }
        }
        
        header("Location: produtos.php");
        exit;
    }

    /* =======================================================
       COMENTÁRIOS
       ======================================================= */
    private function usuarioLogado() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    private function voltarParaComentarios($produtoId) {
        header('Location: detalhes.php?id=' . (int)$produtoId . '#comentarios');
        exit;
    }

    // Visitante clicou em "faça login": volta para o produto depois de entrar
    private function irParaLoginDeComentario($produtoId) {
        $_SESSION['auth_msg'] = "Faça login para comentar.";
        $_SESSION['pos_login_redirect'] = 'detalhes.php?id=' . (int)$produtoId . '#comentarios';
        header('Location: login.php');
        exit;
    }

    private function salvarComentario($produtoId, $commentModel) {
        if (!$this->usuarioLogado()) {
            $this->irParaLoginDeComentario($produtoId);
        }

        $texto = trim($_POST['texto'] ?? '');
        $tamanho = mb_strlen($texto);

        if ($tamanho < 3) {
            $_SESSION['comentario_erro'] = "Escreva pelo menos 3 caracteres.";
        } elseif ($tamanho > 1000) {
            $_SESSION['comentario_erro'] = "O comentário pode ter no máximo 1000 caracteres.";
        } elseif (!$commentModel->create($produtoId, (int)$_SESSION['user_id'], $texto)) {
            $_SESSION['comentario_erro'] = "Não foi possível publicar o comentário. Tente novamente.";
        }

        $this->voltarParaComentarios($produtoId);
    }

    // Só o autor do comentário ou um admin pode excluir
    private function excluirComentario($produtoId, $commentModel) {
        if (!$this->usuarioLogado()) {
            $this->irParaLoginDeComentario($produtoId);
        }

        $comentarioId = (int)($_POST['comentario_id'] ?? 0);
        $comentario = $commentModel->getById($comentarioId);

        if ($comentario && (int)$comentario['produto_id'] === (int)$produtoId) {
            $ehAdmin = ($_SESSION['user_type'] ?? '') === 'admin';
            $ehAutor = (int)$comentario['usuario_id'] === (int)$_SESSION['user_id'];

            if ($ehAdmin || $ehAutor) {
                $commentModel->delete($comentarioId);
            }
        }

        $this->voltarParaComentarios($produtoId);
    }
}
?>