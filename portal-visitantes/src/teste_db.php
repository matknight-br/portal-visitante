<?php
require_once 'config.php';

echo "<h2>🕵️‍♂️ Teste de Conexão com o Banco de Dados</h2>";

try {
    // 1. Testa a conexão básica
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<p style='color: green;'>✅ 1. Conexão com o MariaDB bem sucedida!</p>";

    // 2. Testa se a tabela 'usuarios' existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ 2. A tabela 'usuarios' existe e está pronta.</p>";
        
        // 3. Conta quantos visitantes existem
        $count = $pdo->query("SELECT count(*) FROM usuarios")->fetchColumn();
        echo "<p style='color: blue;'>📊 Total de visitantes cadastrados no banco: <b>{$count}</b></p>";
    } else {
        echo "<p style='color: red;'>❌ 2. O Banco existe, mas a tabela 'usuarios' NÃO FOI CRIADA.</p>";
        echo "<p>O arquivo init.sql não foi executado pelo Docker.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ 1. FALHA NA CONEXÃO: " . $e->getMessage() . "</p>";
}
?>