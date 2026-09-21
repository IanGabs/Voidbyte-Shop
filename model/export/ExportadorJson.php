<?php
/**
 * ALVO (Target) do padrão Adapter.
 *
 * É a interface que o Cliente (ExportController) espera consumir.
 * O sistema Voidbyte trabalha internamente com JSON, então toda
 * exportação precisa chegar até ele neste formato.
 */
interface ExportadorJson {
    /**
     * @param array $dados Linhas vindas do banco (array associativo)
     * @return string Conteúdo em JSON
     */
    public function exportarJson(array $dados): string;
}
?>
