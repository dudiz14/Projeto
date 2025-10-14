<?php
$carrinho = isset($_SESSION['carrinho']) && is_array($_SESSION['carrinho']) ? $_SESSION['carrinho'] : [];
$total_carrinho = 0;
$produtos_no_carrinho = [];

if (!empty($carrinho)) {
    // ... (o código PHP para buscar produtos no carrinho continua o mesmo)
    $ids = array_keys($carrinho);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids)) . 'i';
    $params = array_merge($ids, [$id_empresa]);

    $stmt = $conn->prepare("SELECT * FROM produtos WHERE id IN ($placeholders) AND id_empresa = ?");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){
        $produtos_no_carrinho[] = $row;
        $total_carrinho += $row['preco'] * $carrinho[$row['id']];
    }
    $stmt->close();
}
?>

<div class="content-header">
    <h2><i class="fas fa-shopping-cart"></i> Carrinho de Compras</h2>
</div>

<div class="card">
    <h3>Itens Selecionados</h3>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Preço Unitário</th>
                <th>Quantidade</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($produtos_no_carrinho)): ?>
                <tr><td colspan="4">O carrinho está vazio. Adicione itens do estoque!</td></tr>
            <?php else: ?>
                <?php foreach ($produtos_no_carrinho as $produto): ?>
                <tr>
                    <td><?= htmlspecialchars($produto['nome']) ?></td>
                    <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                    <td><?= $carrinho[$produto['id']] ?></td>
                    <td>R$ <?= number_format($produto['preco'] * $carrinho[$produto['id']], 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Total</th>
                <th id="valorTotalCarrinho" data-total="<?= $total_carrinho ?>">R$ <?= number_format($total_carrinho, 2, ',', '.') ?></th>
            </tr>
        </tfoot>
    </table>
</div>

<div class="card">
    <h3>Finalizar Venda</h3>
    <div class="payment-footer">
        <button type="button" class="btn-primary btn-large" onclick="abrirModalPagamento()" <?= empty($produtos_no_carrinho) ? 'disabled' : '' ?>>
            Finalizar Venda
        </button>
    </div>
</div>


<div id="modalPagamento" class="modal-overlay">
    <div class="modal-content">
        <span class="close-button" onclick="fecharModalPagamento()">&times;</span>
        <h2>Registrar Pagamento</h2>
        <h3 class="modal-total">Total da Venda: R$ <span id="modalTotalValor"><?= number_format($total_carrinho, 2, ',', '.') ?></span></h3>
        
        <form id="formPagamento" action="finalizar_venda.php" method="POST">
            <input type="hidden" name="valor_total_venda" value="<?= $total_carrinho ?>">
            
            <div id="pagamentos-container">
                </div>

            <button type="button" class="btn-add-payment" onclick="adicionarPagamento()">
                <i class="fas fa-plus"></i> Adicionar outra forma de pagamento
            </button>
            
            <div class="modal-summary">
                Valor Restante: <span id="valorRestante">R$ <?= number_format($total_carrinho, 2, ',', '.') ?></span>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="fecharModalPagamento()">Cancelar</button>
                <button type="submit" id="btnConfirmarPagamento" class="btn-primary" disabled>Confirmar Venda</button>
            </div>
        </form>
    </div>
</div>