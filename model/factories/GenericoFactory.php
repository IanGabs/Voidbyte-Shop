<?php
require_once __DIR__ . '/ProdutoFactory.php';
require_once __DIR__ . '/../products/ProdutoGenerico.php';

class GenericoFactory extends ProdutoFactory {
    private $categoria;

    public function __construct($categoria) {
        $this->categoria = $categoria;
    }

    public function createProduct($nome, $preco, $descricao) {
        // Cria o produto passando a categoria dinâmica escolhida no painel
        return new ProdutoGenerico($nome, $preco, $descricao, $this->categoria);
    }
}
?>