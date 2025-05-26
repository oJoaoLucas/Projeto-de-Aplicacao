<?php
require_once 'db.php';

session_start();

function getDados($conn) {
    $stmt = $conn->prepare("SELECT * FROM dados");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addDado($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO dados (campo1, campo2) VALUES (:campo1, :campo2)");
    $stmt->bindParam(':campo1', $data['campo1']);
    $stmt->bindParam(':campo2', $data['campo2']);
    return $stmt->execute();
}

function updateDado($conn, $id, $data) {
    $stmt = $conn->prepare("UPDATE dados SET campo1 = :campo1, campo2 = :campo2 WHERE id = :id");
    $stmt->bindParam(':campo1', $data['campo1']);
    $stmt->bindParam(':campo2', $data['campo2']);
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}

function deleteDado($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM dados WHERE id = :id");
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}
?>