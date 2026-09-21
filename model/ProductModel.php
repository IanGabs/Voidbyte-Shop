<?php
require_once __DIR__ . '/../config/Database.php';

class ProductModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // CREATE
    public function createProduct($nome, $descricao, $preco, $imagem, $categoria, $desconto = 0, $especificacoes = null) {
        $sql = "INSERT INTO produtos (nome, descricao, preco, imagem, categoria, desconto, especificacoes)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die("<b>ERRO SQL (Prepare Insert):</b> " . $this->conn->error);
        }

        $stmt->bind_param("ssdssds", $nome, $descricao, $preco, $imagem, $categoria, $desconto, $especificacoes);

        if (!$stmt->execute()) {
            die("<b>ERRO SQL (Execute Insert):</b> " . $stmt->error);
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

    // READ (vários de uma vez, usado pelo comparador)
    public function getProductsByIds(array $ids) {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) return [];

        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        $tipos = str_repeat('i', count($ids));

        $stmt = $this->conn->prepare("SELECT * FROM produtos WHERE id IN ($marcadores)");
        $stmt->bind_param($tipos, ...$ids);
        $stmt->execute();
        $linhas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Devolve na mesma ordem em que os ids foram pedidos
        $porId = [];
        foreach ($linhas as $l) { $porId[$l['id']] = $l; }

        $ordenado = [];
        foreach ($ids as $id) {
            if (isset($porId[$id])) $ordenado[] = $porId[$id];
        }
        return $ordenado;
    }

    /**
     * UPDATE
     * Se $imagem vier null, a imagem atual do produto é mantida.
     */
    public function updateProduct($id, $nome, $descricao, $preco, $categoria, $desconto = 0, $especificacoes = null, $imagem = null) {

        if ($imagem !== null) {
            $sql = "UPDATE produtos
                    SET nome=?, descricao=?, preco=?, categoria=?, desconto=?, especificacoes=?, imagem=?
                    WHERE id=?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) { die("<b>ERRO SQL (Prepare Update):</b> " . $this->conn->error); }

            // nome s | descricao s | preco d | categoria s | desconto d | espec s | imagem s | id i
            $stmt->bind_param("ssdsdssi", $nome, $descricao, $preco, $categoria, $desconto, $especificacoes, $imagem, $id);

        } else {
            $sql = "UPDATE produtos
                    SET nome=?, descricao=?, preco=?, categoria=?, desconto=?, especificacoes=?
                    WHERE id=?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) { die("<b>ERRO SQL (Prepare Update):</b> " . $this->conn->error); }

            // nome s | descricao s | preco d | categoria s | desconto d | espec s | id i
            $stmt->bind_param("ssdsdsi", $nome, $descricao, $preco, $categoria, $desconto, $especificacoes, $id);
        }

        if (!$stmt->execute()) {
            die("<b>ERRO SQL (Execute Update):</b> " . $stmt->error);
        }
        return true;
    }

    // DELETE
    public function deleteProduct($id) {
        $stmt = $this->conn->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Lista as categorias distintas (usado nos filtros do comparador)
    public function getCategorias() {
        $res = $this->conn->query("SELECT DISTINCT categoria FROM produtos ORDER BY categoria ASC");
        $cats = [];
        while ($linha = $res->fetch_assoc()) { $cats[] = $linha['categoria']; }
        return $cats;
    }
}
?>