<?php
require_once __DIR__ . '/../model/ProductModel.php';
// Importando as fábricas originais + A nova Super Fábrica
require_once __DIR__ . '/../model/factories/TecladoFactory.php';
require_once __DIR__ . '/../model/factories/MouseFactory.php';
require_once __DIR__ . '/../model/factories/GenericoFactory.php'; 

class AdminController {
    private $productModel;
    
    public function __construct() {
        $this->checkAuth();
        $this->productModel = new ProductModel();
    }
    
    private function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'admin') {
            header('Location: login.php');
            exit;
        }
    }
    
    public function router() {
        $action = $_POST['action'] ?? $_GET['action'] ?? 'dashboard';
        switch ($action) {
            case 'store':  $this->store(); break;
            case 'update': $this->update(); break;
            case 'delete': $this->delete(); break;
            case 'edit':   $this->edit($_GET['id'] ?? 0); break;
            default:       $this->dashboard(); break;
        }
    }
    
    public function dashboard() {
        $produtos = $this->productModel->getAllProducts();
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipo = $_POST['tipo'] ?? '';
            $nome = $_POST['nome'] ?? '';
            $descricao = $_POST['descricao'] ?? '';
            $preco = floatval($_POST['preco'] ?? 0);
            $desconto = floatval($_POST['desconto'] ?? 0);
            
            // Validação do JSON para o Comparador
            $especificacoes_raw = trim($_POST['especificacoes'] ?? '');
            $especificacoes = null;
            if (!empty($especificacoes_raw)) {
                json_decode($especificacoes_raw);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $especificacoes = $especificacoes_raw;
                } else {
                    $_SESSION['admin_erro'] = "Erro: As especificações informadas não são um JSON válido.";
                    header('Location: admin.php');
                    exit;
                }
            }

            $imagemFinalPath = './assets/imgs/default-hardware.png'; 
            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
                if (in_array($extensao, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $novoNome = uniqid('hw_') . '.' . $extensao;
                    $destino = __DIR__ . '/../assets/imgs/products/' . $novoNome;
                    if (!is_dir(__DIR__ . '/../assets/imgs/products/')) { mkdir(__DIR__ . '/../assets/imgs/products/', 0755, true); }
                    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) { $imagemFinalPath = './assets/imgs/products/' . $novoNome; }
                }
            }

            $categoriasGerais = [
                // Hardware Central
                'cpu' => 'Processadores',
                'gpu' => 'Placas de Vídeo',
                'ram' => 'Memórias RAM',
                'motherboard' => 'Placas-Mãe',
                'storage' => 'Armazenamento (SSD/NVMe)',
                'psu' => 'Fontes de Alimentação',
                'cooler' => 'Refrigeração e Coolers',
                'gabinete' => 'Gabinetes',

                // Outros Periféricos e Áudio
                'monitor' => 'Monitores',
                'headset' => 'Headsets e Áudio',
                'webcam' => 'Webcams e Streaming',
                'microfone' => 'Microfones',

                // Eletrônicos e Acessórios de Setup
                'cadeira' => 'Cadeiras Ergonômicas',
                'mesa' => 'Mesas Tech',
                'nobreak' => 'Nobreaks e Energia',
                'cabos' => 'Cabos e Adaptadores',
                'hub' => 'Hubs e Conectividade',
                'roteador' => 'Roteadores e Rede',
                'impressora' => 'Impressoras 3D',
                'vr' => 'Óculos VR e Simuladores'
            ];

            $factory = null;

            if ($tipo === 'teclado') {
                $factory = new TecladoFactory();
            } elseif ($tipo === 'mouse') {
                $factory = new MouseFactory();
            } elseif (isset($categoriasGerais[$tipo])) {
                // Instancia a super fábrica genérica passando o nome real da categoria!
                $factory = new GenericoFactory($categoriasGerais[$tipo]);
            }

            if ($factory) {
                $produtoObj = $factory->createProduct($nome, $preco, $descricao);
                $categoriaAutomatica = $produtoObj->getCategoryName();

                if (!empty($nome) && $preco > 0) {
                    if ($this->productModel->createProduct($nome, $descricao, $preco, $imagemFinalPath, $categoriaAutomatica, $desconto, $especificacoes)) {
                        $_SESSION['admin_msg'] = "Componente '{$nome}' registrado com sucesso no Voidbyte!";
                    } else {
                        $_SESSION['admin_erro'] = "Falha ao registrar hardware no banco de dados.";
                    }
                } else {
                    $_SESSION['admin_erro'] = "Nome e preço são obrigatórios.";
                }
            } else {
                $_SESSION['admin_erro'] = "Acesso Negado: Tipo de produto inválido na linha de produção.";
            }
            
            header('Location: admin.php');
            exit;
        }
    }

    public function delete() {
        if (isset($_POST['id']) && $_POST['id'] > 0) {
            if ($this->productModel->deleteProduct($_POST['id'])) { $_SESSION['admin_msg'] = "Componente expurgado do sistema."; } 
            else { $_SESSION['admin_erro'] = "Falha ao remover o componente."; }
        }
        header('Location: admin.php');
        exit;
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $nome = $_POST['nome'] ?? '';
            $descricao = $_POST['descricao'] ?? '';
            $preco = floatval($_POST['preco'] ?? 0);
            $desconto = floatval($_POST['desconto'] ?? 0);
            $categoria = $_POST['categoria'] ?? 'Geral'; 

            $especificacoes_raw = trim($_POST['especificacoes'] ?? '');
            $especificacoes = null;
            if (!empty($especificacoes_raw)) {
                json_decode($especificacoes_raw);
                if (json_last_error() === JSON_ERROR_NONE) { $especificacoes = $especificacoes_raw; } 
                else {
                    $_SESSION['admin_erro'] = "Erro: As especificações informadas não são um JSON válido.";
                    header('Location: admin.php');
                    exit;
                }
            }

            if ($id > 0 && !empty($nome)) {
                if ($this->productModel->updateProduct($id, $nome, $descricao, $preco, $categoria, $desconto, $especificacoes)) {
                    $_SESSION['admin_msg'] = "Dados atualizados com sucesso.";
                } else { $_SESSION['admin_erro'] = "Falha ao atualizar os dados."; }
            }
            header('Location: admin.php');
            exit;
        }
    }

    public function edit($id) {
        if ($id > 0) {
            $produto = $this->productModel->getProductById($id);
            if ($produto) {
                echo "<pre>View de edição em construção:\n"; print_r($produto); echo "</pre>";
                return;
            }
        }
        $_SESSION['admin_erro'] = "Hardware não encontrado.";
        header('Location: admin.php');
        exit;
    }
}
?>