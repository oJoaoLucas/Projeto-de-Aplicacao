<?php
session_start();
require 'conexao.php';
require_once 'db.php';

// Inicializa a tarifa na sessão, se não existir
if (!isset($_SESSION['tarifa'])) {
    $_SESSION['tarifa'] = 0.89;
}

// Atualiza a tarifa se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova = str_replace(',', '.', $_POST['nova_tarifa']);
    $_SESSION['tarifa'] = floatval($nova);
    header("Location: index.php");
    exit;
}

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$tarifa = $_SESSION['tarifa'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Tarifa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container">
    <h2 class="mb-4">Editar Tarifa de Energia</h2>
    <a href="index.php" class="btn btn-secondary mb-3">← Voltar para a Home</a>

    <form method="POST" class="row g-3">
        <div class="col-md-6">
            <label for="nova_tarifa" class="form-label">Nova Tarifa (R$/kWh)</label>
            <input type="number" step="0.01" name="nova_tarifa" id="nova_tarifa" class="form-control"
                   value="<?= htmlspecialchars(number_format($tarifa, 2, '.', '')) ?>" required>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Salvar Tarifa</button>
        </div>
    </form>
</div>
</body>
</html>