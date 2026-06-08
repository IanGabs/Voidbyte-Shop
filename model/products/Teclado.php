<?php
require_once 'ProdutoItem.php';

class Teclado extends ProdutoItem {
    // Aqui você define exatamente aonde esse produto pertence!
    public function getCategoryName() {
        return "Teclados Mecânicos";
    }
}
?>