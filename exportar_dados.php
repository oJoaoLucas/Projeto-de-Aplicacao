<?php
require 'conexao.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="dados_casas.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Casa', 'Tipo', 'Valor (kWh)', 'Data']);

$sql = "SELECT casas.nome, dados.tipo, dados.valor, dados.data
        FROM dados
        JOIN casas ON dados.casa_id = casas.id
        ORDER BY dados.data DESC";
$stmt = $pdo->query($sql);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}
fclose($output);
?>