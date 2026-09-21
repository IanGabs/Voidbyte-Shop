/* =============================================
   VOIDBYTE — Construtor de Especificações
   Substitui o campo de JSON cru por linhas chave/valor
   ============================================= */

// Sugestões de campos por tipo de produto.
// Servem só como atalho: o admin pode digitar qualquer chave.
const SUGESTOES_SPEC = {
    teclado:     ['Switch', 'Layout', 'Conexão', 'Iluminação', 'Material', 'Taxa de Resposta (ms)'],
    mouse:       ['DPI', 'Sensor', 'Botões', 'Peso (g)', 'Conexão', 'Taxa de Resposta (ms)'],
    monitor:     ['Tamanho (pol)', 'Resolução', 'Painel', 'Taxa de Atualização (Hz)', 'Tempo de Resposta (ms)', 'Conexões'],
    cpu:         ['Núcleos', 'Threads', 'Clock Base (GHz)', 'Clock Turbo (GHz)', 'Socket', 'Consumo (W)', 'Cache (MB)'],
    gpu:         ['VRAM (GB)', 'Tipo de Memória', 'Clock (MHz)', 'Consumo (W)', 'Conexões', 'Ray Tracing'],
    ram:         ['Capacidade (GB)', 'Tipo', 'Frequência (MHz)', 'Latência (CL)', 'Módulos'],
    motherboard: ['Socket', 'Chipset', 'Formato', 'Slots RAM', 'Tipo de RAM', 'Slots M.2'],
    storage:     ['Capacidade (GB)', 'Tipo', 'Interface', 'Leitura (MB/s)', 'Escrita (MB/s)'],
    psu:         ['Potência (W)', 'Certificação', 'Modular', 'Formato'],
    cooler:      ['Tipo', 'Sockets Suportados', 'Ruído (dB)', 'Tamanho do Fan (mm)'],
    gabinete:    ['Formato', 'Baias', 'Fans Inclusos', 'Lateral', 'Peso (kg)'],
    headset:     ['Driver (mm)', 'Conexão', 'Microfone', 'Surround', 'Peso (g)'],
    cadeira:     ['Peso Suportado (kg)', 'Material', 'Reclinação', 'Apoio Lombar'],
    roteador:    ['Padrão Wi-Fi', 'Velocidade (Mbps)', 'Bandas', 'Portas LAN'],
    nobreak:     ['Potência (VA)', 'Autonomia (min)', 'Tomadas', 'Entrada']
};

/**
 * Adiciona uma linha de especificação ao container.
 */
function addSpecRow(chave = '', valor = '') {
    const container = document.getElementById('specs-container');
    if (!container) return;

    const linha = document.createElement('div');
    linha.className = 'spec-row';

    linha.innerHTML = `
        <input type="text" name="spec_chave[]" class="input-modern" list="spec-sugestoes"
               placeholder="Ex: Frequência (Hz)" value="${escapeAttr(chave)}">
        <input type="text" name="spec_valor[]" class="input-modern"
               placeholder="Ex: 144" value="${escapeAttr(valor)}">
        <button type="button" class="btn-danger btn-sm spec-remove" title="Remover">
            <i class="fas fa-times"></i>
        </button>
    `;

    linha.querySelector('.spec-remove').addEventListener('click', () => linha.remove());
    container.appendChild(linha);
}

function escapeAttr(txt) {
    return String(txt).replace(/"/g, '&quot;').replace(/</g, '&lt;');
}

/**
 * Preenche o datalist com as sugestões do tipo escolhido.
 */
function atualizarSugestoes(tipo) {
    const datalist = document.getElementById('spec-sugestoes');
    if (!datalist) return;

    const lista = SUGESTOES_SPEC[tipo] || [];
    datalist.innerHTML = lista.map(s => `<option value="${s}">`).join('');
}

/**
 * Cria automaticamente as linhas sugeridas para o tipo escolhido,
 * mas só se o admin ainda não tiver preenchido nada.
 */
function preencherSugestoes(tipo) {
    const container = document.getElementById('specs-container');
    if (!container) return;

    const preenchidas = [...container.querySelectorAll('input[name="spec_chave[]"]')]
        .filter(i => i.value.trim() !== '');

    if (preenchidas.length > 0) return;

    container.innerHTML = '';
    const lista = SUGESTOES_SPEC[tipo] || [];

    if (lista.length === 0) {
        addSpecRow();
        return;
    }
    lista.forEach(chave => addSpecRow(chave, ''));
}

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('specs-container');
    if (!container) return;

    // Liga o botão "adicionar campo"
    const btnAdd = document.getElementById('btn-add-spec');
    if (btnAdd) btnAdd.addEventListener('click', () => addSpecRow());

    // Liga os botões de remover das linhas que já vieram do PHP
    container.querySelectorAll('.spec-remove').forEach(btn => {
        btn.addEventListener('click', () => btn.closest('.spec-row').remove());
    });

    // Reage à troca de tipo no formulário de cadastro
    const selectTipo = document.querySelector('select[name="tipo"]');
    if (selectTipo) {
        selectTipo.addEventListener('change', (e) => {
            atualizarSugestoes(e.target.value);
            preencherSugestoes(e.target.value);
        });
    }

    // Garante pelo menos uma linha em branco
    if (container.children.length === 0) addSpecRow();
});