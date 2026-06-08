<?php
require_once 'HardwareFactory.php';
require_once __DIR__ . '/../products/Mouse.php';

class MouseFactory extends HardwareFactory {
    public function createProduct($nome, $preco, $descricao) {
        return new Mouse($nome, $preco, $descricao);
    }
}
?>