(function () {
    'use strict';

    const form = document.getElementById('ia-setup-form');
    if (!form) return;

    const input = document.getElementById('ia-pedido');
    const btn   = document.getElementById('ia-btn');
    const out   = document.getElementById('ia-resultado');
    const SLOTS = window.SETUP_SLOTS || {};
    const TEXTO_BTN = btn.innerHTML;

    function formatarBRL(n) {
        return 'R$ ' + Number(n).toFixed(2).replace('.', ',');
    }

    // Texto simples (usa textContent, então nada vindo da IA vira HTML)
    function mostrarMensagem(classe, texto) {
        out.hidden = false;
        out.className = 'ia-resultado ' + classe;
        out.textContent = texto;
    }

    function mostrarSucesso(data) {
        out.hidden = false;
        out.className = 'ia-resultado ia-ok';
        out.innerHTML = '';

        const titulo = document.createElement('strong');
        titulo.textContent = 'Sugestão da IA — total ' + formatarBRL(data.total);
        out.appendChild(titulo);

        const explicacao = document.createElement('p');
        explicacao.textContent = data.explicacao || '';
        out.appendChild(explicacao);

        if (Array.isArray(data.avisos) && data.avisos.length > 0) {
            const lista = document.createElement('ul');
            data.avisos.forEach(a => {
                const li = document.createElement('li');
                li.textContent = a;
                lista.appendChild(li);
            });
            out.appendChild(lista);
        }

        const dica = document.createElement('small');
        dica.textContent = 'As peças foram preenchidas abaixo. Você pode trocar qualquer uma manualmente.';
        out.appendChild(dica);
    }

    // Preenche os selects e dispara "change" para o diagnóstico de compatibilidade rodar
    function aplicarPecas(pecas) {
        Object.keys(SLOTS).forEach(key => {
            const select = document.getElementById('slot-' + key);
            if (!select) return;

            const id = pecas && pecas[key] ? String(pecas[key]) : '';
            select.value = id;
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const pedido = input.value.trim();
        if (pedido.length < 5) {
            mostrarMensagem('ia-erro', 'Descreva um pouco mais o setup que você quer.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> A IA está montando...';
        mostrarMensagem('ia-carregando', 'Consultando a IA, isso pode levar alguns segundos...');

        const controle = new AbortController();
        const timer = setTimeout(() => controle.abort(), 90000);

        try {
            const resp = await fetch('montar-setup.php?action=ajax_ai', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ pedido }),
                signal: controle.signal
            });
            const data = await resp.json();

            if (!resp.ok || data.erro) {
                if (data.debug) console.warn('[IA]', data.debug);
                mostrarMensagem('ia-erro', data.erro || 'Não foi possível obter a sugestão.');
                return;
            }

            aplicarPecas(data.pecas);
            mostrarSucesso(data);
        } catch (err) {
            mostrarMensagem('ia-erro', err.name === 'AbortError'
                ? 'A IA demorou demais para responder. Tente novamente.'
                : 'Falha de comunicação com o servidor.');
        } finally {
            clearTimeout(timer);
            btn.disabled = false;
            btn.innerHTML = TEXTO_BTN;
        }
    });

    // Enter envia; Shift+Enter quebra linha
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            btn.click();
        }
    });

    // Botões de ideias rápidas
    document.querySelectorAll('.ia-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            input.value = this.dataset.pedido;
            input.focus();
        });
    });
})();