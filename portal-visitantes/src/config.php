<?php
// Carrega variáveis de ambiente do arquivo .env
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}
loadEnv(__DIR__ . '/.env');

// 1. CONFIGURAÇÕES GERAIS DO PORTAL
$app_config = [
    'nome_empresa' => getenv('APP_NOME_EMPRESA') ?: 'Matheus Guerreiro',
    'landing_page' => getenv('APP_LANDING_PAGE') ?: 'https://www.google.com.br',
    'syslog_ip'    => getenv('SYSLOG_IP') ?: '127.0.0.1',
    'syslog_port'  => (int)(getenv('SYSLOG_PORT') ?: 514)
];

// 2. CONFIGURAÇÕES DO BANCO DE DADOS (Supabase via PostgreSQL)
$db_config = [
    'driver'  => 'pgsql',
    'host'    => getenv('DB_HOST') ?: 'db.0ec90b57d6e95fcbda19832f.supabase.co',
    'port'    => (int)(getenv('DB_PORT') ?: 5432),
    'dbname'  => getenv('DB_NAME') ?: 'postgres',
    'user'    => getenv('DB_USER') ?: 'postgres',
    'pass'    => getenv('DB_PASS') ?: '',
    'charset' => 'utf8'
];

// Supabase API (para operações via REST)
$supabase_config = [
    'url'     => getenv('SUPABASE_URL') ?: 'https://0ec90b57d6e95fcbda19832f.supabase.co',
    'anon_key' => getenv('SUPABASE_ANON_KEY') ?: ''
];

// 3. CONFIGURAÇÕES OMADA
$omada_config = [
    'ip'   => getenv('OMADA_IP') ?: 'omada.empresa.com.br',
    'port' => getenv('OMADA_PORT') ?: '8043',
    'user' => getenv('OMADA_USER') ?: 'portalapi',
    'pass' => getenv('OMADA_PASS') ?: '',
    'site' => 'default'
];

// 4. CONFIGURAÇÕES SERPRO (ConectaGov - API CPF Light v2)
$serpro_config = [
    'consumer_key'    => getenv('SERPRO_CONSUMER_KEY') ?: '',
    'consumer_secret' => getenv('SERPRO_CONSUMER_SECRET') ?: '',
    'cpf_gestor'      => getenv('SERPRO_CPF_GESTOR') ?: '',
    'url_token'       => 'https://apigateway.conectagov.estaleiro.serpro.gov.br/oauth2/jwt-token',
    'url_consulta'    => 'https://apigateway.conectagov.estaleiro.serpro.gov.br/api-cpf-light/v2/consulta/cpf'
];