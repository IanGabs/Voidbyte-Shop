<?php
require_once __DIR__ . '/../config/Database.php';

class ProductModel {
    private $conn;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // CREATE
    public function createProduct($nome, $descricao, $preco, $imagem, $categoria, $desconto = 0) {
        $stmt = $this->conn->prepare("INSERT INTO produtos (nome, descricao, preco, imagem, categoria, desconto) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssd", $nome, $descricao, $preco, $imagem, $categoria, $desconto);
        return $stmt->execute();
    }

    // READ (Todos)
    public function getAllProducts() {
        $stmt = $this->conn->prepare("SELECT * FROM produtos ORDER BY id DESC");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // READ (Único)
    public function getProductById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // UPDATE
    public function updateProduct($id, $nome, $descricao, $preco, $categoria, $desconto = 0) {
        $stmt = $this->conn->prepare("UPDATE produtos SET nome=?, descricao=?, preco=?, categoria=?, desconto=? WHERE id=?");
        $stmt->bind_param("ssdssi", $nome, $descricao, $preco, $categoria, $desconto, $id);
        return $stmt->execute();
    }

    // DELETE
    public function deleteProduct($id) {
        $stmt = $this->conn->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>