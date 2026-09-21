<?php
/**
 * ADAPTADO (Adaptee) do padrão Adapter.
 *
 * Representa a "biblioteca legada" do exemplo da aula: ela sabe
 * fazer muito bem uma coisa só — cuspir XML — e a sua interface
 * (exportarXml) é INCOMPATÍVEL com o que o sistema espera (exportarJson).
 *
 * Repare que esta classe NÃO conhece o Adapter nem o JSON.
 */
class ExportadorXml {

    /**
     * Gera o XML do conjunto de dados recebido.
     *
     * @param array  $dados      Linhas do banco (array de arrays associativos)
     * @param string $raiz       Nome do elemento raiz
     * @param string $elemento   Nome de cada item
     */
    public function exportarXml(array $dados, string $raiz = 'produtos', string $elemento = 'produto'): string {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $noRaiz = $doc->createElement($raiz);
        $noRaiz->setAttribute('gerado_em', date('Y-m-d H:i:s'));
        $noRaiz->setAttribute('total', (string) count($dados));
        $doc->appendChild($noRaiz);

        foreach ($dados as $linha) {
            $noItem = $doc->createElement($elemento);

            foreach ($linha as $campo => $valor) {
                // Nomes de tag não podem começar com número nem ter espaços
                $tag = preg_replace('/[^a-zA-Z0-9_]/', '_', $campo);

                $noCampo = $doc->createElement($tag);
                // CDATA protege acentos, aspas e o JSON de especificacoes
                $noCampo->appendChild($doc->createCDATASection((string) ($valor ?? '')));
                $noItem->appendChild($noCampo);
            }

            $noRaiz->appendChild($noItem);
        }

        return $doc->saveXML();
    }
}
?>
