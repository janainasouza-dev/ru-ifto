<?php
require_once __DIR__ . '/config/config.php';
if (Auth::usuario()) {
    redirecionar('dashboard.php');
}

$erro = '';
$email = '';
if (ehPost()) {
    validarCsrf();
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    if ($email === '' || $senha === '') {
        $erro = 'Informe e-mail e senha.';
    } elseif (Auth::login($email, $senha)) {
        flash('Login realizado com sucesso!', 'success');
        redirecionar('dashboard.php');
    } else {
        $erro = 'Email ou senha incorretos!';
    }
}

$titulo = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-page">
    <div class="auth-card">
        <div class="logo-icon"><img src="<?= url('assets/img/logo.png') ?>" alt="RU IFTO" class="auth-logo"></div>
        <h2>RU IFTO</h2>
        <p class="subtitle">Restaurante Universitário do IFTO</p>

        <?php if ($erro): ?><div class="error-message show">❌ <?= e($erro) ?></div><?php endif; ?>

        <form method="post" action="<?= url('login.php') ?>">
            <?= csrfCampo() ?>
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-icon"><i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="seu@email.com" value="<?= e($email) ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <div class="input-icon"><i class="fas fa-lock"></i>
                    <input type="password" id="senha" name="senha" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn-primary">Entrar</button>
        </form>

        <form method="post" action="<?= url('visitante.php') ?>">
            <?= csrfCampo() ?>
            <button type="submit" class="btn-secondary"><i class="fas fa-user-clock"></i> Entrar como Visitante</button>
        </form>

        <div class="auth-link">Não tem uma conta? <a href="<?= url('register.php') ?>">Registre-se</a></div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php';
