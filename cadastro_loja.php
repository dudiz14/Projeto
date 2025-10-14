<?php
include("conexao.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];

    // Previne SQL Injection
    $stmt = $conn->prepare("INSERT INTO empresas (nome_loja, email, senha, telefone, endereco) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nome, $email, $senha, $telefone, $endereco);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit;
    } else {
        $erro = "Erro ao cadastrar loja! O email talvez já exista.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Cadastro de Loja</title>
<link rel="stylesheet" href="style.css"> </head>
<body>
<div class="login-container">
<h2>Cadastrar Loja</h2>
<form method="POST" class="login-form">
    <div class="form-group"><input type="text" name="nome" placeholder="Nome da Loja" required></div>
    <div class="form-group"><input type="email" name="email" placeholder="Email" required></div>
    <div class="form-group"><input type="password" name="senha" placeholder="Senha" required></div>
    <div class="form-group"><input type="text" name="telefone" placeholder="Telefone" required></div>
    <div class="form-group"><input type="text" name="endereco" placeholder="Endereço" required></div>
    <button type="submit" class="btn-primary">Cadastrar</button>
    <?php if(isset($erro)): ?>
        <p class="erro"><?= $erro ?></p>
    <?php endif; ?>
    <p class="login-footer">Já tem conta? <a href="login.php">Faça login</a></p>
</form>
</div>
</body>
</html>