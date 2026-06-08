<?php include __DIR__ . '/layout/header.php'; ?>

<main>
    <section class="hero">
        <div class="hero-content">
            <span class="hero-badge">Acesso Liberado v2.0</span>
            <h1>Equipe seu setup no <span>Vazio</span></h1>
            <p>Hardware focado em performance, design minimalista e temas escuros. O ambiente perfeito para desenvolvedores e entusiastas.</p>
            <div class="hero-actions">
                <a href="produtos.php" class="btn-primary"><i class="fas fa-rocket"></i> Explorar Hardware</a>
                <a href="#destaques" class="btn-secondary">Ver Destaques</a>
            </div>
        </div>
    </section>

    <section class="categories-strip">
        <div class="categories-grid">
            <a href="produtos.php?cat=teclados" class="category-chip">
                <i class="fas fa-keyboard"></i>
                <span>Teclados Mecânicos</span>
            </a>
            <a href="produtos.php?cat=mouses" class="category-chip">
                <i class="fas fa-mouse"></i>
                <span>Mouses Cyber</span>
            </a>
            <a href="produtos.php?cat=monitores" class="category-chip">
                <i class="fas fa-desktop"></i>
                <span>Monitores</span>
            </a>
            <a href="produtos.php?cat=acessorios" class="category-chip">
                <i class="fas fa-gamepad"></i>
                <span>Controles & Acessórios</span>
            </a>
            <a href="produtos.php?cat=componentes" class="category-chip">
                <i class="fas fa-memory"></i>
                <span>Componentes Internos</span>
            </a>
        </div>
    </section>

    <section id="destaques" class="products-section">
        <div class="container">
            <h2 class="section-title">Hardware em <span>Destaque</span></h2>
            
            <div class="produtos-grid">
                <?php 
                // Simulação de dados para você testar o Front-end antes do Controller/Banco estarem prontos
                $produtos = $produtos ?? [
                    ['id' => 1, 'nome' => 'Teclado Mecânico Void Minimalist', 'descricao' => 'Switches silenciosos, chassi de alumínio escovado.', 'preco' => 599.90, 'preco_final' => 499.90, 'desconto' => 15, 'imagem' => 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=500&auto=format&fit=crop&q=60', 'categoria' => 'Teclados'],
                    ['id' => 2, 'nome' => 'Mousepad Neural Cyber', 'descricao' => 'Superfície de microfibra escura para precisão absoluta.', 'preco' => 129.90, 'preco_final' => 129.90, 'desconto' => 0, 'imagem' => 'https://images.unsplash.com/photo-1615663245857-ac1eeb536674?w=500&auto=format&fit=crop&q=60', 'categoria' => 'Acessórios'],
                    ['id' => 3, 'nome' => 'Monitor Dark Mode 144Hz', 'descricao' => 'Painel IPS com calibração profunda de pretos.', 'preco' => 1499.90, 'preco_final' => 1499.90, 'desconto' => 0, 'imagem' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500&auto=format&fit=crop&q=60', 'categoria' => 'Monitores'],
                ];

                foreach($produtos as $produto): 
                ?>
                    <div class="produto-card">
                        <?php if ($produto['desconto'] > 0): ?>
                            <div class="produto-card-badge"><?php echo intval($produto['desconto']); ?>% OFF</div>
                        <?php endif; ?>
                        
                        <a href="detalhes.php?id=<?php echo $produto['id']; ?>" class="produto-card-img-wrapper">
                            <img src="<?php echo $produto['imagem']; ?>" alt="<?php echo $produto['nome']; ?>">
                        </a>
                        
                        <div class="produto-card-body">
                            <span class="produto-card-category"><?php echo $produto['categoria'] ?? 'Hardware'; ?></span>
                            <h3><?php echo $produto['nome']; ?></h3>
                            
                            <div class="price-tag">
                                <?php 
                                // Calcula o preço final aqui mesmo para evitar erros
                                $precoBase = $produto['preco'];
                                $desconto = $produto['desconto'] ?? 0;
                                $precoFinal = $precoBase - ($precoBase * ($desconto / 100));
                                ?>
                                <?php if ($desconto > 0): ?>
                                    <span class="preco-antigo">R$ <?php echo number_format($precoBase, 2, ',', '.'); ?></span>
                                <?php endif; ?>
    
                                <div class="preco-final">R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?></div>
                            </div>
                            
                            <p class="desc"><?php echo $produto['descricao']; ?></p>
                            
                            <button class="btn-adicionar-carrinho" data-produto-id="<?php echo $produto['id']; ?>">
                                <i class="fas fa-cart-plus"></i> Adicionar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>