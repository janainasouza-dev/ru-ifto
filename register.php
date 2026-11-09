<?php
require_once __DIR__ . '/config/config.php';
if (Auth::usuario()) {
    redirecionar('dashboard.php');
}

$erros = [];
$u = new Usuario();
if (ehPost()) {
    validarCsrf();
    $senha = $_POST['senha'] ?? '';
    $u->nome      = trim($_POST['nome'] ?? '');
    $u->email     = trim($_POST['email'] ?? '');
    $u->matricula = trim($_POST['matricula'] ?? '') ?: '-';
    $u->curso     = trim($_POST['curso'] ?? '') ?: 'Não informado';
    $u->perfil    = 'aluno';          // auto-cadastro é sempre aluno, sem auxílio
    $u->periodo   = date('Y') . '.' . (date('n') <= 6 ? '1' : '2');
    $u->auxilio_ativo = 0;

    $erros = $u->validar($senha, true);
    if ($senha !== ($_POST['confirmar'] ?? '')) {
        $erros[] = 'As senhas não coincidem!';
    }
    if (!$erros) {
        $u->definirSenha($senha);
        $u->salvar();
        flash('Cadastro realizado! Aguarde a ativação do auxílio pelo administrador.', 'success');
        redirecionar('login.php');
    }
}

$titulo = 'Criar conta';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-page">
    <div class="auth-card">
        <div class="logo-icon"><i class="fas fa-user-plus"></i></div>
        <h2>Criar Conta</h2>
        <p class="subtitle">Cadastre-se no Restaurante Universitário</p>

        <?php if ($erros): ?>
            <div class="alert-erro"><ul><?php foreach ($erros as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <form method="post" action="<?= url('register.php') ?>">
            <?= csrfCampo() ?>
            <div class="form-group"><label>Nome</label>
                <div class="input-icon"><i class="fas fa-user"></i>
                <input type="text" name="nome" placeholder="Seu nome completo" value="<?= e($u->nome) ?>" required></div></div>
            <div class="form-group"><label>Email</label>
                <div class="input-icon"><i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="seu@email.com" value="<?= e($u->email) ?>" required></div></div>
            <div class="form-group"><label>Matrícula</label>
                <div class="input-icon"><i class="fas fa-id-card"></i>
                <input type="text" name="matricula" placeholder="Número da matrícula" value="<?= e($u->matricula === '-' ? '' : $u->matricula) ?>"></div></div>
            <div class="form-group"><label>Curso</label>
                <div class="input-icon"><i class="fas fa-graduation-cap"></i>
                <input type="text" name="curso" placeholder="Seu curso" value="<?= e($u->curso === 'Não informado' ? '' : $u->curso) ?>"></div></div>
            <div class="form-group"><label>Senha</label>
                <div class="input-icon"><i class="fas fa-lock"></i>
                <input type="password" name="senha" placeholder="Mínimo 6 caracteres" minlength="6" required></div></div>
            <div class="form-group"><label>Confirmar Senha</label>
                <div class="input-icon"><i class="fas fa-lock"></i>
                <input type="password" name="confirmar" placeholder="Confirme sua senha" required></div></div>
            <button type="submit" class="btn-primary">Cadastrar</button>
        </form>
        <div class="auth-link">Já tem uma conta? <a href="<?= url('login.php') ?>">Faça login</a></div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php';
