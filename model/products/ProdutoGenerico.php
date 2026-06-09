<?php
require_once __DIR__ . '/ProdutoItem.php';

class ProdutoGenerico extends ProdutoItem {
    private $categoriaNome;

    public function __construct($nome, $preco, $descricao, $categoriaNome) {
        parent::__construct($nome, $preco, $descricao);
        $this->categoriaNome = $categoriaNome;
    }

    public function getCategoryName() {
        return $this->categoriaNome;
    }
}
?>