<?php
require_once dirname(__DIR__) . '/config/config.php';
$admin = Auth::exigirAdmin();

// DELETE
if (ehPost() && ($_POST['acao'] ?? '') === 'excluir') {
    validarCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id === $admin->id) {
        flash('Você não pode excluir a própria conta.', 'error');
    } elseif (Usuario::excluir($id)) {
        flash('Usuário excluído.', 'success');
    }
    redirecionar('admin/usuarios.php');
}

// READ (com filtros)
$perfil = $_GET['perfil'] ?? '';
$busca  = trim($_GET['q'] ?? '');
$usuarios = Usuario::listar(in_array($perfil, ['admin', 'aluno', 'visitante'], true) ? $perfil : null, $busca);

$titulo = 'Usuários';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="dashboard-header" style="background:linear-gradient(135deg,#dc2626,#991b1b);">
    <h1><i class="fas fa-users"></i> Usuários</h1><p>Cadastre, edite e exclua usuários, auxílios e tickets</p>
</div>
<div class="panel">
    <div class="page-actions">
        <form method="get" class="filtros">
            <input type="search" name="q" placeholder="Buscar nome, e-mail ou matrícula" value="<?= e($busca) ?>">
            <select name="perfil">
                <option value="">Todos os perfis</option>
                <?php foreach (['admin', 'aluno', 'visitante'] as $p): ?>
                    <option value="<?= $p ?>" <?= $perfil === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-cinza">Filtrar</button>
        </form>
        <a class="btn btn-verde" href="<?= url('admin/usuario_form.php') ?>"><i class="fas fa-plus"></i> Novo usuário</a>
    </div>
    <table class="admin-table">
        <thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Curso / Matrícula</th><th>Auxílio</th><th>Tickets</th><th>Ações</th></tr></thead>
        <tbody>
        <?php if (!$usuarios): ?><tr><td colspan="7" class="vazio">Nenhum usuário encontrado.</td></tr><?php endif; ?>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= e($u->nome) ?></td>
                <td><?= e($u->email) ?></td>
                <td><span class="badge <?= e($u->perfil) ?>"><?= e($u->perfil) ?></span></td>
                <td><?= e($u->curso) ?> · <?= e($u->matricula) ?></td>
                <td><?= $u->isAluno() ? ($u->auxilio_ativo ? '✅ Ativo' : '❌ Inativo') : '—' ?></td>
                <td><?= $u->isAluno() ? $u->ticketsDisponiveis() . '/' . (int) $u->tickets_totais : '—' ?></td>
                <td class="tabela-acoes">
                    <a class="btn btn-cinza" href="<?= url('admin/usuario_form.php?id=' . $u->id) ?>"><i class="fas fa-pen"></i> Editar</a>
                    <?php if ($u->id !== $admin->id): ?>
                    <form method="post" onsubmit="return confirm('Excluir <?= e(addslashes($u->nome)) ?>? Suas reservas também serão removidas.')">
                        <?= csrfCampo() ?><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= $u->id ?>">
                        <button class="btn btn-vermelho"><i class="fas fa-trash"></i> Excluir</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php';
