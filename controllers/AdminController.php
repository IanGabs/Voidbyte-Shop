<?php
require_once __DIR__ . '/../model/ProductModel.php';
require_once __DIR__ . '/../model/factories/TecladoFactory.php';
require_once __DIR__ . '/../model/factories/MouseFactory.php';
require_once __DIR__ . '/../model/factories/GenericoFactory.php';

class AdminController {
    private $productModel;

    // Mapa usado pelo Factory Method e também pelo <select> das views
    public static $categoriasGerais = [
        'cpu'         => 'Processadores',
        'gpu'         => 'Placas de Vídeo',
        'ram'         => 'Memórias RAM',
        'motherboard' => 'Placas-Mãe',
        'storage'     => 'Armazenamento (SSD/NVMe)',
        'psu'         => 'Fontes de Alimentação',
        'cooler'      => 'Refrigeração e Coolers',
        'gabinete'    => 'Gabinetes',
        'monitor'     => 'Monitores',
        'headset'     => 'Headsets e Áudio',
        'webcam'      => 'Webcams e Streaming',
        'microfone'   => 'Microfones',
        'cadeira'     => 'Cadeiras Ergonômicas',
        'mesa'        => 'Mesas Tech',
        'nobreak'     => 'Nobreaks e Energia',
        'cabos'       => 'Cabos e Adaptadores',
        'hub'         => 'Hubs e Conectividade',
        'roteador'    => 'Roteadores e Rede',
        'impressora'  => 'Impressoras 3D',
        'vr'          => 'Óculos VR e Simuladores'
    ];

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
            case 'store':  $this->store();  break;
            case 'update': $this->update(); break;
            case 'delete': $this->delete(); break;
            case 'edit':   $this->edit($_GET['id'] ?? 0); break;
            default:       $this->dashboard(); break;
        }
    }

    public function dashboard() {
        $produtos = $this->productModel->getAllProducts();
        $title = 'Terminal Administrativo | Voidbyte Shop';
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * Monta o JSON de especificações a partir dos pares de campos
     * spec_chave[] / spec_valor[] enviados pelo formulário.
     * O admin nunca mais precisa digitar JSON na mão.
     */
    private function montarEspecificacoes(): ?string {
        $chaves  = $_POST['spec_chave'] ?? [];
        $valores = $_POST['spec_valor'] ?? [];

        $specs = [];
        foreach ($chaves as $i => $chave) {
            $chave = trim($chave);
            $valor = trim($valores[$i] ?? '');
            if ($chave !== '' && $valor !== '') {
                $specs[$chave] = $valor;
            }
        }

        return empty($specs) ? null : json_encode($specs, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Trata o upload da imagem.
     * Devolve o caminho salvo ou null se nenhum arquivo válido foi enviado.
     */
    private function tratarUpload(): ?string {
        if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $_SESSION['admin_erro'] = "Formato de imagem não suportado ({$extensao}).";
            return null;
        }

        // Limite de 3 MB
        if ($_FILES['imagem']['size'] > 3 * 1024 * 1024) {
            $_SESSION['admin_erro'] = "A imagem excede o limite de 3 MB.";
            return null;
        }

        $pasta = __DIR__ . '/../assets/imgs/products/';
        if (!is_dir($pasta)) { mkdir($pasta, 0755, true); }

        $novoNome = uniqid('hw_') . '.' . $extensao;
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $pasta . $novoNome)) {
            return './assets/imgs/products/' . $novoNome;
        }
        return null;
    }

    /** Resolve a fábrica correta (Factory Method) */
    private function resolverFactory(string $tipo) {
        if ($tipo === 'teclado') return new TecladoFactory();
        if ($tipo === 'mouse')   return new MouseFactory();
        if (isset(self::$categoriasGerais[$tipo])) {
            return new GenericoFactory(self::$categoriasGerais[$tipo]);
        }
        return null;
    }

    // =========================================================
    // CRUD
    // =========================================================

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: admin.php'); exit; }

        $tipo      = $_POST['tipo'] ?? '';
        $nome      = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $preco     = floatval($_POST['preco'] ?? 0);
        $desconto  = floatval($_POST['desconto'] ?? 0);

        $especificacoes = $this->montarEspecificacoes();
        $imagem = $this->tratarUpload() ?? './assets/imgs/default-hardware.png';

        $factory = $this->resolverFactory($tipo);

        if (!$factory) {
            $_SESSION['admin_erro'] = "Tipo de produto inválido na linha de produção.";
            header('Location: admin.php'); exit;
        }

        if ($nome === '' || $preco <= 0) {
            $_SESSION['admin_erro'] = "Nome e preço são obrigatórios.";
            header('Location: admin.php'); exit;
        }

        // Factory Method decide a categoria a partir do objeto criado
        $produtoObj = $factory->createProduct($nome, $preco, $descricao);
        $categoria  = $produtoObj->getCategoryName();

        if ($this->productModel->createProduct($nome, $descricao, $preco, $imagem, $categoria, $desconto, $especificacoes)) {
            $_SESSION['admin_msg'] = "Componente '{$nome}' registrado com sucesso.";
        } else {
            $_SESSION['admin_erro'] = "Falha ao registrar hardware no banco.";
        }

        header('Location: admin.php');
        exit;
    }

    /** Abre a tela de edição com os dados já preenchidos */
    public function edit($id) {
        $id = intval($id);

        if ($id > 0) {
            $produto = $this->productModel->getProductById($id);

            if ($produto) {
                // Transforma o JSON salvo em array para preencher os campos
                $specs = json_decode($produto['especificacoes'] ?? '{}', true);
                if (!is_array($specs)) { $specs = []; }

                $categorias = $this->productModel->getCategorias();
                $title = 'Editar: ' . $produto['nome'] . ' | Voidbyte Shop';

                require_once __DIR__ . '/../views/admin/edit.php';
                return;
            }
        }

        $_SESSION['admin_erro'] = "Hardware não encontrado.";
        header('Location: admin.php');
        exit;
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: admin.php'); exit; }

        $id        = intval($_POST['id'] ?? 0);
        $nome      = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $preco     = floatval($_POST['preco'] ?? 0);
        $desconto  = floatval($_POST['desconto'] ?? 0);
        $categoria = trim($_POST['categoria'] ?? 'Geral');

        $especificacoes = $this->montarEspecificacoes();

        // null = mantém a imagem que já está no banco
        $imagem = $this->tratarUpload();

        if ($id <= 0 || $nome === '') {
            $_SESSION['admin_erro'] = "Dados inválidos para atualização.";
            header('Location: admin.php'); exit;
        }

        if ($this->productModel->updateProduct($id, $nome, $descricao, $preco, $categoria, $desconto, $especificacoes, $imagem)) {
            $_SESSION['admin_msg'] = "Produto '{$nome}' atualizado com sucesso.";
        } else {
            $_SESSION['admin_erro'] = "Falha ao atualizar os dados.";
        }

        header('Location: admin.php');
        exit;
    }

    public function delete() {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0 && $this->productModel->deleteProduct($id)) {
            $_SESSION['admin_msg'] = "Componente removido do sistema.";
        } else {
            $_SESSION['admin_erro'] = "Falha ao remover o componente.";
        }
        header('Location: admin.php');
        exit;
    }
}
?>  