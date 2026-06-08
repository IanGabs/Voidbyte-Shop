document.addEventListener('DOMContentLoaded', () => {
    // ==========================================
    // LÓGICA DE ADIÇÃO SILENCIOSA AO CARRINHO (AJAX)
    // ==========================================
    const formsAdicionar = document.querySelectorAll('form[action="carrinho.php"]');
    
    formsAdicionar.forEach(form => {
        const actionInput = form.querySelector('input[name="action"]');
        
        // Só intercepta se for o formulário de ADICIONAR
        if (actionInput && actionInput.value === 'add') {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); 

                const formData = new FormData(this);
                formData.append('ajax', '1'); 

                fetch('carrinho.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // 1. Atualiza a bolinha laranja no header
                        const contador = document.querySelector('.carrinho-contador');
                        if (contador) {
                            contador.textContent = data.total_itens;
                            contador.style.transform = 'scale(1.5)';
                            setTimeout(() => contador.style.transform = 'scale(1)', 200);
                        }

                        // 2. Aciona o Toast na tela
                        mostrarNotificacao(data.msg);
                    }
                })
                .catch(error => console.error('Falha de conexão na API do carrinho:', error));
            });
        }
    });

    // ==========================================
    // LÓGICA DE CARRINHO SILENCIOSA (AJAX GERAL)
    // ==========================================
    const formsCarrinho = document.querySelectorAll('form[action="carrinho.php"]');
    
    formsCarrinho.forEach(form => {
        form.addEventListener('submit', function(e) {
            const actionInput = this.querySelector('input[name="action"]');
            if (!actionInput) return;

            const action = actionInput.value;

            // Interceta as 3 ações (Adicionar, Atualizar quantidade e Remover)
            if (action === 'add' || action === 'update' || action === 'remove') {
                e.preventDefault(); 

                const formData = new FormData(this);
                formData.append('ajax', '1'); 

                fetch('carrinho.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // 1. Atualiza a bolinha laranja no header sempre
                        const contador = document.querySelector('.carrinho-contador');
                        if (contador) {
                            contador.textContent = data.total_itens;
                            contador.style.transform = 'scale(1.5)';
                            setTimeout(() => contador.style.transform = 'scale(1)', 200);
                        }

                        // Se for adição na home/produtos, mostra o aviso
                        if (action === 'add') {
                            mostrarNotificacao(data.msg);
                        } 
                        // Se for dentro da aba carrinho (alterando quantidade ou apagando)
                        else {
                            if (data.removido) {
                                // Se a quantidade chegou a 0 ou clicou em remover, apaga o bloco HTML
                                const itemCard = document.getElementById('item-carrinho-' + data.produto_id);
                                if (itemCard) {
                                    itemCard.style.transition = "all 0.3s";
                                    itemCard.style.opacity = "0";
                                    itemCard.style.transform = "translateX(50px)";
                                    setTimeout(() => itemCard.remove(), 300);
                                }
                            } else {
                                // Apenas atualiza o número da quantidade e o preço no cantinho
                                const spanQtd = document.getElementById('qtd-' + data.produto_id);
                                const divItemTotal = document.getElementById('item-total-' + data.produto_id);
                                
                                if (spanQtd) spanQtd.textContent = data.item_qtd;
                                if (divItemTotal) divItemTotal.textContent = 'R$ ' + data.item_total_formatado;
                            }

                            // Atualiza os resumos da fatura no canto direito
                            const cartSubtotals = document.querySelectorAll('.cart-subtotal-val');
                            const cartTotals = document.querySelectorAll('.total-price');
                            
                            cartSubtotals.forEach(el => el.textContent = 'R$ ' + data.total_carrinho_formatado);
                            cartTotals.forEach(el => el.textContent = 'R$ ' + data.total_carrinho_formatado);

                            // Se apagou o último item, dá um reload para mostrar a arte do fantasma ("O vácuo está presente...")
                            if (data.carrinho_vazio) {
                                setTimeout(() => window.location.reload(), 300);
                            }
                        }
                    }
                })
                .catch(error => console.error('Erro de API no carrinho:', error));
            }
        });
    });

    // ==========================================
    // MENU DROPDOWN DE PERFIL (Transferido do Footer)
    // ==========================================
    const profileTrigger = document.getElementById('profileTrigger');
    const profileDropdown = document.getElementById('profileDropdown');

    if (profileTrigger && profileDropdown) {
        profileTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!profileDropdown.contains(e.target) && !profileTrigger.contains(e.target)) {
                profileDropdown.classList.remove('show');
            }
        });
    }
});

// Função para criar a notificação visual na tela
function mostrarNotificacao(mensagem) {
    const toastAntigo = document.querySelector('.notificacao');
    if (toastAntigo) toastAntigo.remove();

    const toast = document.createElement('div');
    toast.className = 'notificacao';
    toast.innerHTML = `<i class="fas fa-check-circle" style="color: var(--cyan); margin-right: 8px;"></i> ${mensagem}`;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.4s ease';
        setTimeout(() => toast.remove(), 400); 
    }, 3000);
}