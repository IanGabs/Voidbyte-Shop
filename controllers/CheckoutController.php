<?php
require_once __DIR__ . '/../model/OrderModel.php';

class CheckoutController {

    private $orderModel;

    const FRETE_GRATIS_A_PARTIR_DE = 300.00;
    const VALOR_FRETE = 29.90;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $this->checkAuth();
        $this->orderModel = new OrderModel();
    }

    /** Precisa estar logado (admin ou cliente) para finalizar compra */
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
            case 'finalizar': $this->finalizar(); break;
            case 'sucesso':   $this->sucesso(); break;
            case 'view':
            default:          $this->view(); break;
        }
    }

    private function calcularTotais(): array {
        $itens = $_SESSION['carrinho'] ?? [];
        $subtotal = 0;
        foreach ($itens as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }
        $frete = ($subtotal >= self::FRETE_GRATIS_A_PARTIR_DE || $subtotal == 0) ? 0.00 : self::VALOR_FRETE;

        return [
            'itens' => $itens,
            'subtotal' => $subtotal,
            'frete' => $frete,
            'total' => $subtotal + $frete
        ];
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
        $total = $totais['total'];

        // Pré-preenche nome com o do usuário logado (já disponível na sessão)
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

        // Validação simples de obrigatoriedade
        $obrigatorios = ['nome', 'endereco', 'numero', 'bairro', 'cidade', 'estado', 'cep', 'forma_pagamento'];
        foreach ($obrigatorios as $campo) {
            if ($dadosEntrega[$campo] === '') {
                $_SESSION['checkout_erro'] = "Preencha todos os campos obrigatórios de entrega e pagamento.";
                header('Location: checkout.php');
                exit;
            }
        }

        if (strlen($dadosEntrega['estado']) !== 2) {
            $_SESSION['checkout_erro'] = "Informe a UF do estado com 2 letras (ex: SP).";
            header('Location: checkout.php');
            exit;
        }

        $formasValidas = ['cartao', 'pix', 'boleto'];
        if (!in_array($dadosEntrega['forma_pagamento'], $formasValidas)) {
            $_SESSION['checkout_erro'] = "Selecione uma forma de pagamento válida.";
            header('Location: checkout.php');
            exit;
        }

        // Se for cartão, exige os campos simulados do cartão
        if ($dadosEntrega['forma_pagamento'] === 'cartao') {
            $numeroCartao = preg_replace('/\D/', '', $_POST['cartao_numero'] ?? '');
            if (strlen($numeroCartao) < 13 || empty($_POST['cartao_nome']) || empty($_POST['cartao_validade']) || empty($_POST['cartao_cvv'])) {
                $_SESSION['checkout_erro'] = "Preencha corretamente os dados do cartão.";
                header('Location: checkout.php');
                exit;
            }
        }

        // Totais SEMPRE recalculados no servidor a partir da sessão —
        // nunca confiar em valores vindos do formulário/cliente.
        $totais = $this->calcularTotais();

        $pedidoId = $this->orderModel->createOrder(
            (int) $_SESSION['user_id'],
            $dadosEntrega,
            $totais['itens'],
            $totais['frete']
        );

        if (!$pedidoId) {
            $_SESSION['checkout_erro'] = "Não foi possível processar seu pedido. Tente novamente.";
            header('Location: checkout.php');
            exit;
        }

        // Esvazia o carrinho e guarda o id do último pedido feito
        $_SESSION['carrinho'] = [];
        $_SESSION['ultimo_pedido_id'] = $pedidoId;

        header('Location: checkout.php?action=sucesso&pedido=' . $pedidoId);
        exit;
    }

    public function sucesso() {
        $pedidoId = intval($_GET['pedido'] ?? 0);

        // Só mostra se o id bate com o pedido que ESTE usuário acabou de criar
        if ($pedidoId <= 0 || ($_SESSION['ultimo_pedido_id'] ?? 0) != $pedidoId) {
            header('Location: index.php');
            exit;
        }

        $pedido = $this->orderModel->getOrderById($pedidoId, (int) $_SESSION['user_id']);

        if (!$pedido) {
            header('Location: index.php');
            exit;
        }

        unset($_SESSION['ultimo_pedido_id']); // link de sucesso só funciona uma vez

        $title = 'Pedido Confirmado | Voidbyte Shop';
        require_once __DIR__ . '/../views/checkout_sucesso.php';
    }
}
?>