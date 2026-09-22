<?php
require_once __DIR__ . '/../model/UserModel.php';

class ProfileController {

    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $this->checkAuth();
        $this->userModel = new UserModel();
    }

    private function checkAuth() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            $_SESSION['auth_msg'] = "Faça login para acessar seu protocolo de conta.";
            $_SESSION['pos_login_redirect'] = 'profile.php';
            header('Location: login.php');
            exit;
        }
    }

    public function router() {
        $action = $_POST['action'] ?? $_GET['action'] ?? 'view';

        switch ($action) {
            case 'update_dados': $this->updateDados(); break;
            case 'update_senha': $this->updateSenha(); break;
            case 'view':
            default:             $this->view(); break;
        }
    }

    public function view() {
        $usuario = $this->userModel->findById($_SESSION['user_id']);

        if (!$usuario) {
            // Sessão aponta pra um usuário que não existe mais no banco
            session_unset();
            session_destroy();
            header('Location: login.php');
            exit;
        }

        $title = 'Protocolo de Conta | Voidbyte Shop';
        require_once __DIR__ . '/../views/profile.php';
    }

    /** Trata o upload da foto de perfil (mesmas regras do upload de produtos) */
    private function tratarUploadFoto(): ?string {
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $_SESSION['profile_erro'] = "Formato de imagem não suportado ({$extensao}).";
            return null;
        }

        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) { // 2 MB
            $_SESSION['profile_erro'] = "A imagem excede o limite de 2 MB.";
            return null;
        }

        $pasta = __DIR__ . '/../assets/imgs/avatars/';
        if (!is_dir($pasta)) { mkdir($pasta, 0755, true); }

        $novoNome = 'user_' . $_SESSION['user_id'] . '_' . uniqid() . '.' . $extensao;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $pasta . $novoNome)) {
            return './assets/imgs/avatars/' . $novoNome;
        }
        return null;
    }

    public function updateDados() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: profile.php'); exit; }

        $id = (int) $_SESSION['user_id'];
        $nome = trim($_POST['nome'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

        if ($nome === '' || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['profile_erro'] = "Preencha um nome e um e-mail válidos.";
            header('Location: profile.php');
            exit;
        }

        // Impede usar um e-mail que já pertence a OUTRO usuário
        $existente = $this->userModel->findByEmail($email);
        if ($existente && (int) $existente['id'] !== $id) {
            $_SESSION['profile_erro'] = "Este e-mail já está registrado em outro protocolo.";
            header('Location: profile.php');
            exit;
        }

        // null = mantém a foto atual
        $foto = $this->tratarUploadFoto();

        if (isset($_SESSION['profile_erro'])) {
            // A validação do upload já setou o erro (formato/tamanho inválido)
            header('Location: profile.php');
            exit;
        }

        if ($this->userModel->updateProfile($id, $nome, $email, $foto)) {
            // Atualiza a sessão para o header refletir a mudança sem precisar logar de novo
            $_SESSION['user_name'] = $nome;
            if ($foto !== null) {
                $_SESSION['user_photo'] = $foto;
            }
            $_SESSION['profile_msg'] = "Dados do protocolo atualizados com sucesso.";
        } else {
            $_SESSION['profile_erro'] = "Falha ao atualizar os dados. Tente novamente.";
        }

        header('Location: profile.php');
        exit;
    }

    public function updateSenha() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: profile.php'); exit; }

        $id = (int) $_SESSION['user_id'];
        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';

        $usuario = $this->userModel->findById($id);

        if (!$usuario || !password_verify($senhaAtual, $usuario['senha'])) {
            $_SESSION['senha_erro'] = "Senha atual incorreta.";
            header('Location: profile.php');
            exit;
        }

        if (strlen($novaSenha) < 6) {
            $_SESSION['senha_erro'] = "A nova senha deve ter no mínimo 6 caracteres.";
            header('Location: profile.php');
            exit;
        }

        if ($novaSenha !== $confirmarSenha) {
            $_SESSION['senha_erro'] = "As senhas não coincidem.";
            header('Location: profile.php');
            exit;
        }

        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);

        if ($this->userModel->updatePassword($id, $hash)) {
            $_SESSION['senha_msg'] = "Senha atualizada com sucesso.";
        } else {
            $_SESSION['senha_erro'] = "Falha ao atualizar a senha. Tente novamente.";
        }

        header('Location: profile.php');
        exit;
    }
}
?>