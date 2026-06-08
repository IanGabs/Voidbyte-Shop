<?php
abstract class ProdutoItem {
    protected $nome;
    protected $preco;
    protected $descricao;

    public function __construct($nome, $preco, $descricao) {
        $this->nome = $nome;
        $this->preco = $preco;
        $this->descricao = $descricao;
    }

    // Define a qual categoria o produto pertence
    abstract public function getCategoryName();

    public function getDetails() {
        return "Produto: {$this->nome} | Preço: R$ {$this->preco} | Categoria: " . $this->getCategoryName();
    }
}
?>