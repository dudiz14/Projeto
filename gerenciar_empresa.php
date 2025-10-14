<?php
// Atualizar dados
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];

    $stmt = $conn->prepare("UPDATE empresas SET nome_loja=?, telefone=?, endereco=? WHERE id=?");
    $stmt->bind_param("sssi", $nome, $telefone, $endereco, $id_empresa);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['nome_loja'] = $nome;

    header("Location: index.php?page=gerenciar_empresa&success=1");
    exit;
}

// Buscar dados
$stmt = $conn->prepare("SELECT * FROM empresas WHERE id=?");
$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$empresa = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<div class="content-header">
    <h2><i class="fas fa-building"></i> Gerenciar Dados da Empresa</h2>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert-success">Dados atualizados com sucesso!</div>
<?php endif; ?>

<div class="card form-card">
    <form method="POST" action="index.php?page=gerenciar_empresa">
        <div class="form-group">
            <label>Nome da Loja</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($empresa['nome_loja']) ?>" required>
        </div>
        <div class="form-group">
            <label>Telefone</label>
            <input type="text" name="telefone" value="<?= htmlspecialchars($empresa['telefone']) ?>" required>
        </div>
        <div class="form-group">
            <label>Endereço</label>
            <input type="text" name="endereco" value="<?= htmlspecialchars($empresa['endereco']) ?>" required>
        </div>
        <button type="submit" class="btn-primary">Salvar Alterações</button>
    </form>
</div>