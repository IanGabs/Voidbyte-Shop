(function () {
    'use strict';

    const DATA  = window.SETUP_DATA  || {};
    const SLOTS = window.SETUP_SLOTS || {};

    // Peça selecionada em cada slot (objeto do produto ou null)
    const selecionados = {};
    Object.keys(SLOTS).forEach(key => { selecionados[key] = null; });

    /* ---------- Utilitários de leitura de especificação ---------- */

    function normalizar(txt) {
        return String(txt || '')
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .toLowerCase().trim();
    }

    /** Procura uma spec cuja chave contenha algum dos termos dados */
    function buscarSpec(produto, ...termos) {
        if (!produto || !produto.especificacoes) return null;
        const termosNorm = termos.map(normalizar);

        for (const chave in produto.especificacoes) {
            const chaveNorm = normalizar(chave);
            if (termosNorm.some(t => chaveNorm.includes(t))) {
                return produto.especificacoes[chave];
            }
        }
        return null;
    }

    function extrairNumero(valor) {
        if (valor === null || valor === undefined) return null;
        const m = String(valor).replace(',', '.').match(/-?\d+(\.\d+)?/);
        return m ? parseFloat(m[0]) : null;
    }

    function formatarBRL(n) {
        return 'R$ ' + Number(n).toFixed(2).replace('.', ',');
    }

    /* ---------- Regras de compatibilidade ---------- */

    const RANKING_FORMATO = { 'itx': 1, 'mini itx': 1, 'matx': 2, 'micro atx': 2, 'atx': 3, 'eatx': 4, 'e atx': 4 };

    function rankFormato(texto) {
        const n = normalizar(texto);
        for (const chave in RANKING_FORMATO) {
            if (n.includes(chave)) return RANKING_FORMATO[chave];
        }
        return null;
    }

    function checarSocket(cpu, mobo) {
        if (!cpu || !mobo) return null;
        const sCpu  = buscarSpec(cpu, 'socket');
        const sMobo = buscarSpec(mobo, 'socket');

        if (!sCpu || !sMobo) {
            return { tipo: 'aviso', texto: 'Socket da CPU ou da placa-mãe não informado — confira manualmente antes de comprar.' };
        }
        if (normalizar(sCpu) === normalizar(sMobo)) {
            return { tipo: 'ok', texto: `Socket compatível (${sCpu}).` };
        }
        return { tipo: 'erro', texto: `Socket incompatível: CPU usa ${sCpu}, placa-mãe usa ${sMobo}.` };
    }

    function checarRam(ram, mobo) {
        if (!ram || !mobo) return null;
        const tipoRam  = buscarSpec(ram, 'tipo');
        const tipoMobo = buscarSpec(mobo, 'tipo de ram') || buscarSpec(mobo, 'tipo ram');

        const checagens = [];

        if (tipoRam && tipoMobo) {
            if (normalizar(tipoRam) === normalizar(tipoMobo)) {
                checagens.push({ tipo: 'ok', texto: `Tipo de memória compatível (${tipoRam}).` });
            } else {
                checagens.push({ tipo: 'erro', texto: `Memória ${tipoRam} não é suportada pela placa-mãe (aceita ${tipoMobo}).` });
            }
        } else {
            checagens.push({ tipo: 'aviso', texto: 'Tipo de memória não informado em um dos produtos — confira manualmente.' });
        }

        const modulos = extrairNumero(buscarSpec(ram, 'modulos') || buscarSpec(ram, 'módulos'));
        const slots   = extrairNumero(buscarSpec(mobo, 'slots ram') || buscarSpec(mobo, 'slots de ram'));

        if (modulos !== null && slots !== null) {
            if (modulos <= slots) {
                checagens.push({ tipo: 'ok', texto: `Placa-mãe tem slots suficientes (${modulos}/${slots} usados).` });
            } else {
                checagens.push({ tipo: 'erro', texto: `A memória precisa de ${modulos} slots, mas a placa-mãe só tem ${slots}.` });
            }
        }

        return checagens;
    }

    function checarEnergia(cpu, gpu, psu) {
        if (!cpu || !psu) return null;

        const MARGEM_OUTROS_COMPONENTES = 150; // placa-mãe, RAM, armazenamento, fans...

        const consumoCpu = extrairNumero(buscarSpec(cpu, 'consumo')) || 0;
        const consumoGpu = gpu ? (extrairNumero(buscarSpec(gpu, 'consumo')) || 0) : 0;
        const potenciaFonte = extrairNumero(buscarSpec(psu, 'potencia') || buscarSpec(psu, 'potência'));

        if (!consumoCpu && !consumoGpu) {
            return { tipo: 'aviso', texto: 'Consumo (W) não informado na CPU/GPU — não foi possível calcular a demanda de energia.' };
        }
        if (potenciaFonte === null) {
            return { tipo: 'aviso', texto: 'Potência da fonte não informada — confira manualmente se ela atende o setup.' };
        }

        const total = consumoCpu + consumoGpu + MARGEM_OUTROS_COMPONENTES;

        if (potenciaFonte < total) {
            return { tipo: 'erro', texto: `Fonte insuficiente: setup consome ~${total}W, fonte entrega ${potenciaFonte}W.` };
        }
        if (potenciaFonte < total * 1.2) {
            return { tipo: 'aviso', texto: `Fonte no limite: ~${total}W estimados contra ${potenciaFonte}W. Recomenda-se folga de 20%.` };
        }
        return { tipo: 'ok', texto: `Fonte com folga confortável (~${total}W estimados / ${potenciaFonte}W disponíveis).` };
    }

    function checarFormato(mobo, gabinete) {
        if (!mobo || !gabinete) return null;

        const formatoMobo     = buscarSpec(mobo, 'formato');
        const formatoGabinete = buscarSpec(gabinete, 'formato');

        if (!formatoMobo || !formatoGabinete) {
            return { tipo: 'aviso', texto: 'Formato da placa-mãe ou do gabinete não informado — confira manualmente.' };
        }

        const rankMobo = rankFormato(formatoMobo);
        const rankGabinete = rankFormato(formatoGabinete);

        if (rankMobo === null || rankGabinete === null) {
            return { tipo: 'aviso', texto: `Não foi possível interpretar os formatos (${formatoMobo} / ${formatoGabinete}) — confira manualmente.` };
        }
        if (rankGabinete >= rankMobo) {
            return { tipo: 'ok', texto: `Placa-mãe ${formatoMobo} cabe no gabinete ${formatoGabinete}.` };
        }
        return { tipo: 'erro', texto: `Placa-mãe ${formatoMobo} NÃO cabe em um gabinete ${formatoGabinete}.` };
    }

    function checarCooler(cpu, cooler) {
        if (!cpu || !cooler) return null;

        const socketCpu = buscarSpec(cpu, 'socket');
        const suportados = buscarSpec(cooler, 'sockets suportados') || buscarSpec(cooler, 'socket');

        if (!socketCpu || !suportados) {
            return { tipo: 'info', texto: 'Compatibilidade do cooler com o socket não pôde ser verificada automaticamente.' };
        }
        if (normalizar(suportados).includes(normalizar(socketCpu))) {
            return { tipo: 'ok', texto: `Cooler compatível com o socket ${socketCpu}.` };
        }
        return { tipo: 'aviso', texto: `Cooler pode não suportar o socket ${socketCpu} — confira a lista de sockets do fabricante.` };
    }

    /* ---------- Renderização ---------- */

    function iconePorTipo(tipo) {
        switch (tipo) {
            case 'ok':    return 'fa-check-circle';
            case 'erro':  return 'fa-times-circle';
            case 'aviso': return 'fa-exclamation-triangle';
            default:      return 'fa-info-circle';
        }
    }

    function renderizarResumo() {
        const listaEl = document.getElementById('setup-itens-lista');
        const totalEl = document.getElementById('setup-total');
        const btnCarrinho = document.getElementById('btn-add-carrinho-setup');

        const itens = Object.keys(selecionados)
            .map(key => ({ key, produto: selecionados[key] }))
            .filter(i => i.produto !== null);

        if (itens.length === 0) {
            listaEl.innerHTML = '<p class="setup-vazio-msg">Nenhuma peça selecionada ainda.</p>';
            totalEl.textContent = formatarBRL(0);
            btnCarrinho.disabled = true;
            return;
        }

        let total = 0;
        listaEl.innerHTML = itens.map(({ key, produto }) => {
            total += produto.preco_final;
            const label = SLOTS[key] ? SLOTS[key].label : key;
            return `
                <div class="setup-item-linha">
                    <div>
                        <span class="setup-item-cat">${label}</span>
                        <span class="setup-item-nome">${produto.nome}</span>
                    </div>
                    <span class="setup-item-preco">${formatarBRL(produto.preco_final)}</span>
                </div>
            `;
        }).join('');

        totalEl.textContent = formatarBRL(total);
        btnCarrinho.disabled = false;
    }

    function renderizarCompatibilidade() {
        const container = document.getElementById('setup-checagens');
        const statusGeral = document.getElementById('setup-status-geral');

        const cpu      = selecionados.cpu;
        const mobo     = selecionados.motherboard;
        const ram      = selecionados.ram;
        const gpu      = selecionados.gpu;
        const psu      = selecionados.psu;
        const gabinete = selecionados.gabinete;
        const cooler   = selecionados.cooler;

        let checagens = [];

        const socketCheck = checarSocket(cpu, mobo);
        if (socketCheck) checagens.push(socketCheck);

        const ramCheck = checarRam(ram, mobo);
        if (ramCheck) checagens = checagens.concat(ramCheck);

        const energiaCheck = checarEnergia(cpu, gpu, psu);
        if (energiaCheck) checagens.push(energiaCheck);

        const formatoCheck = checarFormato(mobo, gabinete);
        if (formatoCheck) checagens.push(formatoCheck);

        const coolerCheck = checarCooler(cpu, cooler);
        if (coolerCheck) checagens.push(coolerCheck);

        if (checagens.length === 0) {
            statusGeral.className = 'setup-status-geral status-neutro';
            statusGeral.innerHTML = '<i class="fas fa-info-circle"></i> Selecione mais peças para começar a análise.';
            container.innerHTML = '';
            return;
        }

        container.innerHTML = checagens.map(c => `
            <div class="setup-check setup-check-${c.tipo}">
                <i class="fas ${iconePorTipo(c.tipo)}"></i>
                <span>${c.texto}</span>
            </div>
        `).join('');

        const temErro  = checagens.some(c => c.tipo === 'erro');
        const temAviso = checagens.some(c => c.tipo === 'aviso');

        // Slots obrigatórios ainda vazios
        const faltando = Object.keys(SLOTS).filter(k => SLOTS[k].obrigatorio && !selecionados[k]);

        if (temErro) {
            statusGeral.className = 'setup-status-geral status-erro';
            statusGeral.innerHTML = '<i class="fas fa-times-circle"></i> Configuração incompatível — corrija os itens em vermelho.';
        } else if (faltando.length > 0) {
            const nomes = faltando.map(k => SLOTS[k].label).join(', ');
            statusGeral.className = 'setup-status-geral status-aviso';
            statusGeral.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Setup incompleto — faltam: ${nomes}.`;
        } else if (temAviso) {
            statusGeral.className = 'setup-status-geral status-aviso';
            statusGeral.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Compatível, mas com pontos para conferir manualmente.';
        } else {
            statusGeral.className = 'setup-status-geral status-ok';
            statusGeral.innerHTML = '<i class="fas fa-check-circle"></i> Setup compatível e pronto para compra!';
        }
    }

    function atualizarTudo() {
        renderizarResumo();
        renderizarCompatibilidade();
    }

    /* ---------- Eventos ---------- */

    document.querySelectorAll('.setup-select').forEach(select => {
        select.addEventListener('change', function () {
            const key = this.dataset.slot;
            const id = this.value;

            if (!id) {
                selecionados[key] = null;
            } else {
                const lista = DATA[key] || [];
                selecionados[key] = lista.find(p => String(p.id) === String(id)) || null;
            }

            atualizarTudo();
        });
    });

    document.getElementById('btn-add-carrinho-setup').addEventListener('click', async function () {
        const itens = Object.values(selecionados).filter(p => p !== null);
        if (itens.length === 0) return;

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adicionando...';

        try {
            for (const produto of itens) {
                const form = new URLSearchParams();
                form.append('action', 'add');
                form.append('produto_id', produto.id);
                form.append('quantidade', 1);

                await fetch('carrinho.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: form
                });
            }
            window.location.href = 'carrinho.php';
        } catch (e) {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-cart-plus"></i> Adicionar tudo ao carrinho';
            alert('Não foi possível adicionar todos os itens ao carrinho. Tente novamente.');
        }
    });

    atualizarTudo();
})();