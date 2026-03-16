<?php
session_start();

$usuarios_arquivo = 'usuarios.json';
$erros = [];

// Verifica e cria o arquivo JSON se não existir
if (!file_exists($usuarios_arquivo)) {
    file_put_contents($usuarios_arquivo, json_encode([]));
}

// Cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if ($email === '' || $senha === '') {
        $erros[] = "Preencha todos os campos.";
    } else {
        $usuarios = json_decode(file_get_contents($usuarios_arquivo), true);

        // Verifica se o e-mail já existe
        if (isset($usuarios[$email])) {
            $erros[] = "Este e-mail já está registrado.";
        } else {
            // Registra o usuário (senha armazenada com hash)
            $usuarios[$email] = password_hash($senha, PASSWORD_DEFAULT);
            file_put_contents($usuarios_arquivo, json_encode($usuarios, JSON_PRETTY_PRINT));

            // Redireciona para login
            $_SESSION['cadastro_sucesso'] = true;
            header("Location: login.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cadastrar - Remédio Já</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f4f8;
      margin: 0;
    }
    header {
      background-color: #0d47a1;
      color: white;
      padding: 1rem;
      text-align: center;
      font-size: 2rem;
    }
    .register-container {
      max-width: 400px;
      margin: 3rem auto;
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .form-group {
      margin-bottom: 1.5rem;
    }
    label {
      display: block;
      margin-bottom: 0.5rem;
    }
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 0.75rem;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    button {
      padding: 0.75rem 2rem;
      background-color: #0d47a1;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      width: 100%;
    }
    .error {
      color: #c62828;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
  <header>Remédio Já</header>

  <div class="register-container">
    <h2>Criar Conta</h2>
    <?php foreach ($erros as $erro): ?>
      <div class="error"><?= htmlspecialchars($erro) ?></div>
    <?php endforeach; ?>
    <form method="post">
      <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required placeholder="usuario@exemplo.com">
      </div>
      <div class="form-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required placeholder="Crie uma senha">
      </div>
      <button type="submit">Registrar</button>
    </form>
    <p style="text-align:center; margin-top:1rem;">
      Já tem uma conta? <a href="login.php">Entrar</a>
    </p>
  </div>
</body>
</html>
