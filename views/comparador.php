<?php include __DIR__ . '/layout/header.php'; ?>

<main class="container" style="padding: 4rem 2rem; min-height: 80vh;">
    <div class="form-header">
        <h1><i class="fas fa-balance-scale"></i> Matriz de Comparação</h1>
        <p>Cruze até três equipamentos e veja qual leva vantagem em cada critério.</p>
    </div>

    <!-- Filtro de categoria -->
    <div class="form-group" style="max-width: 420px; margin-bottom: 2rem;">
        <label><i class="fas fa-filter"></i> Filtrar por categoria</label>
        <select id="filtro-categoria" class="input-modern">
            <option value="">Todas as categorias</option>
            <?php foreach($categorias as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
        </select>
        <small style="color: var(--text-dim);">Comparar itens da mesma categoria dá resultados muito mais úteis.</small>
    </div>

    <div class="compare-selectors">
        <?php
            $slots = [
                ['id' => 'a', 'rotulo' => 'Hardware A', 'cor' => 'var(--purple-light)'],
                ['id' => 'b', 'rotulo' => 'Hardware B', 'cor' => 'var(--cyan)'],
                ['id' => 'c', 'rotulo' => 'Hardware C (opcional)', 'cor' => 'var(--orange)'],
            ];
        ?>
        <?php foreach($slots as $slot): ?>
            <div class="form-group">
                <label style="color: <?php echo $slot['cor']; ?>;">
                    <i class="fas fa-microchip"></i> <?php echo $slot['rotulo']; ?>
                </label>
                <select id="select-prod-<?php echo $slot['id']; ?>" class="input-modern select-compare" onchange="compararHardwares()">
                    <option value="">Selecione um equipamento...</option>
                    <?php foreach($produtos as $p): ?>
                        <option value="<?php echo $p['id']; ?>" data-cat="<?php echo htmlspecialchars($p['categoria']); ?>">
                            <?php echo htmlspecialchars($p['categoria'] . ' — ' . $p['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="resultado-comparacao">
        <div class="compare-empty">
            <i class="fas fa-satellite-dish"></i>
            <h3>Aguardando Parâmetros</h3>
            <p>Selecione pelo menos dois componentes acima para iniciar a varredura.</p>
        </div>
    </div>
</main>

<script>
/* =========================================================
   Campos em que MENOR é melhor (latência, consumo, peso...).
   Em todos os outros, maior valor numérico vence.
   ========================================================= */
const MENOR_E_MELHOR = ['latencia', 'latência', 'resposta', 'tempo', 'consumo',
                        'peso', 'ruido', 'ruído', 'preco', 'preço', 'cl'];

function menorEhMelhor(chave) {
    const c = chave.toLowerCase();
    return MENOR_E_MELHOR.some(termo => c.includes(termo));
}

/** Extrai o primeiro número de um texto tipo "144 Hz" ou "1,5 GB" */
function extrairNumero(valor) {
    if (valor === undefined || valor === null) return null;
    const m = String(valor).replace(',', '.').match(/-?\d+(\.\d+)?/);
    return m ? parseFloat(m[0]) : null;
}

function formatarBRL(n) {
    return 'R$ ' + Number(n).toFixed(2).replace('.', ',');
}

/* Filtro de categoria nos três selects */
document.getElementById('filtro-categoria').addEventListener('change', function () {
    const cat = this.value;

    document.querySelectorAll('.select-compare').forEach(sel => {
        let precisaLimpar = false;

        sel.querySelectorAll('option[data-cat]').forEach(opt => {
            const visivel = (cat === '' || opt.dataset.cat === cat);
            opt.hidden = !visivel;
            if (!visivel && opt.selected) precisaLimpar = true;
        });

        if (precisaLimpar) sel.value = '';
    });

    compararHardwares();
});

function compararHardwares() {
    const ids = ['a', 'b', 'c']
        .map(s => document.getElementById('select-prod-' + s).value)
        .filter(v => v !== '');

    const div = document.getElementById('resultado-comparacao');

    // Impede comparar o mesmo produto duas vezes
    if (new Set(ids).size !== ids.length) {
        div.innerHTML = '<div class="compare-empty"><i class="fas fa-exclamation-triangle" style="color: var(--orange);"></i><h3>Produtos repetidos</h3><p>Escolha equipamentos diferentes em cada coluna.</p></div>';
        return;
    }

    if (ids.length < 2) {
        div.innerHTML = '<div class="compare-empty"><i class="fas fa-satellite-dish"></i><h3>Aguardando Parâmetros</h3><p>Selecione pelo menos dois componentes acima.</p></div>';
        return;
    }

    div.innerHTML = '<div class="compare-empty"><i class="fas fa-spinner fa-spin" style="color: var(--cyan);"></i><p style="margin-top:1rem;">Processando telemetria...</p></div>';

    fetch(`comparar.php?action=ajax_compare&ids=${ids.join(',')}`)
        .then(r => r.json())
        .then(data => {
            if (data.erro || !data.produtos || data.produtos.length < 2) {
                div.innerHTML = '<p class="error-message">Não foi possível carregar os produtos selecionados.</p>';
                return;
            }
            div.innerHTML = montarTabela(data.produtos);
        })
        .catch(() => {
            div.innerHTML = '<p class="error-message">Falha de comunicação com o servidor.</p>';
        });
}

function montarTabela(prods) {
    const n = prods.length;

    // ---- Reúne todas as chaves de specs de todos os produtos ----
    const todasChaves = [...new Set(prods.flatMap(p => Object.keys(p.especificacoes || {})))];

    // ---- Vencedor de preço (menor preço final) ----
    const precos = prods.map(p => p.preco_final);
    const menorPreco = Math.min(...precos);

    // ---- Pontuação por critério, para o custo-benefício ----
    const pontos = new Array(n).fill(0);
    let criteriosNumericos = 0;

    const vencedoresPorChave = {};

    todasChaves.forEach(chave => {
        const nums = prods.map(p => extrairNumero((p.especificacoes || {})[chave]));
        const validos = nums.filter(v => v !== null);

        // Só dá pra eleger vencedor se todos tiverem valor numérico
        if (validos.length !== n || new Set(validos).size === 1) {
            vencedoresPorChave[chave] = [];
            return;
        }

        criteriosNumericos++;
        const alvo = menorEhMelhor(chave) ? Math.min(...nums) : Math.max(...nums);

        const idxVencedores = [];
        nums.forEach((v, i) => {
            if (v === alvo) { idxVencedores.push(i); pontos[i] += 1; }
        });
        vencedoresPorChave[chave] = idxVencedores;
    });

    // ---- Custo-benefício: pontos ganhos por real gasto ----
    let idxCustoBeneficio = -1;
    if (criteriosNumericos > 0) {
        const scores = prods.map((p, i) => (pontos[i] / criteriosNumericos) / (p.preco_final || 1));
        const melhor = Math.max(...scores);
        if (melhor > 0) idxCustoBeneficio = scores.indexOf(melhor);
    }

    const larguraCol = Math.floor(76 / n);

    // ---- Cabeçalho ----
    let html = `<div class="compare-wrapper"><table class="compare-table">
        <thead><tr>
            <th style="width: 24%;">Especificação</th>`;

    prods.forEach((p, i) => {
        html += `<th style="width: ${larguraCol}%;">
            <img src="${p.imagem}" alt="${p.nome}">
            <div class="compare-nome">${p.nome}</div>
            <div class="compare-cat">${p.categoria}</div>
            ${i === idxCustoBeneficio ? '<span class="badge-cb"><i class="fas fa-award"></i> Melhor custo-benefício</span>' : ''}
        </th>`;
    });
    html += `</tr></thead><tbody>`;

    // ---- Linha de preço ----
    html += `<tr><td class="compare-label">Preço final</td>`;
    prods.forEach(p => {
        const venceu = p.preco_final === menorPreco;
        html += `<td class="${venceu ? 'cell-win' : ''}">
            ${p.desconto > 0 ? `<span class="preco-antigo">${formatarBRL(p.preco)}</span><br>` : ''}
            <span class="compare-preco">${formatarBRL(p.preco_final)}</span>
            ${p.desconto > 0 ? `<div class="compare-off">-${Math.round(p.desconto)}%</div>` : ''}
            ${venceu ? '<div class="tag-win"><i class="fas fa-check"></i> Mais barato</div>' : ''}
        </td>`;
    });
    html += `</tr>`;

    // ---- Linhas de especificações ----
    if (todasChaves.length === 0) {
        html += `<tr><td colspan="${n + 1}" class="compare-vazio">
            Nenhuma especificação técnica cadastrada para estes produtos.
        </td></tr>`;
    } else {
        todasChaves.forEach(chave => {
            const vencedores = vencedoresPorChave[chave] || [];

            html += `<tr><td class="compare-label">${chave}
                ${vencedores.length > 0 && menorEhMelhor(chave) ? '<i class="fas fa-arrow-down" title="Menor é melhor"></i>' : ''}
            </td>`;

            prods.forEach((p, i) => {
                const valor = (p.especificacoes || {})[chave];
                const temValor = valor !== undefined && valor !== null && valor !== '';
                const venceu = vencedores.includes(i);

                html += `<td class="${venceu ? 'cell-win' : ''}">
                    ${temValor ? valor : '<span class="sem-dado">—</span>'}
                    ${venceu ? '<i class="fas fa-crown crown"></i>' : ''}
                </td>`;
            });
            html += `</tr>`;
        });

        // ---- Linha de placar ----
        html += `<tr class="linha-placar"><td class="compare-label">Critérios vencidos</td>`;
        prods.forEach((p, i) => {
            html += `<td><strong>${pontos[i]}</strong> <span style="color: var(--text-dim);">/ ${criteriosNumericos}</span></td>`;
        });
        html += `</tr>`;
    }

    // ---- Ação: adicionar ao carrinho ----
    html += `<tr><td class="compare-label"></td>`;
    prods.forEach(p => {
        html += `<td>
            <form method="POST" action="carrinho.php">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="produto_id" value="${p.id}">
                <input type="hidden" name="quantidade" value="1">
                <button type="submit" class="btn-primary btn-sm"><i class="fas fa-cart-plus"></i> Adicionar</button>
            </form>
        </td>`;
    });
    html += `</tr>`;

    html += `</tbody></table></div>
        <p class="compare-nota">
            <i class="fas fa-info-circle"></i>
            A coroa marca o melhor valor de cada linha. Critérios como latência, consumo,
            peso e tempo de resposta são avaliados ao contrário: quanto menor, melhor.
            Linhas em que nem todos os produtos têm valor numérico não entram no placar.
        </p>`;

    return html;
}
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>