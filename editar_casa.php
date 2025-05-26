<?php
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remover_id'])) {
        $stmt = $pdo->prepare("DELETE FROM dados WHERE casa_id = ?");
        $stmt->execute([$_POST['remover_id']]);

        $stmt = $pdo->prepare("DELETE FROM casas WHERE id = ?");
        $stmt->execute([$_POST['remover_id']]);
    }

    if (isset($_POST['editar_id'], $_POST['novo_nome'])) {
        $id = $_POST['editar_id'];
        $nome = $_POST['novo_nome'];
        $consumo = floatval($_POST['novo_consumo']);
        $geracao = floatval($_POST['nova_geracao']);

        // Atualizar nome
        $stmt = $pdo->prepare("UPDATE casas SET nome = ? WHERE id = ?");
        $stmt->execute([$nome, $id]);

        // Deletar dados antigos e inserir novos
        $pdo->prepare("DELETE FROM dados WHERE casa_id = ? AND tipo = 'consumo'")->execute([$id]);
        $pdo->prepare("DELETE FROM dados WHERE casa_id = ? AND tipo = 'geracao'")->execute([$id]);

        $pdo->prepare("INSERT INTO dados (casa_id, tipo, valor) VALUES (?, 'consumo', ?)")->execute([$id, $consumo]);
        $pdo->prepare("INSERT INTO dados (casa_id, tipo, valor) VALUES (?, 'geracao', ?)")->execute([$id, $geracao]);
    }
}

// Função para buscar total atual de cada tipo
function getValorAtual($pdo, $id, $tipo) {
    $stmt = $pdo->prepare("SELECT SUM(valor) FROM dados WHERE casa_id = ? AND tipo = ?");
    $stmt->execute([$id, $tipo]);
    return $stmt->fetchColumn() ?: 0;
}

$casas = $pdo->query("SELECT * FROM casas")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar/Remover Casas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container">
    <h2 class="mb-4">Editar ou Remover Casas</h2>
    <a href="index.php" class="btn btn-secondary mb-3">← Voltar para a Home</a>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Nome</th>
            <th>Consumo (kWh)</th>
            <th>Geração (kWh)</th>
            <th>Salvar</th>
            <th>Remover</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($casas as $casa): 
            $consumo = getValorAtual($pdo, $casa['id'], 'consumo');
            $geracao = getValorAtual($pdo, $casa['id'], 'geracao');
        ?>
            <tr>
                <form method="POST" class="d-flex gap-2">
                    <td><input type="text" name="novo_nome" value="<?= $casa['nome'] ?>" class="form-control" required></td>
                    <td><input type="number" name="novo_consumo" value="<?= $consumo ?>" step="0.01" class="form-control" required></td>
                    <td><input type="number" name="nova_geracao" value="<?= $geracao ?>" step="0.01" class="form-control" required></td>
                    <td>
                        <input type="hidden" name="editar_id" value="<?= $casa['id'] ?>">
                        <button type="submit" class="btn btn-primary btn-sm">Salvar</button>
                    </td>
                </form>
                <form method="POST">
                    <td>
                        <input type="hidden" name="remover_id" value="<?= $casa['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Remover esta casa e todos os dados?')">Remover</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
