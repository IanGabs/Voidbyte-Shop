<?php
require_once __DIR__ . '/../model/UserModel.php';

class AuthController {
    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new UserModel();
    }

    // Roteador atualizado: aceita uma ação padrão dinâmica
    public function router($defaultAction = 'login_form') {
        $action = $_POST['action'] ?? $_GET['action'] ?? $defaultAction;

        switch ($action) {
            case 'login':
                $this->login();
                break;
            case 'logout':
                $this->logout();
                break;
            case 'register_form':
                $this->showRegisterForm();
                break;
            case 'register':
                $this->register();
                break;
            case 'login_form':
            default:
                $this->showLoginForm();
                break;
        }
    }

    /* =======================================================
       MÉTODOS DE LOGIN E LOGOUT (Já existiam)
       ======================================================= */
    public function showLoginForm() {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            header('Location: ' . ($_SESSION['user_type'] === 'admin' ? 'admin.php' : 'index.php'));
            exit;
        }
        require_once __DIR__ . '/../views/login.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $senha = $_POST['senha'] ?? '';

            if (empty($email) || empty($senha)) {
                $_SESSION['auth_erro'] = "Preencha todos os campos do protocolo.";
                header('Location: login.php');
                exit;
            }

            $user = $this->userModel->findByEmail($email);

            if ($user && password_verify($senha, $user['senha'])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nome'];
                $_SESSION['user_type'] = $user['tipo']; 
                $_SESSION['user_photo'] = $user['foto'];

                header('Location: ' . ($user['tipo'] === 'admin' ? 'admin.php' : 'index.php'));
                exit;
            } else {
                $_SESSION['auth_erro'] = "Credenciais inválidas. Acesso negado.";
                header('Location: login.php');
                exit;
            }
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }

    /* =======================================================
       NOVOS MÉTODOS DE REGISTRO
       ======================================================= */
    
    // Exibe a tela de registro
    public function showRegisterForm() {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            header('Location: index.php');
            exit;
        }
        require_once __DIR__ . '/../views/register.php';
    }

    // Lógica para cadastrar um novo cliente
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = trim($_POST['nome'] ?? '');
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $senha = $_POST['senha'] ?? '';
            $senha_confirm = $_POST['senha_confirm'] ?? '';

            // Validação Básica
            if (empty($nome) || empty($email) || empty($senha) || empty($senha_confirm)) {
                $_SESSION['auth_erro'] = "Dados incompletos. Preencha todos os campos.";
                header('Location: register.php');
                exit;
            }

            // Verifica se as senhas coincidem
            if ($senha !== $senha_confirm) {
                $_SESSION['auth_erro'] = "As chaves de criptografia não coincidem.";
                header('Location: register.php');
                exit;
            }

            // Verifica tamanho mínimo da senha
            if (strlen($senha) < 6) {
                $_SESSION['auth_erro'] = "A senha deve ter no mínimo 6 caracteres para segurança do sistema.";
                header('Location: register.php');
                exit;
            }

            // Verifica se o e-mail já está em uso
            if ($this->userModel->findByEmail($email)) {
                $_SESSION['auth_erro'] = "Este e-mail já possui um protocolo ativo no Voidbyte.";
                header('Location: register.php');
                exit;
            }

            // Criptografa a senha com BCRYPT
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            // Salva o novo usuário (por padrão o Model já o define como 'cliente')
            if ($this->userModel->createUser($nome, $email, $senha_hash)) {
                $_SESSION['auth_msg'] = "Registro concluído. Você agora faz parte do Vazio. Faça seu login.";
                header('Location: login.php'); // Manda pro login com mensagem de sucesso
                exit;
            } else {
                $_SESSION['auth_erro'] = "Erro interno no servidor ao tentar criar o registro.";
                header('Location: register.php');
                exit;
            }
        }
    }
}
?>