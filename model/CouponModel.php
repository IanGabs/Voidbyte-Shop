<?php
require_once __DIR__ . '/../config/Database.php';

class CouponModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getByCode($codigo) {
        $stmt = $this->conn->prepare("SELECT * FROM cupons WHERE codigo = ? AND ativo = 1 LIMIT 1");
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }
}
?>