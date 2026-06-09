<?php
require_once __DIR__ . '/../config/Database.php';

class ProductModel {
    private $conn;
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // CREATE
    public function createProduct($nome, $descricao, $preco, $imagem, $categoria, $desconto = 0, $especificacoes = null) {
        $sql = "INSERT INTO produtos (nome, descricao, preco, imagem, categoria, desconto, especificacoes) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        // Se a coluna não existir ou houver erro de sintaxe no SQL, o erro rebenta aqui:
        if (!$stmt) {
            die("<div style='background:#222; color:#ff4444; padding:20px; border-left: 4px solid #ff4444; font-family:monospace; font-size:16px;'><b>ERRO SQL (Prepare):</b> " . $this->conn->error . "<br><br>Verifique se executou o comando ALTER TABLE no phpMyAdmin para adicionar a coluna 'especificacoes'.</div>");
        }

        $stmt->bind_param("ssdssds", $nome, $descricao, $preco, $imagem, $categoria, $desconto, $especificacoes);
        
        // Se falhar na hora de inserir os dados (ex: JSON mal formatado a tentar entrar na BD):
        if (!$stmt->execute()) {
            die("<div style='background:#222; color:#ff4444; padding:20px; border-left: 4px solid #ff4444; font-family:monospace; font-size:16px;'><b>ERRO SQL (Execute):</b> " . $stmt->error . "</div>");
        }
        
        return true;
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
    public function updateProduct($id, $nome, $descricao, $preco, $categoria, $desconto = 0, $especificacoes = null) {
        $sql = "UPDATE produtos SET nome=?, descricao=?, preco=?, categoria=?, desconto=?, especificacoes=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            die("<div style='background:#222; color:#ff4444; padding:20px; border-left: 4px solid #ff4444; font-family:monospace; font-size:16px;'><b>ERRO SQL (Prepare Update):</b> " . $this->conn->error . "</div>");
        }

        $stmt->bind_param("ssdssdsi", $nome, $descricao, $preco, $categoria, $desconto, $especificacoes, $id);
        
        if (!$stmt->execute()) {
            die("<div style='background:#222; color:#ff4444; padding:20px; border-left: 4px solid #ff4444; font-family:monospace; font-size:16px;'><b>ERRO SQL (Execute Update):</b> " . $stmt->error . "</div>");
        }
        return true;
    }

    // DELETE
    public function deleteProduct($id) {
        $stmt = $this->conn->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>