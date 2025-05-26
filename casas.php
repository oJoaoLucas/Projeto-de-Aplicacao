<?php
session_start();
require 'conexao.php';
require 'db.php';
$conn = connectDatabase();

// Função para registrar dados históricos
function registrarDadosHistoricos($conn, $casa_id, $consumo, $geracao) {
    try {
        // Registra consumo
        $stmtDados = $conn->prepare("INSERT INTO dados (casa_id, tipo, valor, data) VALUES (?, 'consumo', ?, NOW())");
        $stmtDados->execute([$casa_id, $consumo]);
        
        // Registra geração
        $stmtDados = $conn->prepare("INSERT INTO dados (casa_id, tipo, valor, data) VALUES (?, 'geracao', ?, NOW())");
        $stmtDados->execute([$casa_id, $geracao]);
        
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Exemplo de função para abater excedente do filho no pai
function abaterExcedentePaiFilho($conn, $casa_id) {
    $stmt = $conn->prepare("SELECT * FROM casas WHERE id = ?");
    $stmt->execute([$casa_id]);
    $casa = $stmt->fetch(PDO::FETCH_ASSOC);

    $excedente = max($casa['acoes'], 0);

    if ($casa['casa_pai_id']) {
        $stmt = $conn->prepare("UPDATE casas SET acoes = acoes - ? WHERE id = ?");
        $stmt->execute([$excedente, $casa['casa_pai_id']]);
    }
}

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nome = $_POST['nome'] ?? '';
                $endereco = $_POST['endereco'] ?? '';
                $consumo = $_POST['consumo'] ?? '';
                $geracao = $_POST['geracao'] ?? '';
                $tarifa = $_SESSION['tarifa'] ?? 0.89;

                if ($geracao > $consumo) {
                    $credito = $geracao - $consumo;
                    $debito = 0;
                } else {
                    $credito = 0;
                    $debito = ($consumo - $geracao) * $tarifa;
                }

                // Validação:
                if (empty($nome) || empty($endereco) || $consumo === '' || $geracao === '') {
                    $_SESSION['erro'] = "Todos os campos são obrigatórios!";
                } else {
                    try {
                        $stmt = $conn->prepare("INSERT INTO casas (nome, endereco, consumo, geracao, credito, debito) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$nome, $endereco, $consumo, $geracao, $credito, $debito]);
                        $casa_id = $conn->lastInsertId();

                        // Registra dados históricos
                        if (registrarDadosHistoricos($conn, $casa_id, $consumo, $geracao)) {
                            $_SESSION['sucesso'] = "Casa adicionada com sucesso! Dados históricos registrados.";
                        } else {
                            $_SESSION['sucesso'] = "Casa adicionada com sucesso!";
                            $_SESSION['aviso'] = "Atenção: Houve um problema ao registrar os dados históricos.";
                        }
                    } catch (PDOException $e) {
                        $_SESSION['erro'] = "Erro ao adicionar casa: " . $e->getMessage();
                    }
                }
                break;

            case 'update':
                $id = $_POST['id'];
                $nome = $_POST['nome'];
                $endereco = $_POST['endereco'];
                $consumo = $_POST['consumo'];
                $geracao = $_POST['geracao'];
                $tarifa = $_SESSION['tarifa'] ?? 0.89;

                if ($geracao > $consumo) {
                    $credito = $geracao - $consumo;
                    $debito = 0;
                } else {
                    $credito = 0;
                    $debito = ($consumo - $geracao) * $tarifa;
                }

                if (empty($nome) || empty($endereco) || $consumo === '' || $geracao === '') {
                    $_SESSION['erro'] = "Todos os campos são obrigatórios!";
                } else {
                    try {
                        $stmt = $conn->prepare("UPDATE casas SET nome = ?, endereco = ?, consumo = ?, geracao = ?, credito = ?, debito = ? WHERE id = ?");
                        $stmt->execute([$nome, $endereco, $consumo, $geracao, $credito, $debito, $id]);
                        
                        // Registra dados históricos da atualização
                        if (registrarDadosHistoricos($conn, $id, $consumo, $geracao)) {
                            $_SESSION['sucesso'] = "Casa atualizada com sucesso! Novos dados históricos registrados.";
                        } else {
                            $_SESSION['sucesso'] = "Casa atualizada com sucesso!";
                            $_SESSION['aviso'] = "Atenção: Houve um problema ao registrar os dados históricos.";
                        }
                    } catch (PDOException $e) {
                        $_SESSION['erro'] = "Erro ao atualizar casa: " . $e->getMessage();
                    }
                }
                break;

            case 'delete':
                $id = $_POST['id'];
                try {
                    $stmt = $conn->prepare("DELETE FROM casas WHERE id = ?");
                    $stmt->execute([$id]);
                    $_SESSION['sucesso'] = "Casa removida com sucesso!";
                } catch (PDOException $e) {
                    $_SESSION['erro'] = "Erro ao remover casa: " . $e->getMessage();
                }
                break;

            case 'test_data':
                // Ação especial para inserir dados de teste
                try {
                    $casas_existentes = $conn->query("SELECT id, nome FROM casas ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (empty($casas_existentes)) {
                        $_SESSION['erro'] = "Nenhuma casa encontrada. Adicione casas primeiro!";
                    } else {
                        $dados_inseridos = 0;
                        foreach ($casas_existentes as $casa) {
                            // Gera dados aleatórios para teste
                            $consumo_teste = rand(100, 500) / 10; // 10.0 a 50.0 kWh
                            $geracao_teste = rand(80, 600) / 10;  // 8.0 a 60.0 kWh
                            
                            if (registrarDadosHistoricos($conn, $casa['id'], $consumo_teste, $geracao_teste)) {
                                $dados_inseridos++;
                            }
                        }
                        
                        if ($dados_inseridos > 0) {
                            $_SESSION['sucesso'] = "Dados de teste inseridos com sucesso! {$dados_inseridos} registros criados.";
                        } else {
                            $_SESSION['erro'] = "Erro ao inserir dados de teste.";
                        }
                    }
                } catch (PDOException $e) {
                    $_SESSION['erro'] = "Erro ao inserir dados de teste: " . $e->getMessage();
                }
                break;

            case 'clear_all':
                try {
                    // Remove todos os registros e reseta os IDs
                    $conn->exec("SET FOREIGN_KEY_CHECKS = 0"); // Desabilita restrições de chave estrangeira temporariamente
                    $conn->exec("TRUNCATE TABLE dados");
                    $conn->exec("TRUNCATE TABLE relatorio");
                    $conn->exec("TRUNCATE TABLE casas");
                    $conn->exec("SET FOREIGN_KEY_CHECKS = 1"); // Reabilita restrições de chave estrangeira
                    $_SESSION['sucesso'] = "Todos os registros foram removidos e os IDs foram zerados com sucesso!";
                } catch (PDOException $e) {
                    $_SESSION['erro'] = "Erro ao limpar registros: " . $e->getMessage();
                }
                break;
        }
        
        // Redireciona para evitar reenvio do formulário
        header("Location: casas.php");
        exit;
    }
}

// Obtém a lista de casas
$stmt = $conn->query("SELECT * FROM casas ORDER BY id DESC");
$casas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tarifa = $_SESSION['tarifa'] ?? 0.89;

foreach ($casas as &$casa) {
    // Crédito em kWh
    if ($casa['geracao'] > $casa['consumo']) {
        $casa['credito'] = $casa['geracao'] - $casa['consumo'];
        $casa['debito'] = 0;
    } else {
        $casa['credito'] = 0;
        // Débito em R$
        $casa['debito'] = ($casa['consumo'] - $casa['geracao']) * $tarifa;
    }
}
unset($casa);

// Contar registros recentes na tabela dados
$stmt_count = $conn->query("SELECT COUNT(*) as total FROM dados WHERE data >= (NOW() - INTERVAL 5 MINUTE)");
$registros_recentes = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Casas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .required-field::after {
            content: " *";
            color: red;
        }
        .test-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 0.5rem;
            border: 2px dashed #007bff;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0"><i class="bi bi-house-door me-2"></i>Gerenciar Casas</h1>
                <small class="text-muted">
                    Registros nos últimos 5 min: <span class="badge bg-info"><?= $registros_recentes ?></span>
                </small>
            </div>
            <div class="d-flex gap-2">
                <a href="historico.php" class="btn btn-info btn-sm">
                    <i class="bi bi-clock-history"></i> Ver Histórico
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar para a Home
                </a>
                <form method="POST" onsubmit="return confirm('Tem certeza que deseja limpar todos os registros? Esta ação não pode ser desfeita!');">
                    <input type="hidden" name="action" value="clear_all">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> Limpar Registros
                    </button>
                </form>
            </div>
        </div>

        <!-- Mensagens de feedback -->
        <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['sucesso'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['sucesso']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['erro'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['erro'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['erro']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['aviso'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?= $_SESSION['aviso'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['aviso']); ?>
        <?php endif; ?>

        <!-- Seção de Teste Rápido -->
        <div class="card mb-4 test-section">
            <div class="card-header bg-info text-white">
                <i class="bi bi-flask"></i> Seção de Teste - Histórico dos Últimos 5 Minutos
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                                        <div class="col-md-8">
                        <p class="mb-0">
                            <small>Esta seção permite testar o sistema de histórico. Clique no botão para inserir dados aleatórios nas últimas 5 casas cadastradas.</small>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <form method="POST">
                            <input type="hidden" name="action" value="test_data">
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-magic"></i> Inserir Dados de Teste
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card de Adicionar Casa -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-plus-circle"></i> Adicionar Nova Casa
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="nome" class="form-label required-field">Nome da Casa</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="col-md-3">
                            <label for="endereco" class="form-label required-field">Endereço</label>
                            <input type="text" class="form-control" id="endereco" name="endereco" required>
                        </div>
                        <div class="col-md-2">
                            <label for="geracao" class="form-label required-field">Geração (kWh)</label>
                            <input type="number" step="0.01" class="form-control" id="geracao" name="geracao" required>
                        </div>
                        <div class="col-md-2">
                            <label for="consumo" class="form-label required-field">Consumo (kWh)</label>
                            <input type="number" step="0.01" class="form-control" id="consumo" name="consumo" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <input type="hidden" name="action" value="add">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-save"></i> Salvar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Casas -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Lista de Casas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Endereço</th>
                                <th>Consumo (kWh)</th>
                                <th>Geração (kWh)</th>
                                <th>Crédito (kWh)</th>
                                <th>Débito (R$)</th>
                                <th>Opções</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($casas as $casa): ?>
                                <tr>
                                    <td><?= htmlspecialchars($casa['id']) ?></td>
                                    <td><?= htmlspecialchars($casa['nome']) ?></td>
                                    <td><?= htmlspecialchars($casa['endereco']) ?></td>
                                    <td><?= htmlspecialchars($casa['consumo']) ?></td>
                                    <td><?= htmlspecialchars($casa['geracao']) ?></td>
                                    <td><?= number_format($casa['credito'], 2) ?></td>
                                    <td><?= number_format($casa['debito'], 2) ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <!-- Formulário de Edição -->
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="id" value="<?= $casa['id'] ?>">
                                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $casa['id'] ?>">
                                                    <i class="bi bi-pencil"></i> Editar
                                                </button>
                                                
                                                <!-- Modal de Edição -->
                                                <div class="modal fade" id="editModal<?= $casa['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $casa['id'] ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editModalLabel<?= $casa['id'] ?>">Editar Casa</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="editNome<?= $casa['id'] ?>" class="form-label required-field">Nome da Casa</label>
                                                                    <input type="text" class="form-control" id="editNome<?= $casa['id'] ?>" name="nome" value="<?= htmlspecialchars($casa['nome']) ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="editEndereco<?= $casa['id'] ?>" class="form-label required-field">Endereço</label>
                                                                    <input type="text" class="form-control" id="editEndereco<?= $casa['id'] ?>" name="endereco" value="<?= htmlspecialchars($casa['endereco']) ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="editConsumo<?= $casa['id'] ?>" class="form-label required-field">Consumo (kWh)</label>
                                                                    <input type="number" step="0.01" class="form-control" id="editConsumo<?= $casa['id'] ?>" name="consumo" value="<?= htmlspecialchars($casa['consumo']) ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="editGeracao<?= $casa['id'] ?>" class="form-label required-field">Geração (kWh)</label>
                                                                    <input type="number" step="0.01" class="form-control" id="editGeracao<?= $casa['id'] ?>" name="geracao" value="<?= htmlspecialchars($casa['geracao']) ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            
                                            <!-- Formulário de Exclusão -->
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $casa['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja remover esta casa?')">
                                                    <i class="bi bi-trash"></i> Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Atualiza o contador de registros recentes a cada 30 segundos
        setInterval(function() {
            fetch('atualizar_contador.php')
                .then(response => response.json())
                .then(data => {
                    if (data.total !== undefined) {
                        document.querySelector('.badge.bg-info').textContent = data.total;
                    }
                })
                .catch(error => console.error('Erro ao atualizar contador:', error));
        }, 30000);
    </script>
</body>
</html>