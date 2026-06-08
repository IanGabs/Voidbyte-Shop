<?php
require_once 'HardwareFactory.php';
require_once __DIR__ . '/../products/Teclado.php';

class TecladoFactory extends HardwareFactory {
    public function createProduct($nome, $preco, $descricao) {
        return new Teclado($nome, $preco, $descricao);
    }
}
?>