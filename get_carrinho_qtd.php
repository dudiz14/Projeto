<?php
session_start();

$total_items = 0;
if (isset($_SESSION['carrinho'])) {
    $total_items = array_sum($_SESSION['carrinho']);
}

header('Content-Type: application/json');
echo json_encode(['total_items' => $total_items]);
?>