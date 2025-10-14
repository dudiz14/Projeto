<?php
// Nova query com JOIN para buscar os nomes e quantidades dos produtos vendidos
$stmt = $conn->prepare("
    SELECT 
        v.data_venda,
        v.valor_total,
        v.forma_pagamento,
        p.nome AS nome_produto,
        iv.quantidade AS quantidade_vendida
    FROM 
        vendas AS v
    JOIN 
        itens_venda AS iv ON v.id = iv.id_venda
    JOIN 
        produtos AS p ON iv.id_produto = p.id
    WHERE 
        v.id_empresa = ? 
    ORDER BY 
        v.data_venda DESC
");

$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$vendas_detalhadas = $stmt->get_result();
$stmt->close();
?>

<div class="content-header">
    <h2><i class="fas fa-chart-line"></i> Relatório de Vendas</h2>
</div>
<div class="card">
    <table class="styled-table">
        <thead>
            <tr>
                <th>Data da Venda</th>
                <th>Produto Vendido</th>
                <th>Quantidade</th>
                <th>Forma de Pagamento</th>
                <th>Valor Total (da Venda)</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($vendas_detalhadas->num_rows > 0): ?>
                <?php while ($v = $vendas_detalhadas->fetch_assoc()): ?>
                <tr>
                    <td><?= date("d/m/Y H:i:s", strtotime($v['data_venda'])) ?></td>
                    <td><?= htmlspecialchars($v['nome_produto']) ?></td>
                    <td><?= $v['quantidade_vendida'] ?></td>
                    <td><?= htmlspecialchars($v['forma_pagamento']) ?></td>
                    <td>R$ <?= number_format($v['valor_total'], 2, ',', '.') ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">Nenhuma venda registrada.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>