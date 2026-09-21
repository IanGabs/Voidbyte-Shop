<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Restrito | Voidbyte Shop</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-microchip auth-logo" style="color: var(--cyan); font-size: 3rem; filter: drop-shadow(0 0 12px var(--purple));"></i>
                <h1>VOIDBYTE SYSTEM</h1>
                <p style="color: var(--text-muted); font-family: 'Rajdhani', sans-serif; margin-top: 8px;">Protocolo de Autenticação Requerido</p>
            </div>

            <?php 
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (isset($_SESSION['auth_erro'])): 
            ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['auth_erro']; unset($_SESSION['auth_erro']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['auth_msg'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['auth_msg']; unset($_SESSION['auth_msg']); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="auth-form">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> E-mail de Acesso</label>
                    <input type="email" id="email" name="email" class="input-modern" placeholder="admin@voidbyte.com" required autocomplete="email">
                </div>

                <div class="form-group" style="margin-bottom: 2rem;">
                    <label for="senha"><i class="fas fa-key"></i> Chave de Criptografia (Senha)</label>
                    <input type="password" id="senha" name="senha" class="input-modern" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Inicializar Sessão
                </button>

                <br>
                <br>
                <div class="form-group" style="text-align: right; margin-top: -10px; margin-bottom: 15px;">
                    <a href="recuperar_senha.php" style="color: var(--cyan-light); text-decoration: none; font-size: 0.9em;">
                        <i class="fas fa-key"></i> Esqueci minha senha
                    </a>
                </div>
            </form>

            <div class="auth-footer">
                <p>Ainda não faz parte do Vazio? <a href="register.php">Registrar-se</a></p>
                <p style="margin-top: 8px;"><a href="index.php" style="color: var(--text-dim);"><i class="fas fa-arrow-left"></i> Retornar à Loja</a></p>
            </div>
        </div>
    </div>

</body>
</html>