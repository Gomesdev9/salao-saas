# Salão SaaS

Sistema web para salões de beleza: agenda, clientes, profissionais, serviços, lembretes por WhatsApp e financeiro básico. Cada salão tem sua própria conta e só enxerga os próprios dados.

Feito com **Laravel 13**, **Blade**, **Tailwind CSS** e **SQLite** no desenvolvimento (MySQL em produção).

## O que já funciona (Fase 1)

- Cadastro de salão + login (cada conta nova cria um salão)
- **Agenda do dia** com uma coluna por profissional: clique num horário vazio para agendar, cores por situação (agendado, confirmado, atendido, faltou, cancelado) e bloqueio de horário duplicado
- Profissionais: telefone, % de comissão, serviços que cada um faz, ativo/inativo
- Serviços: categoria, duração, preço, ativo/inativo
- Clientes: WhatsApp, aniversário, observações, busca por nome ou telefone
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
| `lang/pt_BR` | Textos e mensagens de erro em português |
| `tests/Feature` | Testes automáticos |

### Como o isolamento entre salões funciona

Todo usuário pertence a um salão (`users.salon_id`). Os models `Professional`, `Service` e `Client` usam o trait `BelongsToSalon`, que:

1. em toda consulta, adiciona `where salon_id = <salão do usuário logado>`;
2. ao criar um registro, preenche o `salon_id` sozinho.

Por isso os controllers podem fazer só `Client::all()` ou `Client::create(...)`, sem se preocupar com o salão. Se alguém tentar abrir `/clientes/5/editar` de outro salão, recebe "404 não encontrado".
