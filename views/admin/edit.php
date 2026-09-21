<?php include __DIR__ . '/../layout/header.php'; ?>

<main class="admin-container">
    <div class="admin-content-wrapper">
        <div class="form-header">
            <h1><i class="fas fa-edit"></i> Editar Hardware</h1>
            <p>Alterando o registro #<?php echo $produto['id']; ?> — <?php echo htmlspecialchars($produto['nome']); ?></p>
        </div>

        <a href="admin.php" class="btn-secondary btn-sm" style="margin-bottom: 2rem; display: inline-flex;">
            <i class="fas fa-arrow-left"></i> Voltar ao inventário
        </a>

        <div class="form-section">
            <form method="POST" action="admin.php" class="modern-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo $produto['id']; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label>Categoria</label>
                        <input type="text" name="categoria" class="input-modern" list="lista-categorias"
                               value="<?php echo htmlspecialchars($produto['categoria']); ?>" required>
                        <datalist id="lista-categorias">
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label>Nome do Hardware</label>
                        <input type="text" name="nome" class="input-modern"
                               value="<?php echo htmlspecialchars($produto['nome']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Preço (R$)</label>
                        <input type="number" step="0.01" name="preco" class="input-modern"
                               value="<?php echo $produto['preco']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Desconto (%)</label>
                        <input type="number" name="desconto" class="input-modern"
                               value="<?php echo intval($produto['desconto']); ?>">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Imagem do Produto</label>
                    <div style="display: flex; gap: 1.5rem; align-items: flex-start; flex-wrap: wrap;">
                        <div style="text-align: center;">
                            <small style="color: var(--text-dim);">Atual</small>
                            <img src="<?php echo htmlspecialchars($produto['imagem']); ?>" class="img-preview" style="display:block; margin-top:6px;">
                        </div>
                        <div style="flex: 1; min-width: 240px;">
                            <input type="file" name="imagem" accept="image/*" class="input-modern input-file"
                                   onchange="previewImagem(this, 'preview-novo')">
                            <small style="color: var(--text-dim);">Deixe em branco para manter a imagem atual.</small>
                            <img id="preview-novo" class="img-preview" style="display:none; margin-top:10px;">
                        </div>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Descrição</label>
                    <textarea name="descricao" class="input-modern" required><?php echo htmlspecialchars($produto['descricao']); ?></textarea>
                </div>

                <!-- ========= CONSTRUTOR DE ESPECIFICAÇÕES ========= -->
                <div class="form-group full-width">
                    <label>
                        Especificações Técnicas
                        <small style="color: var(--cyan);">usadas no comparador</small>
                    </label>

                    <div class="spec-head">
                        <span>Característica</span>
                        <span>Valor</span>
                        <span></span>
                    </div>

                    <div id="specs-container">
                        <?php foreach($specs as $chave => $valor): ?>
                            <div class="spec-row">
                                <input type="text" name="spec_chave[]" class="input-modern" list="spec-sugestoes"
                                       value="<?php echo htmlspecialchars($chave); ?>">
                                <input type="text" name="spec_valor[]" class="input-modern"
                                       value="<?php echo htmlspecialchars($valor); ?>">
                                <button type="button" class="btn-danger btn-sm spec-remove" title="Remover">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <datalist id="spec-sugestoes"></datalist>

                    <button type="button" id="btn-add-spec" class="btn-secondary btn-sm" style="margin-top: 10px;">
                        <i class="fas fa-plus"></i> Adicionar campo
                    </button>
                </div>

                <div class="form-actions right">
                    <a href="admin.php" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
function previewImagem(input, alvoId) {
    const img = document.getElementById(alvoId);
    if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        img.style.display = 'block';
    }
}
</script>
<script src="assets/js/admin-specs.js"></script>

<?php include __DIR__ . '/../layout/footer.php'; ?>