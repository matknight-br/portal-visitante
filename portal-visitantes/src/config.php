<?php
// Evita o acesso direto a este arquivo se necessário
// if (!defined('INCLUDED_BY_INDEX')) die('Acesso direto não permitido');

// 1. CONFIGURAÇÕES GERAIS DO PORTAL
$app_config = [
    'nome_empresa' => 'Matheus Guerreiro',
    'landing_page' => 'https://www.google.com.br', // Para onde o usuário vai após conectar
    'syslog_ip'    => 'ip_firewall',                 // IP do Firewall
    'syslog_port'  => 514
];

// 2. CONFIGURAÇÕES DO BANCO DE DADOS (MySQL/MariaDB)
$db_config = [
    'host'    => 'sql101.infinityfree.com', // Nome do contêiner no docker-compose
    'dbname'  => 'if0_42215181_portal_visitantes',
    'user'    => 'if0_42215181',
    'pass'    => 'E4RaGrdZcvH4',
    'charset' => 'utf8mb4'
];

// 3. CONFIGURAÇÕES OMADA
$omada_config = [
    'ip'   => 'omada.empresa.com.br',
    'port' => '8043',
    'user' => 'portalapi',
    'pass' => '<configured_password>',
    'site' => 'default' // Será substituído dinamicamente no init.php
];

// 4. CONFIGURAÇÕES SERPRO (ConectaGov - API CPF Light v2)
$serpro_config = [
    'consumer_key'    => 'SUA_CHAVE_AQUI',
    'consumer_secret' => 'SEU_SECRET_AQUI',
    'cpf_gestor'      => 'CPF_RESPONSAVEL',
    'url_token'       => 'https://apigateway.conectagov.estaleiro.serpro.gov.br/oauth2/jwt-token',
    'url_consulta'    => 'https://apigateway.conectagov.estaleiro.serpro.gov.br/api-cpf-light/v2/consulta/cpf'
];