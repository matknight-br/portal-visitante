<?php
require_once 'init.php';
require_once 'process_forms.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Wi-Fi - <?= htmlspecialchars($app_config['nome_empresa']) ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background-color: #fff; padding: 35px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); max-width: 420px; width: 100%; border-top: 6px solid #005C40; }
        .text-center { text-align: center; }
        
        /* Nova classe para o Logótipo */
        .logo { max-width: 180px; height: auto; margin-bottom: 15px; display: inline-block; }
        
        h2 { color: #333; margin: 0 0 5px 0; font-size: 22px; }
        .subtitle { font-size: 14px; color: #666; margin-bottom: 25px; }
        .tabs { display: flex; border-bottom: 2px solid #eaeaea; margin-bottom: 20px; }
        .tabs a { flex: 1; text-align: center; padding: 12px 5px; text-decoration: none; color: #666; font-weight: 600; font-size: 14px; transition: 0.3s; }
        .tabs a:hover { color: #005C40; }
        .tabs a.active { border-bottom: 3px solid #005C40; color: #005C40; }
        label { display: block; margin-top: 15px; font-weight: 600; color: #444; font-size: 13px; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 12px; margin: 6px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 14px; transition: border-color 0.3s;}
        input:focus { border-color: #005C40; outline: none; }
        button { background-color: #005C40; color: white; padding: 14px; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-size: 15px; font-weight: bold; margin-top: 20px; transition: background 0.3s; }
        button:hover { background-color: #004494; }
        .lgpd-box { background: #f8f9fa; padding: 15px; border: 1px solid #e9ecef; border-radius: 6px; font-size: 12px; margin: 20px 0; color: #555; line-height: 1.5; }
        .error { color: #721c24; background-color: #f8d7da; padding: 12px; border-radius: 6px; border: 1px solid #f5c6cb; margin-bottom: 20px; font-size: 13px; }
        .success { color: #155724; background-color: #d4edda; padding: 15px; border-radius: 6px; border: 1px solid #c3e6cb; margin-bottom: 20px; text-align: center; font-size: 14px; }
        .password-wrapper { position: relative; margin: 6px 0; }
        .password-wrapper input { padding-right: 40px !important; }
        .toggle-password { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 18px; opacity: 0.6; user-select: none; }
        .toggle-password:hover { opacity: 1; }
    </style>
</head>
<body>

<div class="container">
    <div class="text-center">
        <img src="logo.png" alt="Logotipo <?= htmlspecialchars($app_config['nome_empresa']) ?>" class="logo">
        
        <h2>Acesso Wi-Fi</h2>
        <p class="subtitle">Portal de Visitantes</p>
    </div>

    <?php if(!empty($mensagem)) echo $mensagem; ?>

    <div class="tabs">
        <a href="<?= urlAba('login') ?>" class="<?= $acao == 'login' ? 'active' : '' ?>">Login</a>
        <a href="<?= urlAba('cadastro') ?>" class="<?= $acao == 'cadastro' ? 'active' : '' ?>">Cadastro</a>
        <a href="<?= urlAba('trocar_senha') ?>" class="<?= $acao == 'trocar_senha' ? 'active' : '' ?>">Senha</a>
    </div>

    <?php if ($acao == 'login'): ?>
        <form method="POST" action="<?= urlAba('login') ?>">
            <input type="hidden" name="tipo_form" value="login">
            <label>CPF:</label>
            <input type="text" name="login_cpf" required pattern="\d*" inputmode="numeric" placeholder="Apenas números">
            <label>Senha:</label>
            <div class="password-wrapper">
                <input type="password" name="login_senha" id="login_senha" required placeholder="Sua senha">
                <span class="toggle-password" onclick="alternarSenha('login_senha', this)">👁️</span>
            </div>
            <button type="submit">CONECTAR À REDE</button>
        </form>

    <?php elseif ($acao == 'cadastro'): ?>
        <form method="POST" action="<?= urlAba('cadastro') ?>">
            <input type="hidden" name="tipo_form" value="cadastro">
            <label>Nome Completo:</label>
            <input type="text" name="nome_completo" required placeholder="Como consta no documento">
            <label>CPF:</label>
            <input type="text" name="cpf" required maxlength="11" pattern="\d*" inputmode="numeric" placeholder="Sua senha inicial será o próprio CPF">
            <label>E-mail Corporativo / Pessoal:</label>
            <input type="email" name="email" required placeholder="seu@email.com">

            <div class="lgpd-box">
                <label style="font-weight: normal; cursor: pointer; margin-top: 0;">
                    <input type="checkbox" name="lgpd" required style="width: auto; margin: 0 10px 0 0;">
                    Aceito que meus dados sejam processados para fins de auditoria e segurança de rede (Marco Civil da Internet) e confirmo que os dados informados são verdadeiros.
                </label>
            </div>
            <button type="submit">CADASTRAR E CONECTAR</button>
        </form>

    <?php elseif ($acao == 'trocar_senha'): ?>
        <form method="POST" action="<?= urlAba('trocar_senha') ?>">
            <input type="hidden" name="tipo_form" value="trocar_senha">
            <label>CPF:</label>
            <input type="text" name="cpf_troca" required maxlength="11" pattern="\d*" inputmode="numeric" placeholder="Apenas números">
            
            <label>Senha Atual:</label>
            <div class="password-wrapper">
                <input type="password" name="senha_atual" id="senha_atual" required placeholder="Senha atual ou CPF">
                <span class="toggle-password" onclick="alternarSenha('senha_atual', this)">👁️</span>
            </div>
            
            <label>Nova Senha (Mín. 6 caracteres):</label>
            <div class="password-wrapper">
                <input type="password" name="nova_senha" id="nova_senha" required placeholder="Nova senha segura">
                <span class="toggle-password" onclick="alternarSenha('nova_senha', this)">👁️</span>
            </div>
            
            <label>Confirmar Nova Senha:</label>
            <div class="password-wrapper">
                <input type="password" name="confirma_senha" id="confirma_senha" required placeholder="Repita a nova senha">
                <span class="toggle-password" onclick="alternarSenha('confirma_senha', this)">👁️</span>
            </div>
            <button type="submit">ALTERAR SENHA</button>
        </form>
    <?php endif; ?>
</div>

<script>
// Lógica de validação em tempo real de CPF no Frontend
document.addEventListener("DOMContentLoaded", function() {
    const camposCpf = document.querySelectorAll('input[name="cpf"], input[name="login_cpf"], input[name="cpf_troca"]');
    
    function isCpfValidoJS(cpf) {
        cpf = cpf.replace(/[^\d]+/g,'');
        if(cpf === '' || cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
        let soma = 0;
        for (let i = 0; i < 9; i++) soma += parseInt(cpf.charAt(i)) * (10 - i);
        let resto = 11 - (soma % 11);
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.charAt(9))) return false;
        soma = 0;
        for (let i = 0; i < 10; i++) soma += parseInt(cpf.charAt(i)) * (11 - i);
        resto = 11 - (soma % 11);
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.charAt(10))) return false;
        return true;
    }

    camposCpf.forEach(function(campo) {
        const msgErro = document.createElement('div');
        msgErro.style.color = '#dc3545'; msgErro.style.fontSize = '12px'; msgErro.style.fontWeight = 'bold';
        msgErro.style.display = 'none'; msgErro.style.marginTop = '-5px'; msgErro.style.marginBottom = '10px';
        msgErro.innerText = '❌ CPF inválido.';
        campo.parentNode.insertBefore(msgErro, campo.nextSibling);

        const btnSubmit = campo.closest('form').querySelector('button[type="submit"]');

        campo.addEventListener('input', function(e) {
            let valor = e.target.value.replace(/\D/g, '');
            e.target.value = valor;

            if (valor.length === 11) {
                if (isCpfValidoJS(valor)) {
                    campo.style.borderColor = '#28a745'; 
                    msgErro.style.display = 'none';
                    btnSubmit.disabled = false; btnSubmit.style.opacity = '1';
                } else {
                    campo.style.borderColor = '#dc3545';
                    msgErro.style.display = 'block';
                    btnSubmit.disabled = true; btnSubmit.style.opacity = '0.5';
                }
            } else {
                campo.style.borderColor = '#ccc';
                msgErro.style.display = 'none';
                if (valor.length > 0) {
                    btnSubmit.disabled = true; btnSubmit.style.opacity = '0.5';
                } else {
                    btnSubmit.disabled = false; btnSubmit.style.opacity = '1';
                }
            }
        });
    });
});

function alternarSenha(inputId, icone) {
    var input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text"; icone.innerText = "🙈";
    } else {
        input.type = "password"; icone.innerText = "👁️";
    }
}
</script>
</body>
</html>