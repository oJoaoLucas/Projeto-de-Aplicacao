<?php
session_start();
require 'conexao.php';

// Redirecionar para login se não estiver logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Processar logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$tarifa = isset($_SESSION['tarifa']) ? $_SESSION['tarifa'] : 0.89;
$casas = $pdo->query("SELECT * FROM casas")->fetchAll();

// Obter nome do usuário para exibição
$stmt = $pdo->prepare("SELECT NOME FROM users WHERE ID = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_nome = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energy Control - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --dark-color: #5a5c69;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .card-icon {
            font-size: 2rem;
            opacity: 0.3;
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
        }
        
        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-success-custom {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .welcome-header {
            color: var(--dark-color);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand navbar-dark navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-lightning-charge-fill me-2"></i>Energy Control
            </a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($user_nome) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="?logout=1"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="container">
        <!-- Cabeçalho -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 welcome-header">Bem-vindo, <?= htmlspecialchars($user_nome) ?>!</h1>
            <div>
                <span class="me-2">Tarifa atual: R$ <?= number_format($tarifa, 2) ?>/kWh</span>
                <a href="editar_tarifa.php" class="btn btn-sm btn-warning">
                    <i class="bi bi-pencil-square"></i> Editar
                </a>
            </div>
        </div>

        <!-- Cards de Funcionalidades -->
        <div class="row">
            <!-- Gerenciar Casas -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="h5 font-weight-bold text-primary mb-1">Gerenciar Casas</div>
                                <div class="text-muted small">Adicione e edite propriedades</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-house-door-fill text-primary" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <a href="casas.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <!-- Comparar Casas -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="h5 font-weight-bold text-success mb-1">Comparar Casas</div>
                                <div class="text-muted small">Analise o consumo entre propriedades</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-graph-up-arrow text-success" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <a href="comparar.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <!-- Histórico Mensal -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="h5 font-weight-bold text-info mb-1">Histórico</div>
                                <div class="text-muted small">Consulte dados históricos</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-month text-info" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <a href="historico.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <!-- Exportar Dados -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="h5 font-weight-bold text-warning mb-1">Exportar Dados</div>
                                <div class="text-muted small">Gere relatórios em CSV</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-file-earmark-arrow-down text-warning" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <a href="exportar_dados.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção de Ações Rápidas -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Ações Rápidas</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3">
                    <a href="casas.php" class="btn btn-primary-custom">
                        <i class="bi bi-plus-circle me-1"></i> Adicionar Nova Casa
                    </a>
                    <a href="comparar.php" class="btn btn-success-custom">
                        <i class="bi bi-bar-chart-line me-1"></i> Comparar Consumo
                    </a>
                    <a href="exportar_dados.php" class="btn btn-dark">
                        <i class="bi bi-download me-1"></i> Exportar Relatório
                    </a>
                    <a href="editar_tarifa.php" class="btn btn-warning">
                        <i class="bi bi-currency-dollar me-1"></i> Alterar Tarifa
                    </a>
                </div>
            </div>
        </div>

        <!-- Rodapé -->
        <footer class="text-center text-muted small mt-5 mb-4">
            <div class="mb-2">
                <i class="bi bi-lightning-charge-fill text-primary"></i> Energy Control
            </div>
            <div>Sistema de Monitoramento Energético</div>
            <div class="mt-2">© <?= date('Y') ?> Todos os direitos reservados</div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Ativar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>