<?php
session_start();
require 'db.php';

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $confirmar_senha = trim($_POST['confirmar_senha'] ?? '');

    if ($email === '' || $senha === '' || $confirmar_senha === '') {
        $erros[] = "Todos os campos são obrigatórios.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Por favor, insira um e-mail válido.";
    }

    if (strlen($senha) < 6) {
        $erros[] = "A senha deve ter pelo menos 6 caracteres.";
    }

    if ($senha !== $confirmar_senha) {
        $erros[] = "As senhas não coincidem.";
    }

    // Verifica se e-mail já está no banco
    if (empty($erros)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $erros[] = "Este e-mail já está registrado.";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)");
            $stmt->execute(['Usuário', $email, $hash]); // “Usuário” como nome padrão

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - Remédio Já</title>
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
                <a href="login.php" class="nav-link">Fazer Login</a>
            </nav>
        </div>
    </header>

    <!-- Container Principal -->
    <div class="container">
        <div class="card auth-card fade-in">
            <h1 class="page-title">Criar Conta</h1>
            <p class="text-center" style="color: var(--text-secondary); margin-bottom: 2rem;">
                Crie sua conta gratuita e organize seus medicamentos
            </p>

            <!-- Mensagens de erro -->
            <?php if (!empty($erros)): ?>
                <div class="error-message">
                    <?php foreach ($erros as $erro): ?>
                        <div>❌ <?= htmlspecialchars($erro) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Formulário de Cadastro -->
            <form method="post" id="registerForm">
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
                    <small id="emailHelp" style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem; display: block;">
                        Use um e-mail válido para acessar sua conta
                    </small>
                </div>

                <div class="form-group">
                    <label for="senha" class="form-label">Senha</label>
                    <input 
                        type="password" 
                        id="senha" 
                        name="senha" 
                        class="form-input" 
                        placeholder="Crie uma senha segura"
                        required
                        autocomplete="new-password"
                        minlength="6"
                    >
                    <small id="senhaHelp" style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem; display: block;">
                        Mínimo de 6 caracteres
                    </small>
                </div>

                <div class="form-group">
                    <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                    <input 
                        type="password" 
                        id="confirmar_senha" 
                        name="confirmar_senha" 
                        class="form-input" 
                        placeholder="Digite a senha novamente"
                        required
                        autocomplete="new-password"
                        minlength="6"
                    >
                    <small id="confirmarSenhaHelp" style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem; display: block;">
                        Repita a senha para confirmar
                    </small>
                </div>

                <!-- Indicador de força da senha -->
                <div id="passwordStrength" style="margin-bottom: 1rem; display: none;">
                    <div style="display: flex; gap: 0.25rem; margin-bottom: 0.5rem;">
                        <div class="strength-bar" style="height: 4px; flex: 1; background: var(--border-color); border-radius: 2px;"></div>
                        <div class="strength-bar" style="height: 4px; flex: 1; background: var(--border-color); border-radius: 2px;"></div>
                        <div class="strength-bar" style="height: 4px; flex: 1; background: var(--border-color); border-radius: 2px;"></div>
                        <div class="strength-bar" style="height: 4px; flex: 1; background: var(--border-color); border-radius: 2px;"></div>
                    </div>
                    <small id="strengthText" style="color: var(--text-secondary); font-size: 0.75rem;"></small>
                </div>

                <button type="submit" class="btn btn-primary btn-full" id="submitBtn">
                    Criar Conta Gratuita
                </button>
            </form>

            <!-- Link para login -->
            <div class="text-center" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">
                    Já tem uma conta?
                </p>
                <a href="login.php" class="link">
                    Fazer login
                </a>
            </div>
        </div>

        <!-- Benefícios do cadastro -->
        <div class="card" style="max-width: 600px; margin: 2rem auto;">
            <h2 class="section-title">Benefícios da sua conta</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <div style="text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🔒</div>
                    <h3 style="color: var(--text-primary); margin-bottom: 0.5rem;">Segurança</h3>
                    <p style="color: var(--text-secondary); font-size: 0.875rem;">
                        Seus dados são protegidos com criptografia
                    </p>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">☁️</div>
                    <h3 style="color: var(--text-primary); margin-bottom: 0.5rem;">Sincronização</h3>
                    <p style="color: var(--text-secondary); font-size: 0.875rem;">
                        Acesse de qualquer dispositivo
                    </p>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🆓</div>
                    <h3 style="color: var(--text-primary); margin-bottom: 0.5rem;">Gratuito</h3>
                    <p style="color: var(--text-secondary); font-size: 0.875rem;">
                        Sem custos, sem pegadinhas
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
        const confirmarSenhaInput = document.getElementById('confirmar_senha');
        const submitBtn = document.getElementById('submitBtn');
        const passwordStrength = document.getElementById('passwordStrength');
        const strengthBars = document.querySelectorAll('.strength-bar');
        const strengthText = document.getElementById('strengthText');

        // Validação de e-mail
        emailInput.addEventListener('input', function() {
            const email = this.value;
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            
            if (email && !emailRegex.test(email)) {
                this.style.borderColor = 'var(--danger-color)';
                document.getElementById('emailHelp').textContent = 'Por favor, insira um e-mail válido';
                document.getElementById('emailHelp').style.color = 'var(--danger-color)';
            } else {
                this.style.borderColor = 'var(--border-color)';
                document.getElementById('emailHelp').textContent = 'Use um e-mail válido para acessar sua conta';
                document.getElementById('emailHelp').style.color = 'var(--text-secondary)';
            }
            validateForm();
        });

        // Indicador de força da senha
        senhaInput.addEventListener('input', function() {
            const senha = this.value;
            passwordStrength.style.display = senha ? 'block' : 'none';
            
            let strength = 0;
            let strengthLabel = '';
            
            if (senha.length >= 6) strength++;
            if (senha.match(/[a-z]/) && senha.match(/[A-Z]/)) strength++;
            if (senha.match(/[0-9]/)) strength++;
            if (senha.match(/[^a-zA-Z0-9]/)) strength++;
            
            // Reset das barras
            strengthBars.forEach(bar => {
                bar.style.background = 'var(--border-color)';
            });
            
            // Preenche as barras baseado na força
            for (let i = 0; i < strength; i++) {
                if (strength <= 1) {
                    strengthBars[i].style.background = 'var(--danger-color)';
                    strengthLabel = 'Senha fraca';
                } else if (strength <= 2) {
                    strengthBars[i].style.background = 'var(--warning-color)';
                    strengthLabel = 'Senha média';
                } else if (strength <= 3) {
                    strengthBars[i].style.background = 'var(--primary-color)';
                    strengthLabel = 'Senha boa';
                } else {
                    strengthBars[i].style.background = 'var(--secondary-color)';
                    strengthLabel = 'Senha forte';
                }
            }
            
            strengthText.textContent = strengthLabel;
            validateForm();
        });

        // Validação de confirmação de senha
        confirmarSenhaInput.addEventListener('input', function() {
            const senha = senhaInput.value;
            const confirmarSenha = this.value;
            
            if (confirmarSenha && senha !== confirmarSenha) {
                this.style.borderColor = 'var(--danger-color)';
                document.getElementById('confirmarSenhaHelp').textContent = 'As senhas não coincidem';
                document.getElementById('confirmarSenhaHelp').style.color = 'var(--danger-color)';
            } else {
                this.style.borderColor = 'var(--border-color)';
                document.getElementById('confirmarSenhaHelp').textContent = 'Repita a senha para confirmar';
                document.getElementById('confirmarSenhaHelp').style.color = 'var(--text-secondary)';
            }
            validateForm();
        });

        // Validação geral do formulário
        function validateForm() {
            const email = emailInput.value;
            const senha = senhaInput.value;
            const confirmarSenha = confirmarSenhaInput.value;
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            
            const isValid = emailRegex.test(email) && 
                           senha.length >= 6 && 
                           senha === confirmarSenha;
            
            submitBtn.disabled = !isValid;
            submitBtn.style.opacity = isValid ? '1' : '0.6';
        }

        // Validação inicial
        validateForm();
    </script>
</body>
</html>

