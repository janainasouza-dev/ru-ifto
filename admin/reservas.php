<?php
require_once dirname(__DIR__) . '/config/config.php';
Auth::exigirAdmin();

if (ehPost()) {
    validarCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $acao = $_POST['acao'] ?? '';
    try {
        if ($acao === 'excluir') {
            Reserva::excluir($id);
            flash('Reserva excluída.', 'success');
        } elseif (in_array($acao, ['utilizada', 'cancelada'], true)) {
            Reserva::alterarStatus($id, $acao);
            flash('Reserva atualizada para ' . $acao . '.', 'success');
        }
    } catch (RuntimeException $e) {
        flash($e->getMessage(), 'error');
    }
    redirecionar('admin/reservas.php' . (!empty($_POST['status']) ? '?status=' . urlencode($_POST['status']) : ''));
}

$status = $_GET['status'] ?? '';
$status = in_array($status, ['confirmada', 'utilizada', 'cancelada'], true) ? $status : '';
$reservas = Reserva::listarTodas($status ?: null);
$nomes = ['confirmada' => 'Confirmada', 'utilizada' => 'Utilizada', 'cancelada' => 'Cancelada'];

$titulo = 'Reservas';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="dashboard-header" style="background:linear-gradient(135deg,#dc2626,#991b1b);">
    <h1><i class="fas fa-calendar-check"></i> Reservas</h1><p>Confirme o uso (retirada da refeição), cancele ou exclua reservas</p>
</div>
<div class="panel">
    <div class="page-actions">
        <form method="get" class="filtros">
            <select name="status" onchange="this.form.submit()">
                <option value="">Todos os status</option>
                <?php foreach ($nomes as $k => $v): ?><option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
            </select>
        </form>
    </div>
    <table class="admin-table">
        <thead><tr><th>#</th><th>Usuário</th><th>Refeição</th><th>Reservado em</th><th>Valor</th><th>Status</th><th>Ações</th></tr></thead>
        <tbody>
        <?php if (!$reservas): ?><tr><td colspan="7" class="vazio">Nenhuma reserva encontrada.</td></tr><?php endif; ?>
        <?php foreach ($reservas as $r): ?>
            <tr>
                <td><?= (int) $r['id'] ?></td>
                <td><?= e($r['usuario_nome']) ?> <span class="badge <?= e($r['tipo_usuario']) ?>"><?= e($r['tipo_usuario']) ?></span></td>
                <td><?= e(dataBR($r['refeicao_data'])) ?> — <?= e($r['cardapio_principal']) ?></td>
                <td><?= e(dataHora($r['data_reserva'])) ?></td>
                <td><?= $r['valor'] > 0 ? e(dinheiro((float) $r['valor'])) : '—' ?></td>
                <td><span class="reserva-status <?= e($r['status']) ?>"><?= e($nomes[$r['status']]) ?></span></td>
                <td class="tabela-acoes">
                    <?php foreach (($r['status'] === 'confirmada' ? ['utilizada' => ['btn-verde', 'fa-check', 'Marcar utilizada'], 'cancelada' => ['btn-amarelo', 'fa-ban', 'Cancelar']] : []) as $acao => [$cls, $ico, $txt]): ?>
                    <form method="post">
                        <?= csrfCampo() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="acao" value="<?= $acao ?>"><input type="hidden" name="status" value="<?= e($status) ?>">
                        <button class="btn <?= $cls ?>"><i class="fas <?= $ico ?>"></i> <?= $txt ?></button>
                    </form>
                    <?php endforeach; ?>
                    <form method="post" onsubmit="return confirm('Excluir esta reserva?')">
                        <?= csrfCampo() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="status" value="<?= e($status) ?>">
                        <button class="btn btn-vermelho"><i class="fas fa-trash"></i> Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php';
