<?php
require 'conexao.php';

// Use PDO para conexão, se não estiver usando ainda
$pdo = new PDO("mysql:host=localhost;dbname=energia", "root", "");

// Busca todas as casas com os campos já calculados
$casas = $pdo->query("SELECT * FROM casas")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Comparar Casas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h2>Comparativo de Casas</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Casa</th>
                <th>Consumo (kWh)</th>
                <th>Geração (kWh)</th>
                <th>Crédito (kWh)</th>
                <th>Débito (R$)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($casas as $casa): ?>
            <tr>
                <td><?= htmlspecialchars($casa['nome']) ?></td>
                <td><?= htmlspecialchars($casa['consumo']) ?></td>
                <td><?= htmlspecialchars($casa['geracao']) ?></td>
                <td><?= number_format($casa['credito'], 2) ?></td>
                <td><?= number_format($casa['debito'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>