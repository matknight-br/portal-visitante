<?php
// Gerencia as abas da interface
function urlAba($novaAcao) {
    $p = $_GET;
    $p['acao'] = $novaAcao;
    return "?" . http_build_query($p);
}

// Validação matemática básica do CPF
function isCpfValido($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) return false;
    }
    return true;
}

// Normalização para comparar similaridade (Remove acentos)
function normalizarNome($nome) {
    $mapa = ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','õ'=>'o','ô'=>'o','ú'=>'u','ç'=>'c'];
    $nome = strtr(mb_strtolower($nome, 'UTF-8'), $mapa);
    return strtoupper(trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z ]/', '', $nome))));
}

// Formatação visual para o banco de dados (Self-Healing)
function formatarNomeProprio($nome) {
    $nome = mb_convert_case(mb_strtolower(trim($nome), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    $preposicoes = [' Da ', ' De ', ' Do ', ' Das ', ' Dos ', ' E '];
    $substitutos = [' da ', ' de ', ' do ', ' das ', ' dos ', ' e '];
    return str_replace($preposicoes, $substitutos, $nome);
}

// Validação Oficial na Receita Federal (ConectaGov v2)
function validarCpfNaReceita($cpf, &$nome_digitado, $config, &$msg_erro) {
    if (empty($config['consumer_key']) || empty($config['cpf_gestor'])) {
        return true; // Fallback
    }

    $ch_token = curl_init($config['url_token']);
    $credentials = base64_encode($config['consumer_key'] . ':' . $config['consumer_secret']);
    curl_setopt_array($ch_token, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER => ['Authorization: Basic ' . $credentials, 'Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT => 5, CURLOPT_SSL_VERIFYPEER => false 
    ]);
    $resposta_token = curl_exec($ch_token);
    if (curl_getinfo($ch_token, CURLINFO_HTTP_CODE) != 200) { curl_close($ch_token); return true; }
    curl_close($ch_token);
    
    $jwt_token = json_decode($resposta_token, true)['access_token'] ?? '';

    $ch_consulta = curl_init($config['url_consulta']);
    curl_setopt_array($ch_consulta, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(["listaCpf" => [$cpf]]),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $jwt_token,
            'Content-Type: application/json', 'Accept: application/json',
            'x-cpf-usuario: ' . $config['cpf_gestor']
        ],
        CURLOPT_TIMEOUT => 5, CURLOPT_SSL_VERIFYPEER => false
    ]);
    $resposta_consulta = curl_exec($ch_consulta);
    if (curl_getinfo($ch_consulta, CURLINFO_HTTP_CODE) != 200) { curl_close($ch_consulta); return true; }
    curl_close($ch_consulta);

    $dados_cpf = json_decode($resposta_consulta, true)[0] ?? [];
    
    if (!isset($dados_cpf['SituacaoCadastral']) || $dados_cpf['SituacaoCadastral'] !== 0) {
        $msg_erro = "O CPF possui pendências cadastrais na Receita Federal.";
        return false;
    }
    
    if (isset($dados_cpf['Nome'])) {
        $nome_receita = $dados_cpf['Nome'];
        $norm_dig = normalizarNome($nome_digitado);
        $norm_rec = normalizarNome($nome_receita);
        
        similar_text($norm_dig, $norm_rec, $percentual);
        $arr_dig = explode(' ', $norm_dig);
        $arr_rec = explode(' ', $norm_rec);
        $primeiro_nome_igual = (!empty($arr_dig) && !empty($arr_rec) && $arr_dig[0] === $arr_rec[0]);
        
        if ($percentual >= 65 || $primeiro_nome_igual) {
            $nome_digitado = formatarNomeProprio($nome_receita); // Autocura
            return true; 
        } else {
            $msg_erro = "O nome não corresponde ao titular do CPF. Evite usar apelidos.";
            $ip_cliente = $_SESSION['clientIp'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Desconhecido';
            $dispositivo = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido', 0, 60);
            error_log(sprintf("FRAUDE BLOQUEADA | CPF: %s | Digitado: '%s' | Oficial: '%s' | IP: %s | Aparelho: %s", $cpf, $norm_dig, $norm_rec, $ip_cliente, $dispositivo));
            return false;
        }
    }
    $msg_erro = "Falha ao validar identidade na base do governo.";
    return false;
}

// Autorização na controladora SDN Omada
function autorizarNaOmada($mac, $config, &$debug_msg, $identificacao = null) {
    $base_url = "https://{$config['ip']}:{$config['port']}";
    $cookie_file = sys_get_temp_dir() . '/cookie_' . md5($mac) . '.txt';
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false, CURLOPT_COOKIEJAR => $cookie_file,
        CURLOPT_COOKIEFILE => $cookie_file, CURLOPT_TIMEOUT => 15, CURLOPT_HEADER => true
    ]);

    curl_setopt($ch, CURLOPT_URL, "{$base_url}/api/v2/login");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username' => $config['user'], 'password' => $config['pass']]));
    
    $res_login = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header_text = substr($res_login, 0, $header_size);
    $data_login = json_decode(substr($res_login, $header_size), true);

    preg_match('/Csrf-Token:\s*([a-zA-Z0-9_-]+)/i', $header_text, $matches);
    $token = $matches[1] ?? ($data_login['result']['token'] ?? '');
    $omadacId = $data_login['result']['omadacId'] ?? '';

    if (!$token || !$omadacId) { $debug_msg = "Falha Login Omada."; curl_close($ch); return false; }

    $mac_fmt = str_replace(':', '-', strtoupper($mac));
    $auth_url = "{$base_url}/{$omadacId}/api/v2/hotspot/extPortal/auth";
    $duracao_ms = 480 * 60 * 1000; // 8 Horas de acesso

    $payload = json_encode([
        'clientMac' => $mac_fmt,
        'apMac'     => str_replace(':', '-', strtoupper($_SESSION['apMac'] ?? '')),
        'ssidName'  => $_SESSION['ssidName'] ?? '',
        'radioId'   => (int)($_SESSION['radioId'] ?? 1),
        'authType'  => 4,
        'time'      => $duracao_ms
    ]);

    curl_setopt($ch, CURLOPT_HEADER, false); 
    curl_setopt($ch, CURLOPT_URL, $auth_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', "Csrf-Token: {$token}"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    
    $data_auth = json_decode(curl_exec($ch), true);

    if (isset($data_auth['errorCode']) && $data_auth['errorCode'] === 0) {
        if (!empty($identificacao)) {
            $rename_url = "{$base_url}/{$omadacId}/api/v2/sites/{$config['site']}/clients/{$mac_fmt}";
            curl_setopt($ch, CURLOPT_URL, $rename_url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH'); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['name' => $identificacao]));
            curl_exec($ch); 
        }
        curl_close($ch);
        if (file_exists($cookie_file)) unlink($cookie_file);
        return true;
    }
    
    $debug_msg = "Erro Omada: " . ($data_auth['msg'] ?? '');
    curl_close($ch);
    return false;
}

// Envia Log de Auditoria para o Syslog do Firewall (Ex: Palo Alto)
function notificarFirewall($identificacao, $mac, $ip, $app_config) {
    $mensagem = "<14>PortalVisitantes: Auth-Success | Username: {$identificacao} | IP: {$ip} | MAC: {$mac}";
    $fp = @stream_socket_client("udp://{$app_config['syslog_ip']}:{$app_config['syslog_port']}", $errno, $errstr);
    if ($fp) {
        fwrite($fp, $mensagem);
        fclose($fp);
    } else {
        error_log("Falha ao enviar log Syslog: $errstr ($errno)");
    }
}