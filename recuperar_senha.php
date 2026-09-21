<?php
session_start();
require_once __DIR__ . '/config/Database.php';

$mensagem = '';
$link_recuperacao = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    $db = Database::getInstance()->getConnection();
    
    // Verifica se o e-mail existe (Ajuste 'usuarios' para o nome da sua tabela)
    $query = "SELECT id FROM usuarios WHERE email = ? LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bind_param('s', $email); // 's' significa string
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Gera um token seguro e uma validade de 1 hora
        $token = bin2hex(random_bytes(32));
        $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $updateQuery = "UPDATE usuarios SET reset_token = ?, reset_expiracao = ? WHERE email = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bind_param('sss', $token, $expiracao, $email);
        $updateStmt->execute();
        
        // Em um sistema real, aqui você usaria o PHPMailer para enviar o e-mail.
        // Para testes locais, vamos exibir o link na tela:
        $link_recuperacao = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/redefinir_senha.php?token=" . $token;
        $mensagem = "<div class='success-message'>E-mail de recuperação gerado! (Simulação)</div>";
    } else {
        // Por segurança, não confirmamos se o e-mail existe ou não
        $mensagem = "<div class='success-message'>Se o e-mail existir, um link foi enviado.</div>";
    }
}
?>

<?php include __DIR__ . '/views/layout/header.php'; ?>
<main class="admin-container" style="display: flex; justify-content: center; align-items: center; min-height: 70vh;">
    <div class="form-section" style="width: 100%; max-width: 500px;">
        <h3 style="text-align: center;"><i class="fas fa-unlock-alt"></i> Recuperar Senha</h3>
        
        <?php echo $mensagem; ?>
        
        <?php if ($link_recuperacao): ?>
            <div style="background: var(--surface-2); padding: 15px; border-left: 4px solid var(--cyan); margin-bottom: 15px; word-break: break-all;">
                <p style="margin: 0; color: #fff;"><strong>Link de Recuperação:</strong><br>
                <a href="<?php echo $link_recuperacao; ?>" style="color: var(--cyan-light);"><?php echo $link_recuperacao; ?></a></p>
                <small style="color: var(--text-muted);">*Em produção, este link iria para o e-mail do usuário.</small>
            </div>
        <?php endif; ?>

        <form method="POST" class="modern-form">
            <div class="form-group full-width">
                <label>E-mail da sua conta</label>
                <input type="email" name="email" class="input-modern" required placeholder="admin@voidbyte.com">
            </div>
            <div class="form-actions" style="margin-top: 20px;">
                <button type="submit" class="btn-primary full-width">Solicitar Link</button>
            </div>
        </form>
        <div style="text-align: center; margin-top: 15px;">
            <a href="login.php" style="color: var(--text-muted); text-decoration: none;">&larr; Voltar ao Login</a>
        </div>
    </div>
</main>
<?php include __DIR__ . '/views/layout/footer.php'; ?>