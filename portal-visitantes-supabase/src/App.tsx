import { useState } from 'react';
import { buscarUsuarioPorCpf, criarUsuario, atualizarUltimoAcesso } from './supabase';

type TabType = 'login' | 'cadastro' | 'trocar_senha';

function isCpfValido(cpf: string): boolean {
  cpf = cpf.replace(/\D/g, '');
  if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;

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

async function hashPassword(password: string): Promise<string> {
  const encoder = new TextEncoder();
  const data = encoder.encode(password);
  const hashBuffer = await crypto.subtle.digest('SHA-256', data);
  const hashArray = Array.from(new Uint8Array(hashBuffer));
  return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}

export default function App() {
  const [activeTab, setActiveTab] = useState<TabType>('login');
  const [mensagem, setMensagem] = useState<{ tipo: 'sucesso' | 'erro'; texto: string } | null>(null);
  const [loading, setLoading] = useState(false);

  const wifiParams = (window as any).wifiParams || {};

  async function handleLogin(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setLoading(true);
    setMensagem(null);

    const form = e.currentTarget;
    const cpf = (form.login_cpf as HTMLInputElement).value.replace(/\D/g, '');
    const senha = (form.login_senha as HTMLInputElement).value;

    if (!isCpfValido(cpf)) {
      setMensagem({ tipo: 'erro', texto: 'CPF inválido.' });
      setLoading(false);
      return;
    }

    try {
      const usuario = await buscarUsuarioPorCpf(cpf);
      const senhaHash = await hashPassword(senha);

      if (usuario && usuario.senha_hash === senhaHash) {
        await atualizarUltimoAcesso(cpf);
        setMensagem({ tipo: 'sucesso', texto: `Bem-vindo(a), ${usuario.nome}! Conexão liberada.` });
        setTimeout(() => {
          window.location.href = 'https://www.google.com.br';
        }, 2000);
      } else {
        setMensagem({ tipo: 'erro', texto: 'CPF ou Senha incorretos.' });
      }
    } catch (err) {
      setMensagem({ tipo: 'erro', texto: 'Erro ao conectar com o servidor.' });
    }

    setLoading(false);
  }

  async function handleCadastro(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setLoading(true);
    setMensagem(null);

    const form = e.currentTarget;
    const nome = (form.nome_completo as HTMLInputElement).value.trim();
    const cpf = (form.cpf as HTMLInputElement).value.replace(/\D/g, '');
    const senha = (form.senha as HTMLInputElement).value;
    const email = (form.email as HTMLInputElement).value.trim();
    const lgpd = (form.lgpd as HTMLInputElement).checked;

    if (!lgpd) {
      setMensagem({ tipo: 'erro', texto: 'Você deve aceitar os termos de uso.' });
      setLoading(false);
      return;
    }

    if (!isCpfValido(cpf)) {
      setMensagem({ tipo: 'erro', texto: 'CPF inválido.' });
      setLoading(false);
      return;
    }

    if (senha.length < 6) {
      setMensagem({ tipo: 'erro', texto: 'A senha deve ter no mínimo 6 caracteres.' });
      setLoading(false);
      return;
    }

    try {
      const existente = await buscarUsuarioPorCpf(cpf);
      if (existente) {
        setMensagem({ tipo: 'erro', texto: 'Este CPF já possui cadastro. Use a aba de Login.' });
        setLoading(false);
        return;
      }

      const senhaHash = await hashPassword(senha);
      await criarUsuario(cpf, nome, email, senhaHash, wifiParams.clientMac, wifiParams.clientIp);

      setMensagem({ tipo: 'sucesso', texto: 'Cadastro realizado e conexão liberada!' });
      setTimeout(() => {
        window.location.href = 'https://www.google.com.br';
      }, 2000);
    } catch (err) {
      setMensagem({ tipo: 'erro', texto: 'Erro ao realizar cadastro.' });
    }

    setLoading(false);
  }

  async function handleTrocarSenha(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setLoading(true);
    setMensagem(null);

    const form = e.currentTarget;
    const cpf = (form.cpf_troca as HTMLInputElement).value.replace(/\D/g, '');
    const senhaAtual = (form.senha_atual as HTMLInputElement).value;
    const novaSenha = (form.nova_senha as HTMLInputElement).value;
    const confirmaSenha = (form.confirma_senha as HTMLInputElement).value;

    if (!isCpfValido(cpf)) {
      setMensagem({ tipo: 'erro', texto: 'CPF inválido.' });
      setLoading(false);
      return;
    }

    if (novaSenha !== confirmaSenha) {
      setMensagem({ tipo: 'erro', texto: 'As novas senhas não coincidem.' });
      setLoading(false);
      return;
    }

    if (novaSenha.length < 6) {
      setMensagem({ tipo: 'erro', texto: 'A senha deve ter no mínimo 6 caracteres.' });
      setLoading(false);
      return;
    }

    setMensagem({ tipo: 'sucesso', texto: 'Funcionalidade em desenvolvimento. Use o portal PHP para troca de senha.' });
    setLoading(false);
  }

  function renderMessage() {
    if (!mensagem) return null;
    return (
      <div className={mensagem.tipo === 'sucesso' ? 'success' : 'error'}>
        {mensagem.texto}
      </div>
    );
  }

  return (
    <div className="container">
      <div className="header">
        <h2>Acesso Wi-Fi</h2>
        <p className="subtitle">Portal Cativo de Visitantes</p>
      </div>

      {renderMessage()}

      <div className="tabs">
        <button
          className={activeTab === 'login' ? 'active' : ''}
          onClick={() => { setActiveTab('login'); setMensagem(null); }}
        >
          Login
        </button>
        <button
          className={activeTab === 'cadastro' ? 'active' : ''}
          onClick={() => { setActiveTab('cadastro'); setMensagem(null); }}
        >
          Cadastro
        </button>
        <button
          className={activeTab === 'trocar_senha' ? 'active' : ''}
          onClick={() => { setActiveTab('trocar_senha'); setMensagem(null); }}
        >
          Trocar Senha
        </button>
      </div>

      {activeTab === 'login' && (
        <form onSubmit={handleLogin}>
          <label>CPF:</label>
          <input type="text" name="login_cpf" required placeholder="Apenas números" maxLength={11} />

          <label>Senha:</label>
          <input type="password" name="login_senha" required placeholder="Sua senha" />

          <button type="submit" disabled={loading}>
            {loading ? 'Conectando...' : 'CONECTAR À REDE'}
          </button>
        </form>
      )}

      {activeTab === 'cadastro' && (
        <form onSubmit={handleCadastro}>
          <label>Nome Completo:</label>
          <input type="text" name="nome_completo" required placeholder="Como consta no documento" />

          <label>CPF:</label>
          <input type="text" name="cpf" required maxLength={11} placeholder="Somente números" />

          <label>E-mail:</label>
          <input type="email" name="email" required placeholder="seu@email.com" />

          <label>Senha:</label>
          <input type="password" name="senha" required placeholder="Senha segura (mín. 6 caracteres)" />

          <div className="lgpd-box">
            <label className="checkbox-label">
              <input type="checkbox" name="lgpd" required />
              <span>
                Aceito que meus dados sejam processados para fins de auditoria e segurança de rede
                (Marco Civil da Internet) e confirmo que os dados informados são verdadeiros.
              </span>
            </label>
          </div>

          <button type="submit" disabled={loading}>
            {loading ? 'Cadastrando...' : 'CADASTRAR E CONECTAR'}
          </button>
        </form>
      )}

      {activeTab === 'trocar_senha' && (
        <form onSubmit={handleTrocarSenha}>
          <label>CPF:</label>
          <input type="text" name="cpf_troca" required maxLength={11} placeholder="Apenas números" />

          <label>Senha Atual:</label>
          <input type="password" name="senha_atual" required placeholder="Senha atual" />

          <label>Nova Senha (Mín. 6 caracteres):</label>
          <input type="password" name="nova_senha" required placeholder="Nova senha segura" />

          <label>Confirmar Nova Senha:</label>
          <input type="password" name="confirma_senha" required placeholder="Repita a nova senha" />

          <button type="submit" disabled={loading}>
            {loading ? 'Alterando...' : 'ALTERAR SENHA'}
          </button>
        </form>
      )}
    </div>
  );
}
