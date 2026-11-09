<?php
// Acesso rápido de visitante: não exige cadastro nem senha (paga avulso no caixa do RU).
require_once __DIR__ . '/config/config.php';
if (!ehPost()) {
    redirecionar('login.php');
}
validarCsrf();
if (Auth::loginVisitante()) {
    flash('Acesso de visitante iniciado!', 'info');
    redirecionar('dashboard.php');
}
flash('Conta de visitante não encontrada.', 'error');
redirecionar('login.php');
