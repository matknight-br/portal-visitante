/*
# Create usuarios table (Portal Visitantes WiFi)

1. New Tables
- `usuarios`: Armazena dados dos visitantes WiFi
  - `id` (uuid, primary key)
  - `cpf` (text, unique, 11 caracteres)
  - `nome` (text, nome completo do visitante)
  - `email` (text, email do visitante)
  - `senha_hash` (text, hash bcrypt da senha)
  - `data_cadastro` (timestamptz, data/hora do cadastro)
  - `ultimo_acesso` (timestamptz, último login realizado)
  - `mac_address` (text, MAC do dispositivo)
  - `ip_address` (text, IP do dispositivo)

2. Security
- Enable RLS on `usuarios`.
- Políticas para anon + authenticated (portal usa anon key).
- CPF é identificador único do sistema.

3. Notes
- Este é um sistema single-tenant para portal cativo WiFi.
- Não usa auth.uid() pois a autenticação é via CPF.
- Todos os visitantes podem se cadastrar e fazer login.
- Logs de auditoria são enviados via Syslog externo.
*/

CREATE TABLE IF NOT EXISTS usuarios (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    cpf text UNIQUE NOT NULL CHECK (char_length(cpf) = 11),
    nome text NOT NULL CHECK (char_length(nome) >= 3),
    email text NOT NULL,
    senha_hash text NOT NULL,
    data_cadastro timestamptz DEFAULT now(),
    ultimo_acesso timestamptz,
    mac_address text,
    ip_address text
);

-- Índices para consultas frequentes
CREATE INDEX IF NOT EXISTS idx_usuarios_cpf ON usuarios(cpf);
CREATE INDEX IF NOT EXISTS idx_usuarios_data_cadastro ON usuarios(data_cadastro DESC);

-- Habilitar RLS
ALTER TABLE usuarios ENABLE ROW LEVEL SECURITY;

-- Políticas para permitir CRUD pelo portal (anon key)
DROP POLICY IF EXISTS "anon_select_usuarios" ON usuarios;
CREATE POLICY "anon_select_usuarios" ON usuarios FOR SELECT
    TO anon, authenticated USING (true);

DROP POLICY IF EXISTS "anon_insert_usuarios" ON usuarios;
CREATE POLICY "anon_insert_usuarios" ON usuarios FOR INSERT
    TO anon, authenticated WITH CHECK (true);

DROP POLICY IF EXISTS "anon_update_usuarios" ON usuarios;
CREATE POLICY "anon_update_usuarios" ON usuarios FOR UPDATE
    TO anon, authenticated USING (true) WITH CHECK (true);

DROP POLICY IF EXISTS "anon_delete_usuarios" ON usuarios;
CREATE POLICY "anon_delete_usuarios" ON usuarios FOR DELETE
    TO anon, authenticated USING (true);