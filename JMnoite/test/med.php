<?php 
session_start();

if (!isset($_SESSION['meds'])) {
    $_SESSION['meds'] = [];
}

// Exclusão de item //
if (isset($_POST['delete_index'])) {
    $delete_index = (int) $_POST['delete_index'];
    if (isset($_SESSION['meds'][$delete_index])) {
        array_splice($_SESSION['meds'], $delete_index, 1);
    }
    header('Location: ' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
    exit();
}

// Adição de item //
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['med_name'])) {
    $med_name = trim($_POST['med_name'] ?? '');
    $dosage = trim($_POST['dosage'] ?? '');
    $schedule_date = trim($_POST['schedule_date'] ?? '');

    if ($med_name !== '' && $dosage !== '' && $schedule_date !== '') {
        $_SESSION['meds'][] = [
            'name' => htmlspecialchars($med_name, ENT_QUOTES, 'UTF-8'),
            'dosage' => htmlspecialchars($dosage, ENT_QUOTES, 'UTF-8'),
            'schedule_date' => htmlspecialchars($schedule_date, ENT_QUOTES, 'UTF-8'),
        ];
        header('Location: ' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
        exit();
    }
}

function buy_link($med_name) {
    $remedios = [
        "Dipirona" => "https://www.saojoaofarmacias.com.br/dipirona-sodica-generico-medley-500mg-10-comprimidos--venda-sob-prescricao---av---10087408/p",
        "Paracetamol" => "https://www.saojoaofarmacias.com.br/paracetamol-750mg-20-comprimidos-generico-globo-10040354/p",
        "Ibuprofeno" => "https://www.saojoaofarmacias.com.br/ibuprofeno-600mg-20-comprimidos-13308/p",
        "Amoxicilina" => "https://www.saojoaofarmacias.com.br/amoxicilina-500mg-generico-cimed-21-capsulas-duras-10091320/p",
        "Losartana" => "https://www.saojoaofarmacias.com.br/losartana-generico-ems-50mg-30-comprimidos-revestidos-10094058/p",
        "Sinvastatina" => "https://www.saojoaofarmacias.com.br/sinvastatina-40mg-generico-cimed-30-comprimidos-revestidos-100016263/p"];
    foreach ($remedios as $nome => $url) {
        if (stripos($med_name, $nome) !== false) {
            return $url;
        }
    }

    $clean_name = iconv('UTF-8', 'ASCII//TRANSLIT', $med_name);
    $clean_name = preg_replace('/[^a-zA-Z0-9\s]/', '', $clean_name);
    $query = urlencode(trim($clean_name));
    return "https://www.saojoaofarmacias.com.br/busca?q=$query";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Remédio Já</title>
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
    nav {
      background: #1976d2;
      display: flex;
      justify-content: center;
    }
    nav a {
      color: white;
      padding: 1rem;
      text-decoration: none;
      font-weight: bold;
    }
    nav a:hover {
      background-color: #1565c0;
    }
    .container {
      max-width: 900px;
      margin: auto;
      padding: 1rem;
    }
    form {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
      margin-bottom: 2rem;
    }
    .form-group {
      margin-bottom: 1.5rem;
    }
    input[type="text"],
    input[type="datetime-local"] {
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
    }
    .meds-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1rem;
    }
    .med-card, .med-item {
      background: white;
      padding: 1rem;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .med-schedule { color: #1565c0; font-weight: bold; }
    .buy-link {
      display: inline-block;
      margin-top: 0.5rem;
      background-color: #2e7d32;
      color: white;
      padding: 0.4rem 0.8rem;
      border-radius: 4px;
      text-decoration: none;
    }
    .buy-link:hover {
      background-color: #1b5e20;
    }
    .remove-button {
      background-color: #c62828;
      padding: 0.4rem 0.8rem;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    h2 {
      color: #0d47a1;
    }
  </style>
</head>
<body>
  <header>Remédio Já</header>
  <nav>
    <a href="#lista">Minha Lista</a>
    <a href="#todos">Todos os Remédios</a>
  </nav>

  <div class="container" id="lista">
    <h2>Agendar Medicamento</h2>
    <form method="post">
      <div class="form-group">
        <label for="med_name">Nome do Medicamento</label>
        <input type="text" id="med_name" name="med_name" required>
      </div>
      <div class="form-group">
        <label for="dosage">Dosagem / Instruções</label>
        <input type="text" id="dosage" name="dosage" required>
      </div>
      <div class="form-group">
        <label for="schedule_date">Data e Hora</label>
        <input type="datetime-local" id="schedule_date" name="schedule_date" required>
      </div>
      <button type="submit">Adicionar</button>
    </form>

    <?php if (!empty($_SESSION['meds'])): ?>
      <h2>Minha Lista</h2>
      <section class="meds-list">
        <?php foreach ($_SESSION['meds'] as $index => $med): ?>
          <div class="med-card">
            <h3><?= htmlspecialchars($med['name']) ?></h3>
            <p class="med-schedule">Agendado para: <?= date('d/m/Y H:i', strtotime($med['schedule_date'])) ?></p>
            <p>Dosagem: <?= htmlspecialchars($med['dosage']) ?></p>
            <a class="buy-link" href="<?= buy_link($med['name']) ?>" target="_blank">Comprar</a>
            <form method="post" style="margin-top:10px;">
              <input type="hidden" name="delete_index" value="<?= $index ?>">
              <button class="remove-button" type="submit">Remover</button>
            </form>
          </div>
        <?php endforeach; ?>
      </section>
    <?php else: ?>
      <p>Nenhum medicamento agendado.</p>
    <?php endif; ?>
  </div>

  <div class="container" id="todos">
    <h2>Adicionar Rápido</h2>
    <div class="meds-list">
      <?php
      $remedios = [
        "Dipirona" => "https://www.saojoaofarmacias.com.br/dipirona-sodica-generico-medley-500mg-10-comprimidos--venda-sob-prescricao---av---10087408/p",
        "Paracetamol" => "https://www.saojoaofarmacias.com.br/paracetamol-750mg-20-comprimidos-generico-globo-10040354/p",
        "Ibuprofeno" => "https://www.saojoaofarmacias.com.br/ibuprofeno-600mg-20-comprimidos-13308/p",
        "Amoxicilina" => "https://www.saojoaofarmacias.com.br/amoxicilina-500mg-generico-cimed-21-capsulas-duras-10091320/p",
        "Losartana" => "https://www.saojoaofarmacias.com.br/busca?q=losartana",
        "Sinvastatina" => "https://www.saojoaofarmacias.com.br/busca?q=sinvastatina"
      ];
      foreach ($remedios as $nome => $link): ?>
        <div class="med-item">
          <?= htmlspecialchars($nome) ?><br>
          <a class="buy-link" href="<?= htmlspecialchars($link) ?>" target="_blank">Comprar</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
