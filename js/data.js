// ============================================
// DADOS DO SISTEMA — RU IFTO
// ============================================

// Valor cobrado por refeição avulsa  para visitantes,estudante que pagara em dinheiro ou servidores 
const PRECO_REFEICAO_VISITANTE = 13.00;

// Usuários pré-cadastrados
const USERS = [
    {
        id: 1,
        name: 'Administrador',
        email: 'admin@ru.com',
        password: 'admin123',
        role: 'admin',
        matricula: 'ADMIN001',
        curso: 'Administração',
        auxilio_ativo: false,
        tickets_totais: 0,
        tickets_usados: 0,
        tickets_disponiveis: 0,
        periodo: '2024.2'
    },
    {
        id: 2,
        name: 'João Silva',
        email: 'joao@email.com',
        password: '123456',
        role: 'aluno',
        matricula: '202512170015',
        curso: 'Engenharia Agrônomica',
        auxilio_ativo: true,
        tickets_totais: 20,
        tickets_usados: 2,
        tickets_disponiveis: 18,
        periodo: '2024.2'
    },
    {
        id: 3,
        name: 'Maria Santos',
        email: 'maria@email.com',
        password: '123456',
        role: 'aluno',
        matricula: '202512170013',
        curso: 'Análise de Sistemas',
        auxilio_ativo: true,
        tickets_totais: 20,
        tickets_usados: 5,
        tickets_disponiveis: 15,
        periodo: '2024.2'
    },
    {
        id: 4,
        name: 'Visitante',
        email: 'visitante@ru.ifto.edu.br',
        password: 'visitante',
        role: 'visitante',
        matricula: '-',
        curso: '-',
        auxilio_ativo: false,
        tickets_totais: 0,
        tickets_usados: 0,
        tickets_disponiveis: 0,
        periodo: '-',
        valor_refeicao: PRECO_REFEICAO_VISITANTE
    }
];

// Data de hoje (formato YYYY-MM-DD)
function getHoje() {
    return new Date().toISOString().split('T')[0];
}

// Data de amanhã
function getAmanha() {
    const d = new Date();
    d.setDate(d.getDate() + 1);
    return d.toISOString().split('T')[0];
}

// Refeições — apenas almoço
const REFEICOES = [
    {
        id: 1,
        tipo: 'almoco',
        data: getHoje(),
        cardapio_principal: 'Arroz, feijão, bife acebolado',
        cardapio_acompanhamento: 'Batata frita',
        cardapio_salada: 'Salada verde com tomate',
        cardapio_sobremesa: 'Pudim de leite',
        cardapio_suco: 'Suco de maracujá',
        vagas_disponiveis: 32,
        vagas_totais: 100,
        status: 'disponivel'
    },
    {
        id: 2,
        tipo: 'almoco',
        data: getAmanha(),
        cardapio_principal: 'Frango grelhado com legumes',
        cardapio_acompanhamento: 'Purê de batata',
        cardapio_salada: 'Salada de beterraba',
        cardapio_sobremesa: 'Gelatina',
        cardapio_suco: 'Suco de limão',
        vagas_disponiveis: 50,
        vagas_totais: 100,
        status: 'disponivel'
    }
];

// Reservas (histórico)
const RESERVAS = [
    {
        id: 1,
        usuario_id: 2,
        refeicao_id: 1,
        status: 'utilizada',
        data_reserva: new Date(Date.now() - 86400000 * 2).toISOString(),
        data_utilizacao: new Date(Date.now() - 86400000 * 1).toISOString()
    },
    {
        id: 2,
        usuario_id: 3,
        refeicao_id: 1,
        status: 'utilizada',
        data_reserva: new Date(Date.now() - 86400000 * 1).toISOString(),
        data_utilizacao: new Date(Date.now() - 86400000 * 1).toISOString()
    }
];

// Histórico de tickets
const TICKETS_HISTORICO = [
    {
        id: 1,
        usuario_id: 2,
        quantidade: 1,
        periodo: '2024.2',
        status: 'utilizada',
        data_utilizacao: new Date(Date.now() - 86400000 * 2).toISOString(),
        createdAt: new Date(Date.now() - 86400000 * 2).toISOString()
    },
    {
        id: 2,
        usuario_id: 3,
        quantidade: 1,
        periodo: '2024.2',
        status: 'utilizada',
        data_utilizacao: new Date(Date.now() - 86400000 * 1).toISOString(),
        createdAt: new Date(Date.now() - 86400000 * 1).toISOString()
    }
];

// Variáveis de estado
let currentUser = null;
let currentReservas = [...RESERVAS];
let currentRefeicoes = [...REFEICOES];
let currentTickets = [...TICKETS_HISTORICO];

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

function getRefeicaoById(id) {
    return currentRefeicoes.find(r => r.id === id);
}

function getUserById(id) {
    return USERS.find(u => u.id === id);
}

function getReservasByUser(userId) {
    return currentReservas.filter(r => r.usuario_id === userId);
}

function getTicketsByUser(userId) {
    return currentTickets.filter(t => t.usuario_id === userId);
}

// Retorna apenas o almoço do dia
function getAlmocoDoDia() {
    const hoje = getHoje();
    return currentRefeicoes.filter(r =>
        r.tipo === 'almoco' && r.data === hoje
    );
}

function getTipoRefeicao(tipo) {
    const tipos = {
        'almoco': '🍽️ Almoço'
    };
    return tipos[tipo] || tipo;
}

// Verifica se ainda está dentro do prazo de cancelamento (até 10h do dia)
function dentroDoPrazoCancelamento() {
    const agora = new Date();
    const hora = agora.getHours();
    return hora < 10;
}
