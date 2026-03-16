<?php
session_start();
require_once 'db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Processa a finalização da compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalize_purchase'])) {
    try {
        $pdo->beginTransaction();

        // Busca itens do carrinho do usuário
        $stmt = $pdo->prepare("SELECT c.medicamento_id, c.quantidade, m.preco FROM carrinho c JOIN medicamentos m ON c.medicamento_id = m.id WHERE c.usuario_id = ?");
        $stmt->execute([$user_id]);
        $itens_carrinho = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($itens_carrinho)) {
            $message = 'Seu carrinho está vazio. Adicione itens antes de finalizar a compra.';
            $message_type = 'error';
            $pdo->rollBack();
        } else {
            $total_compra = 0;
            foreach ($itens_carrinho as $item) {
                $total_compra += $item['quantidade'] * $item['preco'];
            }

            // Insere o pedido na tabela de pedidos
            $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, data_pedido, total) VALUES (?, NOW(), ?)");
            $stmt->execute([$user_id, $total_compra]);
            $pedido_id = $pdo->lastInsertId();

            // Insere os itens do pedido na tabela de detalhes do pedido
            $stmt_detalhes = $pdo->prepare("INSERT INTO pedido_itens (pedido_id, medicamento_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
            foreach ($itens_carrinho as $item) {
                $stmt_detalhes->execute([$pedido_id, $item['medicamento_id'], $item['quantidade'], $item['preco']]);
            }

            // Limpa o carrinho do usuário
            $stmt = $pdo->prepare("DELETE FROM carrinho WHERE usuario_id = ?");
            $stmt->execute([$user_id]);

            $pdo->commit();
            $message = 'Compra finalizada com sucesso! Seu pedido #' . $pedido_id . ' foi realizado.';
            $message_type = 'success';
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = 'Erro ao finalizar a compra: ' . $e->getMessage();
        $message_type = 'error';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Remédio Já</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <img src="image.png" alt="Remédio Já Logo">
                Remédio Já
            </a>
            <nav class="nav">
                <a href="index.php#agenda" class="nav-link">Agendar</a>
                <a href="index.php#minha-lista" class="nav-link">Minha Lista</a>
                <a href="index.php#medicamentos" class="nav-link">Medicamentos</a>
                <a href="carrinho.php" class="nav-link">🛒 Carrinho</a>
                <form action="logout.php" method="post" class="logout-form">
                    <button type="submit" class="logout-button">Sair</button>
                </form>
            </nav>
        </div>
    </header>

    <!-- Container Principal -->
    <div class="container">
        <section id="checkout" class="fade-in">
            <h1 class="page-title">Finalizar Compra</h1>
            
            <?php if (!empty($message)): ?>
                <div class="<?= $message_type === 'success' ? 'success-message' : 'error-message' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="card form-card">
                <h2 class="section-title">Detalhes do Pedido</h2>
                <p>Seu pedido será processado com base nos itens do seu carrinho.</p>
                <p>Certifique-se de que todos os itens e quantidades estão corretos antes de finalizar.</p>
                
                <form method="post">
                    <input type="hidden" name="finalize_purchase" value="1">
                    <button type="submit" class="btn btn-primary btn-full checkout-button">
                        Confirmar e Finalizar Compra
                    </button>
                </form>
                <div class="text-center" style="margin-top: 1rem;">
                    <a href="carrinho.php" class="link">Voltar para o Carrinho</a>
                </div>
            </div>
        </section>
    </div>

    <script>
        // Adiciona animação de fade-in
        document.addEventListener("DOMContentLoaded", function() {
            const elements = document.querySelectorAll(".fade-in");
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.opacity = "1";
                    el.style.transform = "translateY(0)";
                }, index * 100);
            });
        });
    </script>
</body>
</html>

