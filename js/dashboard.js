// ============================================
// RENDERIZAÇÃO DO DASHBOARD — RU IFTO
// ============================================

function renderDashboard() {
    const main = document.getElementById('mainContent');

    if (!currentUser) {
        renderLogin();
        return;
    }

    // Administrador não usa o dashboard de aluno/visitante:
    // vai direto para o Painel Administrativo, sem cliques extras.
    if (isAdmin()) {
        renderAdmin();
        return;
    }

    const almocoDoDia = getAlmocoDoDia();
    const minhasReservas = getReservasByUser(currentUser.id);
    const reservasAtivas = minhasReservas.filter(r => r.status === 'confirmada');

    const visitante = isVisitante();

    main.innerHTML = `
        <!-- Header -->
        <div class="dashboard-header">
            <h1>${visitante ? '👤' : '🎓'} Bem-vindo, ${currentUser.name}!</h1>
            <p>${visitante
                ? 'Reserve seu almoço avulso no Restaurante Universitário'
                : 'Reserve seu almoço no Restaurante Universitário'}</p>
        </div>

        <!-- Stats -->
        <section class="stats-grid" aria-label="Resumo da conta">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-label">Minhas Reservas</div>
                <div class="stat-value">${reservasAtivas.length}</div>
            </div>
            ${visitante ? `
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-label">Valor por Refeição</div>
                <div class="stat-value">R$ ${(currentUser.valor_refeicao || 0).toFixed(2)}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-cash-register"></i></div>
                <div class="stat-label">Pagamento</div>
                <div class="stat-value" style="font-size: 1.1rem;">Avulso no caixa</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-id-badge"></i></div>
                <div class="stat-label">Tipo de Conta</div>
                <div class="stat-value" style="font-size: 1.1rem;">Visitante</div>
            </div>
            ` : `
            <div class="stat-card">
                <div class="stat-icon ${currentUser.auxilio_ativo ? 'green' : 'red'}">
                    <i class="fas fa-id-card"></i>
                </div>
                <div class="stat-label">Auxílio</div>
                <div class="stat-value">${currentUser.auxilio_ativo ? 'Ativo' : 'Inativo'}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-ticket-alt"></i></div>
                <div class="stat-label">Tickets Disponíveis</div>
                <div class="stat-value">${currentUser.tickets_disponiveis || 0}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-check"></i></div>
                <div class="stat-label">Tickets Usados</div>
                <div class="stat-value">${currentUser.tickets_usados || 0}</div>
            </div>
            `}
        </section>

        <!-- Almoço do dia -->
        <div class="section-title">
            <i class="fas fa-utensils"></i> Almoço de Hoje
        </div>
        <section class="refeicoes-grid" aria-label="Cardápio do dia">
            ${almocoDoDia.length === 0 ? `
                <div style="padding: 2rem; text-align: center; color: var(--gray-500); background: white; border-radius: var(--radius); box-shadow: var(--shadow-md);">
                    <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                    Nenhum almoço cadastrado para hoje
                </div>
            ` : almocoDoDia.map(refeicao => {
                const jaReservou = reservasAtivas.some(r => r.refeicao_id === refeicao.id);
                const estaDisponivel = refeicao.status === 'disponivel';

                return `
                    <article class="refeicao-card ${!estaDisponivel ? 'lotado' : ''}">
                        <header class="card-header">
                            <h3>${getTipoRefeicao(refeicao.tipo)}</h3>
                            <span class="status ${refeicao.status}">
                                ${refeicao.status === 'disponivel' ? '✅ Disponível' : '❌ Lotado'}
                            </span>
                        </header>
                        <div class="card-body">
                            <p>🍲 ${refeicao.cardapio_principal || 'Não informado'}</p>
                            ${refeicao.cardapio_acompanhamento ? `<p>🍟 ${refeicao.cardapio_acompanhamento}</p>` : ''}
                            <p>🥗 ${refeicao.cardapio_salada || ''}</p>
                            ${refeicao.cardapio_sobremesa ? `<p>🍮 ${refeicao.cardapio_sobremesa}</p>` : ''}
                            <p>🍹 ${refeicao.cardapio_suco || ''}</p>
                        </div>
                        <footer class="card-footer">
                            <span class="vagas">${refeicao.vagas_disponiveis}/${refeicao.vagas_totais} vagas</span>
                            ${visitante ? `
                            <button
                                class="btn-reservar ${jaReservou ? 'reservado' : ''}"
                                onclick="fazerReserva(${refeicao.id})"
                                ${!estaDisponivel || jaReservou ? 'disabled' : ''}
                            >
                                ${jaReservou ? '✅ Já Reservado' : estaDisponivel ? `Reservar (R$ ${(currentUser.valor_refeicao || 0).toFixed(2)})` : 'Lotado'}
                            </button>
                            ` : `
                            <button
                                class="btn-reservar ${jaReservou ? 'reservado' : ''}"
                                onclick="fazerReserva(${refeicao.id})"
                                ${!estaDisponivel || jaReservou || !currentUser.auxilio_ativo ? 'disabled' : ''}
                            >
                                ${jaReservou ? '✅ Já Reservado' : !currentUser.auxilio_ativo ? 'Sem Auxílio' : estaDisponivel ? 'Reservar' : 'Lotado'}
                            </button>
                            `}
                        </footer>
                    </article>
                `;
            }).join('')}
        </section>

        <!-- Minhas Reservas -->
        <div class="section-title" style="margin-top: 2rem;">
            <i class="fas fa-list"></i> Minhas Reservas Ativas
        </div>
        <ul class="reservas-list">
            ${reservasAtivas.length === 0 ? `
                <li style="padding: 2rem; text-align: center; color: var(--gray-500); list-style: none;">
                    <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                    Você não tem reservas ativas
                </li>
            ` : reservasAtivas.map(reserva => {
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
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span class="reserva-status ${reserva.status}">
                                ${reserva.status === 'confirmada' ? 'Confirmada' : reserva.status}
                            </span>
                            <button class="btn-cancelar" onclick="cancelarReserva(${reserva.id})">
                                Cancelar
                            </button>
                        </div>
                    </li>
                `;
            }).join('')}
        </ul>
    `;

    updateNavbar();
}

// ============================================
// AÇÕES
// ============================================

function fazerReserva(refeicaoId) {
    if (!currentUser) {
        showToast('❌ Faça login primeiro!', 'error');
        return;
    }

    const refeicao = getRefeicaoById(refeicaoId);
    if (!refeicao || refeicao.vagas_disponiveis <= 0) {
        showToast('❌ Não há vagas disponíveis!', 'error');
        return;
    }

    const jaReservou = currentReservas.some(r =>
        r.usuario_id === currentUser.id &&
        r.refeicao_id === refeicaoId &&
        r.status === 'confirmada'
    );

    if (jaReservou) {
        showToast('❌ Você já reservou esta refeição!', 'error');
        return;
    }

    // Regra de negócio: visitante paga avulso, não usa tickets do auxílio
    // e pode manter apenas 1 reserva ativa por vez.
    if (isVisitante()) {
        const reservasAtivasVisitante = currentReservas.filter(r =>
            r.usuario_id === currentUser.id && r.status === 'confirmada'
        );
        if (reservasAtivasVisitante.length > 0) {
            showToast('❌ Visitante só pode ter 1 reserva ativa por vez!', 'error');
            return;
        }
        const valor = (currentUser.valor_refeicao || PRECO_REFEICAO_VISITANTE).toFixed(2);
        if (!confirm(`Confirmar reserva avulsa no valor de R$ ${valor}, a ser pago no caixa do RU?`)) {
            return;
        }
    } else {
        if (!currentUser.auxilio_ativo) {
            showToast('❌ Você não tem auxílio alimentação ativo!', 'error');
            return;
        }
        if (currentUser.tickets_disponiveis <= 0) {
            showToast('❌ Você não tem tickets disponíveis!', 'error');
            return;
        }
    }

    const novaReserva = {
        id: currentReservas.length + 1,
        usuario_id: currentUser.id,
        refeicao_id: refeicaoId,
        status: 'confirmada',
        tipo_usuario: currentUser.role,
        valor: isVisitante() ? (currentUser.valor_refeicao || PRECO_REFEICAO_VISITANTE) : 0,
        data_reserva: new Date().toISOString()
    };

    currentReservas.push(novaReserva);

    refeicao.vagas_disponiveis -= 1;
    if (refeicao.vagas_disponiveis === 0) {
        refeicao.status = 'lotado';
    }

    if (!isVisitante()) {
        updateUserTickets(1);

        currentTickets.push({
            id: currentTickets.length + 1,
            usuario_id: currentUser.id,
            quantidade: 1,
            periodo: currentUser.periodo,
            status: 'utilizada',
            data_utilizacao: new Date().toISOString(),
            createdAt: new Date().toISOString()
        });
    }

    showToast('✅ Reserva realizada com sucesso!', 'success');
    renderDashboard();
}

function cancelarReserva(reservaId) {
    if (!currentUser) return;

    if (!dentroDoPrazoCancelamento()) {
        showToast('❌ Prazo de cancelamento expirado (até 10h)!', 'error');
        return;
    }

    const reserva = currentReservas.find(r => r.id === reservaId);
    if (!reserva) return;

    if (reserva.usuario_id !== currentUser.id) {
        showToast('❌ Você não pode cancelar esta reserva!', 'error');
        return;
    }

    if (reserva.status !== 'confirmada') {
        showToast('❌ Esta reserva já foi utilizada ou cancelada!', 'error');
        return;
    }

    if (!confirm('Tem certeza que deseja cancelar esta reserva?')) return;

    reserva.status = 'cancelada';

    const refeicao = getRefeicaoById(reserva.refeicao_id);
    if (refeicao) {
        refeicao.vagas_disponiveis += 1;
        if (refeicao.vagas_disponiveis > 0) {
            refeicao.status = 'disponivel';
        }
    }

    if (currentUser && !isVisitante()) {
        currentUser.tickets_disponiveis += 1;
        currentUser.tickets_usados -= 1;
        localStorage.setItem('ru_user', JSON.stringify(currentUser));
    }

    showToast('✅ Reserva cancelada com sucesso!', 'success');
    renderDashboard();
}
