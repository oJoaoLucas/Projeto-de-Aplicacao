<?php
require 'conexao.php';
require 'fpdf.php';

$tarifa = 0.89;

function getHistoricoMensal($pdo, $casa_id) {
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(data, '%Y-%m') as mes,
            SUM(CASE WHEN tipo = 'consumo' THEN valor ELSE 0 END) as consumo,
            SUM(CASE WHEN tipo = 'geracao' THEN valor ELSE 0 END) as geracao
        FROM dados
        WHERE casa_id = ?
        GROUP BY mes
        ORDER BY mes
    ");
    $stmt->execute([$casa_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (!isset($_GET['id'])) {
    die("ID da casa não informado.");
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT nome FROM casas WHERE id = ?");
$stmt->execute([$id]);
$nome = $stmt->fetchColumn();

if (!$nome) {
    die("Casa não encontrada.");
}

$historico = getHistoricoMensal($pdo, $id);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Relatorio Historico Mensal - '.$nome,0,1);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,10,'Mes',1);
$pdf->Cell(40,10,'Consumo',1);
$pdf->Cell(40,10,'Geracao',1);
$pdf->Cell(30,10,'Saldo',1);
$pdf->Cell(40,10,'Credito (R$)',1);
$pdf->Ln();

$pdf->SetFont('Arial','',12);
foreach ($historico as $mes) {
    $saldo = $mes['geracao'] - $mes['consumo'];
    $credito = $saldo > 0 ? $saldo * $tarifa : 0;
    $pdf->Cell(40,10,$mes['mes'],1);
    $pdf->Cell(40,10,$mes['consumo'],1);
    $pdf->Cell(40,10,$mes['geracao'],1);
    $pdf->Cell(30,10,$saldo,1);
    $pdf->Cell(40,10,number_format($credito,2),1);
    $pdf->Ln();
}

$pdf->Output();
exit;