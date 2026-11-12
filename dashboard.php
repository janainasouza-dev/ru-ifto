<?php
require_once __DIR__ . '/config/config.php';
$user = Auth::exigirUsuarioComum();

$almocos  = Refeicao::doDia();
$ativas   = Reserva::listarPorUsuario($user->id, 'confirmada');
$idsReservados = array_column($ativas, 'refeicao_id');
$visitante = $user->isVisitante();
$valor = (float) ($user->valor_refeicao ?: PRECO_REFEICAO_VISITANTE);
// Visitante e aluno sem auxílio reservam e pagam na hora, no caixa
$pagaNaHora = $visitante || !$user->auxilio_ativo;
$podeCancelar = Reserva::dentroDoPrazoCancelamento();
$podeReservar = $podeCancelar; // reservas encerram no mesmo horário do cancelamento

$titulo = 'Início';
require __DIR__ . '/includes/header.php';
?>
<div class="dashboard-header">
    <h1><?= $visitante ? '👤' : '🎓' ?> Bem-vindo, <?= e($user->nome) ?>!</h1>
    <p><?= $visitante ? 'Reserve seu almoço avulso no Restaurante Universitário' : 'Reserve seu almoço no Restaurante Universitário' ?></p>
<?php if (!$visitante && !$user->auxilio_ativo): ?>
    <p>Você está sem auxílio ativo: pode reservar normalmente pagando <?= e(dinheiro($valor)) ?> no caixa.</p>
<?php endif; ?>
</div>

<section class="stats-grid" aria-label="Resumo da conta">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-label">Minhas Reservas</div>
        <div class="stat-value"><?= count($ativas) ?></div>
    </div>
<?php if ($visitante): ?>
    <div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-label">Valor por Refeição</div><div class="stat-value"><?= dinheiro($valor) ?></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-cash-register"></i></div>
        <div class="stat-label">Pagamento</div><div class="stat-value" style="font-size:1.1rem;">Avulso no caixa</div></div>
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-id-badge"></i></div>
        <div class="stat-label">Tipo de Conta</div><div class="stat-value" style="font-size:1.1rem;">Visitante</div></div>
<?php else: ?>
    <div class="stat-card"><div class="stat-icon <?= $user->auxilio_ativo ? 'green' : 'red' ?>"><i class="fas fa-id-card"></i></div>
        <div class="stat-label">Auxílio</div><div class="stat-value"><?= $user->auxilio_ativo ? 'Ativo' : 'Inativo' ?></div></div>
    <div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-ticket-alt"></i></div>
        <div class="stat-label">Tickets Disponíveis</div><div class="stat-value"><?= $user->ticketsDisponiveis() ?></div></div>
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-check"></i></div>
        <div class="stat-label">Tickets Usados</div><div class="stat-value"><?= (int) $user->tickets_usados ?></div></div>
<?php endif; ?>
</section>

<div class="section-title"><i class="fas fa-utensils"></i> Almoço de Hoje</div>
<section class="refeicoes-grid" aria-label="Cardápio do dia">
<?php if (!$almocos): ?>
    <div class="panel vazio"><i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>Nenhum almoço cadastrado para hoje</div>
<?php endif; ?>
<?php foreach ($almocos as $r):
    $jaReservou = in_array($r->id, $idsReservados);
    $disp = $r->estaDisponivel();
    if ($jaReservou)            { $rotulo = '✅ Já Reservado'; $bloq = true; }
    elseif (!$disp)             { $rotulo = 'Lotado'; $bloq = true; }
    elseif (!$podeReservar)     { $rotulo = 'Reservas encerradas'; $bloq = true; }
    elseif ($pagaNaHora)        { $rotulo = 'Reservar (' . dinheiro($valor) . ')'; $bloq = false; }
    elseif ($user->ticketsDisponiveis() <= 0) { $rotulo = 'Sem Tickets'; $bloq = true; }
    else                        { $rotulo = 'Reservar'; $bloq = false; }
?>
    <article class="refeicao-card <?= !$disp ? 'lotado' : '' ?>">
        <header class="card-header">
            <h3>🍽️ Almoço</h3>
            <span class="status <?= e($r->status) ?>"><?= $r->status === 'disponivel' ? '✅ Disponível' : '❌ Lotado' ?></span>
        </header>
        <div class="card-body">
            <p>🍲 <?= e($r->cardapio_principal) ?></p>
            <?php if ($r->cardapio_acompanhamento): ?><p>🍟 <?= e($r->cardapio_acompanhamento) ?></p><?php endif; ?>
            <?php if ($r->cardapio_salada): ?><p>🥗 <?= e($r->cardapio_salada) ?></p><?php endif; ?>
            <?php if ($r->cardapio_sobremesa): ?><p>🍮 <?= e($r->cardapio_sobremesa) ?></p><?php endif; ?>
            <?php if ($r->cardapio_suco): ?><p>🍹 <?= e($r->cardapio_suco) ?></p><?php endif; ?>
        </div>
        <footer class="card-footer">
            <span class="vagas"><?= (int) $r->vagas_disponiveis ?>/<?= (int) $r->vagas_totais ?> vagas</span>
            <form method="post" action="<?= url('reservar.php') ?>" class="inline-form"
                  <?= $pagaNaHora ? 'onsubmit="return confirm(\'Confirmar reserva no valor de ' . e(dinheiro($valor)) . ', a ser paga no caixa do RU?\')"' : '' ?>>
                <?= csrfCampo() ?>
                <input type="hidden" name="refeicao_id" value="<?= $r->id ?>">
                <button class="btn-reservar <?= $jaReservou ? 'reservado' : '' ?>" <?= $bloq ? 'disabled' : '' ?>><?= e($rotulo) ?></button>
            </form>
        </footer>
    </article>
<?php endforeach; ?>
</section>

<div class="section-title" style="margin-top:2rem;"><i class="fas fa-list"></i> Minhas Reservas Ativas</div>
<ul class="reservas-list">
<?php if (!$ativas): ?>
    <li class="vazio"><i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>Você não tem reservas ativas</li>
<?php endif; ?>
<?php foreach ($ativas as $a): ?>
    <li class="reserva-item">
        <div class="reserva-info">
            <span class="titulo">🍽️ Almoço — <?= e(dataBR($a['refeicao_data'])) ?></span>
            <span class="data">📅 Reservado em <?= e(dataHora($a['data_reserva'])) ?></span>
            <?php if ((float) $a['valor'] > 0): ?>
                <span class="data">💵 Pagar no caixa: <?= e(dinheiro((float) $a['valor'])) ?></span>
            <?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:1rem;">
            <span class="reserva-status confirmada">Confirmada</span>
            <form method="post" action="<?= url('cancelar.php') ?>" class="inline-form"
                  onsubmit="return confirm('Tem certeza que deseja cancelar esta reserva?')">
                <?= csrfCampo() ?>
                <input type="hidden" name="reserva_id" value="<?= (int) $a['id'] ?>">
                <button class="btn-cancelar" <?= $podeCancelar ? '' : 'disabled title="Prazo de cancelamento encerrado (até 9:30h)"' ?>>Cancelar</button>
            </form>
        </div>
    </li>
<?php endforeach; ?>
</ul>
<?php require __DIR__ . '/includes/footer.php';