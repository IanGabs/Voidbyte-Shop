<?php include __DIR__ . '/layout/header.php'; ?>

<main class="container" style="padding: 4rem 2rem; min-height: 80vh;">
    <div class="form-header">
        <h1><i class="fas fa-check-circle"></i> Finalizar Compra</h1>
        <p>Confirme os dados de entrega e escolha a forma de pagamento.</p>
    </div>

    <?php if (isset($_SESSION['checkout_erro'])): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['checkout_erro']; unset($_SESSION['checkout_erro']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="checkout.php" id="form-checkout" class="checkout-grid">
        <input type="hidden" name="action" value="finalizar">

        <!-- ===================== COLUNA ESQUERDA: FORM ===================== -->
        <div class="checkout-form-col">

            <div class="form-section">
                <h3><i class="fas fa-truck"></i> Endereço de Entrega</h3>

                <div class="form-group full-width">
                    <label>Nome do destinatário</label>
                    <input type="text" name="nome" class="input-modern"
                           value="<?php echo htmlspecialchars($nomeUsuario); ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 3;">
                        <label>Endereço</label>
                        <input type="text" name="endereco" class="input-modern" placeholder="Rua, Avenida..." required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Número</label>
                        <input type="text" name="numero" class="input-modern" required>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Complemento <small style="color: var(--text-dim);">opcional</small></label>
                    <input type="text" name="complemento" class="input-modern" placeholder="Apto, bloco, referência...">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Bairro</label>
                        <input type="text" name="bairro" class="input-modern" required>
                    </div>
                    <div class="form-group">
                        <label>Cidade</label>
                        <input type="text" name="cidade" class="input-modern" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label>UF</label>
                        <input type="text" name="estado" class="input-modern" maxlength="2" placeholder="SP" style="text-transform: uppercase;" required>
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label>CEP</label>
                        <input type="text" name="cep" id="campo-cep" class="input-modern" placeholder="00000-000" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-credit-card"></i> Forma de Pagamento</h3>

                <div class="pagamento-opcoes">
                    <label class="pagamento-opcao">
                        <input type="radio" name="forma_pagamento" value="cartao" checked>
                        <span><i class="fas fa-credit-card"></i> Cartão de Crédito</span>
                    </label>
                    <label class="pagamento-opcao">
                        <input type="radio" name="forma_pagamento" value="pix">
                        <span><i class="fas fa-qrcode"></i> Pix</span>
                    </label>
                    <label class="pagamento-opcao">
                        <input type="radio" name="forma_pagamento" value="boleto">
                        <span><i class="fas fa-barcode"></i> Boleto</span>
                    </label>
                </div>

                <!-- Campos do cartão (mostrados só quando "cartao" está marcado) -->
                <div id="campos-cartao" class="campos-cartao">
                    <div class="form-group full-width">
                        <label>Número do cartão</label>
                        <input type="text" name="cartao_numero" id="cartao_numero" class="input-modern" placeholder="0000 0000 0000 0000" maxlength="19">
                    </div>
                    <div class="form-group full-width">
                        <label>Nome impresso no cartão</label>
                        <input type="text" name="cartao_nome" class="input-modern" placeholder="Como está no cartão">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Validade</label>
                            <input type="text" name="cartao_validade" id="cartao_validade" class="input-modern" placeholder="MM/AA" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="text" name="cartao_cvv" class="input-modern" placeholder="123" maxlength="4">
                        </div>
                    </div>
                </div>

                <!-- Aviso do Pix -->
                <div id="aviso-pix" class="pagamento-aviso" style="display:none;">
                    <i class="fas fa-info-circle"></i> O QR Code do Pix será exibido na tela de confirmação após finalizar o pedido.
                </div>

                <!-- Aviso do Boleto -->
                <div id="aviso-boleto" class="pagamento-aviso" style="display:none;">
                    <i class="fas fa-info-circle"></i> O boleto vence em 3 dias úteis e será gerado na tela de confirmação.
                </div>

                <p class="checkout-simulacao-nota">
                    <i class="fas fa-flask"></i> Ambiente de simulação — nenhum dado de pagamento real é processado ou armazenado.
                </p>
            </div>
        </div>

        <!-- ===================== COLUNA DIREITA: RESUMO ===================== -->
        <aside class="checkout-resumo">
            <div class="checkout-resumo-box">
                <h3><i class="fas fa-receipt"></i> Resumo do Pedido</h3>

                <div class="checkout-itens-lista">
                    <?php foreach($itensCarrinho as $item): ?>
                        <div class="checkout-item-linha">
                            <img src="<?php echo htmlspecialchars($item['imagem']); ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>">
                            <div class="checkout-item-info">
                                <span class="checkout-item-nome"><?php echo htmlspecialchars($item['nome']); ?></span>
                                <span class="checkout-item-qtd">Qtd: <?php echo $item['quantidade']; ?></span>
                            </div>
                            <span class="checkout-item-preco">
                                R$ <?php echo number_format($item['preco'] * $item['quantidade'], 2, ',', '.'); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--border); margin: 1rem 0;">

                <div class="checkout-linha-total">
                    <span>Subtotal</span>
                    <span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                </div>
                <div class="checkout-linha-total">
                    <span>Frete</span>
                    <span style="<?php echo $frete == 0 ? 'color: var(--green);' : ''; ?>">
                        <?php echo $frete == 0 ? 'Grátis' : 'R$ ' . number_format($frete, 2, ',', '.'); ?>
                    </span>
                </div>

                <?php if ($frete > 0): ?>
                    <p class="checkout-frete-dica">
                        <i class="fas fa-truck"></i>
                        Faltam R$ <?php echo number_format(CheckoutController::FRETE_GRATIS_A_PARTIR_DE - $subtotal, 2, ',', '.'); ?>
                        para o frete grátis.
                    </p>
                <?php endif; ?>

                <div class="checkout-total-final">
                    <span>Total</span>
                    <strong>R$ <?php echo number_format($total, 2, ',', '.'); ?></strong>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1.2rem;">
                    <i class="fas fa-lock"></i> Confirmar Pedido
                </button>

                <a href="carrinho.php" class="btn-ghost" style="display: block; text-align: center; margin-top: 12px; padding: 10px; border-radius: var(--radius);">
                    Voltar ao carrinho
                </a>
            </div>
        </aside>
    </form>
</main>

<script>
    // Alterna os campos exibidos conforme a forma de pagamento escolhida
    document.querySelectorAll('input[name="forma_pagamento"]').forEach(radio => {
        radio.addEventListener('change', function () {
            document.getElementById('campos-cartao').style.display = (this.value === 'cartao') ? 'block' : 'none';
            document.getElementById('aviso-pix').style.display = (this.value === 'pix') ? 'block' : 'none';
            document.getElementById('aviso-boleto').style.display = (this.value === 'boleto') ? 'block' : 'none';

            document.querySelectorAll('#campos-cartao input').forEach(i => {
                i.required = (this.value === 'cartao');
            });
        });
    });

    // Máscara simples do número do cartão (0000 0000 0000 0000)
    const campoCartao = document.getElementById('cartao_numero');
    if (campoCartao) {
        campoCartao.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim().slice(0, 19);
        });
        document.querySelectorAll('#campos-cartao input').forEach(i => i.required = true);
    }

    // Máscara de validade MM/AA
    const campoValidade = document.getElementById('cartao_validade');
    if (campoValidade) {
        campoValidade.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').slice(0, 4);
            if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
            this.value = v;
        });
    }

    // Máscara de CEP
    const campoCep = document.getElementById('campo-cep');
    if (campoCep) {
        campoCep.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').slice(0, 8);
            if (v.length > 5) v = v.slice(0, 5) + '-' + v.slice(5);
            this.value = v;
        });
    }
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>