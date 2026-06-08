<?php
abstract class HardwareItem {
    protected $nome;
    protected $preco;
    protected $descricao;

    public function __construct($nome, $preco, $descricao) {
        $this->nome = $nome;
        $this->preco = $preco;
        $this->descricao = $descricao;
    }

    // Método obrigatório para definir aonde o produto pertence
    abstract public function getCategoryName();

    public function getDetails() {
        return "Hardware: {$this->nome} | Preço: R$ {$this->preco} | Categoria: " . $this->getCategoryName();
    }
}
?>