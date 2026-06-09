<?php include __DIR__ . '/layout/header.php'; ?>

<main class="container" style="padding: 4rem 2rem; min-height: 80vh;">
    <div class="form-header">
        <h1><i class="fas fa-balance-scale"></i> Matriz de Comparação</h1>
        <p>Cruze dados técnicos e descubra qual hardware se adapta melhor ao seu setup.</p>
    </div>

    <div class="compare-selectors" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">
        <div class="form-group">
            <label style="color: var(--purple-light);"><i class="fas fa-microchip"></i> Hardware Primário (A)</label>
            <select id="select-prod-a" class="input-modern" onchange="compararHardwares()">
                <option value="">Selecione um equipamento...</option>
                <?php foreach($produtos as $p): ?>
                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['categoria'] . ' - ' . $p['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label style="color: var(--cyan);"><i class="fas fa-microchip"></i> Hardware Secundário (B)</label>
            <select id="select-prod-b" class="input-modern" onchange="compararHardwares()">
                <option value="">Selecione um equipamento...</option>
                <?php foreach($produtos as $p): ?>
                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['categoria'] . ' - ' . $p['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div id="resultado-comparacao">
        <div style="text-align: center; padding: 4rem; color: var(--text-muted); background: var(--surface-2); border-radius: var(--radius-lg); border: 1px dashed var(--border);">
            <i class="fas fa-satellite-dish" style="font-size: 3rem; margin-bottom: 1rem; color: var(--purple-dim);"></i>
            <h3>Aguardando Parâmetros</h3>
            <p>Selecione dois componentes acima para iniciar a varredura do sistema.</p>
        </div>
    </div>
</main>

<script>
function compararHardwares() {
    const idA = document.getElementById('select-prod-a').value;
    const idB = document.getElementById('select-prod-b').value;
    const resultadoDiv = document.getElementById('resultado-comparacao');

    if (!idA || !idB) return;

    // Efeito visual de carregamento
    resultadoDiv.innerHTML = '<div style="text-align: center; padding: 3rem;"><i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--cyan);"></i><p style="margin-top: 1rem;">Processando telemetria...</p></div>';

    fetch(`comparar.php?action=ajax_compare&id_a=${idA}&id_b=${idB}`)
        .then(res => res.json())
        .then(data => {
            const pA = data.produtoA;
            const pB = data.produtoB;

            if(!pA || !pB) {
                resultadoDiv.innerHTML = '<p class="error-message">Erro de leitura. Hardware não localizado no banco de dados.</p>';
                return;
            }

            // Junta todas as chaves do JSON dos dois produtos para não faltar nenhuma linha na tabela
            const specsA = pA.especificacoes || {};
            const specsB = pB.especificacoes || {};
            const todasChaves = [...new Set([...Object.keys(specsA), ...Object.keys(specsB)])];

            let html = `
            <table style="width: 100%; border-collapse: collapse; background: var(--surface-2); border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--border);">
                <thead>
                    <tr style="background: var(--surface-3); border-bottom: 2px solid var(--border);">
                        <th style="padding: 20px; width: 20%;">Especificação</th>
                        <th style="padding: 20px; width: 40%; text-align: center; color: var(--purple-light); border-left: 1px solid var(--border-subtle);"><img src="${pA.imagem}" style="height: 60px; object-fit: contain; margin-bottom:10px;"><br>${pA.nome}</th>
                        <th style="padding: 20px; width: 40%; text-align: center; color: var(--cyan); border-left: 1px solid var(--border-subtle);"><img src="${pB.imagem}" style="height: 60px; object-fit: contain; margin-bottom:10px;"><br>${pB.nome}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--border-subtle);">
                        <td style="padding: 15px 20px; font-weight: bold; color: var(--text-dim);">Preço</td>
                        <td style="padding: 15px 20px; text-align: center; font-size: 1.2rem; border-left: 1px solid var(--border-subtle);">R$ ${parseFloat(pA.preco).toFixed(2)}</td>
                        <td style="padding: 15px 20px; text-align: center; font-size: 1.2rem; border-left: 1px solid var(--border-subtle);">R$ ${parseFloat(pB.preco).toFixed(2)}</td>
                    </tr>
            `;

            if (todasChaves.length === 0) {
                html += `<tr><td colspan="3" style="padding: 30px; text-align: center; color: var(--text-muted);">Nenhuma especificação técnica detalhada disponível para estas peças.</td></tr>`;
            } else {
                todasChaves.forEach(chave => {
                    const valA = specsA[chave] || '<span style="color: var(--text-dim);">-</span>';
                    const valB = specsB[chave] || '<span style="color: var(--text-dim);">-</span>';
                    
                    html += `
                    <tr style="border-bottom: 1px solid var(--border-subtle); transition: background 0.2s;" onmouseover="this.style.background='var(--surface-3)'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 15px 20px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; font-size: 0.85rem;">${chave}</td>
                        <td style="padding: 15px 20px; text-align: center; border-left: 1px solid var(--border-subtle);">${valA}</td>
                        <td style="padding: 15px 20px; text-align: center; border-left: 1px solid var(--border-subtle);">${valB}</td>
                    </tr>`;
                });
            }

            html += `</tbody></table>`;
            resultadoDiv.innerHTML = html;
        });
}
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>