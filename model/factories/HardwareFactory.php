<?php
abstract class HardwareFactory {
    abstract public function createProduct($nome, $preco, $descricao);
    
    public function registerLog($nome) {
        return "System Log: O hardware '{$nome}' foi forjado no Voidbyte.";
    }
}
?>