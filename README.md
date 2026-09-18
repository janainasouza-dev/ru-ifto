# RU IFTO — Restaurante Universitário

Sistema web para otimizar o processo de reserva de refeições, controlar o fluxo de alunos e gerenciar o auxílio estudantil no Restaurante Universitário (RU) do Instituto Federal do Tocantins.

## O Problema

O Restaurante Universitário do IFTO enfrenta desafios como:

- Filas longas na hora do almoço;
- Dificuldade de controle de acesso;
- Desperdício de comida por falta de previsibilidade da demanda.

O **RU IFTO** propõe uma solução digital para resolver esses gargalos, trazendo agilidade ao atendimento e substituindo o controle manual por um sistema de reservas.

## Público-Alvo

- **Alunos** (com e sem auxílio estudantil ativo)
- **Visitantes** (sem necessidade de cadastro prévio)
- **Administradores** (gestão do sistema)

##  Funcionalidades

### Login e Cadastro
- Validação de campos (e-mail e senha);
- Opção **"Entrar como Visitante"**, sem necessidade de cadastro prévio.

### Dashboard do Aluno
- Visualização do cardápio do dia (almoço);
- Reserva de refeição disponível apenas se o aluno tiver **auxílio ativo** e **tickets disponíveis**;
- Desconto automático de 1 ticket ao confirmar a reserva;
- Histórico de tickets consumidos;
- Histórico de reservas realizadas.

### Dashboard do Visitante
- Não utiliza tickets do auxílio estudantil;
- Valor da refeição cobrado à parte (**R$ 13,00**), pago diretamente no caixa do RU;
- Limite de **1 reserva ativa por vez**.

### Painel Administrativo
- Acesso restrito (tentativa de acesso negado para usuários não administradores);
- Estatísticas gerais: total de alunos, alunos com auxílio ativo, reservas do dia e receita avulsa;
- Listagem de alunos cadastrados com status do auxílio;
- Controle de vagas do almoço do dia em tempo real.

## Regras de Negócio

- **Controle de Tickets:** o sistema abate automaticamente 1 ticket ao reservar e devolve o ticket caso a reserva seja cancelada;
- **Prazo de Cancelamento:** cancelamentos só são permitidos até as **10h da manhã** (`dentroDoPrazoCancelamento`);
- **Gestão de Vagas:** as vagas disponíveis são decrementadas em tempo real, e o status da refeição muda automaticamente para "Lotado" quando esgotadas;
- **Separação de Perfis:** Admin, Aluno e Visitante possuem visões e permissões distintas dentro do sistema.

##  Arquitetura e Tecnologias

Projeto desenvolvido com **HTML5, CSS3 e JavaScript puro** (Vanilla JS), sem frameworks ou dependências de build.

### Estrutura de Arquivos

```
├── index.html          # Ponto de entrada e estrutura semântica
├── css/
│   └── style.css       # Estilização, responsividade e design system
├── js/
│   ├── data.js          # "Banco de dados" simulado (usuários, refeições, reservas)
│   ├── auth.js           # Lógica de autenticação e controle de sessão
│   ├── dashboard.js  # Renderização dinâmica das telas do usuário
│   └── app.js             # Inicialização e roteamento básico
└── assets/icons/       # Favicons e ícones do PWA
```

## Contas para teste

- Administrador: admin@ru.com / admin123
- João Silva: joao@email.com / 123456
- Maria Santos: maria@email.com / 123456

## Como rodar o protótipo do projeto

-git clone https://github.com/janainasouza-dev/ruifto.git
-coloque no navegador o arquivo index.html

### Destaques Técnicos

- **Semântica HTML:** uso de tags como `<nav>`, `<main>`, `<section>`, `<article>` e `<footer>` para estruturar o conteúdo corretamente;
- **Responsividade:** layout adaptável a celulares e tablets, com uso de *Media Queries* no CSS;
- **CSS:** uso de variáveis CSS (`:root`) para manter a paleta de cores institucional (verde) consistente e facilitar a manutenção, além de Flexbox e Grid para o layout responsivo;
- **JavaScript / SPA:** aplicação estruturada como uma *Single Page Application* (SPA) rudimentar, em que o conteúdo de `<main>` é substituído dinamicamente via JavaScript, sem recarregar a página inteira;
- **Persistência local:** uso de `localStorage` para manter o usuário logado mesmo após atualizar a página;
- **Simulação de banco de dados:** o arquivo `data.js` simula o que seria o banco de dados e as regras de negócio do servidor, permitindo testar o frontend de forma isolada. Funções auxiliares, como `getAlmocoDoDia`, filtram as informações conforme a necessidade da tela.

## Design

Interface limpa, moderna e focada na usabilidade (UX), utilizando a paleta de cores institucional (verde) do IFTO.

## Segurança

Por se tratar de um trabalho acadêmico de frontend, as senhas dos usuários estão expostas diretamente no código-fonte (`data.js`). Em um ambiente de produção real, seria utilizado um backend seguro, com autenticação e armazenamento de senhas criptografado.

## Melhorias Futuras

- Integração com um backend real (Node.js ou PHP);
- Integração com a API do IFTO para validação automática de matrículas;
- Adicionar aba cardápio da semana;
- Implementação de pagamento via PIX para visitantes.

