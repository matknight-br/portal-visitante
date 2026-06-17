<?php
// Puxa as configurações que você já tem prontas
require_once 'config.php';

echo "<h2>📊 Estatísticas do LLDAP</h2>";

$ldap_uri = "{$ldap_config['host']}:{$ldap_config['port']}";
$ldap_conn = @ldap_connect($ldap_uri);

if ($ldap_conn) {
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);

    if (@ldap_bind($ldap_conn, $ldap_config['admin_dn'], $ldap_config['admin_pass'])) {
        
        $search_base = "ou=people," . $ldap_config['base_dn'];
        $filtro = "(objectclass=person)";
        
        // ldap_search busca os dados, ldap_count_entries conta os resultados
        $busca = ldap_search($ldap_conn, $search_base, $filtro, ['uid']);
        $total = ldap_count_entries($ldap_conn, $busca);
        
        echo "<div style='background:#d4edda; color:#155724; padding:20px; border-radius:8px; display:inline-block; font-family:Arial;'>";
        echo "<h1 style='margin:0; font-size:48px; text-align:center;'>{$total}</h1>";
        echo "<p style='margin:0; text-align:center; font-weight:bold;'>Visitantes Cadastrados</p>";
        echo "</div>";

    } else {
        echo "<b style='color:red;'>Erro:</b> Não foi possível autenticar com o usuário admin do LDAP.";
    }
    ldap_close($ldap_conn);
} else {
    echo "<b style='color:red;'>Erro:</b> Não foi possível conectar ao servidor LLDAP.";
}
?>
