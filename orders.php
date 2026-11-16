<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['auth_msg'] = "Faça login para ver seus pedidos.";
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/model/OrderModel.php';
$orderModel = new OrderModel();
$pedidos = $orderModel->getOrdersByUserId((int) $_SESSION['user_id']);

$title = 'Data Logs (Pedidos) | Voidbyte Shop';
include __DIR__ . '/views/layout/header.php';
?>

<main class="container profile-container">
    <div class="form-header profile-header">
        <h1><i class="fas fa-history"></i> Data Logs (Pedidos)</h1>
        <p>Histórico completo de transações e hardwares adquiridos na rede.</p>
    </div>

    <?php if (empty($pedidos)): ?>
        <div class="compare-empty profile-empty-state">
            <i class="fas fa-shopping-bag profile-empty-icon"></i>
            <h3>Nenhum registro de pedido encontrado</h3>
            <p class="profile-empty-text">Explore nosso catálogo e monte o seu setup de alta performance.</p>
            <a href="index.php" class="btn-primary">Ver Produtos</a>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php foreach ($pedidos as $ped): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <span class="order-id">Pedido #<?php echo $ped['id']; ?></span>
                            <span class="order-date">
                                <i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y H:i', strtotime($ped['data_pedido'])); ?>
                            </span>
                        </div>
                        <div>
                            <span class="order-status">
                                <?php echo htmlspecialchars($ped['status'] ?? 'Processando'); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Lista de itens do pedido -->
                    <div class="order-items-list">
                        <?php foreach ($ped['itens'] as $item): ?>
                            <div class="order-item-row">
                                <div class="order-item-info">
                                    <img src="<?php echo htmlspecialchars($item['imagem_produto']); ?>" alt="" class="order-item-img">
                                    <span><?php echo htmlspecialchars($item['nome_produto']); ?> <strong class="order-item-qty">(x<?php echo $item['quantidade']; ?>)</strong></span>
                                </div>
                                <span class="order-item-price">R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Resumo Financeiro -->
                    <div class="order-summary-box">
                        <div class="order-summary-line">
                            <span class="order-summary-label">Subtotal:</span>
                            <span>R$ <?php echo number_format($ped['subtotal'], 2, ',', '.'); ?></span>
                        </div>
                        <div class="order-summary-line">
                            <span class="order-summary-label">Frete:</span>
                            <span><?php echo $ped['frete'] > 0 ? 'R$ ' . number_format($ped['frete'], 2, ',', '.') : 'Grátis'; ?></span>
                        </div>
                        <?php if (isset($ped['desconto_cupom']) && $ped['desconto_cupom'] > 0): ?>
                            <div class="order-summary-discount">
                                <span>Cupom aplicado (<?php echo htmlspecialchars($ped['cupom_usado']); ?>):</span>
                                <span>- R$ <?php echo number_format($ped['desconto_cupom'], 2, ',', '.'); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="order-summary-total">
                            <span>Total pago:</span>
                            <span class="order-summary-total-value">R$ <?php echo number_format($ped['total'], 2, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/views/layout/footer.php'; ?>