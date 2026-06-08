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
        
        // Retorna os dados do usuário ou false se não encontrar
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }

    // Método NOVO: Cria um novo usuário no banco de dados
    public function createUser($nome, $email, $senha_hash, $tipo = 'cliente') {
        // Usa uma foto de perfil padrão
        $foto_padrao = 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
        
        $stmt = $this->conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo, foto) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nome, $email, $senha_hash, $tipo, $foto_padrao);
        
        return $stmt->execute();
    }
}
?>