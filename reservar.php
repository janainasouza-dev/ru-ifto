<?php
require_once __DIR__ . '/config/config.php';
$user = Auth::exigirUsuarioComum();
if (!ehPost()) {
    redirecionar('dashboard.php');
}
validarCsrf();
try {
    Reserva::criar($user->id, (int) ($_POST['refeicao_id'] ?? 0));
    flash('Reserva realizada com sucesso!', 'success');
} catch (RuntimeException $e) {
    flash($e->getMessage(), 'error');
}
redirecionar('dashboard.php');
