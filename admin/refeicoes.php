<?php
require_once dirname(__DIR__) . '/config/config.php';
Auth::exigirAdmin();

if (ehPost() && ($_POST['acao'] ?? '') === 'excluir') {
    validarCsrf();
    Refeicao::excluir((int) ($_POST['id'] ?? 0));
    flash('Refeição excluída.', 'success');
    redirecionar('admin/refeicoes.php');
}

$refeicoes = Refeicao::listar();
$hoje = date('Y-m-d');

$titulo = 'Refeições';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="dashboard-header" style="background:linear-gradient(135deg,#dc2626,#991b1b);">
    <h1><i class="fas fa-utensils"></i> Refeições</h1><p>Cardápio e vagas do almoço de cada dia</p>
</div>
<div class="panel">
    <div class="page-actions">
        <span></span>
        <a class="btn btn-verde" href="<?= url('admin/refeicao_form.php') ?>"><i class="fas fa-plus"></i> Nova refeição</a>
    </div>
    <table class="admin-table">
        <thead><tr><th>Data</th><th>Cardápio</th><th>Vagas</th><th>Status</th><th>Ações</th></tr></thead>
        <tbody>
        <?php if (!$refeicoes): ?><tr><td colspan="5" class="vazio">Nenhuma refeição cadastrada.</td></tr><?php endif; ?>
        <?php foreach ($refeicoes as $r): ?>
            <tr>
                <td><?= e(dataBR($r->data)) ?><?= $r->data === $hoje ? ' <span class="badge aluno">hoje</span>' : '' ?></td>
                <td><strong><?= e($r->cardapio_principal) ?></strong><br><small><?= e(implode(' · ', array_filter([$r->cardapio_acompanhamento, $r->cardapio_salada, $r->cardapio_sobremesa, $r->cardapio_suco]))) ?></small></td>
                <td><?= (int) $r->vagas_disponiveis ?>/<?= (int) $r->vagas_totais ?></td>
                <td><span class="badge <?= $r->status === 'disponivel' ? 'aluno' : 'admin' ?>"><?= $r->status === 'disponivel' ? 'Disponível' : 'Lotado' ?></span></td>
                <td class="tabela-acoes">
                    <a class="btn btn-cinza" href="<?= url('admin/refeicao_form.php?id=' . $r->id) ?>"><i class="fas fa-pen"></i> Editar</a>
                    <form method="post" onsubmit="return confirm('Excluir esta refeição? As reservas dela também serão removidas.')">
                        <?= csrfCampo() ?><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= $r->id ?>">
                        <button class="btn btn-vermelho"><i class="fas fa-trash"></i> Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php';
