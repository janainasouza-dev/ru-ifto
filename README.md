# RU IFTO — Restaurante Universitário (PHP + OO + MySQL)

Sistema web para reserva de almoço, controle de tickets do auxílio estudantil e gestão do Restaurante Universitário do IFTO.
Esta versão implementa a **parte funcional** do protótipo front-end: PHP orientado a objetos, banco MySQL, PDO com *prepared statements*, sessões e senhas criptografadas.

## Desenvolvedores
Janaína F. de Souza · Maria Fernanda C. Pereira · Rodrigo G. Brito · Eduardo F. M. Gomes · Emerson F. C. de Oliveira

## Como rodar (XAMPP / WAMP / Laragon)
1. Copie a pasta do projeto para `htdocs/ruifto` (ou `www/ruifto`).
2. Inicie **Apache** e **MySQL**.
3. Importe `database/ruifto.sql` (phpMyAdmin → Importar, ou `mysql -u root < database/ruifto.sql`). O script cria o banco `ruifto`, as tabelas e os dados iniciais.
4. Se seu MySQL não for `root` sem senha, ajuste `config/config.php` (`DB_USER`, `DB_PASS`) — ou use as variáveis de ambiente `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
5. Acesse `http://localhost/ruifto/`.

Alternativa sem Apache: na pasta **acima** do projeto, `php -S localhost:8000` e abra `http://localhost:8000/ruifto/`.
Requisitos: PHP 8.1+ com `pdo_mysql`.

## Contas para teste
| Perfil | E-mail | Senha |
|---|---|---|
| Administrador | admin@ru.com | admin123 |
| Aluno | joao@email.com | 123456 |
| Aluno | maria@email.com | 123456 |
| Aluno | janaina@email.com | 123456 |
| Visitante | botão **Entrar como Visitante** (sem senha) | — |

## Banco de dados (`database/ruifto.sql`)
- **usuarios** — admin, aluno e visitante (senha em `bcrypt`, auxílio, tickets, valor avulso).
- **refeicoes** — almoço de cada dia (cardápio, vagas totais/disponíveis, status).
- **reservas** — usuário × refeição (status `confirmada/utilizada/cancelada`, valor, datas).
- **tickets** — histórico de consumo/devolução do auxílio.

Relacionamentos: `reservas → usuarios`, `reservas → refeicoes`, `tickets → usuarios`, `tickets → reservas` (chaves estrangeiras).

## Orientação a objetos (`src/`)
| Classe | Responsabilidade |
|---|---|
| `Database` | Conexão PDO única (Singleton) |
| `Model` (abstrata) | `fromRow`, `buscar`, `excluir` reutilizados pelas entidades |
| `Usuario` | Entidade + validação + CRUD + hash/verificação de senha |
| `Refeicao` | Entidade + validação + CRUD + almoço do dia |
| `Reserva` | Regras de negócio: vagas, tickets, visitante, prazo de cancelamento (transações) |
| `Ticket` | Histórico de tickets |
| `Auth` | Login/logout por sessão, proteção de páginas (`exigirLogin`, `exigirAdmin`) |

## CRUD
| Entidade | Create | Read | Update | Delete |
|---|---|---|---|---|
| Usuários | `admin/usuario_form.php`, `register.php` | `admin/usuarios.php` (busca/filtro) | `admin/usuario_form.php`, `perfil.php` | `admin/usuarios.php` |
| Refeições | `admin/refeicao_form.php` | `admin/refeicoes.php`, `dashboard.php` | `admin/refeicao_form.php` | `admin/refeicoes.php` |
| Reservas | `reservar.php` | `dashboard.php`, `historico.php`, `admin/reservas.php` | `cancelar.php`, `admin/reservas.php` (utilizada/cancelada) | `admin/reservas.php` |
| Tickets | automático ao reservar | `tickets.php` | devolução automática ao cancelar | — |

## Regras de negócio (mantidas do protótipo)
- Aluno **com auxílio ativo** reserva usando **tickets disponíveis**, sem pagar; 1 ticket é descontado ao reservar e devolvido ao cancelar.
- Aluno **sem auxílio** reserva normalmente, **paga R$ 13,00 no caixa** (como o visitante) e não usa tickets; ao cancelar, nenhum ticket é devolvido.
- Visitante não usa tickets, paga **R$ 13,00** no caixa e só pode ter **1 reserva ativa**.
- Cancelamento só até **9:30h** (fuso `America/Araguaina`).
- Vagas decrementadas em transação (`SELECT ... FOR UPDATE`); status vira **Lotado** ao zerar.
- Só o almoço **do dia** pode ser reservado.

## Segurança
- **PDO + prepared statements** em todas as consultas (sem SQL Injection).
- Senhas com `password_hash()` / `password_verify()` (bcrypt).
- Sessões: cookie `HttpOnly` + `SameSite`, `session_regenerate_id()` no login, logout destrói a sessão.
- Páginas internas exigem login; `/admin/*` exige perfil administrador.
- **Token CSRF** em todos os formulários POST; saída escapada com `htmlspecialchars` (XSS).
- Validação de dados no PHP (e-mail, tamanho de senha, datas, vagas, tickets).

## Estrutura
```
ruifto/
├── index.php  login.php  register.php  logout.php  visitante.php
├── dashboard.php  reservar.php  cancelar.php  tickets.php  historico.php  perfil.php
├── admin/            index, usuarios, usuario_form, refeicoes, refeicao_form, reservas
├── src/              Database, Model, Usuario, Refeicao, Reserva, Ticket, Auth, helpers
├── config/config.php
├── includes/         header.php, footer.php
├── database/ruifto.sql
└── assets/           css/ img/ icons/
```

## Correções importantes e melhorias 

- Exclusão em cascata: apagar um usuário ou refeição remove as reservas (e os tickets do usuário) vinculadas.
- Melhoria planejada: bloquear a exclusão quando houver histórico e desativar usuários em vez de excluí-los
  (script pronto em migracao_sem_cascade.sql para ser implantado em um momento mais oportuno).
- QR Code Pix e e-mail de confirmação com rabbitMQ.