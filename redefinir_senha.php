<?php
session_start();
require_once __DIR__ . '/config/Database.php';

$mensagem = '';
$token_valido = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $db = Database::getInstance()->getConnection();
    
    // Verifica se o token existe e ainda não expirou
    $query = "SELECT id FROM usuarios WHERE reset_token = ? AND reset_expiracao > NOW() LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $token_valido = true;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nova_senha = $_POST['nova_senha'];
            $hash_senha = password_hash($nova_senha, PASSWORD_DEFAULT);
            
            // Atualiza a senha e invalida o token
            $updateQuery = "UPDATE usuarios SET senha = ?, reset_token = NULL, reset_expiracao = NULL WHERE reset_token = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bind_param('ss', $hash_senha, $token);
            
            if ($updateStmt->execute()) {
                $_SESSION['login_msg'] = "Senha atualizada com sucesso! Faça login.";
                header("Location: login.php");
                exit;
            } else {
                $mensagem = "<div class='error-message'>Erro ao atualizar a senha.</div>";
            }
        }
    } else {
        $mensagem = "<div class='error-message'>Link inválido ou expirado. Solicite novamente.</div>";
    }
} else {
    header("Location: login.php");
    exit;
}
?>

<?php include __DIR__ . '/views/layout/header.php'; ?>
<main class="admin-container" style="display: flex; justify-content: center; align-items: center; min-height: 70vh;">
    <div class="form-section" style="width: 100%; max-width: 500px;">
        <h3 style="text-align: center;"><i class="fas fa-key"></i> Criar Nova Senha</h3>
        
        <?php echo $mensagem; ?>
        
        <?php if ($token_valido): ?>
        <form method="POST" class="modern-form">
            <div class="form-group full-width">
                <label>Nova Senha</label>
                <input type="password" name="nova_senha" class="input-modern" required minlength="6">
            </div>
            <div class="form-actions" style="margin-top: 20px;">
                <button type="submit" class="btn-primary full-width">Salvar Nova Senha</button>
            </div>
        </form>
        <?php else: ?>
            <div style="text-align: center; margin-top: 20px;">
                <a href="recuperar_senha.php" class="btn-secondary">Solicitar novo link</a>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/views/layout/footer.php'; ?>