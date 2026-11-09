<?php
require_once dirname(__DIR__) . '/config/config.php';
$admin = Auth::exigirAdmin();

$totalAlunos   = Usuario::contar("perfil = 'aluno'");
$comAuxilio    = Usuario::contar("perfil = 'aluno' AND auxilio_ativo = 1");
$reservasHoje  = Reserva::contarHoje('confirmada');
$canceladas    = Reserva::contarPorStatus('cancelada');
$visitantesHoje = Reserva::contarHoje('confirmada', 'visitante');
$receita       = Reserva::receitaAvulsaHoje();
$almocos       = Refeicao::doDia();
$alunos        = Usuario::listar('aluno');

$titulo = 'Painel Administrativo';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="dashboard-header" style="background:linear-gradient(135deg,#dc2626,#991b1b);">
    <h1><i class="fas fa-cog"></i> Painel Administrativo</h1>
    <p>Gerencie o sistema do Restaurante Universitário</p>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-users"></i></div><div class="stat-label">Total de Alunos</div><div class="stat-value"><?= $totalAlunos ?></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-id-card"></i></div><div class="stat-label">Com Auxílio Ativo</div><div class="stat-value"><?= $comAuxilio ?></div></div>
    <div class="stat-card"><div class="stat-icon yellow"><i class="fas fa-calendar-check"></i></div><div class="stat-label">Reservas Hoje</div><div class="stat-value"><?= $reservasHoje ?></div></div>
    <div class="stat-card"><div class="stat-icon red"><i class="fas fa-times-circle"></i></div><div class="stat-label">Canceladas</div><div class="stat-value"><?= $canceladas ?></div></div>
    <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-user-clock"></i></div><div class="stat-label">Visitantes Hoje</div><div class="stat-value"><?= $visitantesHoje ?></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fas fa-hand-holding-usd"></i></div><div class="stat-label">Receita Avulsa Hoje</div><div class="stat-value"><?= e(dinheiro($receita)) ?></div></div>
</div>

<div class="panel">
    <div class="page-actions">
        <h3><i class="fas fa-users"></i> Alunos Cadastrados</h3>
        <a class="btn btn-verde" href="<?= url('admin/usuarios.php') ?>">Gerenciar usuários</a>
    </div>
    <table class="admin-table">
        <thead><tr><th>Aluno</th><th>Curso / Matrícula</th><th>Auxílio</th><th>Tickets</th></tr></thead>
        <tbody>
        <?php foreach ($alunos as $a): ?>
            <tr>
                <td><?= e($a->nome) ?></td>
                <td><?= e($a->curso) ?> · <?= e($a->matricula) ?></td>
                <td style="color:<?= $a->auxilio_ativo ? 'var(--green-600)' : 'var(--red-600)' ?>;"><?= $a->auxilio_ativo ? '✅ Ativo' : '❌ Sem auxílio' ?></td>
                <td>🎫 <?= $a->ticketsDisponiveis() ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="page-actions">
        <h3><i class="fas fa-utensils"></i> Almoço do Dia</h3>
        <a class="btn btn-verde" href="<?= url('admin/refeicoes.php') ?>">Gerenciar refeições</a>
    </div>
    <?php if (!$almocos): ?><p class="vazio">Nenhum almoço cadastrado para hoje.</p><?php endif; ?>
    <?php foreach ($almocos as $r): ?>
        <div style="padding:.75rem 0;border-bottom:1px solid var(--gray-200);">
            <strong>🍽️ Almoço</strong>
            <p style="font-size:.875rem;color:var(--gray-600);margin-top:.25rem;"><?= e($r->cardapio_principal) ?></p>
            <p style="font-size:.75rem;color:var(--gray-500);margin-top:.25rem;"><?= (int) $r->vagas_disponiveis ?>/<?= (int) $r->vagas_totais ?> vagas disponíveis</p>
        </div>
    <?php endforeach; ?>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php';
