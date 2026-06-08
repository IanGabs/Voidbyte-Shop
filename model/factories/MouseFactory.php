<?php
require_once 'ProdutoFactory.php';
require_once __DIR__ . '/../products/Mouse.php';

class MouseFactory extends ProdutoFactory {
    public function createProduct($nome, $preco, $descricao) {
        return new Mouse($nome, $preco, $descricao);
    }
}
?>