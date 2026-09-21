<?php include __DIR__ . '/layout/header.php'; ?>

<main class="container" style="padding: 4rem 2rem; min-height: 80vh;">

    <div class="pedido-sucesso-header">
        <i class="fas fa-check-circle"></i>
        <h1>Pedido Confirmado!</h1>
        <p>Protocolo #<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?> registrado com sucesso no sistema.</p>
    </div>

    <?php if ($pedido['forma_pagamento'] === 'pix'): ?>
        <div class="pagamento-simulado-box">
            <i class="fas fa-qrcode" style="font-size: 3rem; color: var(--cyan);"></i>
            <h3>Pague com Pix</h3>
            <p>Escaneie o QR Code (simulado) ou copie o código abaixo para concluir o pagamento.</p>
            <code class="pix-codigo-simulado">00020126voidbyte-shop-pix-simulado<?php echo $pedido['id']; ?>5204000053039865802BR</code>
        </div>
    <?php elseif ($pedido['forma_pagamento'] === 'boleto'): ?>
        <div class="pagamento-simulado-box">
            <i class="fas fa-barcode" style="font-size: 3rem; color: var(--orange);"></i>
            <h3>Boleto Gerado</h3>
            <p>Vencimento em 3 dias úteis. Código de barras (simulado):</p>
            <code class="pix-codigo-simulado">34191.79001 01043.510047 91020.150008 <?php echo str_pad($pedido['id'], 1, '0'); ?> 84770026000</code>
        </div>
    <?php endif; ?>

    <div class="pedido-detalhe-grid">
        <div class="form-section">
            <h3><i class="fas fa-box-open"></i> Itens do Pedido</h3>

            <div class="checkout-itens-lista">
                <?php foreach($pedido['itens'] as $item): ?>
                    <div class="checkout-item-linha">
                        <img src="<?php echo htmlspecialchars($item['imagem_produto']); ?>" alt="<?php echo htmlspecialchars($item['nome_produto']); ?>">
                        <div class="checkout-item-info">
                            <span class="checkout-item-nome"><?php echo htmlspecialchars($item['nome_produto']); ?></span>
                            <span class="checkout-item-qtd">Qtd: <?php echo $item['quantidade']; ?> × R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></span>
                        </div>
                        <span class="checkout-item-preco">
                            R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border); margin: 1rem 0;">

            <div class="checkout-linha-total">
                <span>Subtotal</span>
                <span>R$ <?php echo number_format($pedido['subtotal'], 2, ',', '.'); ?></span>
            </div>
            <div class="checkout-linha-total">
                <span>Frete</span>
                <span><?php echo $pedido['frete'] == 0 ? 'Grátis' : 'R$ ' . number_format($pedido['frete'], 2, ',', '.'); ?></span>
            </div>
            <div class="checkout-total-final">
                <span>Total pago</span>
                <strong>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></strong>
            </div>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-truck"></i> Entrega</h3>
            <p class="endereco-texto">
                <?php echo htmlspecialchars($pedido['nome_destinatario']); ?><br>
                <?php echo htmlspecialchars($pedido['endereco']); ?>, <?php echo htmlspecialchars($pedido['numero']); ?>
                <?php if ($pedido['complemento']) echo ' — ' . htmlspecialchars($pedido['complemento']); ?><br>
                <?php echo htmlspecialchars($pedido['bairro']); ?> — <?php echo htmlspecialchars($pedido['cidade']); ?>/<?php echo htmlspecialchars($pedido['estado']); ?><br>
                CEP: <?php echo htmlspecialchars($pedido['cep']); ?>
            </p>

            <hr style="border: 0; border-top: 1px solid var(--border); margin: 1rem 0;">

            <h4 style="margin-bottom: .5rem; color: var(--text-muted); font-size: .85rem; text-transform: uppercase;">Pagamento</h4>
            <p style="color: var(--cyan-light); font-family: 'Orbitron', sans-serif;">
                <?php
                    $rotulos = ['cartao' => 'Cartão de Crédito', 'pix' => 'Pix', 'boleto' => 'Boleto'];
                    echo $rotulos[$pedido['forma_pagamento']] ?? $pedido['forma_pagamento'];
                ?>
            </p>

            <h4 style="margin: 1rem 0 .5rem; color: var(--text-muted); font-size: .85rem; text-transform: uppercase;">Status</h4>
            <span class="badge-status-pedido"><?php echo ucfirst($pedido['status']); ?></span>
        </div>
    </div>

    <div style="text-align: center; margin-top: 2.5rem;">
        <a href="produtos.php" class="btn-primary"><i class="fas fa-search"></i> Continuar Explorando</a>
    </div>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>