<?php
session_start();
require_once 'db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Adicionar item ao carrinho
if (isset($_POST['add_to_cart'])) {
    $medicamento_id = (int)$_POST['add_to_cart'];
    $quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 1;

    try {
        // Verifica se o item já está no carrinho
        $stmt = $pdo->prepare("SELECT * FROM carrinho WHERE usuario_id = ? AND medicamento_id = ?");
        $stmt->execute([$user_id, $medicamento_id]);
        $item_carrinho = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item_carrinho) {
            // Atualiza a quantidade se o item já existe
            $nova_quantidade = $item_carrinho['quantidade'] + $quantidade;
            $stmt = $pdo->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ?");
            $stmt->execute([$nova_quantidade, $item_carrinho['id']]);
        } else {
            // Adiciona novo item ao carrinho
            $stmt = $pdo->prepare("INSERT INTO carrinho (usuario_id, medicamento_id, quantidade) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $medicamento_id, $quantidade]);
        }
        header('Location: carrinho.php');
        exit();
    } catch (PDOException $e) {
        echo "<div class=\"error-message\">Erro ao adicionar ao carrinho: " . $e->getMessage() . "</div>";
    }
}

// Remover item do carrinho
if (isset($_POST['remove_from_cart'])) {
    $carrinho_id = (int)$_POST['remove_from_cart'];
    try {
        $stmt = $pdo->prepare("DELETE FROM carrinho WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$carrinho_id, $user_id]);
        header('Location: carrinho.php');
        exit();
    } catch (PDOException $e) {
        echo "<div class=\"error-message\">Erro ao remover do carrinho: " . $e->getMessage() . "</div>";
    }
}

// Atualizar quantidade do item no carrinho
if (isset($_POST['update_quantity'])) {
    $carrinho_id = (int)$_POST['update_quantity'];
    $nova_quantidade = (int)$_POST['quantidade'];

    if ($nova_quantidade > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$nova_quantidade, $carrinho_id, $user_id]);
            header('Location: carrinho.php');
            exit();
        } catch (PDOException $e) {
            echo "<div class=\"error-message\">Erro ao atualizar quantidade: " . $e->getMessage() . "</div>";
        }
    } else {
        // Se a quantidade for 0 ou menos, remove o item
        header('Location: carrinho.php?remove_from_cart=' . $carrinho_id);
        exit();
    }
}

// Buscar itens do carrinho do usuário
$itens_carrinho = [];
$total_carrinho = 0;
try {
    $stmt = $pdo->prepare("SELECT c.id as carrinho_id, c.quantidade, m.nome, m.descricao, m.imagem, m.preco FROM carrinho c JOIN medicamentos m ON c.medicamento_id = m.id WHERE c.usuario_id = ?");
    $stmt->execute([$user_id]);
    $itens_carrinho = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($itens_carrinho as $item) {
        $total_carrinho += $item['quantidade'] * $item['preco'];
    }
} catch (PDOException $e) {
    echo "<div class=\"error-message\">Erro ao carregar carrinho: " . $e->getMessage() . "</div>";
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Carrinho - Remédio Já</title>
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
        <section id="carrinho" class="fade-in">
            <h1 class="page-title">Meu Carrinho de Compras</h1>
            
            <?php if (!empty($itens_carrinho)): ?>
                <div class="card">
                    <?php foreach ($itens_carrinho as $item): ?>
                        <div class="cart-item">
                            <?php if (!empty($item['imagem'])): ?>
                                <img src="<?= htmlspecialchars($item['imagem']) ?>" alt="<?= htmlspecialchars($item['nome']) ?>" class="cart-item-image">
                            <?php endif; ?>
                            <div class="cart-item-details">
                                <h3 class="cart-item-name"><?= htmlspecialchars($item['nome']) ?></h3>
                                <p class="cart-item-price">R$ <?= number_format($item['preco'], 2, ',', '.') ?></p>
                                <p class="cart-item-price">Subtotal: R$ <?= number_format($item['quantidade'] * $item['preco'], 2, ',', '.') ?></p>
                            </div>
                            <form method="post" class="cart-item-quantity">
                                <input type="hidden" name="update_quantity" value="<?= $item['carrinho_id'] ?>">
                                <input type="number" name="quantidade" value="<?= htmlspecialchars($item['quantidade']) ?>" min="1" class="form-input" style="width: 70px;">
                                <button type="submit" class="btn btn-primary btn-sm">Atualizar</button>
                            </form>
                            <form method="post">
                                <input type="hidden" name="remove_from_cart" value="<?= $item['carrinho_id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Remover</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="cart-total">
                        Total do Carrinho: R$ <?= number_format($total_carrinho, 2, ',', '.') ?>
                    </div>
                    
                    <div class="text-center" style="margin-top: 2rem;">
                        <button type="button" class="btn btn-secondary btn-lg checkout-button">Finalizar Compra</button>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🛒</div>
                    <h2>Seu carrinho está vazio.</h2>
                    <p>Adicione medicamentos da seção <a href="index.php#medicamentos" class="link">Medicamentos Disponíveis</a>.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <script>
        // Adiciona animação de fade-in
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>

