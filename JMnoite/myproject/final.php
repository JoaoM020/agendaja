<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Adicionar medicamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome_med'])) {
    $nome = $_POST['nome_med'];
    $dosagem = $_POST['dosagem'];
    $data = $_POST['data'];

    $stmt = $pdo->prepare("INSERT INTO medicamentos (usuario_id, nome, dosagem, horario) VALUES (?, ?, ?, ?)");
    $stmt->execute([$usuario_id, $nome, $dosagem, $data]);
    header("Location: index.php");
    exit();
}

// Deletar medicamento
if (isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM medicamentos WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$_POST['delete_id'], $usuario_id]);
    header("Location: index.php");
    exit();
}

// Buscar medicamentos
$stmt = $pdo->prepare("SELECT * FROM medicamentos WHERE usuario_id = ? ORDER BY horario ASC");
$stmt->execute([$usuario_id]);
$meds = $stmt->fetchAll();

// Gerar link de compra
function buy_link($med_name) {
    $remedios = [
        "Dipirona" => "https://www.saojoaofarmacias.com.br/busca?q=dipirona",
        "Paracetamol" => "https://www.saojoaofarmacias.com.br/busca?q=paracetamol",
        "Ibuprofeno" => "https://www.saojoaofarmacias.com.br/busca?q=ibuprofeno",
        "Amoxicilina" => "https://www.saojoaofarmacias.com.br/busca?q=amoxicilina",
        "Losartana" => "https://www.saojoaofarmacias.com.br/busca?q=losartana",
        "Sinvastatina" => "https://www.saojoaofarmacias.com.br/busca?q=sinvastatina"
    ];

    foreach ($remedios as $nome => $url) {
        if (stripos($med_name, $nome) !== false) {
            return $url;
        }
    }

    return "https://www.saojoaofarmacias.com.br/busca?q=" . urlencode($med_name);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Remédio Já</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #eef1f5;
      margin: 0;
    }
    header {
      background-color: #0d47a1;
      color: white;
      padding: 1rem;
      text-align: center;
      font-size: 2rem;
    }
    .container {
      max-width: 900px;
      margin: 2rem auto;
      padding: 2rem;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 0 8px rgba(0,0,0,0.1);
    }
    h2 {
      color: #0d47a1;
    }
    form {
      margin-bottom: 2rem;
    }
    input, button {
      padding: 0.7rem;
      margin-top: 0.5rem;
      width: 100%;
      box-sizing: border-box;
      font-size: 1rem;
    }
    input[type="datetime-local"] {
      padding: 0.6rem;
    }
    button {
      background-color: #0d47a1;
      color: white;
      border: none;
      margin-top: 1rem;
      cursor: pointer;
    }
    button:hover {
      background-color: #0b3c91;
    }
    .med-card {
      border: 1px solid #ddd;
      padding: 1rem;
      border-radius: 6px;
      margin-bottom: 1rem;
    }
    .buy-link {
      color: white;
      background-color: #2e7d32;
      padding: 0.4rem 0.8rem;
      text-decoration: none;
      border-radius: 4px;
      display: inline-block;
      margin-top: 0.5rem;
    }
    .buy-link:hover {
      background-color: #1b5e20;
    }
    .logout {
      text-align: right;
      margin: 1rem;
    }
    .logout a {
      color: red;
      text-decoration: none;
    }
  </style>
</head>
<body>

<header>Remédio Já</header>
<div class="logout">
  <strong><?= htmlspecialchars($_SESSION['nome']) ?></strong> | <a href="logout.php">Sair</a>
</div>

<div class="container">
  <h2>Agendar Medicamento</h2>
  <form method="POST">
    <label>Nome do Medicamento</label>
    <input type="text" name="nome_med" required>

    <label>Dosagem / Instruções</label>
    <input type="text" name="dosagem" required>

    <label>Data e Hora</label>
    <input type="datetime-local" name="data" required>

    <button type="submit">Adicionar</button>
  </form>

  <h2>Meus Medicamentos</h2>
  <?php if ($meds): ?>
    <?php foreach ($meds as $med): ?>
      <div class="med-card">
        <strong><?= htmlspecialchars($med['nome']) ?></strong><br>
        Dosagem: <?= htmlspecialchars($med['dosagem']) ?><br>
        Data: <?= date('d/m/Y H:i', strtotime($med['horario'])) ?><br>
        <a class="buy-link" href="<?= buy_link($med['nome']) ?>" target="_blank">Comprar</a>
        <form method="POST" style="margin-top: 10px;">
          <input type="hidden" name="delete_id" value="<?= $med['id'] ?>">
          <button type="submit" style="background-color: #c62828;">Remover</button>
        </form>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p>Você ainda não agendou medicamentos.</p>
  <?php endif; ?>
</div>
</body>
</html>
