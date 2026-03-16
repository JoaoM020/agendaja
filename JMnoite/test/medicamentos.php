<?php
// Inicializa a lista de medicamentos na sessão, se não estiver definida
?>
if (!isset($_SESSION['meds'])) {
    $_SESSION['meds'] = [];
}

// Processa o formulário de adição de medicamentos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

// Gera link de compra com base no nome do medicamento
function buy_link($med_name) {
    $query = htmlspecialchars(urlencode($med_name), ENT_QUOTES, 'UTF-8');
    return "https://www.saojoaofarmacias.com.br/busca?q=$query";
}
?>"
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
      color: #333;
      margin: 0;
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
      max-width: 800px;
      margin: auto;
      padding: 1rem;
    }
    h2 {
      color: #0d47a1;
    }
    .med-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 1rem;
    }
    .med-item {
      background: white;
      padding: 1rem;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      text-align: center;
      cursor: pointer;
      font-size: 1.1rem;
    }
    .med-item:hover {
      background-color: #e3f2fd;
    }
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
  </style>
</head>
<body>
  <header>Remédio Já</header>
  <nav>
    <a href="#lista">Minha Lista</a>
    <a href="#todos">Todos os Remédios</a>
  </nav>
  <div class="container" id="todos">
    <h2>Adicionar Remédios Rápidos</h2>
    <div class="med-list">
      <div class="med-item">Dipirona <br><a class="buy-link" href="https://www.saojoaofarmacias.com.br/dipirona-sodica-generico-medley-500mg-10-comprimidos--venda-sob-prescricao---av---10087408/p" target="_blank">Comprar</a></div>
      <div class="med-item">Paracetamol <br><a class="buy-link" href="https://www.saojoaofarmacias.com.br/paracetamol-750mg-20-comprimidos-generico-globo-10040354/p" target="_blank">Comprar</a></div>
      <div class="med-item">Ibuprofeno <br><a class="buy-link" href="https://www.saojoaofarmacias.com.br/ibuprofeno-600mg-20-comprimidos-13308/p" target="_blank">Comprar</a></div>
      <div class="med-item">Amoxicilina <br><a class="buy-link" href="https://www.saojoaofarmacias.com.br/amoxicilina-500mg-generico-cimed-21-capsulas-duras-10091320/p" target="_blank">Comprar</a></div>
      <div class="med-item">Losartana <br><a class="buy-link" href="https://www.saojoaofarmacias.com.br/busca?q=losartana" target="_blank">Comprar</a></div>
      <div class="med-item">Sinvastatina <br><a class="buy-link" href="https://www.saojoaofarmacias.com.br/busca?q=sinvastatina" target="_blank">Comprar</a></div>
      <!-- Adicione mais conforme necessário -->
    </div>
  </div>
  <div class="container" id="lista">
    <h2>Minha Lista de Medicamentos</h2>
  </div>
</body>
</html>
