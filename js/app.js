// ============================================
// APLICAÇÃO PRINCIPAL — RU IFTO
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    if (checkAuth()) {
        renderDashboard();
    } else {
        renderLogin();
    }

    setupEventListeners();
});

function setupEventListeners() {
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
}

// ============================================
// NAVBAR
// ============================================

function updateNavbar() {
    const navLinks = document.getElementById('navLinks');

    if (!currentUser) {
        navLinks.innerHTML = `
            <a href="#" onclick="renderLogin()">Login</a>
            <a href="#" onclick="renderRegister()">Registrar</a>
        `;
        return;
    }

    // Administrador: painel administrativo já é o "Início" dele,
    // sem link duplicado para a mesma tela.
    if (isAdmin()) {
        navLinks.innerHTML = `
            <a href="#" onclick="renderAdmin()" class="active">
                <i class="fas fa-cog"></i> Painel Administrativo
            </a>
            <button class="btn-logout" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i> Sair
            </button>
        `;
        return;
    }

    // Visitante: não possui tickets nem histórico do auxílio
    if (isVisitante()) {
        navLinks.innerHTML = `
            <a href="#" onclick="renderDashboard()" class="active">
                <i class="fas fa-home"></i> Início
            </a>
            <span class="navbar-badge neutral">👤 Visitante</span>
            <button class="btn-logout" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i> Sair
            </button>
        `;
        return;
    }

    navLinks.innerHTML = `
        <a href="#" onclick="renderDashboard()" class="active">
            <i class="fas fa-home"></i> Início
        </a>
        <a href="#" onclick="renderMeusTickets()">
            <i class="fas fa-ticket-alt"></i> Tickets
        </a>
        <a href="#" onclick="renderHistorico()">
            <i class="fas fa-history"></i> Histórico
        </a>
        <span class="navbar-badge tickets">🎫 ${currentUser.tickets_disponiveis || 0}</span>
        <button class="btn-logout" onclick="logout()">
            <i class="fas fa-sign-out-alt"></i> Sair
        </button>
    `;
}

// ============================================
// RENDERIZAÇÃO DE PÁGINAS
// ============================================

function renderPage(page) {
    switch (page) {
        case 'login': renderLogin(); break;
        case 'register': renderRegister(); break;
        case 'dashboard': renderDashboard(); break;
        case 'tickets': renderMeusTickets(); break;
        case 'historico': renderHistorico(); break;
        case 'admin': renderAdmin(); break;
        default: renderDashboard();
    }
}

// ============================================
// LOGIN
// ============================================

function renderLogin() {
    const main = document.getElementById('mainContent');
    document.getElementById('navLinks').innerHTML = `
        <a href="#" onclick="renderLogin()">Login</a>
        <a href="#" onclick="renderRegister()">Registrar</a>
    `;

    main.innerHTML = `
        <div class="auth-page">
            <div class="auth-card">
                <div class="logo-icon">
                    <img src="img/logo.png" alt="RU IFTO" class="auth-logo">
                </div>
                <h2>RU IFTO</h2>
                <p class="subtitle">Restaurante Universitário do IFTO</p>

                <div class="error-message" id="loginError"></div>

                <form onsubmit="handleLogin(event)">
                    <div class="form-group">
                        <label>Email</label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="loginEmail" placeholder="seu@email.com" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Senha</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="loginPassword" placeholder="••••••••" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Entrar</button>
                </form>

                <button type="button" class="btn-secondary" onclick="handleLoginVisitante()">
                    <i class="fas fa-user-clock"></i> Entrar como Visitante
                </button>

                <div class="auth-link">
                    Não tem uma conta? <a href="#" onclick="renderRegister()">Registre-se</a>
                </div>

             
            </div>
        </div>
    `;
}

function handleLogin(event) {
    event.preventDefault();
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    const errorEl = document.getElementById('loginError');

    errorEl.classList.remove('show');

    if (login(email, password)) {
        renderDashboard();
    } else {
        errorEl.textContent = '❌ Email ou senha incorretos!';
        errorEl.classList.add('show');
    }
}

function handleLoginVisitante() {
    if (loginVisitante()) {
        renderDashboard();
    }
}

// ============================================
// REGISTRO
// ============================================

function renderRegister() {
    const main = document.getElementById('mainContent');
    document.getElementById('navLinks').innerHTML = `
        <a href="#" onclick="renderLogin()">Login</a>
        <a href="#" onclick="renderRegister()">Registrar</a>
    `;

    main.innerHTML = `
        <div class="auth-page">
            <div class="auth-card">
                <div class="logo-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2>Criar Conta</h2>
                <p class="subtitle">Cadastre-se no Restaurante Universitário</p>

                <div class="error-message" id="registerError"></div>

                <form onsubmit="handleRegister(event)">
                    <div class="form-group">
                        <label>Nome</label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="regName" placeholder="Seu nome completo" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="regEmail" placeholder="seu@email.com" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Matrícula</label>
                        <div class="input-icon">
                            <i class="fas fa-id-card"></i>
                            <input type="text" id="regMatricula" placeholder="Número da matrícula">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Curso</label>
                        <div class="input-icon">
                            <i class="fas fa-graduation-cap"></i>
                            <input type="text" id="regCurso" placeholder="Seu curso">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Senha</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="regPassword" placeholder="Mínimo 6 caracteres" required minlength="6">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirmar Senha</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="regConfirm" placeholder="Confirme sua senha" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Cadastrar</button>
                </form>

                <div class="auth-link">
                    Já tem uma conta? <a href="#" onclick="renderLogin()">Faça login</a>
                </div>
            </div>
        </div>
    `;
}

function handleRegister(event) {
    event.preventDefault();
    const errorEl = document.getElementById('registerError');
    errorEl.classList.remove('show');

    const name = document.getElementById('regName').value;
    const email = document.getElementById('regEmail').value;
    const matricula = document.getElementById('regMatricula').value;
    const curso = document.getElementById('regCurso').value;
    const password = document.getElementById('regPassword').value;
    const confirm = document.getElementById('regConfirm').value;

    if (password !== confirm) {
        errorEl.textContent = '❌ As senhas não coincidem!';
        errorEl.classList.add('show');
        return;
    }

    if (password.length < 6) {
        errorEl.textContent = '❌ A senha deve ter no mínimo 6 caracteres!';
        errorEl.classList.add('show');
        return;
    }

    if (USERS.find(u => u.email === email)) {
        errorEl.textContent = '❌ Este email já está cadastrado!';
        errorEl.classList.add('show');
        return;
    }

    const newUser = {
        id: USERS.length + 1,
        name,
        email,
        password,
        role: 'aluno',
        matricula: matricula || `2024${String(USERS.length + 1).padStart(3, '0')}`,
        curso: curso || 'Não informado',
        auxilio_ativo: false,
        tickets_totais: 0,
        tickets_usados: 0,
        tickets_disponiveis: 0,
        periodo: '2024.2'
    };

    USERS.push(newUser);

    showToast('✅ Cadastro realizado! Aguarde ativação do auxílio.', 'success');
    renderLogin();
}

// ============================================
// MEUS TICKETS
// ============================================

function renderMeusTickets() {
    if (!currentUser) {
        renderLogin();
        return;
    }

    const main = document.getElementById('mainContent');
    const historico = getTicketsByUser(currentUser.id);

    main.innerHTML = `
        <div class="dashboard-header" style="background: linear-gradient(135deg, var(--primary-600), var(--primary-800));">
            <h1><i class="fas fa-ticket-alt"></i> Meus Tickets</h1>
            <p>Acompanhe seu consumo de refeições</p>
        </div>

        <section class="tickets-resumo" aria-label="Resumo de tickets">
            <article class="ticket-card">
                <div class="ficha-number blue">${currentUser.tickets_totais || 0}</div>
                <div class="ficha-label">Total de Tickets</div>
            </article>
            <article class="ticket-card">
                <div class="ficha-number green">${currentUser.tickets_disponiveis || 0}</div>
                <div class="ficha-label">Tickets Disponíveis</div>
            </article>
            <article class="ticket-card">
                <div class="ficha-number yellow">${currentUser.tickets_usados || 0}</div>
                <div class="ficha-label">Tickets Utilizados</div>
            </article>
        </section>

        <div class="section-title">
            <i class="fas fa-history"></i> Histórico de Uso
        </div>
        <ul class="reservas-list">
            ${historico.length === 0 ? `
                <li style="padding: 2rem; text-align: center; color: var(--gray-500); list-style: none;">
                    <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                    Nenhum ticket utilizado ainda
                </li>
            ` : historico.map(item => `
                <li class="reserva-item">
                    <div class="reserva-info">
                        <span class="titulo">✅ Ticket Utilizado</span>
                        <span class="data">
                            📅 ${new Date(item.createdAt).toLocaleDateString('pt-BR', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            })}
                        </span>
                    </div>
                    <span class="reserva-status utilizada">Utilizado</span>
                </li>
            `).join('')}
        </ul>
    `;

    updateNavbar();
}

// ============================================
// HISTÓRICO DE RESERVAS
// ============================================

function renderHistorico() {
    if (!currentUser) {
        renderLogin();
        return;
    }

    const main = document.getElementById('mainContent');
    const reservas = getReservasByUser(currentUser.id);

    main.innerHTML = `
        <div class="dashboard-header" style="background: linear-gradient(135deg, var(--primary-700), var(--primary-900));">
            <h1><i class="fas fa-history"></i> Histórico de Reservas</h1>
            <p>Visualize todas as suas reservas</p>
        </div>

        <div class="stats-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-list"></i></div>
                <div class="stat-label">Total</div>
                <div class="stat-value">${reservas.length}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-label">Confirmadas</div>
                <div class="stat-value">${reservas.filter(r => r.status === 'confirmada').length}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-check"></i></div>
                <div class="stat-label">Utilizadas</div>
                <div class="stat-value">${reservas.filter(r => r.status === 'utilizada').length}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
                <div class="stat-label">Canceladas</div>
                <div class="stat-value">${reservas.filter(r => r.status === 'cancelada').length}</div>
            </div>
        </div>

        <div class="section-title">
            <i class="fas fa-list"></i> Todas as Reservas
        </div>
        <ul class="reservas-list">
            ${reservas.length === 0 ? `
                <li style="padding: 2rem; text-align: center; color: var(--gray-500); list-style: none;">
                    <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                    Você ainda não fez nenhuma reserva
                </li>
            ` : reservas.map(reserva => {
                const refeicao = getRefeicaoById(reserva.refeicao_id);
                return `
                    <li class="reserva-item">
                        <div class="reserva-info">
                            <span class="titulo">${getTipoRefeicao(refeicao?.tipo)}</span>
                            <span class="data">
                                📅 ${new Date(reserva.data_reserva).toLocaleDateString('pt-BR', {
                                    day: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}
                            </span>
                        </div>
                        <span class="reserva-status ${reserva.status}">
                            ${reserva.status === 'confirmada' ? 'Confirmada' :
                              reserva.status === 'utilizada' ? 'Utilizada' : 'Cancelada'}
                        </span>
                    </li>
                `;
            }).join('')}
        </ul>
    `;

    updateNavbar();
}

// ============================================
// PAINEL ADMIN
// ============================================

function renderAdmin() {
    if (!currentUser || !isAdmin()) {
        showToast('❌ Acesso negado!', 'error');
        renderDashboard();
        return;
    }

    const main = document.getElementById('mainContent');
    const totalAlunos = USERS.filter(u => u.role === 'aluno').length;
    const alunosComAuxilio = USERS.filter(u => u.role === 'aluno' && u.auxilio_ativo).length;
    const reservasHoje = currentReservas.filter(r =>
        r.status === 'confirmada' &&
        new Date(r.data_reserva).toISOString().split('T')[0] === getHoje()
    ).length;
    const reservasCanceladas = currentReservas.filter(r => r.status === 'cancelada').length;
    const reservasVisitantesHoje = currentReservas.filter(r =>
        r.tipo_usuario === 'visitante' &&
        r.status === 'confirmada' &&
        new Date(r.data_reserva).toISOString().split('T')[0] === getHoje()
    );
    const receitaAvulsaHoje = reservasVisitantesHoje.reduce((soma, r) => soma + (r.valor || 0), 0);

    main.innerHTML = `
        <div class="dashboard-header" style="background: linear-gradient(135deg, #dc2626, #991b1b);">
            <h1><i class="fas fa-cog"></i> Painel Administrativo</h1>
            <p>Gerencie o sistema do Restaurante Universitário</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                <div class="stat-label">Total de Alunos</div>
                <div class="stat-value">${totalAlunos}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-id-card"></i></div>
                <div class="stat-label">Com Auxílio Ativo</div>
                <div class="stat-value">${alunosComAuxilio}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-label">Reservas Hoje</div>
                <div class="stat-value">${reservasHoje}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
                <div class="stat-label">Canceladas</div>
                <div class="stat-value">${reservasCanceladas}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-user-clock"></i></div>
                <div class="stat-label">Visitantes Hoje</div>
                <div class="stat-value">${reservasVisitantesHoje.length}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-hand-holding-usd"></i></div>
                <div class="stat-label">Receita Avulsa Hoje</div>
                <div class="stat-value">R$ ${receitaAvulsaHoje.toFixed(2)}</div>
            </div>
        </div>

        <div style="background: white; border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow-md); margin-bottom: 1.5rem;">
            <h3 style="margin-bottom: 1rem;"><i class="fas fa-users"></i> Alunos Cadastrados</h3>
            <div style="max-height: 300px; overflow-y: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Aluno</th>
                            <th>Curso / Matrícula</th>
                            <th>Auxílio</th>
                            <th>Tickets</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${USERS.filter(u => u.role === 'aluno').map(user => `
                            <tr>
                                <td>${user.name}</td>
                                <td>${user.curso} · ${user.matricula}</td>
                                <td style="color: ${user.auxilio_ativo ? 'var(--green-600)' : 'var(--red-600)'};">
                                    ${user.auxilio_ativo ? '✅ Ativo' : '❌ Sem auxílio'}
                                </td>
                                <td>🎫 ${user.tickets_disponiveis || 0}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>

        <div style="background: white; border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow-md);">
            <h3 style="margin-bottom: 1rem;"><i class="fas fa-utensils"></i> Almoço do Dia</h3>
            ${getAlmocoDoDia().map(refeicao => `
                <div style="padding: 0.75rem 0; border-bottom: 1px solid var(--gray-200);">
                    <strong>${getTipoRefeicao(refeicao.tipo)}</strong>
                    <p style="font-size: 0.875rem; color: var(--gray-600); margin-top: 0.25rem;">
                        ${refeicao.cardapio_principal}
                    </p>
                    <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.25rem;">
                        ${refeicao.vagas_disponiveis}/${refeicao.vagas_totais} vagas disponíveis
                    </p>
                </div>
            `).join('')}
        </div>
    `;

    updateNavbar();
}

// ============================================
// TOAST
// ============================================

function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast ${type}`;

    void toast.offsetWidth;
    toast.classList.add('show');

    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// ============================================
// MODAL
// ============================================

function openModal(content) {
    const modal = document.getElementById('modal');
    const contentEl = document.getElementById('modalContent');
    contentEl.innerHTML = content;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('modal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}