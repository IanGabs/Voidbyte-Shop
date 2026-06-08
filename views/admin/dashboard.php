<?php include __DIR__ . '/../layout/header.php'; ?>

<main class="admin-container">
    <div class="admin-content-wrapper">
        <div class="form-header">
            <h1><i class="fas fa-terminal"></i> Terminal Administrativo</h1>
            <p>Gerencie o inventário do Voidbyte Shop. Acesso Nível: System Admin.</p>
        </div>

        <?php if (isset($_SESSION['admin_msg'])): ?>
            <div class="success-message"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['admin_msg']; unset($_SESSION['admin_msg']); ?></div>
        <?php endif; ?>

        <div class="form-section" style="margin-bottom: 3rem;">
            <h3><i class="fas fa-plus"></i> Adicionar Novo Hardware</h3>
            
            <form method="POST" action="admin.php" class="modern-form">
                <input type="hidden" name="action" value="store">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Tipo (Factory Method)</label>
                        <select name="tipo" class="input-modern" required>
                            <option value="" disabled selected>Selecione a linha de produção...</option>
                            <option value="teclado">Teclado Mecânico</option>
                            <option value="mouse">Mouse Cyber</option>
                            <option value="monitor">Monitor Dark Mode</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nome do Hardware</label>
                        <input type="text" name="nome" class="input-modern" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Preço (R$)</label>
                        <input type="number" step="0.01" name="preco" class="input-modern" required>
                    </div>
                    <div class="form-group">
                        <label>Desconto (%)</label>
                        <input type="number" name="desconto" class="input-modern" value="0">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Descrição</label>
                    <textarea name="descricao" class="input-modern" required></textarea>
                </div>

                <div class="form-actions right">
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Executar Cadastro</button>
                </div>
            </form>
        </div>

        <div class="form-section">
            <h3><i class="fas fa-database"></i> Inventário Ativo</h3>
            
            <div class="produtos-grid" style="grid-template-columns: 1fr; gap: 15px;">
                <?php foreach($produtos as $produto): ?>
                    <div class="order-card" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: var(--surface-3);">
                        
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <img src="<?php echo $produto['imagem']; ?>" alt="Hardware" style="width: 60px; height: 60px; object-fit: contain; background: var(--surface-2); border-radius: 8px; padding: 5px;">
                            <div>
                                <h4 style="color: var(--cyan-light); font-family: 'Orbitron', sans-serif;"><?php echo $produto['nome']; ?></h4>
                                <span class="badge-categoria"><?php echo $produto['categoria']; ?></span>
                                <span style="color: var(--text-muted); margin-left: 10px;">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <a href="admin.php?action=edit&id=<?php echo $produto['id']; ?>" class="btn-secondary btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>

                            <form method="POST" action="admin.php" onsubmit="return confirm('Expurgar este hardware do sistema de forma permanente?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $produto['id']; ?>">
                                <button type="submit" class="btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Deletar
                                </button>
                            </form>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../layout/footer.php'; ?> 