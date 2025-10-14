<?php
session_start();
include("conexao.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Previne SQL Injection
    $stmt = $conn->prepare("SELECT * FROM empresas WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $empresa = $result->fetch_assoc();
        if (password_verify($senha, $empresa['senha'])) {
            $_SESSION['id_empresa'] = $empresa['id'];
            $_SESSION['nome_loja'] = $empresa['nome_loja'];
            header("Location: index.php");
            exit;
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "Email não encontrado!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Login - Gerenciador</title>
<link rel="stylesheet" href="style.css"> </head>
<body>
<div class="login-container">
    <h2>Login da Loja</h2>
    <form method="POST" class="login-form">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="seu@email.com" required>
        </div>
        <div class="form-group">
            <label>Senha</label>
            <input type="password" name="senha" placeholder="Sua senha" required>
        </div>
        <button type="submit" class="btn-primary">Entrar</button>
        <?php if(isset($erro)): ?>
            <p class="erro"><?= $erro ?></p>
        <?php endif; ?>
        <p class="login-footer">Não tem conta? <a href="cadastro_loja.php">Cadastre-se</a></p>
    </form>
</div>
</body>
</html>