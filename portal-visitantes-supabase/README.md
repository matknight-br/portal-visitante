# Portal Visitantes WiFi - Versao Supabase

Versao moderna do portal cativo usando React + TypeScript + Supabase.

## Estrutura

```
portal-visitantes-supabase/
+-- src/
|   +-- App.tsx          # Componente principal com formularios
|   +-- main.tsx         # Ponto de entrada React
|   +-- supabase.ts      # Cliente Supabase + funcoes de banco
|   +-- index.css        # Estilos do portal
+-- index.html
+-- package.json
+-- vite.config.ts
+-- tsconfig.json
```

## Diferencas da Versao PHP

| Aspecto           | Versao PHP                    | Versao Supabase                    |
|-------------------|-------------------------------|-------------------------------------|
| Backend           | PHP + MySQL                   | React + Supabase (PostgreSQL)      |
| Autenticacao      | CPF + senha local             | CPF + senha local (Supabase table) |
| Hash de senha     | bcrypt (PHP password_hash)    | SHA-256 (browser)                  |
| Integacao Omada   | PHP cURL                      | Necessaria Edge Function           |
| Validacao SERPRO  | PHP cURL                      | Necessaria Edge Function           |

## Seguranca Implementada

1. **Variaveis de ambiente** - Credenciais em `.env` (nao commitadas)
2. **RLS habilitado** - Policias para anon + authenticated
3. **CPF unico** - Constraint no banco de dados
4. **Logs de auditoria** - Campos de ultimo_acesso, mac_address, ip_address

## Como Executar

```bash
cd portal-visitantes-supabase
npm install
npm run dev
```

## Proximos Passos (Producao)

1. Criar Edge Function para hash bcrypt (seguranca server-side)
2. Criar Edge Function para integracao Omada SDN
3. Criar Edge Function para validacao SERPRO
4. Implementar rate limiting
5. Adicionar logs para firewall via Syslog

## Parametros de URL (Portal Cativo)

O portal recebe parametros do controlador Omada:

```
/?clientMac=AA:BB:CC:DD:EE:FF&apMac=11:22:33:44:55:66&ssidName=Visitantes&radioId=1&clientIp=192.168.1.100&site=default
```

## Tabela Supabase (usuarios)

```sql
CREATE TABLE usuarios (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  cpf text UNIQUE NOT NULL,
  nome text NOT NULL,
  email text NOT NULL,
  senha_hash text NOT NULL,
  data_cadastro timestamptz DEFAULT now(),
  ultimo_acesso timestamptz,
  mac_address text,
  ip_address text
);
```
