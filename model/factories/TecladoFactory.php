<?php
require_once 'ProdutoFactory.php';
require_once __DIR__ . '/../products/Teclado.php';

class TecladoFactory extends ProdutoFactory {
    public function createProduct($nome, $preco, $descricao) {
        return new Teclado($nome, $preco, $descricao);
    }
}
?>