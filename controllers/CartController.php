<?php
require_once __DIR__ . '/../model/ProductModel.php';

class CartController {
    private $productModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }
        $this->productModel = new ProductModel();
    }

    public function router() {
        $action = $_POST['action'] ?? $_GET['action'] ?? 'view';

        switch ($action) {
            case 'add':    $this->add(); break;
            case 'remove': $this->remove(); break;
            case 'update': $this->update(); break;
            case 'view':
            default:       $this->view(); break;
        }
    }

    // ==========================================
    // ADICIONAR AO CARRINHO
    // ==========================================
    public function add() {
        $produto_id = isset($_POST['produto_id']) ? intval($_POST['produto_id']) : 0;
        $quantidade = isset($_POST['quantidade']) ? intval($_POST['quantidade']) : 1;
        $isAjax = isset($_POST['ajax']) && $_POST['ajax'] === '1';

        if ($produto_id > 0) {
            $produto = $this->productModel->getProductById($produto_id);
            if ($produto) {
                if (isset($_SESSION['carrinho'][$produto_id])) {
                    $_SESSION['carrinho'][$produto_id]['quantidade'] += $quantidade;
                } else {
                    $precoBase = $produto['preco'];
                    $desconto = $produto['desconto'] ?? 0;
                    $precoFinal = $precoBase - ($precoBase * ($desconto / 100));

                    $_SESSION['carrinho'][$produto_id] = [
                        'id' => $produto['id'],
                        'nome' => $produto['nome'],
                        'preco' => $precoFinal,
                        'imagem' => $produto['imagem'],
                        'quantidade' => $quantidade,
                        'categoria' => $produto['categoria']
                    ];
                }
                
                $msgToast = "Protocolo adicionado: {$produto['nome']} no carrinho!";

                if ($isAjax) {
                    $totalItens = 0;
                    foreach ($_SESSION['carrinho'] as $item) $totalItens += $item['quantidade'];
                    
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'success', 'total_itens' => $totalItens, 'msg' => $msgToast]);
                    exit;
                }
                $_SESSION['toast_msg'] = $msgToast;
            }
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? 'carrinho.php';
        header("Location: $referer");
        exit;
    }

    // ==========================================
    // ATUALIZAR QUANTIDADE (+ e -)
    // ==========================================
    public function update() {
        $produto_id = isset($_POST['produto_id']) ? intval($_POST['produto_id']) : 0;
        $operacao = $_POST['operacao'] ?? '';
        $isAjax = isset($_POST['ajax']) && $_POST['ajax'] === '1';

        if ($produto_id > 0 && isset($_SESSION['carrinho'][$produto_id])) {
            if ($operacao === 'aumentar') {
                $_SESSION['carrinho'][$produto_id]['quantidade']++;
            } elseif ($operacao === 'diminuir') {
                $_SESSION['carrinho'][$produto_id]['quantidade']--;
            }

            $removido = false;
            if ($_SESSION['carrinho'][$produto_id]['quantidade'] <= 0) {
                unset($_SESSION['carrinho'][$produto_id]);
                $removido = true;
            }

            // Se for AJAX, devolve o JSON com as contas refeitas
            if ($isAjax) {
                $this->enviarRespostaJson($produto_id, $removido);
            }
        }

        header('Location: carrinho.php');
        exit;
    }

    // ==========================================
    // REMOVER DO CARRINHO
    // ==========================================
    public function remove() {
        $produto_id = isset($_POST['produto_id']) ? intval($_POST['produto_id']) : 0;
        $isAjax = isset($_POST['ajax']) && $_POST['ajax'] === '1';
        
        if ($produto_id > 0 && isset($_SESSION['carrinho'][$produto_id])) {
            unset($_SESSION['carrinho'][$produto_id]);
            
            if ($isAjax) {
                $this->enviarRespostaJson($produto_id, true);
            }
            $_SESSION['toast_msg'] = "Item ejetado do carrinho.";
        }
        
        header('Location: carrinho.php');
        exit;
    }

    // ==========================================
    // VIEW PRINCIPAL
    // ==========================================
    public function view() {
        $itensCarrinho = $_SESSION['carrinho'];
        $total = 0;
        foreach ($itensCarrinho as $item) {
            $total += $item['preco'] * $item['quantidade'];
        }
        require_once __DIR__ . '/../views/carrinho.php';
    }

    // ==========================================
    // FUNÇÃO AUXILIAR: GERA O JSON PARA A TELA
    // ==========================================
    private function enviarRespostaJson($produto_id, $removido) {
        $totalItens = 0;
        $totalCarrinho = 0;
        $itemQtd = 0;
        $itemTotal = 0;

        if (isset($_SESSION['carrinho'])) {
            foreach ($_SESSION['carrinho'] as $id => $item) {
                $totalItens += $item['quantidade'];
                $totalCarrinho += ($item['preco'] * $item['quantidade']);
                
                if ($id == $produto_id) {
                    $itemQtd = $item['quantidade'];
                    $itemTotal = ($item['preco'] * $item['quantidade']);
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'removido' => $removido,
            'produto_id' => $produto_id,
            'item_qtd' => $itemQtd,
            'item_total_formatado' => number_format($itemTotal, 2, ',', '.'),
            'total_carrinho_formatado' => number_format($totalCarrinho, 2, ',', '.'),
            'total_itens' => $totalItens,
            'carrinho_vazio' => empty($_SESSION['carrinho'])
        ]);
        exit;
    }
}
?>