<?php include __DIR__ . '/layout/header.php'; ?>

<main class="container" style="padding: 4rem 2rem; min-height: 80vh;">
    <div class="form-header">
        <h1><i class="fas fa-tools"></i> Montador de Setup</h1>
        <p>Escolha as peças e o sistema verifica compatibilidade de socket, memória, energia e formato em tempo real.</p>
    </div>

    <div class="setup-grid">

        <!-- ===================== COLUNA DE SELEÇÃO ===================== -->
        <div class="setup-slots">
            <?php foreach($slots as $key => $slot): ?>
                <div class="setup-slot" data-slot="<?php echo $key; ?>">
                    <label>
                        <i class="fas <?php echo $slot['icon']; ?>"></i>
                        <?php echo $slot['label']; ?>
                        <?php if ($slot['obrigatorio']): ?>
                            <span class="req-mark" title="Obrigatório">*</span>
                        <?php else: ?>
                            <span class="opt-mark">opcional</span>
                        <?php endif; ?>
                    </label>

                    <select id="slot-<?php echo $key; ?>" class="input-modern setup-select" data-slot="<?php echo $key; ?>">
                        <option value="">— Nenhum selecionado —</option>
                        <?php foreach($produtosPorSlot[$key] as $p): ?>
                            <option value="<?php echo $p['id']; ?>">
                                <?php echo htmlspecialchars($p['nome']); ?> — R$ <?php echo number_format($p['preco_final'], 2, ',', '.'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php if (empty($produtosPorSlot[$key])): ?>
                        <small class="setup-empty-cat">Nenhum produto cadastrado nesta categoria ainda.</small>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ===================== PAINEL LATERAL ===================== -->
        <aside class="setup-summary">
            <div class="setup-summary-box">
                <h3><i class="fas fa-receipt"></i> Resumo do Setup</h3>

                <div id="setup-itens-lista" class="setup-itens-lista">
                    <p class="setup-vazio-msg">Nenhuma peça selecionada ainda.</p>
                </div>

                <div class="setup-total-row">
                    <span>Total</span>
                    <strong id="setup-total">R$ 0,00</strong>
                </div>

                <button type="button" id="btn-add-carrinho-setup" class="btn-primary" style="width: 100%; margin-top: 1rem;" disabled>
                    <i class="fas fa-cart-plus"></i> Adicionar tudo ao carrinho
                </button>
            </div>

            <div class="setup-compat-box">
                <h3><i class="fas fa-shield-alt"></i> Diagnóstico de Compatibilidade</h3>
                <div id="setup-status-geral" class="setup-status-geral status-neutro">
                    <i class="fas fa-info-circle"></i> Selecione as peças para iniciar a análise.
                </div>
                <div id="setup-checagens" class="setup-checagens"></div>
            </div>
        </aside>
    </div>
</main>

<!-- Dados dos produtos embutidos para a validação rodar sem round-trip ao servidor -->
<script>
    window.SETUP_DATA = <?php echo json_encode($produtosPorSlot, JSON_UNESCAPED_UNICODE); ?>;
    window.SETUP_SLOTS = <?php echo json_encode($slots, JSON_UNESCAPED_UNICODE); ?>;
</script>
<script src="assets/js/setup-builder.js"></script>

<?php include __DIR__ . '/layout/footer.php'; ?>