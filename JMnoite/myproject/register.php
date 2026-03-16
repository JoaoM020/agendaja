<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
    
    try {
        $stmt->execute([$nome, $email, $senha]);
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        $erro = "Email já registrado!";
    }
}
?>

<form method="POST">
  <h2>Registrar</h2>
  <input name="nome" placeholder="Nome" required><br>
  <input name="email" type="email" placeholder="Email" required><br>
  <input name="senha" type="password" placeholder="Senha" required><br>
  <button type="submit">Registrar</button>
  <?php if (isset($erro)) echo "<p style='color:red;'>$erro</p>"; ?>
</form>
<a href="login.php">Já tem conta? Login</a>
