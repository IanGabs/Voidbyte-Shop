<?php
require_once __DIR__ . '/../model/OrderModel.php';
require_once __DIR__ . '/../model/CouponModel.php';

class CheckoutController {

    private $orderModel;
    private $couponModel;

    const FRETE_GRATIS_A_PARTIR_DE = 300.00;
    const VALOR_FRETE = 29.90;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $this->checkAuth();
        $this->orderModel = new OrderModel();
        $this->couponModel = new CouponModel();
    }

    private function checkAuth() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $_SESSION['auth_msg'] = "Faça login para finalizar sua compra.";
            $_SESSION['pos_login_redirect'] = 'checkout.php';
            header('Location: login.php');
            exit;
        }
    }

    public function router() {
        $action = $_POST['action'] ?? $_GET['action'] ?? 'view';

        switch ($action) {
            case 'aplicar_cupom': $this->aplicarCupom(); break;
            case 'remover_cupom': $this->removerCupom(); break;
            case 'finalizar':     $this->finalizar(); break;
            case 'sucesso':       $this->sucesso(); break;
            case 'view':
            default:              $this->view(); break;
        }
    }

    private function calcularTotais(): array {
        $itens = $_SESSION['carrinho'] ?? [];
        $subtotal = 0;
        foreach ($itens as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }
        
        $frete = ($subtotal >= self::FRETE_GRATIS_A_PARTIR_DE || $subtotal == 0) ? 0.00 : self::VALOR_FRETE;

        // Cálculo do Cupom armazenado na Sessão
        $descontoCupom = 0;
        $cupomAplicado = $_SESSION['cupom_aplicado'] ?? null;

        if ($cupomAplicado) {
            if ($cupomAplicado['tipo'] === 'percentual') {
                $descontoCupom = round($subtotal * ($cupomAplicado['valor'] / 100), 2);
            } else {
                $descontoCupom = min($subtotal, floatval($cupomAplicado['valor']));
            }
        }

        $total = max(0, ($subtotal - $descontoCupom) + $frete);

        return [
            'itens'         => $itens,
            'subtotal'      => $subtotal,
            'frete'         => $frete,
            'desconto_cupom'=> $descontoCupom,
            'cupom_info'    => $cupomAplicado,
            'total'         => $total
        ];
    }

    public function aplicarCupom() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: checkout.php');
            exit;
        }

        $codigo = trim($_POST['codigo_cupom'] ?? '');
        $cupom = $this->couponModel->getByCode($codigo);

        if ($cupom) {
            $_SESSION['cupom_aplicado'] = $cupom;
            $_SESSION['checkout_msg'] = "Cupom '{$cupom['codigo']}' aplicado com sucesso!";
        } else {
            $_SESSION['checkout_erro'] = "Cupom inválido ou expirado.";
        }

        header('Location: checkout.php');
        exit;
    }

    public function removerCupom() {
        unset($_SESSION['cupom_aplicado']);
        $_SESSION['checkout_msg'] = "Cupom removido com sucesso.";
        header('Location: checkout.php');
        exit;
    }

    public function view() {
        if (empty($_SESSION['carrinho'])) {
            header('Location: carrinho.php');
            exit;
        }

        $totais = $this->calcularTotais();
        $itensCarrinho = $totais['itens'];
        $subtotal = $totais['subtotal'];
        $frete = $totais['frete'];
        $descontoCupom = $totais['desconto_cupom'];
        $cupomInfo = $totais['cupom_info'];
        $total = $totais['total'];
        $nomeUsuario = $_SESSION['user_name'] ?? '';

        $title = 'Finalizar Compra | Voidbyte Shop';
        require_once __DIR__ . '/../views/checkout.php';
    }

    public function finalizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['carrinho'])) {
            header('Location: carrinho.php');
            exit;
        }

        $dadosEntrega = [
            'nome'            => trim($_POST['nome'] ?? ''),
            'endereco'        => trim($_POST['endereco'] ?? ''),
            'numero'          => trim($_POST['numero'] ?? ''),
            'complemento'     => trim($_POST['complemento'] ?? ''),
            'bairro'          => trim($_POST['bairro'] ?? ''),
            'cidade'          => trim($_POST['cidade'] ?? ''),
            'estado'          => strtoupper(trim($_POST['estado'] ?? '')),
            'cep'             => trim($_POST['cep'] ?? ''),
            'forma_pagamento' => trim($_POST['forma_pagamento'] ?? ''),
        ];

        $obrigatorios = ['nome', 'endereco', 'numero', 'bairro', 'cidade', 'estado', 'cep', 'forma_pagamento'];
        foreach ($obrigatorios as $campo) {
            if ($dadosEntrega[$campo] === '') {
                $_SESSION['checkout_erro'] = "Preencha todos os campos obrigatórios de entrega e pagamento.";
                header('Location: checkout.php');
                exit;
            }
        }

        $totais = $this->calcularTotais();

        // Passa os dados financeiros calculados no servidor (incluindo o desconto do cupom)
        $pedidoId = $this->orderModel->createOrder(
            (int) $_SESSION['user_id'],
            $dadosEntrega,
            $totais['itens'],
            $totais['frete'],
            $totais['desconto_cupom'],
            $totais['cupom_info']['codigo'] ?? null
        );

        if (!$pedidoId) {
            $_SESSION['checkout_erro'] = "Não foi possível processar seu pedido. Tente novamente.";
            header('Location: checkout.php');
            exit;
        }

        $_SESSION['carrinho'] = [];
        unset($_SESSION['cupom_aplicado']);
        $_SESSION['ultimo_pedido_id'] = $pedidoId;

        header('Location: checkout.php?action=sucesso&pedido=' . $pedidoId);
        exit;
    }

    public function sucesso() {
        $pedidoId = intval($_GET['pedido'] ?? 0);
        if ($pedidoId <= 0 || ($_SESSION['ultimo_pedido_id'] ?? 0) != $pedidoId) {
            header('Location: index.php');
            exit;
        }

        $pedido = $this->orderModel->getOrderById($pedidoId, (int) $_SESSION['user_id']);
        if (!$pedido) {
            header('Location: index.php');
            exit;
        }

        unset($_SESSION['ultimo_pedido_id']);
        $title = 'Pedido Confirmado | Voidbyte Shop';
        require_once __DIR__ . '/../views/checkout_sucesso.php';
    }
}
?>