<?php
// Carrega as configurações (onde está o $db_config)
require_once 'config.php';

$total_usuarios = 0;
$status_db = "Desconectado";
$erro = "";

try {
    // Verifica se a configuração do banco existe no config.php
    if (isset($db_config)) {
        $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
        $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $status_db = "Conectado";
        
        // Faz a contagem de registros na tabela 'usuarios'
        $stmt = $pdo->query("SELECT count(*) FROM usuarios");
        $count = $stmt->fetchColumn();
        
        $total_usuarios = $count ? $count : 0;
    } else {
        $erro = "Configuração do banco de dados (\$db_config) não encontrada no arquivo config.php.";
    }
} catch (PDOException $e) {
    $erro = "Erro ao conectar ao banco de dados: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status da Base de Dados</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f0f2f5; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .card { 
            background: white; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 8px 24px rgba(0,0,0,0.1); 
            text-align: center; 
            max-width: 400px; 
            width: 100%; 
            border-top: 6px solid #0056b3;
        }
        h1 { color: #333; margin-top: 0; margin-bottom: 5px; font-size: 24px;}
        .status { font-size: 14px; color: #666; margin-bottom: 30px; }
        .number-display { 
            background: #e3f2fd; 
            color: #0056b3; 
            font-size: 72px; 
            font-weight: bold; 
            padding: 30px 20px; 
            border-radius: 12px; 
            margin-bottom: 15px; 
            line-height: 1;
        }
        .error { 
            color: #721c24; 
            background: #f8d7da; 
            padding: 15px; 
            border-radius: 8px; 
            font-size: 13px; 
            margin-top: 20px; 
            border: 1px solid #f5c6cb; 
            text-align: left;
        }
        .badge { 
            display: inline-block; 
            padding: 5px 10px; 
            border-radius: 20px; 
            font-size: 12px; 
            font-weight: bold; 
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .footer { margin-top: 30px; font-size: 12px; color: #aaa; }
    </style>
</head>
<body>

<div class="card">
    <h1>Estatísticas do Portal</h1>
    <div class="status">
        Banco de Dados: 
        <?php if ($status_db === "Conectado"): ?>
            <span class="badge badge-success">Online</span>
        <?php else: ?>
            <span class="badge badge-danger">Offline</span>
        <?php endif; ?>
    </div>

    <?php if (empty($erro)): ?>
        <div class="number-display">
            <?= number_format($total_usuarios, 0, ',', '.') ?>
        </div>
        <p style="color: #555; font-weight: 600; font-size: 16px; margin: 0;">Visitantes Cadastrados</p>
    <?php else: ?>
        <div class="error">
            <b>Falha de Comunicação:</b><br>
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <div class="footer">
        Atualizado em: <?= date('d/m/Y H:i:s') ?>
    </div>
</div>

</body>
</html>