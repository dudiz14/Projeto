<?php
session_start();
include("conexao.php");

if (!isset($_SESSION['id_empresa'])) {
    header("Location: login.php");
    exit;
}

$id_empresa = $_SESSION['id_empresa'];

$page = isset($_GET['page']) ? $_GET['page'] : 'estoque';
$page_file = "{$page}.php";

// Verifica se o arquivo da página existe para evitar erros
if (!file_exists($page_file)) {
    $page_file = "estoque.php"; // Página padrão
}

// NOVIDADE: Calcula a contagem de itens no carrinho
$cart_count = isset($_SESSION['carrinho']) ? count($_SESSION['carrinho']) : 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle - <?= htmlspecialchars($_SESSION['nome_loja']) ?></title>
    <link rel="stylesheet" href="style.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3><?= htmlspecialchars($_SESSION['nome_loja']) ?></h3>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php?page=estoque" class="<?= ($page == 'estoque') ? 'active' : '' ?>">
                    <i class="fas fa-box-open"></i> Estoque
                </a>
                <a href="index.php?page=carrinho" class="<?= ($page == 'carrinho') ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i> Carrinho
                    <span id="cart-count" class="cart-count"><?= $cart_count ?></span>
                </a>
                <a href="index.php?page=relatorio_vendas" class="<?= ($page == 'relatorio_vendas') ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i> Relatório de Vendas
                </a>
                <a href="index.php?page=gerenciar_empresa" class="<?= ($page == 'gerenciar_empresa') ? 'active' : '' ?>">
                    <i class="fas fa-building"></i> Gerenciar Empresa
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </aside>

        <main class="main-content">
            <?php include($page_file); // Carrega a página da mesma pasta ?>
        </main>
    </div>

    <script src="script.js"></script> 
</body>
</html>