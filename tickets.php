<?php
require_once __DIR__ . '/config/config.php';
$user = Auth::exigirUsuarioComum();
if ($user->isVisitante()) {
    redirecionar('dashboard.php');
}
$historico = Ticket::listarPorUsuario($user->id);

$titulo = 'Meus Tickets';
require __DIR__ . '/includes/header.php';
?>
<div class="dashboard-header" style="background:linear-gradient(135deg,var(--primary-600),var(--primary-800));">
    <h1><i class="fas fa-ticket-alt"></i> Meus Tickets</h1>
    <p>Acompanhe seu consumo de refeições</p>
</div>
<section class="tickets-resumo" aria-label="Resumo de tickets">
    <article class="ticket-card"><div class="ficha-number blue"><?= (int) $user->tickets_totais ?></div><div class="ficha-label">Total de Tickets</div></article>
    <article class="ticket-card"><div class="ficha-number green"><?= $user->ticketsDisponiveis() ?></div><div class="ficha-label">Tickets Disponíveis</div></article>
    <article class="ticket-card"><div class="ficha-number yellow"><?= (int) $user->tickets_usados ?></div><div class="ficha-label">Tickets Utilizados</div></article>
</section>
<div class="section-title"><i class="fas fa-history"></i> Histórico de Uso</div>
<ul class="reservas-list">
<?php if (!$historico): ?>
    <li class="vazio"><i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>Nenhum ticket utilizado ainda</li>
<?php endif; ?>
<?php foreach ($historico as $t): $dev = $t->status === 'devolvida'; ?>
    <li class="reserva-item">
        <div class="reserva-info">
            <span class="titulo"><?= $dev ? '↩️ Ticket Devolvido' : '✅ Ticket Utilizado' ?></span>
            <span class="data">📅 <?= e(dataHora($t->criado_em)) ?></span>
        </div>
        <span class="reserva-status <?= $dev ? 'cancelada' : 'utilizada' ?>"><?= $dev ? 'Devolvido' : 'Utilizado' ?></span>
    </li>
<?php endforeach; ?>
</ul>
<?php require __DIR__ . '/includes/footer.php';
