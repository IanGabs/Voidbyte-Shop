<?php
require_once __DIR__ . '/../config/Database.php';

class CommentModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // READ (todos os comentários de um produto, mais recentes primeiro)
    public function getByProduct($produtoId) {
        try {
            $sql = "SELECT c.id, c.produto_id, c.usuario_id, c.texto, c.data_criacao, u.nome, u.foto
                    FROM comentarios c
                    INNER JOIN usuarios u ON u.id = c.usuario_id
                    WHERE c.produto_id = ?
                    ORDER BY c.data_criacao DESC, c.id DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $produtoId);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $e) {
            // Tabela ainda não importada: a página do produto continua abrindo, só sem comentários
            return [];
        }
    }

    // READ (único)
    public function getById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM comentarios WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (mysqli_sql_exception $e) {
            return null;
        }
    }

    // CREATE
    public function create($produtoId, $usuarioId, $texto) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO comentarios (produto_id, usuario_id, texto) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $produtoId, $usuarioId, $texto);
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    // DELETE
    public function delete($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM comentarios WHERE id = ?");
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}
?>