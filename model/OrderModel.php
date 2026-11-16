<?php
require_once __DIR__ . '/../config/Database.php';

class OrderModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Cria um novo pedido no banco de dados com transação e suporte a cupons
     */
    public function createOrder(int $usuarioId, array $dadosEntrega, array $itensCarrinho, float $frete = 0, float $descontoCupom = 0, ?string $cupomUsado = null) {
        if (empty($itensCarrinho)) { return false; }

        $subtotal = 0;
        foreach ($itensCarrinho as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }
        $total = max(0, ($subtotal - $descontoCupom) + $frete);

        $this->conn->begin_transaction();

        try {
            $sqlPedido = "INSERT INTO pedidos
                (usuario_id, nome_destinatario, endereco, numero, complemento, bairro, cidade, estado, cep, forma_pagamento, subtotal, frete, desconto_cupom, cupom_usado, total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($sqlPedido);
            if (!$stmt) throw new Exception($this->conn->error);

            $stmt->bind_param(
                "isssssssssdddsd",
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
                $descontoCupom,
                $cupomUsado,
                $total
            );

            if (!$stmt->execute()) throw new Exception($stmt->error);
            $pedidoId = $this->conn->insert_id;

            $sqlItem = "INSERT INTO pedido_itens (pedido_id, produto_id, nome_produto, imagem_produto, preco_unitario, quantidade)
                        VALUES (?, ?, ?, ?, ?, ?)";
            $stmtItem = $this->conn->prepare($sqlItem);
            if (!$stmtItem) throw new Exception($this->conn->error);

            foreach ($itensCarrinho as $item) {
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

    /**
     * Retorna todos os pedidos de um usuário específico
     */
    public function getOrdersByUserId(int $usuarioId): array {
        $stmt = $this->conn->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY data_criacao DESC");
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $row['itens'] = $this->getOrderItems($row['id']);
            $pedidos[] = $row;
        }
        return $pedidos;
    }

    /**
     * Retorna os itens vinculados a um pedido
     */
    public function getOrderItems(int $pedidoId): array {
        $stmt = $this->conn->prepare("SELECT * FROM pedido_itens WHERE pedido_id = ?");
        $stmt->bind_param("i", $pedidoId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $itens = [];
        while ($row = $result->fetch_assoc()) {
            $itens[] = $row;
        }
        return $itens;
    }

    /**
     * Retorna um pedido específico pelo ID (garantindo que pertence ao usuário)
     */
    public function getOrderById(int $pedidoId, int $usuarioId) {
        $stmt = $this->conn->prepare("SELECT * FROM pedidos WHERE id = ? AND usuario_id = ? LIMIT 1");
        $stmt->bind_param("ii", $pedidoId, $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) return null;
        
        $pedido = $result->fetch_assoc();
        $pedido['itens'] = $this->getOrderItems($pedidoId);
        return $pedido;
    }
}