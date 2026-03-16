<?php
session_start();
require_once 'database_config.php'; // Inclui o arquivo de conexão com o banco de dados

// Verifica se o usuário está logado
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Processa a adição de medicamento à agenda
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['med_name'])) {
    $med_name = trim($_POST['med_name'] ?? '');
    $dosage = trim($_POST['dosage'] ?? '');
    $schedule_date = trim($_POST['schedule_date'] ?? '');

    if ($med_name !== '' && $dosage !== '' && $schedule_date !== '') {
        try {
            // Verifica se o medicamento já existe na tabela `medicamentos`
            $stmt = $pdo->prepare("SELECT id FROM medicamentos WHERE nome = ?");
            $stmt->execute([$med_name]);
            $medicamento = $stmt->fetch(PDO::FETCH_ASSOC);

            $medicamento_id = null;
            if ($medicamento) {
                $medicamento_id = $medicamento['id'];
            } else {
                // Se não existir, insere um novo medicamento genérico
                $stmt = $pdo->prepare("INSERT INTO medicamentos (nome, descricao, preco) VALUES (?, ?, ?)");
                $stmt->execute([$med_name, "Descrição não disponível.", 0.00]);
                $medicamento_id = $pdo->lastInsertId();
            }

            // Insere na agenda do usuário
            $stmt = $pdo->prepare("INSERT INTO agendamentos (usuario_id, medicamento_id, dosagem, data_agendada) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $medicamento_id, $dosage, $schedule_date]);

            header('Location: ' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
            exit();
        } catch (PDOException $e) {
            // Tratar erro de banco de dados
            echo "<div class=\"error-message\">Erro ao adicionar medicamento: " . $e->getMessage() . "</div>";
        }
    }
}

// Processa a exclusão de medicamento da agenda
if (isset($_POST['delete_agenda_id'])) {
    $agenda_id = (int) $_POST['delete_agenda_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$agenda_id, $user_id]);
        header('Location: ' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
        exit();
    } catch (PDOException $e) {
        echo "<div class=\"error-message\">Erro ao remover medicamento: " . $e->getMessage() . "</div>";
    }
}

// Busca medicamentos agendados do usuário
$agendamentos = [];
try {
    $stmt = $pdo->prepare("SELECT ag.id as agenda_id, ag.dosagem, ag.data_agendada, m.nome as med_nome, m.descricao, m.imagem, m.link_compra FROM agendamentos ag JOIN medicamentos m ON ag.medicamento_id = m.id WHERE ag.usuario_id = ? ORDER BY ag.data_agendada ASC");
    $stmt->execute([$user_id]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class=\"error-message\">Erro ao carregar agenda: " . $e->getMessage() . "</div>";
}

function buyLink($med_name) {
    $remedios = [
        "Dipirona" => "https://www.saojoaofarmacias.com.br/dipirona-sodica-generico-prati-donaduzzi-500mg-30-comprimidos-10004407/p",
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

/**
 * Formata data para exibição
 */
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remédio Já - Sua Agenda de Medicamentos</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="#" class="logo">
                <img src="image.png" alt="Remédio Já Logo">
                Remédio Já
            </a>
            <nav class="nav">
                <a href="#agenda" class="nav-link">Agendar</a>
                <a href="#minha-lista" class="nav-link">Minha Lista</a>
                <a href="#medicamentos" class="nav-link">Medicamentos</a>
                <form action="logout.php" method="post" class="logout-form">
                    <button type="submit" class="logout-button">Sair</button>
                </form>
            </nav>
        </div>
    </header>

    <!-- Container Principal -->
    <div class="container">
        <!-- Seção de Agendamento -->
        <section id="agenda" class="fade-in">
            <h1 class="page-title">Agende seus Medicamentos</h1>
            
            <div class="card form-card">
                <h2 class="section-title">Novo Medicamento</h2>
                <form method="post">
                    <div class="form-group">
                        <label for="med_name" class="form-label">Nome do Medicamento</label>
                        <input 
                            type="text" 
                            id="med_name" 
                            name="med_name" 
                            class="form-input" 
                            placeholder="Ex: Dipirona, Paracetamol..."
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="dosage" class="form-label">Dosagem / Instruções</label>
                        <input 
                            type="text" 
                            id="dosage" 
                            name="dosage" 
                            class="form-input" 
                            placeholder="Ex: 500mg a cada 8 horas"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="schedule_date" class="form-label">Data e Hora</label>
                        <input 
                            type="datetime-local" 
                            id="schedule_date" 
                            name="schedule_date" 
                            class="form-input" 
                            required
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-full">
                        Adicionar à Agenda
                    </button>
                </form>
            </div>
        </section>

        <!-- Minha Lista de Medicamentos Agendados -->
        <section id="minha-lista" class="fade-in">
            <h2 class="section-title">Minha Lista de Medicamentos Agendados</h2>
            
            <?php if (!empty($agendamentos)): ?>
                <div class="meds-grid">
                    <?php foreach ($agendamentos as $med): ?>
                        <div class="med-card fade-in">
                            <h3 class="med-name"><?= htmlspecialchars($med["med_nome"]) ?></h3>
                            
                            <?php if (!empty($med["imagem"])): ?>
                                <img src="<?= htmlspecialchars($med["imagem"]) ?>" alt="Imagem de <?= htmlspecialchars($med["med_nome"]) ?>" class="med-image">
                            <?php endif; ?>

                            <div class="med-schedule">
                                Agendado para: <?= formatDate($med["data_agendada"]) ?>
                            </div>
                            
                            <div class="med-dosage">
                                Dosagem: <?= htmlspecialchars($med["dosagem"]) ?>
                            </div>

                            <?php if (!empty($med["descricao"])): ?>
                                <p class="med-description"><?= htmlspecialchars($med["descricao"]) ?></p>
                            <?php endif; ?>
                            
                            <div class="med-actions">
                                <?php if (!empty($med["link_compra"])): ?>
                                    <a 
                                        href="<?= htmlspecialchars($med["link_compra"]) ?>" 
                                        target="_blank" 
                                        class="btn btn-secondary"
                                    >
                                        Comprar na Loja
                                    </a>
                                <?php endif; ?>
                                
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="delete_agenda_id" value="<?= $med["agenda_id"] ?>">
                                    <button type="submit" class="btn btn-danger">
                                        Remover da Agenda
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">💊</div>
                    <h2>Nenhum medicamento agendado ainda.</h2>
                    <h2>Adicione seu primeiro medicamento usando o formulário acima!</h2>
                </div>
            <?php endif; ?>
        </section>

        <!-- Medicamentos Disponíveis para Compra -->
        <section id="medicamentos" class="fade-in">
            <h1 class="section-title">Medicamentos Disponíveis</h1>
            <p class="text-center" style="color: var(--text-secondary); margin-bottom: 2rem;">
                Acesso rápido aos medicamentos mais procurados
            </p>
            
           <div class="container" id="todos">
  <h2>Adicionar Rápido</h2>
  <div class="meds-list">
    <?php
    $remedios = [
      "Dipirona" => "https://www.saojoaofarmacias.com.br/dipirona-sodica-generico-prati-donaduzzi-500mg-30-comprimidos-10004407/p",
      "Paracetamol" => "https://www.saojoaofarmacias.com.br/paracetamol-750mg-20-comprimidos-generico-globo-10040354/p",
      "Ibuprofeno" => "https://www.saojoaofarmacias.com.br/ibuprofeno-600mg-20-comprimidos-13308/p",
      "Amoxicilina" => "https://www.saojoaofarmacias.com.br/amoxicilina-500mg-generico-cimed-21-capsulas-duras-10091320/p",
      "Losartana" => "https://www.saojoaofarmacias.com.br/busca?q=losartana",
      "Sinvastatina" => "https://www.saojoaofarmacias.com.br/busca?q=sinvastatina"
    ];
    foreach ($remedios as $nome => $link): 
      $img = strtolower(str_replace('ç', 'c', preg_replace('/[^\w]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $nome)))) . '.jpg';
    ?>
      <div class="med-card">
        <img src="images/<?= $img ?>" alt="<?= htmlspecialchars($nome) ?>">
        <h3><?= htmlspecialchars($nome) ?></h3>
        <a class="buy-link" href="<?= htmlspecialchars($link) ?>" target="_blank">Comprar</a>
      </div>
    <?php endforeach; ?>
  </div>

    <script>
        // Adiciona animação de fade-in aos elementos quando carregam
        document.addEventListener("DOMContentLoaded", function() {
            const elements = document.querySelectorAll(".fade-in");
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.opacity = "1";
                    el.style.transform = "translateY(0)";
                }, index * 100);
            });
        });

        // Smooth scroll para navegação
        document.querySelectorAll("a[href^=\"#\"]").forEach(anchor => {
            anchor.addEventListener("click", function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute("href"));
                if (target) {
                    target.scrollIntoView({
                        behavior: "smooth",
                        block: "start"
                    });
                }
            });
        });

        // Define data mínima como agora
        const dateInput = document.getElementById("schedule_date");
        if (dateInput) {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            dateInput.min = now.toISOString().slice(0, 16);
        }
    </script>
</body>
</html>

