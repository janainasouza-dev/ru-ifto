<?php
// CREATE / UPDATE de usuários
require_once dirname(__DIR__) . '/config/config.php';
Auth::exigirAdmin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$u = $id ? Usuario::buscar($id) : new Usuario();
if (!$u) {
    flash('Usuário não encontrado.', 'error');
    redirecionar('admin/usuarios.php');
}
$edicao = $u->id !== null;
$erros = [];

if (ehPost()) {
    validarCsrf();
    $senha = $_POST['senha'] ?? '';
    $u->nome   = trim($_POST['nome'] ?? '');
    $u->email  = trim($_POST['email'] ?? '');
    $u->perfil = $_POST['perfil'] ?? 'aluno';
    $u->matricula = trim($_POST['matricula'] ?? '') ?: '-';
    $u->curso     = trim($_POST['curso'] ?? '') ?: '-';
    $u->periodo   = trim($_POST['periodo'] ?? '') ?: '-';
    $u->auxilio_ativo   = isset($_POST['auxilio_ativo']) ? 1 : 0;
    $u->tickets_totais  = max(0, (int) ($_POST['tickets_totais'] ?? 0));
    $u->tickets_usados  = max(0, (int) ($_POST['tickets_usados'] ?? 0));
    $u->valor_refeicao  = max(0, (float) str_replace(',', '.', $_POST['valor_refeicao'] ?? '0'));

    $erros = $u->validar($senha, !$edicao);
    if (!$erros) {
        if ($senha !== '') {
            $u->definirSenha($senha);
        }
        $u->salvar();
        flash($edicao ? 'Usuário atualizado!' : 'Usuário cadastrado!', 'success');
        redirecionar('admin/usuarios.php');
    }
}

$titulo = $edicao ? 'Editar usuário' : 'Novo usuário';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="dashboard-header" style="background:linear-gradient(135deg,#dc2626,#991b1b);">
    <h1><i class="fas fa-user-edit"></i> <?= e($titulo) ?></h1>
</div>
<div class="form-card">
    <?php if ($erros): ?><div class="alert-erro"><ul><?php foreach ($erros as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post">
        <?= csrfCampo() ?>
        <input type="hidden" name="id" value="<?= (int) $u->id ?>">
        <div class="form-grid">
            <div class="form-group"><label>Nome</label><input class="no-icon" type="text" name="nome" value="<?= e($u->nome) ?>" required></div>
            <div class="form-group"><label>E-mail</label><input class="no-icon" type="email" name="email" value="<?= e($u->email) ?>" required></div>
            <div class="form-group"><label>Perfil</label>
                <select name="perfil">
                    <?php foreach (['aluno', 'admin', 'visitante'] as $p): ?>
                        <option value="<?= $p ?>" <?= $u->perfil === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div class="form-group"><label>Senha <?= $edicao ? '(vazio = manter)' : '' ?></label>
                <input class="no-icon" type="password" name="senha" <?= $edicao ? '' : 'required' ?> minlength="6"></div>
            <div class="form-group"><label>Matrícula</label><input class="no-icon" type="text" name="matricula" value="<?= e($u->matricula === '-' ? '' : $u->matricula) ?>"></div>
            <div class="form-group"><label>Curso</label><input class="no-icon" type="text" name="curso" value="<?= e($u->curso === '-' ? '' : $u->curso) ?>"></div>
            <div class="form-group"><label>Período</label><input class="no-icon" type="text" name="periodo" placeholder="2026.1" value="<?= e($u->periodo === '-' ? '' : $u->periodo) ?>"></div>
            <div class="form-group"><label>Valor da refeição (visitante)</label><input class="no-icon" type="number" step="0.01" min="0" name="valor_refeicao" value="<?= e(number_format((float) $u->valor_refeicao, 2, '.', '')) ?>"></div>
            <div class="form-group"><label>Tickets totais</label><input class="no-icon" type="number" min="0" name="tickets_totais" value="<?= (int) $u->tickets_totais ?>"></div>
            <div class="form-group"><label>Tickets usados</label><input class="no-icon" type="number" min="0" name="tickets_usados" value="<?= (int) $u->tickets_usados ?>"></div>
            <div class="form-group check full"><label><input type="checkbox" name="auxilio_ativo" <?= $u->auxilio_ativo ? 'checked' : '' ?>> Auxílio alimentação ativo</label></div>
        </div>
        <div class="page-actions" style="justify-content:flex-start;">
            <button class="btn btn-verde" type="submit">Salvar</button>
            <a class="btn btn-cinza" href="<?= url('admin/usuarios.php') ?>">Cancelar</a>
        </div>
    </form>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php';
