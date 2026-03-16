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

// Função para gerar links de compra
function buy_link($nome) {
    $remedios = [
        "Dipirona" => "...", // link como no original
    ];
    foreach ($remedios as $n => $link) {
        if (stripos($nome, $n) !== false) return $link;
    }
    return "https://www.saojoaofarmacias.com.br/busca?q=" . urlencode($nome);
}
?>

<h1>Bem-vindo, <?= htmlspecialchars($_SESSION['nome']) ?>!</h1>
<a href="logout.php">Sair</a>

<form method="POST">
    <h2>Agendar Medicamento</h2>
    <input name="nome_med" required placeholder="Nome do Medicamento"><br>
    <input name="dosagem" required placeholder="Dosagem"><br>
    <input name="data" type="datetime-local" required><br>
    <button type="submit">Adicionar</button>
</form>

<h2>Meus Medicamentos</h2>
<?php foreach ($meds as $med): ?>
    <div>
        <strong><?= htmlspecialchars($med['nome']) ?></strong> - <?= date('d/m/Y H:i', strtotime($med['horario'])) ?><br>
        Dosagem: <?= htmlspecialchars($med['dosagem']) ?><br>
        <a href="<?= buy_link($med['nome']) ?>" target="_blank">Comprar</a>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="delete_id" value="<?= $med['id'] ?>">
            <button type="submit">Remover</button>
        </form>
    </div>
<?php endforeach; ?>
