<?php include __DIR__ . '/layout/header.php'; ?>

<main style="padding-top: 2rem; min-height: 80vh;">
    <div class="container">
        <div class="form-header">
            <h1><i class="fas fa-server"></i> Catálogo de Produtos</h1>
            <p style="color: var(--text-muted);">Explore nosso inventário completo e equipe seu setup com tecnologia de ponta.</p>
        </div>

        <div class="produtos-grid" style="margin-top: 3rem; margin-bottom: 5rem;">
            <?php if (empty($produtos)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 0; color: var(--text-muted);">
                    <i class="fas fa-box-open" style="font-size: 3rem; color: var(--purple-dim); margin-bottom: 1rem;"></i>
                    <h2>O Vazio está literalmente vazio.</h2>
                    <p>Nenhum produto foi cadastrado no sistema ainda.</p>
                </div>
            <?php else: ?>
                <?php foreach($produtos as $produto): ?>
                    <div class="produto-card">
                        <?php 
                        // Calcula o desconto dinamicamente
                        $precoBase = $produto['preco'];
                        $desconto = $produto['desconto'] ?? 0;
                        $precoFinal = $precoBase - ($precoBase * ($desconto / 100));
                        ?>

                        <?php if ($desconto > 0): ?>
                            <div class="produto-card-badge"><?php echo intval($desconto); ?>% OFF</div>
                        <?php endif; ?>
                        
                        <a href="detalhes.php?id=<?php echo $produto['id']; ?>" class="produto-card-img-wrapper">
                            <img src="<?php echo htmlspecialchars($produto['imagem']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                        </a>
                        
                        <div class="produto-card-body">
                            <span class="produto-card-category"><?php echo htmlspecialchars($produto['categoria']); ?></span>
                            <h3><?php echo htmlspecialchars($produto['nome']); ?></h3>
                            
                            <div class="price-tag">
                                <?php if ($desconto > 0): ?>
                                    <span class="preco-antigo">R$ <?php echo number_format($precoBase, 2, ',', '.'); ?></span>
                                <?php endif; ?>
                                <div class="preco-final">R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?></div>
                            </div>
                            <form method="POST" action="carrinho.php" style="width: 100%; margin-top: auto;">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                                <input type="hidden" name="quantidade" value="1">
                                <button type="submit" class="btn-adicionar-carrinho">
                                    <i class="fas fa-cart-plus"></i> Adicionar
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>