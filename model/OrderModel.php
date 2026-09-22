<?php
require_once __DIR__ . '/../config/Database.php';

class OrderModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Cria o pedido e seus itens numa transação: ou tudo é salvo,
     * ou nada é (evita pedido "fantasma" sem itens em caso de falha
     * no meio do processo).
     *
     * @param array $dadosEntrega  nome, endereco, numero, complemento,
     *                             bairro, cidade, estado, cep, forma_pagamento
     * @param array $itensCarrinho o array bruto de $_SESSION['carrinho']
     * @param float $frete
     * @return int|false ID do pedido criado, ou false em caso de erro
     */
    public function createOrder(int $usuarioId, array $dadosEntrega, array $itensCarrinho, float $frete = 0) {
        if (empty($itensCarrinho)) {
            return false;
        }

        $subtotal = 0;
        foreach ($itensCarrinho as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }
        $total = $subtotal + $frete;

        $this->conn->begin_transaction();

        try {
            $sqlPedido = "INSERT INTO pedidos
                (usuario_id, nome_destinatario, endereco, numero, complemento, bairro, cidade, estado, cep, forma_pagamento, subtotal, frete, total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($sqlPedido);
            if (!$stmt) throw new Exception($this->conn->error);

            $stmt->bind_param(
                "isssssssssddd",
                $usuarioId,
                $dadosEntrega['nome'],
                $dadosEntrega['endereco'],
                $dadosEntrega['numero'],
                $dadosEntrega['complemento'],
                $dadosEntrega['bairro'],
                $dadosEntrega['cidade'],
                $dadosEntrega['estado'],
                $dadosEntrega['cep'],
                $dadosEntrega['forma_pagamento'],
                $subtotal,
                $frete,
                $total
            );

            if (!$stmt->execute()) throw new Exception($stmt->error);

            $pedidoId = $this->conn->insert_id;

            $sqlItem = "INSERT INTO pedido_itens (pedido_id, produto_id, nome_produto, imagem_produto, preco_unitario, quantidade)
                        VALUES (?, ?, ?, ?, ?, ?)";
            $stmtItem = $this->conn->prepare($sqlItem);
            if (!$stmtItem) throw new Exception($this->conn->error);

            foreach ($itensCarrinho as $item) {
                // pedido_id(i) produto_id(i) nome(s) imagem(s) preco(d) quantidade(i)
                $stmtItem->bind_param(
                    "iissdi",
                    $pedidoId,
                    $item['id'],
                    $item['nome'],
                    $item['imagem'],
                    $item['preco'],
                    $item['quantidade']
                );

                if (!$stmtItem->execute()) throw new Exception($stmtItem->error);
            }

            $this->conn->commit();
            return $pedidoId;

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log('Falha ao criar pedido: ' . $e->getMessage());
            return false;
        }
    }

    /** Busca um pedido garantindo que pertence ao usuário logado */
    public function getOrderById(int $pedidoId, int $usuarioId) {
        $stmt = $this->conn->prepare("SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $pedidoId, $usuarioId);
        $stmt->execute();
        $pedido = $stmt->get_result()->fetch_assoc();

        if (!$pedido) return null;

        $stmtItens = $this->conn->prepare("SELECT * FROM pedido_itens WHERE pedido_id = ?");
        $stmtItens->bind_param("i", $pedidoId);
        $stmtItens->execute();
        $pedido['itens'] = $stmtItens->get_result()->fetch_all(MYSQLI_ASSOC);

        return $pedido;
    }

    /** Lista os pedidos de um usuário, mais recentes primeiro */
    public function getOrdersByUser(int $usuarioId) {
        $stmt = $this->conn->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY data_criacao DESC");
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>