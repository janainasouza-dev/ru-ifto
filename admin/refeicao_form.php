<?php
// CREATE / UPDATE de refeições
require_once dirname(__DIR__) . '/config/config.php';
Auth::exigirAdmin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$r = $id ? Refeicao::buscar($id) : new Refeicao();
if (!$r) {
    flash('Refeição não encontrada.', 'error');
    redirecionar('admin/refeicoes.php');
}
$edicao = $r->id !== null;
if (!$edicao) {
    $r->data = date('Y-m-d');
}
$erros = [];

if (ehPost()) {
    validarCsrf();
    $r->data = trim($_POST['data'] ?? '');
    $r->cardapio_principal      = trim($_POST['cardapio_principal'] ?? '');
    $r->cardapio_acompanhamento = trim($_POST['cardapio_acompanhamento'] ?? '');
    $r->cardapio_salada         = trim($_POST['cardapio_salada'] ?? '');
    $r->cardapio_sobremesa      = trim($_POST['cardapio_sobremesa'] ?? '');
    $r->cardapio_suco           = trim($_POST['cardapio_suco'] ?? '');
    $r->vagas_totais            = (int) ($_POST['vagas_totais'] ?? 0);
    $r->vagas_disponiveis       = $edicao ? (int) ($_POST['vagas_disponiveis'] ?? 0) : $r->vagas_totais;

    $erros = $r->validar();
    if (!$erros) {
        $r->salvar();
        flash($edicao ? 'Refeição atualizada!' : 'Refeição cadastrada!', 'success');
        redirecionar('admin/refeicoes.php');
    }
}

$titulo = $edicao ? 'Editar refeição' : 'Nova refeição';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="dashboard-header" style="background:linear-gradient(135deg,#dc2626,#991b1b);"><h1><i class="fas fa-utensils"></i> <?= e($titulo) ?></h1></div>
<div class="form-card">
    <?php if ($erros): ?><div class="alert-erro"><ul><?php foreach ($erros as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post">
        <?= csrfCampo() ?>
        <input type="hidden" name="id" value="<?= (int) $r->id ?>">
        <div class="form-grid">
            <div class="form-group"><label>Data</label><input class="no-icon" type="date" name="data" value="<?= e($r->data) ?>" required></div>
            <div class="form-group"><label>Prato principal</label><input class="no-icon" type="text" name="cardapio_principal" value="<?= e($r->cardapio_principal) ?>" required></div>
            <div class="form-group"><label>Acompanhamento</label><input class="no-icon" type="text" name="cardapio_acompanhamento" value="<?= e($r->cardapio_acompanhamento) ?>"></div>
            <div class="form-group"><label>Salada</label><input class="no-icon" type="text" name="cardapio_salada" value="<?= e($r->cardapio_salada) ?>"></div>
            <div class="form-group"><label>Sobremesa</label><input class="no-icon" type="text" name="cardapio_sobremesa" value="<?= e($r->cardapio_sobremesa) ?>"></div>
            <div class="form-group"><label>Suco</label><input class="no-icon" type="text" name="cardapio_suco" value="<?= e($r->cardapio_suco) ?>"></div>
            <div class="form-group"><label>Vagas totais</label><input class="no-icon" type="number" min="1" name="vagas_totais" value="<?= (int) $r->vagas_totais ?>" required></div>
            <?php if ($edicao): ?>
            <div class="form-group"><label>Vagas disponíveis</label><input class="no-icon" type="number" min="0" name="vagas_disponiveis" value="<?= (int) $r->vagas_disponiveis ?>" required></div>
            <?php endif; ?>
        </div>
        <div class="page-actions" style="justify-content:flex-start;">
            <button class="btn btn-verde" type="submit">Salvar</button>
            <a class="btn btn-cinza" href="<?= url('admin/refeicoes.php') ?>">Cancelar</a>
        </div>
    </form>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php';
