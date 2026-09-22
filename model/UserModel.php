<?php
require_once __DIR__ . '/../config/Database.php';

class UserModel {
    private $conn;

    public function __construct() {
        // Inicializa a conexão com o banco de dados usando o Singleton
        $this->conn = Database::getInstance()->getConnection();
    }

    // Busca um usuário pelo e-mail para validar o login e evitar duplicidade no registro
    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return false;
    }

    // NOVO: busca um usuário pelo id (usado na página de perfil)
    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : false;
    }

    // Cria um novo usuário no banco de dados
    public function createUser($nome, $email, $senha_hash, $tipo = 'cliente') {
        $foto_padrao = 'https://cdn-icons-png.flaticon.com/512/149/149071.png';

        $stmt = $this->conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo, foto) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nome, $email, $senha_hash, $tipo, $foto_padrao);

        return $stmt->execute();
    }

    public function updateProfile($id, $nome, $email, $foto = null) {
        if ($foto !== null) {
            $sql = "UPDATE usuarios SET nome = ?, email = ?, foto = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssi", $nome, $email, $foto, $id);
        } else {
            $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssi", $nome, $email, $id);
        }

        return $stmt->execute();
    }

    // NOVO: atualiza somente a senha (já criptografada)
    public function updatePassword($id, $senha_hash) {
        $stmt = $this->conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        $stmt->bind_param("si", $senha_hash, $id);
        return $stmt->execute();
    }
}
?>