<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_form = $_POST['tipo_form'] ?? '';

    // --- FLUXO DE LOGIN ---
    if ($tipo_form == 'login') {
        $cpf = preg_replace('/[^0-9]/', '', $_POST['login_cpf']);
        $senha = preg_replace('/[^0-9.-]/', '', trim($_POST['login_senha'])); 

        $ldap_conn = @ldap_connect($ldap_uri);
        if ($ldap_conn) {
            ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
            $user_dn = "uid={$cpf},ou=people,{$ldap_config['base_dn']}";

            if (@ldap_bind($ldap_conn, $user_dn, $senha)) {
                $debug = "";
                if (autorizarNaOmada($clientMac, $omada_config, $debug, $cpf)) {
                    notificarFirewall($cpf, $clientMac, $_SESSION['clientIp'] ?? '', $app_config);
                    
                    $script_redirect = "<script>setTimeout(function(){ window.location.href = '{$app_config['landing_page']}'; }, 2000);</script>";
                    $mensagem = "<div class='success'>✅ Acesso liberado. Redirecionando...</div>" . $script_redirect;
                } else {
                    $mensagem = "<div class='error'>❌ Erro na Rede: $debug</div>";
                }
            } else {
                $mensagem = "<div class='error'>❌ CPF ou Senha incorretos.</div>";
            }
            ldap_close($ldap_conn);
        } else {
            $mensagem = "<div class='error'>❌ Servidor de autenticação indisponível.</div>";
        }
    }

    // --- FLUXO DE CADASTRO ---
    elseif ($tipo_form == 'cadastro') {
        $nome = trim($_POST['nome_completo']);
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
        $email = trim($_POST['email']);
        $erro_serpro = "";

        // 1. Barreiras de Identidade (Matemática e Oficial Governo)
        if (!isCpfValido($cpf)) {
            $mensagem = "<div class='error'>❌ CPF inválido. Verifique os números.</div>";
        } elseif (!validarCpfNaReceita($cpf, $nome, $serpro_config, $erro_serpro)) {
            $mensagem = "<div class='error'>❌ {$erro_serpro}</div>";
        } else {
            // 2. Cadastro no LDAP
            $ldap_conn = @ldap_connect($ldap_uri);
            if ($ldap_conn) {
                ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                
                if (@ldap_bind($ldap_conn, $ldap_config['admin_dn'], $ldap_config['admin_pass'])) {
                    $partes_nome = explode(" ", $nome, 2);
                    $sobrenome = $partes_nome[1] ?? $partes_nome[0];

                    $novo_usuario = [
                        "objectclass" => ["top", "person", "organizationalPerson", "inetOrgPerson"],
                        "cn" => $nome,
                        "sn" => $sobrenome,
                        "uid" => $cpf,
                        "mail" => $email
                    ];
    
                    $user_dn = "uid={$cpf},ou=people,{$ldap_config['base_dn']}";
                    $adicionado = @ldap_add($ldap_conn, $user_dn, $novo_usuario);
                    $ja_existe = (ldap_errno($ldap_conn) == 68);

                    if ($adicionado || $ja_existe) {
                        // Criptografa a senha (RFC 3062) definindo o próprio CPF como senha inicial
                        $senha_definida = @ldap_exop_passwd($ldap_conn, $user_dn, "", $cpf);

                        if ($senha_definida) {
                            $debug = "";
                            if (autorizarNaOmada($clientMac, $omada_config, $debug, $cpf)) {
                                notificarFirewall($cpf, $clientMac, $_SESSION['clientIp'] ?? '', $app_config);
                                
                                $script_redirect = "<script>setTimeout(function(){ window.location.href = '{$app_config['landing_page']}'; }, 2000);</script>";
                                $mensagem = "<div class='success'>✅ Cadastro realizado e conexão liberada!</div>" . $script_redirect;
                            } else {
                                $mensagem = "<div class='error'>❌ Cadastrado, mas erro na rede: $debug</div>";
                            }
                        } else {
                            $mensagem = "<div class='error'>❌ Falha ao processar senha.</div>";
                        }
                    } else {
                        $mensagem = "<div class='error'>❌ Falha ao criar usuário.</div>";
                    }
                }
                ldap_close($ldap_conn);
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
            $ldap_conn = @ldap_connect($ldap_uri);
            if ($ldap_conn) {
                ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                $user_dn = "uid={$cpf},ou=people,{$ldap_config['base_dn']}";

                if (@ldap_bind($ldap_conn, $user_dn, $senha_atual)) {
                    if (@ldap_exop_passwd($ldap_conn, $user_dn, $senha_atual, $nova_senha)) {
                        $mensagem = "<div class='success'>✅ Senha alterada! Faça login com a nova senha.</div>";
                    } else {
                        $mensagem = "<div class='error'>❌ Erro interno ao alterar a senha.</div>";
                    }
                } else {
                    $mensagem = "<div class='error'>❌ CPF ou Senha Atual incorretos.</div>";
                }
                ldap_close($ldap_conn);
            }
        }
    }
}