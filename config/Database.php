<?php
class Database {
    // Instância única da classe (Singleton)
    private static $instance = null;
    private $conn;

    // Configurações do servidor local
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'voidbyte_shop';

    // O construtor é privado para impedir que a classe seja instanciada com 'new Database()' fora daqui
    private function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);

        // Verifica se houve falha na conexão do terminal MySQL
        if ($this->conn->connect_error) {
            die("Database Connection Failure: " . $this->conn->connect_error);
        }

        // Define o charset para evitar problemas com acentuação no banco
        $this->conn->set_charset("utf8mb4");
    }

    // Método estático para obter a instância única
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Retorna a conexão ativa do MySQLi
    public function getConnection() {
        return $this->conn;
    }

    // Impede a clonagem da instância
    private function __clone() {}

    // Impede a desserialização da instância
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton class.");
    }
}
?>