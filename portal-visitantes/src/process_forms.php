<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_form = $_POST['tipo_form'] ?? '';

    // Conexão com o Banco de Dados (PDO)
    try {
        $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
        $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        error_log("Erro de conexão DB: " . $e->getMessage());
        $mensagem = "<div class='error'>❌ Servidor de banco de dados temporariamente indisponível.</div>";
        $tipo_form = ''; // Aborta o fluxo
    }

    // --- FLUXO DE LOGIN ---
    if ($tipo_form == 'login') {
        $cpf = preg_replace('/[^0-9]/', '', $_POST['login_cpf']);
        $senha = $_POST['login_senha']; 

        $stmt = $pdo->prepare("SELECT nome, senha_hash FROM usuarios WHERE cpf = ?");
        $stmt->execute([$cpf]);
        $usuario = $stmt->fetch();

        // password_verify cruza a senha digitada com o Hash seguro do banco
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            $debug = "";
            if (autorizarNaOmada($clientMac, $omada_config, $debug, $cpf)) {
                notificarFirewall($cpf, $clientMac, $_SESSION['clientIp'] ?? '', $app_config);
                
                $script_redirect = "<script>setTimeout(function(){ window.location.href = '{$app_config['landing_page']}'; }, 2000);</script>";
                $mensagem = "<div class='success'>✅ Bem-vindo(a), {$usuario['nome']}! Conexão liberada.</div>" . $script_redirect;
            } else {
                $mensagem = "<div class='error'>❌ Erro na Rede: $debug</div>";
            }
        } else {
            $mensagem = "<div class='error'>❌ CPF ou Senha incorretos.</div>";
        }
    }

    // --- FLUXO DE CADASTRO ---
    elseif ($tipo_form == 'cadastro') {
        $nome = trim($_POST['nome_completo']);
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
        $senha = $_POST['senha'];
        $email = trim($_POST['email']);
        $erro_serpro = "";

        // 1. Barreiras de Identidade
        if (!isCpfValido($cpf)) {
            $mensagem = "<div class='error'>❌ CPF inválido. Verifique os números.</div>";
        } elseif (!validarCpfNaReceita($cpf, $nome, $serpro_config, $erro_serpro)) {
            $mensagem = "<div class='error'>❌ {$erro_serpro}</div>";
        } else {
            // 2. Verifica se já existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE cpf = ?");
            $stmt->execute([$cpf]);
            
            if ($stmt->fetch()) {
                $mensagem = "<div class='error'>❌ Este CPF já possui cadastro. Use a aba de Login ou recupere a senha.</div>";
            } else {
                // 3. Cadastra no Banco (A senha inicial é o próprio CPF criptografado)
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO usuarios (cpf, nome, email, senha_hash) VALUES (?, ?, ?, ?)");
                
                if ($stmt->execute([$cpf, $nome, $email, $senha_hash])) {
                    $debug = "";
                    if (autorizarNaOmada($clientMac, $omada_config, $debug, $cpf)) {
                        notificarFirewall($cpf, $clientMac, $_SESSION['clientIp'] ?? '', $app_config);
                        
                        $script_redirect = "<script>setTimeout(function(){ window.location.href = '{$app_config['landing_page']}'; }, 2000);</script>";
                        $mensagem = "<div class='success'>✅ Cadastro realizado e conexão liberada!</div>" . $script_redirect;
                    } else {
                        $mensagem = "<div class='error'>❌ Cadastrado com sucesso, mas ocorreu um erro ao liberar a rede: $debug</div>";
                    }
                } else {
                    $mensagem = "<div class='error'>❌ Erro interno ao gravar usuário.</div>";
                }
            }
        }
    }

    // --- FLUXO DE TROCA DE SENHA ---
    elseif ($tipo_form == 'trocar_senha') {
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf_troca']);
        $senha_atual = $_POST['senha_atual'];
        $nova_senha = $_POST['nova_senha'];

        if (!isCpfValido($cpf) || empty($senha_atual) || empty($nova_senha)) {
            $mensagem = "<div class='error'>❌ Verifique os campos preenchidos.</div>";
        } elseif ($nova_senha !== $_POST['confirma_senha']) {
            $mensagem = "<div class='error'>❌ As novas senhas não coincidem.</div>";
        } elseif (strlen($nova_senha) < 6) {
            $mensagem = "<div class='error'>❌ A senha deve ter no mínimo 6 caracteres.</div>";
        } else {
            // Verifica a identidade atual
            $stmt = $pdo->prepare("SELECT senha_hash FROM usuarios WHERE cpf = ?");
            $stmt->execute([$cpf]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha_atual, $usuario['senha_hash'])) {
                // Atualiza para a nova senha
                $novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt_update = $pdo->prepare("UPDATE usuarios SET senha_hash = ? WHERE cpf = ?");
                
                if ($stmt_update->execute([$novo_hash, $cpf])) {
                    $mensagem = "<div class='success'>✅ Senha alterada! Use a aba Login para se conectar à rede.</div>";
                } else {
                    $mensagem = "<div class='error'>❌ Erro interno ao alterar a senha.</div>";
                }
            } else {
                $mensagem = "<div class='error'>❌ CPF não encontrado ou Senha Atual incorreta.</div>";
            }
        }
    }
}