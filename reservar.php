<?php
require_once __DIR__ . '/config/config.php';
$user = Auth::exigirUsuarioComum();
if (!ehPost()) {
    redirecionar('dashboard.php');
}
validarCsrf();
try {
    $valor = Reserva::criar($user->id, (int) ($_POST['refeicao_id'] ?? 0));
    $msg = 'Reserva realizada com sucesso!';
    if ($valor > 0) {
        $msg .= ' Pague ' . dinheiro($valor) . ' no caixa.';
    }
    flash($msg, 'success');
} catch (RuntimeException $e) {
    flash($e->getMessage(), 'error');
}
redirecionar('dashboard.php');