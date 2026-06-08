<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Registro | Voidbyte Shop</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-user-plus auth-logo" style="color: var(--cyan); font-size: 2.8rem; filter: drop-shadow(0 0 12px var(--cyan));"></i>
                <h1>NOVO PROTOCOLO</h1>
                <p style="color: var(--text-muted); font-family: 'Rajdhani', sans-serif; margin-top: 8px;">Crie sua conta e equipe seu setup</p>
            </div>

            <?php 
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (isset($_SESSION['auth_erro'])): 
            ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['auth_erro']; unset($_SESSION['auth_erro']); ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="auth-form">
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <label for="nome"><i class="fas fa-user"></i> Nome de Operador (Nome Completo)</label>
                    <input type="text" id="nome" name="nome" class="input-modern" placeholder="Ex: Alex Mercer" required>
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> E-mail de Conexão</label>
                    <input type="email" id="email" name="email" class="input-modern" placeholder="seuemail@provedor.com" required autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="senha"><i class="fas fa-key"></i> Chave de Criptografia (Senha)</label>
                    <input type="password" id="senha" name="senha" class="input-modern" placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>

                <div class="form-group" style="margin-bottom: 2rem;">
                    <label for="senha_confirm"><i class="fas fa-lock"></i> Confirmar Chave</label>
                    <input type="password" id="senha_confirm" name="senha_confirm" class="input-modern" placeholder="Repita a senha" required minlength="6">
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Registrar Sistema
                </button>
            </form>

            <div class="auth-footer">
                <p>Já possui protocolo ativo? <a href="login.php">Iniciar Sessão</a></p>
                <p style="margin-top: 8px;"><a href="index.php" style="color: var(--text-dim);"><i class="fas fa-arrow-left"></i> Retornar à Loja</a></p>
            </div>
        </div>
    </div>

</body>
</html>