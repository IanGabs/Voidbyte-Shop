<?php
// Simulação da página atual para ativar os links
$current_page = basename($_SERVER['PHP_SELF']);
?>
<?php
// Calcula dinamicamente quantos itens existem no carrinho
$totalItensCarrinho = 0;
if (isset($_SESSION['carrinho'])) {
    foreach ($_SESSION['carrinho'] as $item) {
        $totalItensCarrinho += $item['quantidade'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Voidbyte Shop | Hardware & Cyber Gear'; ?></title>
    <link rel="icon" type="image/png" href="assets/imgs/favicon-removebg-preview.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">
            <i class="fas fa-microchip" style="color: var(--cyan); font-size: 1.8rem; filter: drop-shadow(0 0 8px var(--purple));"></i>
            <span class="logo-text">VOIDBYTE</span>
        </a>
        <nav>
            <ul>
                <li>
                    <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active-link' : ''; ?>">
                        <i class="fas fa-home"></i> Nexus
                    </a>
                </li>
                <li>
                    <a href="produtos.php" class="<?php echo ($current_page == 'produtos.php') ? 'active-link' : ''; ?>">
                        <i class="fas fa-server"></i> Hardware
                    </a>
                </li>
                <li>
                    <a href="comparar.php" class="<?php echo ($current_page == 'comparar.php') ? 'active-link' : ''; ?>">
                        <i class="fas fa-balance-scale"></i> Comparar Hardware
                    </a>
                </li>
                <li>
                    <a href="montar-setup.php" class="<?php echo ($current_page == 'montar-setup.php') ? 'active-link' : ''; ?>">
                        <i class="fas fa-tools"></i> Montar Setup
                    </a>
                </li>
                <li>
                    <a href="carrinho.php" class="<?php echo ($current_page == 'carrinho.php') ? 'active-link' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i> Cart 
                        <span class="carrinho-contador"><?php echo $totalItensCarrinho; ?></span>
                    </a>
                </li>

                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <li class="profile-menu-container">
                        <div class="profile-trigger" id="profileTrigger">
                            <?php $foto_header = $_SESSION['user_photo'] ?? 'https://cdn-icons-png.flaticon.com/512/149/149071.png'; ?>
                            <img src="<?php echo $foto_header; ?>" alt="Perfil" class="profile-img">
                        </div>
                        
                        <div class="dropdown-menu" id="profileDropdown">
                            <div class="user-info-header">
                                <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                                <small><?php echo ($_SESSION['user_type'] === 'admin') ? 'System Admin' : 'System User'; ?></small>
                            </div>
                            <hr>
                            <?php if ($_SESSION['user_type'] === 'admin'): ?>
                                <a href="admin.php" style="color: var(--orange) !important;">
                                    <i class="fas fa-terminal"></i> Terminal Admin
                                </a>
                            <?php endif; ?>
                            <a href="profile.php"><i class="fas fa-user-shield"></i> Protocolos de Conta</a>
                            <a href="orders.php"><i class="fas fa-history"></i> Data Logs (Pedidos)</a>
                            <hr>
                            <a href="logout.php" class="logout-link"><i class="fas fa-power-off"></i> Desconectar</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="login.php" class="<?php echo ($current_page == 'login.php') ? 'active-link' : ''; ?>">
                            <i class="fas fa-sign-in-alt"></i> Access
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>