<?php include __DIR__ . '/layout/header.php'; ?>

<main style="padding-top: 2rem; min-height: 80vh;">
    <div class="container">
        <div class="form-header" style="margin-bottom: 2rem; text-align: left;">
            <a href="produtos.php" style="color: var(--text-muted); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Voltar ao catálogo
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 3rem; margin-bottom: 5rem;">
            
            <!-- Coluna da Imagem -->
            <div style="background: #121215; padding: 2rem; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color, #2d2d35);">
                <img src="<?php echo htmlspecialchars($produto['imagem'] ?? ''); ?>" alt="<?php echo htmlspecialchars($produto['nome'] ?? ''); ?>" style="max-width: 100%; height: auto; border-radius: 8px;">
            </div>

            <!-- Coluna das Informações -->
            <div>
                <span class="produto-card-category" style="display: inline-block; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($produto['categoria'] ?? ''); ?>
                </span>
                
                <h1 style="margin-bottom: 1rem; font-size: 2.5rem;"><?php echo htmlspecialchars($produto['nome'] ?? ''); ?></h1>
                
                <?php 
                $precoBase = $produto['preco'] ?? 0;
                $desconto = $produto['desconto'] ?? 0;
                $precoFinal = $precoBase - ($precoBase * ($desconto / 100));
                ?>

                <div class="price-tag" style="margin-bottom: 2rem; justify-content: flex-start;">
                    <?php if ($desconto > 0): ?>
                        <span class="preco-antigo" style="font-size: 1.2rem;">R$ <?php echo number_format($precoBase, 2, ',', '.'); ?></span>
                    <?php endif; ?>
                    <div class="preco-final" style="font-size: 2.5rem;">R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?></div>
                </div>

                <p style="color: var(--text-muted); line-height: 1.6; margin-bottom: 2rem; font-size: 1.1rem;">
                    <?php echo nl2br(htmlspecialchars($produto['descricao'] ?? '')); ?>
                </p>

                <h3 style="border-bottom: 1px solid var(--border-color, #333); padding-bottom: 0.5rem; margin-bottom: 1rem;">Especificações Técnicas</h3>
                <ul style="list-style: none; padding: 0; margin-bottom: 3rem; color: var(--text-muted);">
                    <?php 
                    $specs = $produto['especificacoes'] ?? '';
                    $specsArray = json_decode($specs, true);
                    
                    if (is_array($specsArray) && !empty($specsArray)): 
                        foreach ($specsArray as $chave => $valor): ?>
                            <li style="padding: 0.8rem 0; border-bottom: 1px solid var(--border-color, #2d2d35);">
                                <strong style="color: #fff;"><?php echo htmlspecialchars(ucfirst($chave)); ?>:</strong> <?php echo htmlspecialchars($valor); ?>
                            </li>
                        <?php endforeach; 
                    elseif (!empty($specs)): ?>
                        <li style="padding: 0.8rem 0; border-bottom: 1px solid var(--border-color, #2d2d35);"><?php echo nl2br(htmlspecialchars($specs)); ?></li>
                    <?php else: ?>
                        <li style="padding: 0.8rem 0;">Nenhuma especificação técnica cadastrada.</li>
                    <?php endif; ?>
                </ul>

                <!-- Formulário Corrigido (Exatamente igual ao do seu catálogo) -->
                <form method="POST" action="carrinho.php" style="width: 100%;">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                    <input type="hidden" name="quantidade" value="1">
                    <button type="submit" class="btn-adicionar-carrinho" style="width: 100%; padding: 1.2rem; font-size: 1.2rem; display: flex; justify-content: center; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-cart-plus"></i> Adicionar ao Carrinho
                    </button>
                </form>
            </div>
        </div>

        <!-- ================= COMENTÁRIOS ================= -->
        <?php
        $comentarios = $comentarios ?? [];
        $logado = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
        $ehAdmin = $logado && ($_SESSION['user_type'] ?? '') === 'admin';
        $comentarioErro = $_SESSION['comentario_erro'] ?? null;
        unset($_SESSION['comentario_erro']);
        ?>
        <section id="comentarios" class="comentarios-section">
            <h2 class="comentarios-titulo">
                <i class="fas fa-comments"></i> Comentários
                <span class="comentarios-count"><?php echo count($comentarios); ?></span>
            </h2>

            <?php if ($comentarioErro): ?>
                <div class="comentario-alerta">
                    <i class="fas fa-triangle-exclamation"></i> <?php echo htmlspecialchars($comentarioErro); ?>
                </div>
            <?php endif; ?>

            <?php if ($logado): ?>
                <form method="POST" action="detalhes.php?id=<?php echo (int)$produto['id']; ?>" class="comentario-form">
                    <input type="hidden" name="action" value="comentar">
                    <label for="comentario-texto">Deixe seu comentário</label>
                    <textarea id="comentario-texto" name="texto" rows="4" maxlength="1000" required
                              placeholder="O que você achou deste produto?"></textarea>
                    <div class="comentario-form-rodape">
                        <small><span id="comentario-contador">0</span>/1000</small>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-paper-plane"></i> Publicar
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="comentario-login-aviso">
                    <i class="fas fa-lock"></i>
                    <a href="detalhes.php?id=<?php echo (int)$produto['id']; ?>&action=login_comentar">Faça login</a>
                    para comentar neste produto.
                </div>
            <?php endif; ?>

            <div class="comentarios-lista">
                <?php if (empty($comentarios)): ?>
                    <p class="comentarios-vazio">Ainda não há comentários. Seja o primeiro a comentar!</p>
                <?php else: ?>
                    <?php foreach ($comentarios as $c): ?>
                        <article class="comentario-item">
                            <img class="comentario-foto"
                                 src="<?php echo htmlspecialchars($c['foto'] ?: 'https://cdn-icons-png.flaticon.com/512/149/149071.png'); ?>"
                                 alt="">
                            <div class="comentario-corpo">
                                <div class="comentario-cabecalho">
                                    <strong><?php echo htmlspecialchars($c['nome']); ?></strong>
                                    <small><?php echo date('d/m/Y \à\s H:i', strtotime($c['data_criacao'])); ?></small>

                                    <?php if ($logado && ($ehAdmin || (int)$c['usuario_id'] === (int)$_SESSION['user_id'])): ?>
                                        <form method="POST" action="detalhes.php?id=<?php echo (int)$produto['id']; ?>"
                                              class="comentario-excluir-form"
                                              onsubmit="return confirm('Excluir este comentário?');">
                                            <input type="hidden" name="action" value="excluir_comentario">
                                            <input type="hidden" name="comentario_id" value="<?php echo (int)$c['id']; ?>">
                                            <button type="submit" class="comentario-excluir" title="Excluir comentário">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <p class="comentario-texto"><?php echo nl2br(htmlspecialchars($c['texto'])); ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <style>
            .comentarios-section { margin-bottom: 5rem; max-width: 900px; }
            .comentarios-titulo {
                font-family: 'Orbitron', sans-serif; font-size: 1.4rem; margin-bottom: 1.5rem;
                padding-bottom: 0.8rem; border-bottom: 1px solid var(--border, #2d2d35);
                display: flex; align-items: center; gap: 0.7rem;
            }
            .comentarios-titulo i { color: var(--cyan-light, #22d3ee); }
            .comentarios-count {
                font-size: 0.85rem; padding: 2px 10px; border-radius: 999px;
                background: var(--purple-dim, rgba(124,58,237,.25)); color: var(--text, #e2e8f0);
            }
            .comentario-alerta {
                background: rgba(239,68,68,.12); border: 1px solid var(--red, #ef4444);
                color: var(--text, #e2e8f0); padding: 12px 16px; border-radius: var(--radius, 8px); margin-bottom: 1rem;
            }
            .comentario-form, .comentario-login-aviso {
                background: var(--surface-2, #0e0e24); border: 1px solid var(--border, rgba(124,58,237,.3));
                border-radius: var(--radius-lg, 12px); padding: 1.2rem; margin-bottom: 2rem;
            }
            .comentario-login-aviso { color: var(--text-muted, #94a3b8); }
            .comentario-login-aviso a { color: var(--cyan-light, #22d3ee); }
            .comentario-form label {
                display: block; font-weight: 600; font-size: 0.82rem; letter-spacing: 0.08em;
                text-transform: uppercase; color: var(--text-muted, #94a3b8); margin-bottom: 8px;
            }
            .comentario-form textarea {
                width: 100%; resize: vertical; min-height: 100px; padding: 13px 16px;
                background: var(--surface-3, #13132d); border: 1px solid var(--border, rgba(124,58,237,.3));
                border-radius: var(--radius, 8px); color: var(--text, #e2e8f0);
                font-family: 'Rajdhani', sans-serif; font-size: 1rem; transition: all 0.25s;
            }
            .comentario-form textarea:focus {
                outline: none; border-color: var(--purple, #7c3aed); background: var(--surface-4, #1a1a3c);
                box-shadow: 0 0 0 3px var(--purple-dim, rgba(124,58,237,.25));
            }
            .comentario-form-rodape { display: flex; justify-content: space-between; align-items: center; margin-top: 0.8rem; }
            .comentario-form-rodape small { color: var(--text-dim, #475569); }
            .comentarios-lista { display: flex; flex-direction: column; gap: 1rem; }
            .comentarios-vazio { color: var(--text-muted, #94a3b8); text-align: center; padding: 2rem 0; }
            .comentario-item {
                display: flex; gap: 1rem; padding: 1.1rem; background: var(--surface-2, #0e0e24);
                border: 1px solid var(--border-subtle, rgba(255,255,255,.05)); border-radius: var(--radius-lg, 12px);
            }
            .comentario-foto { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1px solid var(--border, rgba(124,58,237,.3)); }
            .comentario-corpo { flex: 1; min-width: 0; }
            .comentario-cabecalho { display: flex; align-items: center; gap: 0.8rem; flex-wrap: wrap; margin-bottom: 0.4rem; }
            .comentario-cabecalho small { color: var(--text-dim, #475569); }
            .comentario-texto { color: var(--text, #e2e8f0); line-height: 1.5; margin: 0; word-break: break-word; }
            .comentario-excluir-form { margin-left: auto; }
            .comentario-excluir { background: none; border: none; color: var(--text-dim, #475569); cursor: pointer; font-size: 0.95rem; transition: color 0.2s; }
            .comentario-excluir:hover { color: var(--red, #ef4444); }
        </style>

        <script>
            (function () {
                var campo = document.getElementById('comentario-texto');
                var contador = document.getElementById('comentario-contador');
                if (campo && contador) {
                    campo.addEventListener('input', function () { contador.textContent = campo.value.length; });
                }
            })();
        </script>
    </div>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>