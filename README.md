# Salão SaaS

Sistema web para salões de beleza: agenda, clientes, profissionais, serviços, lembretes por WhatsApp e financeiro básico. Cada salão tem sua própria conta e só enxerga os próprios dados.

Feito com **Laravel 13**, **Blade**, **Tailwind CSS** e **SQLite** no desenvolvimento (MySQL em produção).

## O que já funciona (Fase 1)

- **Página de divulgação** do sistema em `/`, com o convite para a empresa se cadastrar
- Cadastro da empresa pelo proprietário (nome, tipo de negócio, WhatsApp e cidade) + login. Cada conta nova cria uma empresa, e o painel mostra os primeiros passos até o primeiro agendamento
- **Planos**: Essencial (R$ 49,90, até 2 profissionais ativos), Profissional (R$ 89,90, até 6) e Premium (R$ 149,90, sem limite). A empresa escolhe no cadastro, ganha 14 dias grátis e troca de plano na tela "Meu plano". Preços e limites ficam em `app/Enums/Plan.php`. A cobrança automática ainda não está ligada
- **Agenda do dia** com uma coluna por profissional: clique num horário vazio para agendar, cores por situação (agendado, confirmado, atendido, faltou, cancelado) e bloqueio de horário duplicado
- Profissionais: telefone, % de comissão, serviços que cada um faz, ativo/inativo
- Serviços: categoria, duração, preço, ativo/inativo
- Clientes: WhatsApp, aniversário, observações, busca por nome ou telefone
- **Financeiro** (planos Profissional e Premium; no Essencial só o receber pagamento): receber o pagamento do atendimento (Pix, dinheiro com troco, débito ou crédito parcelado, com desconto), visão do mês com gráfico, lançamentos de entradas e despesas, e comissões de cada profissional com vales
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
