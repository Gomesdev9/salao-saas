# Salão SaaS

Sistema web para salões de beleza: agenda, clientes, profissionais, serviços, lembretes por WhatsApp e financeiro básico. Cada salão tem sua própria conta e só enxerga os próprios dados.

Feito com **Laravel 13**, **Blade**, **Tailwind CSS** e **SQLite** no desenvolvimento (Postgres do **Supabase** para os dados de verdade).

## O que já funciona (Fase 1)

- **Página de divulgação** do sistema em `/`, com o convite para a empresa se cadastrar
- Cadastro da empresa pelo proprietário (nome, tipo de negócio, WhatsApp e cidade) + login. Cada conta nova cria uma empresa, e o painel mostra os primeiros passos até o primeiro agendamento
- **Planos**: Essencial (R$ 49,90, até 2 profissionais ativos), Profissional (R$ 89,90, até 6) e Premium (R$ 149,90, sem limite). A empresa escolhe no cadastro, ganha 14 dias grátis e troca de plano na tela "Meu plano". Preços e limites ficam em `app/Enums/Plan.php`. A cobrança automática ainda não está ligada
- **Agenda do dia** com uma coluna por profissional: clique num horário vazio para agendar, cores por situação (agendado, confirmado, atendido, faltou, cancelado) e bloqueio de horário duplicado
- Profissionais: telefone, % de comissão, serviços que cada um faz, ativo/inativo
- Serviços: categoria, duração, preço, ativo/inativo
- Clientes: WhatsApp, aniversário, observações, busca por nome ou telefone
- **Financeiro** (planos Profissional e Premium; no Essencial só o receber pagamento): receber o pagamento do atendimento (Pix, dinheiro com troco, débito ou crédito parcelado, com desconto), visão do mês com gráfico, lançamentos de entradas e despesas, e comissões de cada profissional com vales
- **Agendamento online** (planos Profissional e Premium, e qualquer plano no teste grátis): cada empresa tem a página `/agendar/{slug}`, em que a cliente escolhe o procedimento, o profissional (ou "sem preferência"), o dia e um horário livre, informa nome e WhatsApp e confirma. O horário cai na agenda como "Agendado". Em "Meus horários" ela remarca, cancela ou agenda de novo, pelo link que fica guardado no aparelho. O horário de funcionamento é fixo por enquanto (seg a sex 9h–19h, sáb 9h–14h), em `Salon::openingHours()`
- Isolamento entre salões (um salão nunca vê dados de outro)
- Tudo em português

Próximo passo: **agendamento online** pelo cliente (Fase 2).

## Como rodar no Windows

### 1. Instale as ferramentas (só uma vez)

1. **Laravel Herd**: baixe em <https://herd.laravel.com/windows> e instale. Ele já traz PHP e Composer.
2. **Node.js (versão LTS)**: <https://nodejs.org>
3. **Git**: <https://git-scm.com/download/win>

Abra o **PowerShell** e confira se está tudo certo:

```powershell
php -v
composer -V
node -v
git --version
```

### 2. Baixe o projeto

```powershell
cd $HOME\Herd
git clone https://github.com/Gomesdev9/salao-saas.git
cd salao-saas
```

### 3. Configure

```powershell
composer install
npm install
copy .env.example .env
php artisan key:generate
New-Item database\database.sqlite -ItemType File
php artisan migrate --seed
npm run build
```

O `--seed` cria um salão de demonstração com profissionais, serviços e clientes.

### 4. Abra no navegador

Como o projeto está dentro da pasta `Herd`, ele já abre em **<http://salao-saas.test>**.
(Sem o Herd, rode `php artisan serve` e abra <http://localhost:8000>.)

Login de demonstração:

- E-mail: `dono@salao.test`
- Senha: `password`

Enquanto estiver mexendo no visual, deixe `npm run dev` rodando num terminal: o Tailwind recompila sozinho a cada alteração.

**Depois de um `git pull`**, rode `php artisan migrate` (cria as tabelas novas) e `npm run build`. Para recomeçar com os dados de demonstração, use `php artisan migrate:fresh --seed` (apaga tudo do banco local).

### 5. Rodar os testes

```powershell
php artisan test
```

## Banco de dados no Supabase

No computador o sistema usa um arquivo SQLite, que é ótimo para testar mas fica só na sua máquina. Para guardar os dados de verdade, o sistema se liga ao **Postgres do Supabase** (tem plano grátis). Quem fala com o banco é o Laravel; as telas nunca acessam o Supabase direto.

### 1. Crie o projeto no Supabase (só uma vez)

1. Entre em <https://supabase.com>, crie a conta e clique em **New project**.
2. Dê um nome (ex.: `salao-saas`), crie uma **senha do banco** e guarde-a. Use só letras e números (o botão *Generate a password* já faz assim); símbolos como `@` ou `#` quebram o endereço de conexão.
3. Em **Region**, escolha **South America (São Paulo)**.
4. Espere o projeto ficar pronto (uns 2 minutos).

### 2. Copie o endereço de conexão

1. No topo do painel do projeto, clique em **Connect**.
2. Em **Connection string**, escolha o tipo **URI** e copie o da **Session pooler** (porta `5432`, endereço terminando em `pooler.supabase.com`). Não use a *Direct connection* (ela não funciona em muitas internets de casa) nem a *Transaction pooler* (porta `6543`, não combina com o Laravel).
3. Troque `[YOUR-PASSWORD]` pela senha que você criou.

### 3. Crie as tabelas pelo GitHub (o jeito mais fácil)

Assim não é preciso mexer no PHP do computador:

1. No GitHub, abra o repositório e vá em **Settings > Secrets and variables > Actions > New repository secret**.
2. Em **Name**, escreva `SUPABASE_DB_URL`. Em **Secret**, cole o endereço da Session pooler (já com a senha no lugar de `[YOUR-PASSWORD]`). Clique em **Add secret**. O GitHub guarda a senha escondida: ninguém consegue ver depois, nem nos registros.
3. Vá na aba **Actions**, clique em **Criar tabelas no Supabase** e depois em **Run workflow**. Na primeira vez, marque **Colocar o salão de demonstração** se quiser os dados de exemplo.
4. Quando ficar verde (uns 2 minutos), as tabelas aparecem no **Table Editor** do Supabase.

Sempre que o sistema ganhar tabelas novas, rode de novo (sem marcar a demonstração).

### 4. (Opcional) Ligue o sistema do computador ao Supabase

No arquivo `.env`, troque a parte do banco por:

```env
DB_CONNECTION=pgsql
DB_URL="postgresql://postgres.abcdefghijklmnop:SuaSenha123@aws-0-sa-east-1.pooler.supabase.com:5432/postgres"
DB_SSLMODE=require
```

(com o endereço que você copiou no lugar do exemplo). Depois crie as tabelas lá:

```powershell
php artisan config:clear
php artisan migrate --seed
```

O `--seed` coloca o salão de demonstração; deixe ele de fora se quiser começar vazio. Abra o sistema normalmente: tudo o que for cadastrado agora fica salvo no Supabase, e dá para ver as tabelas em **Table Editor** no painel.

Se aparecer `could not find driver`, o PHP está sem o Postgres ligado. No Herd: **Settings > PHP > Extensions** e marque `pdo_pgsql` e `pgsql` (ou, no `php.ini`, tire o `;` da frente de `extension=pdo_pgsql` e `extension=pgsql`).

Para voltar ao SQLite local, é só pôr `DB_CONNECTION=sqlite` e apagar (ou comentar) o `DB_URL`.

### Segurança

O Supabase abre automaticamente uma API pública para as tabelas do banco. Como o sistema não usa essa API, a migração `lock_supabase_data_api_out_of_tables` fecha essa porta: as chaves públicas do Supabase (`anon`) não conseguem ler nem gravar nada, e a segurança por linha (RLS) fica ligada em todas as tabelas. A separação entre salões continua sendo feita pelo sistema (`BelongsToSalon`). **Nunca** coloque a senha do banco nem a chave `service_role` em arquivos que vão para o GitHub; elas ficam só no `.env`, que já é ignorado pelo Git.

## Onde fica cada coisa

| Pasta / arquivo | O que tem |
| --- | --- |
| `routes/web.php` | As URLs do sistema (`/agenda`, `/profissionais`, `/servicos`, `/clientes`) |
| `app/Http/Controllers` | A lógica de cada tela (listar, salvar, editar, remover) |
| `app/Models` | As tabelas como classes PHP (`Salon`, `Professional`, `Service`, `Client`, `Appointment`) |
| `app/Enums/AppointmentStatus.php` | As situações de um agendamento, com nome e cor |
| `app/Models/Concerns/BelongsToSalon.php` | O filtro automático que separa os dados de cada salão |
| `database/migrations` | A estrutura das tabelas do banco |
| `database/seeders/DatabaseSeeder.php` | Os dados de demonstração |
| `resources/views` | As telas em Blade (HTML com Tailwind) |
| `resources/views/layouts/sidebar.blade.php` | O menu lateral escuro do computador |
| `resources/views/layouts/bottom-nav.blade.php` | O menu flutuante do celular (o "Mais" guarda Profissionais, Serviços e Financeiro) |
| `app/Http/Controllers/PaymentController.php` | Receber o pagamento de um atendimento |
| `app/Http/Controllers/FinanceController.php` | Visão do mês e lista de lançamentos do financeiro |
| `app/Http/Controllers/CommissionController.php` | Comissões do mês (comissão − vales = a pagar) |
| `app/Support/Money.php` | Mostrar e ler valores em reais (`R$ 1.234,56`) |
| `resources/views/components` | Peças reaproveitadas nas telas: botões, campos, `x-page` (título + ações), `x-avatar`, `x-icon` |
| `tailwind.config.js` | As cores do design com nomes em português (`creme`, `cafe`, `caramelo`, `tinta`...) e as fontes Inter e Playfair Display |
| `resources/css/app.css` | Classes prontas do visual: `.cartao`, `.campo`, `.selo` |
| `lang/pt_BR` | Textos e mensagens de erro em português |
| `tests/Feature` | Testes automáticos |

### Como o isolamento entre salões funciona

Todo usuário pertence a um salão (`users.salon_id`). Os models `Professional`, `Service` e `Client` usam o trait `BelongsToSalon`, que:

1. em toda consulta, adiciona `where salon_id = <salão do usuário logado>`;
2. ao criar um registro, preenche o `salon_id` sozinho.

Por isso os controllers podem fazer só `Client::all()` ou `Client::create(...)`, sem se preocupar com o salão. Se alguém tentar abrir `/clientes/5/editar` de outro salão, recebe "404 não encontrado".
