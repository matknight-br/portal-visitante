import { createClient } from '@supabase/supabase-js';

const supabaseUrl = import.meta.env.VITE_SUPABASE_URL;
const supabaseAnonKey = import.meta.env.VITE_SUPABASE_ANON_KEY;

export const supabase = createClient(supabaseUrl, supabaseAnonKey);

export interface Usuario {
  id: string;
  cpf: string;
  nome: string;
  email: string;
  senha_hash: string;
  data_cadastro: string;
  ultimo_acesso: string | null;
  mac_address: string | null;
  ip_address: string | null;
}

export async function buscarUsuarioPorCpf(cpf: string): Promise<Usuario | null> {
  const { data, error } = await supabase
    .from('usuarios')
    .select('*')
    .eq('cpf', cpf)
    .maybeSingle();

  if (error) {
    console.error('Erro ao buscar usuario:', error);
    throw error;
  }

  return data;
}

export async function criarUsuario(
  cpf: string,
  nome: string,
  email: string,
  senhaHash: string,
  macAddress?: string,
  ipAddress?: string
): Promise<Usuario> {
  const { data, error } = await supabase
    .from('usuarios')
    .insert({
      cpf,
      nome,
      email,
      senha_hash: senhaHash,
      mac_address: macAddress || null,
      ip_address: ipAddress || null,
    })
    .select()
    .single();

  if (error) {
    console.error('Erro ao criar usuario:', error);
    throw error;
  }

  return data;
}

export async function atualizarUltimoAcesso(cpf: string): Promise<void> {
  await supabase
    .from('usuarios')
    .update({ ultimo_acesso: new Date().toISOString() })
    .eq('cpf', cpf);
}
