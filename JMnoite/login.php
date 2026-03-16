<?php
session_start();

// Redireciona se já estiver logado
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$login_error = '';
$usuarios_arquivo = 'usuarios.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if ($email === '' || $senha === '') {
        $login_error = 'Preencha todos os campos.';
    } elseif (file_exists($usuarios_arquivo)) {
        $usuarios = json_decode(file_get_contents($usuarios_arquivo), true);

        // Verifica se usuário existe e senha está correta
        if (isset($usuarios[$email]) && password_verify($senha, $usuarios[$email])) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_email'] = $email;
            header("Location: index.php");
            exit();
        } else {
            $login_error = 'E-mail ou senha inválidos.';
        }
    } else {
        $login_error = 'Nenhum usuário registrado ainda.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Remédio Já</title>

 
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f4f8;
      margin: 0;
      color: #333;
    }
    header {
      background-color: #0d47a1;
      color: white;
      padding: 1rem;
      text-align: center;
      font-size: 2rem;
    }
    .container {
      max-width: 400px;
      margin: 3rem auto;
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .container h3 {
      text-align: center;
      color: #0d47a1;
      margin-bottom: 1.5rem;
    }
    .input-box {
      position: relative;
      margin-bottom: 2rem;
    }
    .input-box input {
      width: 90%;
      padding: 0.75rem 2.5rem 0.75rem 0.75rem;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .input-box i {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: #666;
    }
    .forgot-link {
      text-align: right;
      margin-bottom: 1rem;
    }
    .forgot-link a {
      color: #0d47a1;
      font-size: 0.9rem;
      text-decoration: none;
    }
    .forgot-link a:hover {
      text-decoration: underline;
    }
    .btn {
      width: 100%;
      padding: 0.75rem;
      background-color: #0d47a1;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 1rem;
    }
    .error {
      color: #c62828;
      margin-bottom: 1rem;
      text-align: center;
    }
    p.link {
      text-align: center;
      margin-top: 1rem;
    }
    p.link a {
      color: #0d47a1;
      text-decoration: none;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <header>Remédio Já</header>
  <div class="container">
    <form method="post">
      <h3>Login</h3>
      <?php if ($login_error): ?>
        <div class="error"><?= htmlspecialchars($login_error) ?></div>
      <?php endif; ?>
      <div class="input-box">
        <input type="email" name="email" placeholder="E-mail" required>
        <i class="bx bxs-user"></i>
      </div>
      <div class="input-box">
        <input type="password" name="senha" placeholder="Senha" required>
        <i class="bx bxs-lock-alt"></i>
      </div>
      <div class="forgot-link">
        <a href="#">Esqueceu a senha?</a>
      </div>
      <button type="submit" class="btn">Entrar</button>
      <p class="link">
        Não tem uma conta? <a href="register.php">Criar Conta</a>
      </p>
    </form>
  </div>
</body>
</html>
