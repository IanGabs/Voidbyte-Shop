<?php
require_once __DIR__ . '/../model/ProductModel.php';
require_once __DIR__ . '/../model/export/ExportadorXml.php';
require_once __DIR__ . '/../model/export/XmlParaJsonAdapter.php';

/**
 * CLIENTE do padrão Adapter.
 *
 * Repare que ele só conversa com a interface ExportadorJson.
 * Ele não sabe que por baixo existe um ExportadorXml.
 */
class ExportController {

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
        $formato = $_GET['formato'] ?? 'json';

        switch ($formato) {
            case 'xml':     $this->exportarXml();     break;
            case 'preview': $this->preview();         break;
            case 'json':
            default:        $this->exportarJson();    break;
        }
    }

    /** Monta o Adapter injetando o adaptado (composição) */
    private function montarExportador(): XmlParaJsonAdapter {
        return new XmlParaJsonAdapter(new ExportadorXml());
    }

    public function exportarJson() {
        $dados = $this->productModel->getAllProducts();

        // O cliente chama a interface ALVO e recebe JSON,
        // mesmo que a origem real seja XML.
        $exportador = $this->montarExportador();
        $json = $exportador->exportarJson($dados);

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="voidbyte_produtos_' . date('Ymd_His') . '.json"');
        echo $json;
        exit;
    }

    public function exportarXml() {
        $dados = $this->productModel->getAllProducts();

        $exportador = $this->montarExportador();
        $exportador->exportarJson($dados);   // gera o XML por dentro
        $xml = $exportador->getUltimoXml();

        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="voidbyte_produtos_' . date('Ymd_His') . '.xml"');
        echo $xml;
        exit;
    }

    /**
     * Tela que mostra XML e JSON lado a lado.
     * Ótima para demonstrar o padrão na apresentação do trabalho.
     */
    public function preview() {
        $dados = array_slice($this->productModel->getAllProducts(), 0, 3);

        $exportador = $this->montarExportador();
        $json = $exportador->exportarJson($dados);
        $xml  = $exportador->getUltimoXml();

        $title = 'Adapter XML → JSON | Voidbyte Shop';
        require_once __DIR__ . '/../views/admin/export.php';
    }
}
?>
