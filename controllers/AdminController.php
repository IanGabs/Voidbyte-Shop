<?php
require_once __DIR__ . '/../model/ProductModel.php';
// Importando as Fábricas
require_once __DIR__ . '/../model/factories/TecladoFactory.php';
require_once __DIR__ . '/../model/factories/MouseFactory.php';
// require_once __DIR__ . '/../model/factories/MonitorFactory.php'; // Descomente quando criar a fábrica de monitor

class AdminController {
    private $productModel;
    
    public function __construct() {
        $this->checkAuth();
        $this->productModel = new ProductModel();
    }
    
    // Verifica se o usuário logado é realmente um Administrador
    private function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'admin') {
            header('Location: login.php');
            exit;
        }
    }
    
    // Roteador que define qual ação o painel deve tomar
    public function router() {
        $action = $_POST['action'] ?? $_GET['action'] ?? 'dashboard';

        switch ($action) {
            case 'store':
                $this->store();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'edit':
                $this->edit($_GET['id'] ?? 0);
                break;
            default:
                $this->dashboard();
                break;
        }
    }
    
    // Carrega a view principal do painel (Dashboard)
    public function dashboard() {
        $produtos = $this->productModel->getAllProducts();
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    // Lógica para CRIAR um novo produto usando o Factory Method
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // 1. Recebe os dados do formulário
            $tipo = $_POST['tipo'] ?? '';
            $nome = $_POST['nome'] ?? '';
            $descricao = $_POST['descricao'] ?? '';
            $preco = floatval($_POST['preco'] ?? 0);
            $desconto = floatval($_POST['desconto'] ?? 0);
            
            // 2. Lógica de Upload da Imagem (Para não quebrar a variável $imagemFinalPath)
            $imagemFinalPath = './assets/imgs/default-hardware.png'; // Imagem padrão caso falhe
            
            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
                $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                
                if (in_array($extensao, $permitidos)) {
                    $novoNome = uniqid('hw_') . '.' . $extensao;
                    $destino = __DIR__ . '/../assets/imgs/products/' . $novoNome;
                    
                    // Cria o diretório se não existir
                    if (!is_dir(__DIR__ . '/../assets/imgs/products/')) {
                        mkdir(__DIR__ . '/../assets/imgs/products/', 0755, true);
                    }
                    
                    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
                        $imagemFinalPath = './assets/imgs/products/' . $novoNome;
                    }
                }
            }

            // 3. Aplicação do Factory Method
            $factory = null;

            // Direciona para a fábrica correta baseada no select do formulário
            switch ($tipo) {
                case 'teclado': 
                    $factory = new TecladoFactory(); 
                    break;
                case 'mouse':   
                    $factory = new MouseFactory(); 
                    break;
                // case 'monitor': 
                //     $factory = new MonitorFactory(); 
                //     break;
            }

            if ($factory) {
                // A fábrica cria o objeto sem que o Controller saiba os detalhes
                $produtoObj = $factory->createProduct($nome, $preco, $descricao);
                
                // O objeto sabe aonde ele pertence! Extraímos a categoria automaticamente.
                $categoriaAutomatica = $produtoObj->getCategoryName();

                // Salva no banco de dados passando a categoria automática
                if (!empty($nome) && $preco > 0) {
                    if ($this->productModel->createProduct($nome, $descricao, $preco, $imagemFinalPath, $categoriaAutomatica, $desconto)) {
                        $_SESSION['admin_msg'] = "Hardware '{$nome}' registrado com sucesso no Voidbyte!";
                    } else {
                        $_SESSION['admin_erro'] = "Falha ao registrar hardware no banco de dados.";
                    }
                } else {
                    $_SESSION['admin_erro'] = "Nome e preço são obrigatórios.";
                }
            } else {
                $_SESSION['admin_erro'] = "Tipo de hardware inválido. A linha de produção falhou.";
            }
            
            // Redireciona de volta para o painel
            header('Location: admin.php');
            exit;
        }
    }

    // Lógica para DELETAR um produto
    public function delete() {
        if (isset($_POST['id']) && $_POST['id'] > 0) {
            if ($this->productModel->deleteProduct($_POST['id'])) {
                $_SESSION['admin_msg'] = "Hardware expurgado do sistema com sucesso.";
            } else {
                $_SESSION['admin_erro'] = "Falha ao remover o hardware.";
            }
        }
        header('Location: admin.php');
        exit;
    }

    // Lógica para ATUALIZAR (Update) um produto existente
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $nome = $_POST['nome'] ?? '';
            $descricao = $_POST['descricao'] ?? '';
            $preco = floatval($_POST['preco'] ?? 0);
            $desconto = floatval($_POST['desconto'] ?? 0);
            $categoria = $_POST['categoria'] ?? 'Geral'; // No update, costumamos permitir editar a categoria livremente

            if ($id > 0 && !empty($nome)) {
                if ($this->productModel->updateProduct($id, $nome, $descricao, $preco, $categoria, $desconto)) {
                    $_SESSION['admin_msg'] = "Dados do hardware atualizados com sucesso.";
                } else {
                    $_SESSION['admin_erro'] = "Falha ao atualizar os dados.";
                }
            }
            
            header('Location: admin.php');
            exit;
        }
    }

    // Carrega a view de edição (Carrega os dados de um produto específico para o formulário)
    public function edit($id) {
        if ($id > 0) {
            $produto = $this->productModel->getProductById($id);
            if ($produto) {
                // require_once __DIR__ . '/../views/admin/edit_product.php'; // Crie esta view depois
                // Para testes, vamos apenas simular um dump se a view não existir:
                echo "<pre>View de edição não criada ainda. Dados do produto a editar:\n";
                print_r($produto);
                echo "</pre>";
                return;
            }
        }
        $_SESSION['admin_erro'] = "Hardware não encontrado.";
        header('Location: admin.php');
        exit;
    }
}
?>