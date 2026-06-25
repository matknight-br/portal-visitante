<?php
// Evita o acesso direto a este arquivo se necessário
// if (!defined('INCLUDED_BY_INDEX')) die('Acesso direto não permitido');

// 1. CONFIGURAÇÕES GERAIS DO PORTAL
$app_config = [
    'nome_empresa' => 'Unimed Campo Grande',
    'landing_page' => 'https://www.google.com.br', // Para onde o usuário vai após conectar
    'syslog_ip'    => 'ip_firewall',                 // IP do Firewall
    'syslog_port'  => 514
];

/* // 2. CONFIGURAÇÕES LDAP (LLDAP)
$ldap_config = [
    'host'        => 'ldap://lldap-server',
    'port'        => 3890,
    'admin_dn'    => 'uid=admin,ou=people,dc=visitantes,dc=local',
    'admin_pass'  => 'password_lldap_adm',
    'base_dn'     => 'dc=visitantes,dc=empresa,dc=local'
];
 */

// 2. CONFIGURAÇÕES DO BANCO DE DADOS (MySQL/MariaDB)
$db_config = [
    'host'    => 'portal_db', // Nome do contêiner no docker-compose
    'dbname'  => 'portal_visitantes',
    'user'    => 'portal_user',
    'pass'    => 'senha_segura_123',
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