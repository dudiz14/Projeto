<?php
// Adicionar produto
if (isset($_POST['add_produto'])) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = str_replace(',', '.', $_POST['preco']);
    $quantidade = $_POST['quantidade'];

    $stmt = $conn->prepare("INSERT INTO produtos (id_empresa, nome, descricao, preco, quantidade) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $id_empresa, $nome, $descricao, $preco, $quantidade);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php?page=estoque");
    exit;
}

// Deletar produto
if (isset($_GET['del'])) {
    $id = $_GET['del'];
    $stmt = $conn->prepare("DELETE FROM produtos WHERE id=? AND id_empresa=?");
    $stmt->bind_param("ii", $id, $id_empresa);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php?page=estoque");
    exit;
}

// Buscar produtos
$stmt = $conn->prepare("SELECT * FROM produtos WHERE id_empresa=?");
$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$produtos = $stmt->get_result();
$stmt->close();
?>

<div class="content-header">
    <h2><i class="fas fa-box-open"></i> Controle de Estoque</h2>
</div>
<div class="card form-card">
    <h3>Adicionar Novo Produto</h3>
    <form method="POST" action="index.php?page=estoque">
        <div class="form-row">
            <div class="form-group">
                <label for="nome">Nome do Produto</label>
                <input type="text" id="nome" name="nome" placeholder="Ex: Batom Matte" required>
            </div>
            <div class="form-group">
                <label for="preco">Preço (R$)</label>
                <input type="text" id="preco" name="preco" placeholder="Ex: 29,90" required>
            </div>
             <div class="form-group">
                <label for="quantidade">Quantidade</label>
                <input type="number" id="quantidade" name="quantidade" placeholder="Ex: 100" required>
            </div>
        </div>
        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea name="descricao" id="descricao" placeholder="Detalhes do produto..."></textarea>
        </div>
        <button type="submit" name="add_produto" class="btn-primary">Adicionar Produto</button>
    </form>
</div>
<div class="card">
    <h3>Itens em Estoque</h3>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Preço</th>
                <th>Qtd.</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = $produtos->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td><?= htmlspecialchars($p['descricao']) ?></td>
                <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                <td><?= $p['quantidade'] ?></td>
                <td class="actions">
                    <a href="javascript:void(0);" onclick="addToCart(<?= $p['id'] ?>)" class="btn-icon" title="Adicionar ao Carrinho">
                        <i class="fas fa-cart-plus"></i>
                    </a>
                    <a href="index.php?page=estoque&del=<?= $p['id'] ?>" onclick="return confirm('Tem certeza?')" class="btn-icon btn-danger" title="Excluir">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php if ($produtos->num_rows === 0): ?>
                <tr>
                    <td colspan="5">Nenhum produto cadastrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>