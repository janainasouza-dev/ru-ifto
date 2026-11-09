<?php
require_once __DIR__ . '/config/config.php';
$user = Auth::exigirUsuarioComum();
$reservas = Reserva::listarPorUsuario($user->id);
$conta = fn(string $s) => count(array_filter($reservas, fn($r) => $r['status'] === $s));
$nomes = ['confirmada' => 'Confirmada', 'utilizada' => 'Utilizada', 'cancelada' => 'Cancelada'];

$titulo = 'Histórico';
require __DIR__ . '/includes/header.php';
?>
<div class="dashboard-header" style="background:linear-gradient(135deg,var(--primary-700),var(--primary-900));">
    <h1><i class="fas fa-history"></i> Histórico de Reservas</h1>
    <p>Visualize todas as suas reservas</p>
</div>
<div class="stats-grid" style="margin-bottom:2rem;">
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-list"></i></div><div class="stat-label">Total</div><div class="stat-value"><?= count($reservas) ?></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-check-circle"></i></div><div class="stat-label">Confirmadas</div><div class="stat-value"><?= $conta('confirmada') ?></div></div>
    <div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-check"></i></div><div class="stat-label">Utilizadas</div><div class="stat-value"><?= $conta('utilizada') ?></div></div>
    <div class="stat-card"><div class="stat-icon red"><i class="fas fa-times-circle"></i></div><div class="stat-label">Canceladas</div><div class="stat-value"><?= $conta('cancelada') ?></div></div>
</div>
<div class="section-title"><i class="fas fa-list"></i> Todas as Reservas</div>
<ul class="reservas-list">
<?php if (!$reservas): ?>
    <li class="vazio"><i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>Você ainda não fez nenhuma reserva</li>
<?php endif; ?>
<?php foreach ($reservas as $r): ?>
    <li class="reserva-item">
        <div class="reserva-info">
            <span class="titulo">🍽️ Almoço — <?= e(dataBR($r['refeicao_data'])) ?></span>
            <span class="data">📅 <?= e(dataHora($r['data_reserva'])) ?></span>
        </div>
        <span class="reserva-status <?= e($r['status']) ?>"><?= e($nomes[$r['status']] ?? $r['status']) ?></span>
    </li>
<?php endforeach; ?>
</ul>
<?php require __DIR__ . '/includes/footer.php';
