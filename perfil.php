<?php
// Aluno edita os próprios dados (nome, curso, senha). Auxílio/tickets só o admin altera.
require_once __DIR__ . '/config/config.php';
$user = Auth::exigirUsuarioComum();
if ($user->isVisitante()) {
    redirecionar('dashboard.php');
}

$erros = [];
if (ehPost()) {
    validarCsrf();
    $user->nome  = trim($_POST['nome'] ?? '');
    $user->curso = trim($_POST['curso'] ?? '') ?: '-';
    $nova = $_POST['senha'] ?? '';

    $erros = $user->validar($nova, false);
    if ($nova !== '' && $nova !== ($_POST['confirmar'] ?? '')) {
        $erros[] = 'As senhas não coincidem!';
    }
    if (!$erros) {
        if ($nova !== '') {
            $user->definirSenha($nova);
        }
        $user->salvar();
        flash('Perfil atualizado com sucesso!', 'success');
        redirecionar('perfil.php');
    }
}

$titulo = 'Perfil';
require __DIR__ . '/includes/header.php';
?>
<div class="dashboard-header"><h1><i class="fas fa-user"></i> Meu Perfil</h1><p>Atualize seus dados de acesso</p></div>
<div class="form-card">
    <?php if ($erros): ?><div class="alert-erro"><ul><?php foreach ($erros as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" action="<?= url('perfil.php') ?>">
        <?= csrfCampo() ?>
        <div class="form-grid">
            <div class="form-group"><label>Nome</label><div class="input-icon"><i class="fas fa-user"></i>
                <input type="text" name="nome" value="<?= e($user->nome) ?>" required></div></div>
            <div class="form-group"><label>Curso</label><div class="input-icon"><i class="fas fa-graduation-cap"></i>
                <input type="text" name="curso" value="<?= e($user->curso === '-' ? '' : $user->curso) ?>"></div></div>
            <div class="form-group"><label>E-mail</label><div class="input-icon"><i class="fas fa-envelope"></i>
                <input type="email" value="<?= e($user->email) ?>" disabled></div></div>
            <div class="form-group"><label>Matrícula</label><div class="input-icon"><i class="fas fa-id-card"></i>
                <input type="text" value="<?= e($user->matricula) ?>" disabled></div></div>
            <div class="form-group"><label>Nova senha</label><div class="input-icon"><i class="fas fa-lock"></i>
                <input type="password" name="senha" placeholder="Deixe em branco para manter"></div></div>
            <div class="form-group"><label>Confirmar nova senha</label><div class="input-icon"><i class="fas fa-lock"></i>
                <input type="password" name="confirmar"></div></div>
        </div>
        <button class="btn-primary" type="submit">Salvar alterações</button>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php';
