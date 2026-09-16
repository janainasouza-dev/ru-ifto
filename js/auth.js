// ============================================
// SISTEMA DE AUTENTICAÇÃO — RU IFTO
// ============================================

function login(email, password) {
    const user = USERS.find(u => u.email === email && u.password === password);

    if (user) {
        currentUser = { ...user };
        delete currentUser.password;
        localStorage.setItem('ru_user', JSON.stringify(currentUser));
        showToast('✅ Login realizado com sucesso!', 'success');
        return true;
    }

    showToast('❌ Credenciais inválidas!', 'error');
    return false;
}

function logout() {
    currentUser = null;
    localStorage.removeItem('ru_user');
    showToast('👋 Logout realizado!', 'info');
    renderPage('login');
}

function checkAuth() {
    const stored = localStorage.getItem('ru_user');
    if (stored) {
        try {
            currentUser = JSON.parse(stored);
            return true;
        } catch (e) {
            localStorage.removeItem('ru_user');
            return false;
        }
    }
    return false;
}

function isAdmin() {
    return currentUser && currentUser.role === 'admin';
}

function isAluno() {
    return currentUser && currentUser.role === 'aluno';
}

function isVisitante() {
    return currentUser && currentUser.role === 'visitante';
}

// Acesso rápido de visitante — não exige cadastro nem senha,
// já que o visitante paga a refeição avulsa no próprio RU
function loginVisitante() {
    const contaVisitante = USERS.find(u => u.role === 'visitante');
    if (!contaVisitante) return false;

    currentUser = { ...contaVisitante };
    delete currentUser.password;
    localStorage.setItem('ru_user', JSON.stringify(currentUser));
    showToast('👤 Acesso de visitante iniciado!', 'info');
    return true;
}

function getCurrentUser() {
    return currentUser;
}

// Atualiza tickets do usuário logado
function updateUserTickets(quantidade) {
    if (currentUser) {
        currentUser.tickets_disponiveis -= quantidade;
        currentUser.tickets_usados += quantidade;
        localStorage.setItem('ru_user', JSON.stringify(currentUser));
        updateNavbar();
    }
}
