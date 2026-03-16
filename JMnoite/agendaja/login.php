<?php
session_start();
require_once 'database_config.php';

// Redireciona se já estiver logado
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$login_error = '';

// Processa o login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if ($email === '' || $senha === '') {
        $login_error = 'Por favor, preencha todos os campos.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_id'] = $usuario['id'];
    $_SESSION['user_email'] = $usuario['email'];
    $_SESSION['user_name'] = $usuario['nome'];
    header("Location: index.php");
    exit();
        } else {
            $login_error = 'E-mail ou senha inválidos. Verifique suas credenciais.';
        }
    }
}

// Verifica se há mensagem de sucesso do cadastro
$cadastro_sucesso = isset($_SESSION['cadastro_sucesso']);
if ($cadastro_sucesso) {
    unset($_SESSION['cadastro_sucesso']);
}

// Verifica se houve logout
$logout_sucesso = isset($_GET['logout']) && $_GET['logout'] === 'success';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Remédio Já</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="login.php" class="logo">
                <img src="image.png" alt="Remédio Já Logo">
                Remédio Já
            </a>
            <nav class="nav">
                <a href="register.php" class="nav-link">Criar Conta</a>
            </nav>
        </div>
    </header>

    <!-- Container Principal -->
    <div class="container">
        <div class="card auth-card fade-in">
            <h1 class="page-title">Entrar</h1>
            <p class="text-center" style="color: var(--text-secondary); margin-bottom: 2rem;">
                Acesse sua agenda de medicamentos
            </p>

            <!-- Mensagem de sucesso do cadastro -->
            <?php if ($cadastro_sucesso): ?>
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--secondary-color); color: var(--secondary-color); padding: 1rem; border-radius: var(--radius); margin-bottom: 1rem; font-weight: 500; text-align: center;">
                    ✅ Conta criada com sucesso! Faça login para continuar.
                </div>
            <?php endif; ?>

            <!-- Mensagem de logout bem-sucedido -->
            <?php if ($logout_sucesso): ?>
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--secondary-color); color: var(--secondary-color); padding: 1rem; border-radius: var(--radius); margin-bottom: 1rem; font-weight: 500; text-align: center;">
                    ✅ Logout realizado com sucesso! Faça login novamente para acessar.
                </div>
            <?php endif; ?>

            <!-- Mensagem de erro -->
            <?php if ($login_error): ?>
                <div class="error-message">
                    ❌ <?= htmlspecialchars($login_error) ?>
                </div>
            <?php endif; ?>

            <!-- Formulário de Login -->
            <form method="post">
                <div class="form-group">
                    <label for="email" class="form-label">E-mail</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="seu@email.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                        autocomplete="email"
                    >
                </div>

                <div class="form-group">
                    <label for="senha" class="form-label">Senha</label>
                    <input 
                        type="password" 
                        id="senha" 
                        name="senha" 
                        class="form-input" 
                        placeholder="Digite sua senha"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    Entrar na Conta
                </button>
            </form>

            <!-- Link para cadastro -->
            <div class="text-center" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">
                    Ainda não tem uma conta?
                </p>
                <a href="register.php" class="link">
                    Criar conta gratuita
                </a>
            </div>
        </div>

        <!-- Recursos da aplicação -->
        <div class="card" style="max-width: 600px; margin: 2rem auto;">
            <h2 class="section-title">Por que usar o Remédio Já?</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <div style="text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">⏰</div>
                    <h3 style="color: var(--text-primary); margin-bottom: 0.5rem;">Lembretes</h3>
                    <p style="color: var(--text-secondary); font-size: 0.875rem;">
                        Nunca mais esqueça de tomar seus medicamentos
                    </p>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🛒</div>
                    <h3 style="color: var(--text-primary); margin-bottom: 0.5rem;">Compras</h3>
                    <p style="color: var(--text-secondary); font-size: 0.875rem;">
                        Links diretos para comprar seus medicamentos
                    </p>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📱</div>
                    <h3 style="color: var(--text-primary); margin-bottom: 0.5rem;">Organização</h3>
                    <p style="color: var(--text-secondary); font-size: 0.875rem;">
                        Mantenha todos os medicamentos organizados
                    </p>
                </div>
            </div>
        </div>
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

        // Foco automático no primeiro campo
        document.getElementById('email').focus();

        // Validação em tempo real
        const emailInput = document.getElementById('email');
        const senhaInput = document.getElementById('senha');

        emailInput.addEventListener('input', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.style.borderColor = 'var(--danger-color)';
            } else {
                this.style.borderColor = 'var(--border-color)';
            }
        });

        senhaInput.addEventListener('input', function() {
            const senha = this.value;
            
            if (senha && senha.length < 6) {
                this.style.borderColor = 'var(--warning-color)';
            } else {
                this.style.borderColor = 'var(--border-color)';
            }
        });
    </script>
</body>
</html>

