<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nome'] = $usuario['nome'];
        header("Location: index.php");
        exit();
    } else {
        $erro = "Credenciais inválidas";
    }
}
?>

<form method="POST">
  <h2>Login</h2>
  <input name="email" type="email" placeholder="Email" required><br>
  <input name="senha" type="password" placeholder="Senha" required><br>
  <button type="submit">Entrar</button>
  <?php if (isset($erro)) echo "<p style='color:red;'>$erro</p>"; ?>
</form>
<a href="register.php">Criar conta</a>
