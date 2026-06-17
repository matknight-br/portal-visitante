<?php
session_start();

// 1. PERSISTÊNCIA E LIMPEZA DE PARÂMETROS DA REDE
$clientMac = $_GET['clientMac'] ?? $_SESSION['clientMac'] ?? '';
$omadaSite = $_GET['site'] ?? $_SESSION['omada_site_id'] ?? '';
$apMac     = $_GET['apMac'] ?? $_SESSION['apMac'] ?? '';
$ssidName  = $_GET['ssidName'] ?? $_SESSION['ssidName'] ?? '';
$radioId   = $_GET['radioId'] ?? $_SESSION['radioId'] ?? '1';
$clientIp  = $_GET['clientIp'] ?? $_SESSION['clientIp'] ?? '';

if (!empty($clientMac)) $_SESSION['clientMac'] = $clientMac;
if (!empty($omadaSite)) $_SESSION['omada_site_id'] = $omadaSite;
if (!empty($apMac)) $_SESSION['apMac'] = $apMac;
if (!empty($ssidName)) $_SESSION['ssidName'] = $ssidName;
if (!empty($radioId)) $_SESSION['radioId'] = $radioId;
if (!empty($clientIp)) $_SESSION['clientIp'] = $clientIp;

// Define a ação atual (login, cadastro, trocar_senha)
$acao = $_GET['acao'] ?? 'login';

$mensagem = "";

// Carrega dependências
require_once 'functions.php';
require_once 'config.php';

// Ajusta variáveis globais baseadas na sessão
$omada_config['site'] = (!empty($omadaSite) && $omadaSite !== 'default') ? $omadaSite : 'default';
$ldap_uri = "{$ldap_config['host']}:{$ldap_config['port']}";