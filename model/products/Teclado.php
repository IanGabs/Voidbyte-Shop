<?php
require_once 'HardwareItem.php';

class Teclado extends HardwareItem {
    // Aqui você define exatamente aonde esse produto pertence!
    public function getCategoryName() {
        return "Teclados Mecânicos";
    }
}
?>