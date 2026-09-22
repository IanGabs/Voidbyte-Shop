<?php include __DIR__ . '/layout/header.php'; ?>

<main class="admin-container">
    <div class="admin-content-wrapper">

        <!-- ===================== CABEÇALHO: AVATAR + IDENTIDADE ===================== -->
        <div class="perfil-topo">
            <div class="perfil-avatar-wrapper">
                <img src="<?php echo htmlspecialchars($usuario['foto']); ?>" alt="Avatar" class="perfil-avatar" id="avatar-preview">
                <label for="input-foto-avatar" class="perfil-avatar-editar" title="Trocar foto">
                    <i class="fas fa-camera"></i>
                </label>
            </div>

            <div class="perfil-info-topo">
                <h1><?php echo htmlspecialchars($usuario['nome']); ?></h1>
                <div class="perfil-badges">
                    <span class="badge-tipo-usuario badge-<?php echo $usuario['tipo']; ?>">
                        <i class="fas <?php echo $usuario['tipo'] === 'admin' ? 'fa-user-shield' : 'fa-user'; ?>"></i>
                        <?php echo $usuario['tipo'] === 'admin' ? 'System Admin' : 'System User'; ?>
                    </span>
                    <span class="badge-protocolo">
                        Protocolo #<?php echo str_pad($usuario['id'], 5, '0', STR_PAD_LEFT); ?>
                    </span>
                </div>
                <p class="perfil-membro-desde">
                    <i class="fas fa-calendar-alt"></i>
                    Conectado ao Vazio desde <?php echo date('d/m/Y', strtotime($usuario['data_criacao'])); ?>
                </p>
            </div>
        </div>

        <!-- ===================== DADOS DA CONTA ===================== -->
        <div class="form-section">
            <h3><i class="fas fa-id-card"></i> Dados do Protocolo</h3>

            <?php if (isset($_SESSION['profile_msg'])): ?>
                <div class="success-message"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['profile_msg']; unset($_SESSION['profile_msg']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['profile_erro'])): ?>
                <div class="error-message"><i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['profile_erro']; unset($_SESSION['profile_erro']); ?></div>
            <?php endif; ?>

            <form method="POST" action="profile.php" enctype="multipart/form-data" class="modern-form">
                <input type="hidden" name="action" value="update_dados">

                <!-- Input de arquivo escondido, acionado pelo ícone de câmera no avatar -->
                <input type="file" name="foto" id="input-foto-avatar" accept="image/*" style="display:none;" onchange="previewAvatar(this)">

                <div class="form-row">
                    <div class="form-group">
                        <label>Nome</label>
                        <input type="text" name="nome" class="input-modern" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" name="email" class="input-modern" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>
                </div>

                <small style="color: var(--text-dim); display: block;">
                    Para trocar a foto, clique no ícone de câmera sobre o avatar.
                </small>

                <div class="form-actions right">
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Salvar Alterações</button>
                </div>
            </form>
        </div>

        <!-- ===================== ALTERAR SENHA ===================== -->
        <div class="form-section">
            <h3><i class="fas fa-key"></i> Segurança</h3>

            <?php if (isset($_SESSION['senha_msg'])): ?>
                <div class="success-message"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['senha_msg']; unset($_SESSION['senha_msg']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['senha_erro'])): ?>
                <div class="error-message"><i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['senha_erro']; unset($_SESSION['senha_erro']); ?></div>
            <?php endif; ?>

            <form method="POST" action="profile.php" class="modern-form">
                <input type="hidden" name="action" value="update_senha">

                <div class="form-row">
                    <div class="form-group">
                        <label>Senha atual</label>
                        <input type="password" name="senha_atual" class="input-modern" required>
                    </div>
                    <div class="form-group">
                        <label>Nova senha</label>
                        <input type="password" name="nova_senha" class="input-modern" minlength="6" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirmar nova senha</label>
                    <input type="password" name="confirmar_senha" class="input-modern" minlength="6" required>
                </div>

                <small style="color: var(--text-dim); display: block;">
                    Mínimo de 6 caracteres.
                </small>

                <div class="form-actions right">
                    <button type="submit" class="btn-secondary"><i class="fas fa-shield-alt"></i> Atualizar Senha</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    // Envia o formulário de dados automaticamente ao escolher uma foto nova,
    // já mostrando o preview antes disso.
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            document.getElementById('avatar-preview').src = URL.createObjectURL(input.files[0]);
        }
    }
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>