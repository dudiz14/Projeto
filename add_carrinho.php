<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produto'])) {
    $id_produto = (int)$_POST['id_produto'];

    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }

    if (isset($_SESSION['carrinho'][$id_produto])) {
        $_SESSION['carrinho'][$id_produto]++;
    } else {
        $_SESSION['carrinho'][$id_produto] = 1;
    }

    $total_items = array_sum($_SESSION['carrinho']);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'total_items' => $total_items]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>