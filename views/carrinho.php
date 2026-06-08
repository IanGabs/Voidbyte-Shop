<?php include __DIR__ . '/layout/header.php'; ?>

<main class="carrinho">
    <h1 class="section-title">Seu <span>Carrinho</span></h1>

    <?php if (isset($_SESSION['toast_msg'])): ?>
        <div class="notificacao">
            <i class="fas fa-check-circle" style="color: var(--cyan);"></i> <?php echo $_SESSION['toast_msg']; unset($_SESSION['toast_msg']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($itensCarrinho)): ?>
        <div class="carrinho-vazio">
            <i class="fas fa-ghost" style="font-size: 4rem; color: var(--purple-dim); margin-bottom: 1rem;"></i>
            <h2>O vácuo está presente aqui.</h2>
            <p>Nenhum produto foi adicionado ao seu setup ainda.</p>
            <a href="produtos.php" class="btn-primary" style="margin-top: 1rem;"><i class="fas fa-search"></i> Explorar Produtos</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 2rem; align-items: start;">
            
            <div class="itens-carrinho">
                <?php foreach($itensCarrinho as $item): ?>
                    <div class="item-carrinho" id="item-carrinho-<?php echo $item['id']; ?>">
                        <img src="<?php echo htmlspecialchars($item['imagem']); ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>">
                        
                        <div class="item-detalhes">
                            <span class="badge-categoria"><?php echo htmlspecialchars($item['categoria']); ?></span>
                            <h3 style="margin-top: 8px;"><?php echo htmlspecialchars($item['nome']); ?></h3>
                            <p>Valor unitário: R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></p>
                            
                            <div class="quantidade">
                                <form method="POST" action="carrinho.php" style="display: inline;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="produto_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="operacao" value="diminuir">
                                    <button type="submit"><i class="fas fa-minus"></i></button>
                                </form>
                                
                                <span id="qtd-<?php echo $item['id']; ?>"><?php echo $item['quantidade']; ?></span>
                                
                                <form method="POST" action="carrinho.php" style="display: inline;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="produto_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="operacao" value="aumentar">
                                    <button type="submit"><i class="fas fa-plus"></i></button>
                                </form>
                            </div>
                        </div>
                        
                        <div style="text-align: right; display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                            <div id="item-total-<?php echo $item['id']; ?>" style="font-family: 'Orbitron', sans-serif; font-size: 1.2rem; font-weight: 700; color: var(--cyan); margin-bottom: 10px;">
                                R$ <?php echo number_format($item['preco'] * $item['quantidade'], 2, ',', '.'); ?>
                            </div>
                            
                            <form method="POST" action="carrinho.php">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="produto_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn-remover"><i class="fas fa-trash"></i> Remover</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="resumo-carrinho">
                <h2>Resumo do Protocolo</h2>
                <hr style="border: 0; border-top: 1px solid var(--border); margin: 15px 0;">
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px; color: var(--text-muted);">
                    <span>Subtotal:</span>
                    <span class="cart-subtotal-val">R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 20px; color: var(--text-muted);">
                    <span>Frete (Conexão Neural):</span>
                    <span style="color: var(--green);">Grátis</span>
                </div>
                
                <hr style="border: 0; border-top: 1px solid var(--border); margin: 15px 0;">
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <span style="font-weight: 600; font-size: 1.1rem;">Total Final:</span>
                    <span class="total-price">R$ <?php echo number_format($total, 2, ',', '.'); ?></span>
                </div>
                
                <a href="checkout.php" class="btn-add-cart-large" style="text-decoration: none;">
                    <i class="fas fa-check-circle"></i> Finalizar Compra
                </a>
                
                <a href="produtos.php" class="btn-ghost" style="display: block; text-align: center; margin-top: 15px; padding: 12px; border-radius: var(--radius);">
                    Continuar Explorando
                </a>
            </div>
            
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>