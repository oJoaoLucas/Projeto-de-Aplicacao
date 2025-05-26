<?php
session_start();
require 'conexao.php';

// Conexão PDO
$pdo = new PDO("mysql:host=localhost;dbname=energia", "root", "");

$tarifa = $_SESSION['tarifa'] ?? 0.89;

function getTodosHistoricoUltimosMinutos($pdo, $minutos = 5) {
    $stmt = $pdo->prepare("
        SELECT 
            c.id as casa_id,
            c.nome,
            c.endereco,
            DATE_FORMAT(d.data, '%Y-%m-%d %H:%i:%s') as momento,
            SUM(CASE WHEN d.tipo = 'consumo' THEN d.valor ELSE 0 END) as consumo,
            SUM(CASE WHEN d.tipo = 'geracao' THEN d.valor ELSE 0 END) as geracao,
            d.data as data_registro
        FROM casas c
        LEFT JOIN dados d ON c.id = d.casa_id
        WHERE d.data >= (NOW() - INTERVAL ? MINUTE)
        GROUP BY c.id, c.nome, c.endereco, DATE_FORMAT(d.data, '%Y-%m-%d %H:%i:%s')
        ORDER BY d.data DESC, c.nome
    ");
    $stmt->execute([$minutos]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getResumoUltimosMinutos($pdo, $minutos = 5) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT c.id) as total_casas,
            COUNT(d.id) as total_registros,
            MIN(d.data) as primeiro_registro,
            MAX(d.data) as ultimo_registro
        FROM casas c
        LEFT JOIN dados d ON c.id = d.casa_id
        WHERE d.data >= (NOW() - INTERVAL ? MINUTE)
    ");
    $stmt->execute([$minutos]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

date_default_timezone_set('America/Sao_Paulo');
$historico = getTodosHistoricoUltimosMinutos($pdo, 5);
$resumo = getResumoUltimosMinutos($pdo, 5);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico dos Últimos 5 Minutos - Auto Atualização</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        .auto-refresh-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        .countdown {
            font-weight: bold;
            color: #007bff;
        }
        .new-record {
            animation: highlight 2s ease-in-out;
        }
        @keyframes highlight {
            0% { background-color: #fff3cd; }
            100% { background-color: transparent; }
        }
        .status-circle {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-online { background-color: #28a745; }
        .status-offline { background-color: #6c757d; }
    </style>
</head>
<body class="bg-light">
    <!-- Indicador de Auto-Refresh -->
    <div class="auto-refresh-indicator">
    </div>

    <div class="container py-4">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">
                    <i class="bi bi-clock-history me-2"></i>Histórico dos Últimos 5 Minutos
                </h1>
                <p class="text-muted mb-0">Atualização automática a cada 5 minutos</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="forceRefresh()" class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-clockwise"></i> Atualizar Agora
                </button>
                <a href="index.php" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <!-- Cards de Resumo -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="bi bi-house-door text-primary" style="font-size: 2rem;"></i>
                        <h4 class="mt-2 mb-0"><?= $resumo['total_casas'] ?? 0 ?></h4>
                        <small class="text-muted">Casas com Registros</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="bi bi-database text-success" style="font-size: 2rem;"></i>
                        <h4 class="mt-2 mb-0"><?= $resumo['total_registros'] ?? 0 ?></h4>
                        <small class="text-muted">Total de Registros</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="bi bi-clock text-info" style="font-size: 2rem;"></i>
                        <h6 class="mt-2 mb-0">
                            <?= $resumo['primeiro_registro'] ? date('H:i:s', strtotime($resumo['primeiro_registro'])) : '--:--:--' ?>
                        </h6>
                        <small class="text-muted">Primeiro Registro</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-clock-fill text-warning" style="font-size: 2rem;"></i>
                        <h6 class="mt-2 mb-0">
                            <?= $resumo['ultimo_registro'] ? date('H:i:s', strtotime($resumo['ultimo_registro'])) : '--:--:--' ?>
                        </h6>
                        <small class="text-muted">Último Registro</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status da Última Atualização -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Status do Sistema</h6>
                        <small class="text-muted">Última atualização: <span id="last-update"><?= date('d/m/Y H:i:s') ?></span></small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="status-circle status-online"></span>
                        <span class="small text-success">Sistema Online</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Dados -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>Registros dos Últimos 5 Minutos
                    <span class="badge bg-primary ms-2"><?= count($historico) ?> registros</span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($historico)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-info-circle text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-muted">Nenhum registro encontrado</h5>
                        <p class="text-muted">Não há dados inseridos nos últimos 5 minutos.</p>
                        <a href="casas.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Adicionar Nova Casa
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="historico-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Casa ID</th>
                                    <th>Nome</th>
                                    <th>Endereço</th>
                                    <th>Momento</th>
                                    <th>Consumo (kWh)</th>
                                    <th>Geração (kWh)</th>
                                    <th>Crédito (kWh)</th>
                                    <th>Débito (R$)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historico as $linha): 
                                    $credito = 0;
                                    $debito = 0;
                                    $status = '';
                                    $status_class = '';
                                    
                                    if ($linha['geracao'] > $linha['consumo']) {
                                        $credito = $linha['geracao'] - $linha['consumo'];
                                        $status = 'Crédito';
                                        $status_class = 'success';
                                    } else {
                                        $debito = ($linha['consumo'] - $linha['geracao']) * $tarifa;
                                        $status = 'Débito';
                                        $status_class = 'danger';
                                    }
                                    
                                    // Verificar se é um registro muito recente (últimos 30 segundos)
                                    $tempo_registro = strtotime($linha['data_registro']);
                                    $agora = time();
                                    $is_new = ($agora - $tempo_registro) < 30;
                                ?>
                                <tr class="<?= $is_new ? 'new-record' : '' ?>" data-momento="<?= $linha['momento'] ?>">
                                    <td><?= htmlspecialchars($linha['casa_id']) ?></td>
                                    <td><?= htmlspecialchars($linha['nome']) ?></td>
                                    <td><?= htmlspecialchars($linha['endereco']) ?></td>
                                    <td>
                                        <small><?= htmlspecialchars($linha['momento']) ?></small>
                                        <?php if ($is_new): ?>
                                            <span class="badge bg-success ms-1">NOVO</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($linha['consumo'], 2) ?></td>
                                    <td><?= number_format($linha['geracao'], 2) ?></td>
                                    <td class="text-success"><?= number_format($credito, 2) ?></td>
                                    <td class="text-danger">R$ <?= number_format($debito, 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $status_class ?>"><?= $status ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Informações Adicionais -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-info-circle me-1"></i>Como Funciona
                        </h6>
                        <ul class="small mb-0">
                            <li>A página atualiza automaticamente a cada 5 minutos</li>
                            <li>Registros novos são destacados com animação</li>
                            <li>Dados são obtidos da tabela 'dados' dos últimos 5 minutos</li>
                            <li>Use "Gerenciar Casas" para criar novos registros de teste</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-gear me-1"></i>Configurações
                        </h6>
                        <div class="small">
                            <p class="mb-1"><strong>Tarifa Atual:</strong> R$ <?= number_format($tarifa, 2) ?>/kWh</p>
                            <p class="mb-1"><strong>Intervalo:</strong> 5 minutos</p>
                            <p class="mb-0"><strong>Fuso Horário:</strong> America/Sao_Paulo</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh a cada 5 minutos (300 segundos)
        let countdownTimer = 300;
        let countdownInterval;
        let refreshInterval;

        function startCountdown() {
            countdownInterval = setInterval(function() {
                countdownTimer--;
                document.getElementById('countdown').textContent = countdownTimer;
                
                if (countdownTimer <= 0) {
                    refreshPage();
                }
            }, 1000);
        }

        function refreshPage() {
            // Mostrar indicador de carregamento
            const statusCircle = document.querySelector('.status-circle');
            statusCircle.className = 'status-circle status-offline';
            
            // Recarregar a página
            window.location.reload();
        }

        function forceRefresh() {
            clearInterval(countdownInterval);
            clearInterval(refreshInterval);
            refreshPage();
        }

        function resetTimer() {
            countdownTimer = 300;
            clearInterval(countdownInterval);
            startCountdown();
        }

        // Iniciar o sistema
        document.addEventListener('DOMContentLoaded', function() {
            startCountdown();
            
            // Atualizar timestamp da última atualização
            document.getElementById('last-update').textContent = new Date().toLocaleString('pt-BR');
            
            // Auto-refresh principal
            refreshInterval = setInterval(refreshPage, 300000); // 5 minutos
        });

        // Detectar quando a página fica visível novamente (para resetar timer se necessário)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                // Verificar se precisa atualizar
                const lastUpdate = new Date(document.getElementById('last-update').textContent);
                const now = new Date();
                const diffMinutes = (now - lastUpdate) / (1000 * 60);
                
                if (diffMinutes > 5) {
                    forceRefresh();
                }
            }
        });
    </script>
</body>
</html>