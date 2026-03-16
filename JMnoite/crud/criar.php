<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Contato</title>
    <style>
        body { font-family: Arial, sans-serif; }
        form { max-width: 400px; margin: 40px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        label { display: block; margin-top: 15px; }
        input[type="text"], input[type="tel"] { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 20px; padding: 10px 20px; }
    </style>
</head>
<body>
    <h2>Adcionar Novo Contato</h2>
    <form action="processa_criar.php" method="post">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="endereco">Endereço:</label>
        <input type="text" id="endereco" name="endereco" required>

        <label for="telefone">Telefone:</label>
        <input type="tel" id="telefone" name="telefone" required>

        <button type="submit">Salvar</button>
    </form>
    <p><a href="index.php">Voltar para a lista</a></p>
</body>
</html>