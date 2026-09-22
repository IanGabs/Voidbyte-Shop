<?php include __DIR__ . '/layout/header.php'; ?>

<main style="padding-top: 2rem; min-height: 80vh;">
    <div class="container">
        <div class="form-header" style="margin-bottom: 2rem; text-align: left;">
            <a href="produtos.php" style="color: var(--text-muted); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Voltar ao catálogo
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 3rem; margin-bottom: 5rem;">
            
            <!-- Coluna da Imagem -->
            <div style="background: #121215; padding: 2rem; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color, #2d2d35);">
                <img src="<?php echo htmlspecialchars($produto['imagem'] ?? ''); ?>" alt="<?php echo htmlspecialchars($produto['nome'] ?? ''); ?>" style="max-width: 100%; height: auto; border-radius: 8px;">
            </div>

            <!-- Coluna das Informações -->
            <div>
                <span class="produto-card-category" style="display: inline-block; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($produto['categoria'] ?? ''); ?>
                </span>
                
                <h1 style="margin-bottom: 1rem; font-size: 2.5rem;"><?php echo htmlspecialchars($produto['nome'] ?? ''); ?></h1>
                
                <?php 
                $precoBase = $produto['preco'] ?? 0;
                $desconto = $produto['desconto'] ?? 0;
                $precoFinal = $precoBase - ($precoBase * ($desconto / 100));
                ?>

                <div class="price-tag" style="margin-bottom: 2rem; justify-content: flex-start;">
                    <?php if ($desconto > 0): ?>
                        <span class="preco-antigo" style="font-size: 1.2rem;">R$ <?php echo number_format($precoBase, 2, ',', '.'); ?></span>
                    <?php endif; ?>
                    <div class="preco-final" style="font-size: 2.5rem;">R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?></div>
                </div>

                <p style="color: var(--text-muted); line-height: 1.6; margin-bottom: 2rem; font-size: 1.1rem;">
                    <?php echo nl2br(htmlspecialchars($produto['descricao'] ?? '')); ?>
                </p>

                <h3 style="border-bottom: 1px solid var(--border-color, #333); padding-bottom: 0.5rem; margin-bottom: 1rem;">Especificações Técnicas</h3>
                <ul style="list-style: none; padding: 0; margin-bottom: 3rem; color: var(--text-muted);">
                    <?php 
                    $specs = $produto['especificacoes'] ?? '';
                    $specsArray = json_decode($specs, true);
                    
                    if (is_array($specsArray) && !empty($specsArray)): 
                        foreach ($specsArray as $chave => $valor): ?>
                            <li style="padding: 0.8rem 0; border-bottom: 1px solid var(--border-color, #2d2d35);">
                                <strong style="color: #fff;"><?php echo htmlspecialchars(ucfirst($chave)); ?>:</strong> <?php echo htmlspecialchars($valor); ?>
                            </li>
                        <?php endforeach; 
                    elseif (!empty($specs)): ?>
                        <li style="padding: 0.8rem 0; border-bottom: 1px solid var(--border-color, #2d2d35);"><?php echo nl2br(htmlspecialchars($specs)); ?></li>
                    <?php else: ?>
                        <li style="padding: 0.8rem 0;">Nenhuma especificação técnica cadastrada.</li>
                    <?php endif; ?>
                </ul>

                <!-- Formulário Corrigido (Exatamente igual ao do seu catálogo) -->
                <form method="POST" action="carrinho.php" style="width: 100%;">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                    <input type="hidden" name="quantidade" value="1">
                    <button type="submit" class="btn-adicionar-carrinho" style="width: 100%; padding: 1.2rem; font-size: 1.2rem; display: flex; justify-content: center; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-cart-plus"></i> Adicionar ao Carrinho
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>