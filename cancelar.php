<?php
require_once __DIR__ . '/config/config.php';
$user = Auth::exigirUsuarioComum();
if (!ehPost()) {
    redirecionar('dashboard.php');
}
validarCsrf();
try {
    Reserva::cancelar((int) ($_POST['reserva_id'] ?? 0), $user->id);
    flash('Reserva cancelada com sucesso!', 'success');
} catch (RuntimeException $e) {
    flash($e->getMessage(), 'error');
}
redirecionar('dashboard.php');
