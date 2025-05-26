<?php
session_start();
require 'conexao.php';

header('Content-Type: application/json');

try {
    $stmt_count = $pdo->query("SELECT COUNT(*) as total FROM dados WHERE data >= (NOW() - INTERVAL 5 MINUTE)");
    $registros_recentes = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode(['total' => $registros_recentes]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}