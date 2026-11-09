<?php
/**
 * Cabeçalho comum. Variáveis esperadas: $titulo (string).
 * Monta a navbar de acordo com o perfil do usuário logado.
 */
$usuarioNav = Auth::usuario();
$paginaAtual = basename($_SERVER['SCRIPT_NAME']);
$ativo = fn(string $p) => $paginaAtual === $p ? 'class="active"' : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo ?? 'RU IFTO') ?> - RU IFTO</title>
    <link rel="icon" type="image/x-icon" href="<?= url('assets/icons/favicon.ico') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= url('assets/icons/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= url('assets/icons/favicon-16x16.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= url('assets/icons/favicon-180x180.png') ?>">
    <link rel="manifest" href="<?= url('assets/icons/site.webmanifest') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/css/extra.css') ?>">
</head>
<body>
<div id="app">
    <nav class="navbar" id="navbar">
        <div class="container">
            <div class="navbar-content">
                <div class="navbar-brand">
                    <img src="<?= url('assets/img/logo_branco.png') ?>" alt="RU IFTO" class="navbar-logo">
                    <span>RU IFTO</span>
                </div>
                <div class="navbar-links" id="navLinks">
                <?php if (!$usuarioNav): ?>
                    <a href="<?= url('login.php') ?>">Login</a>
                    <a href="<?= url('register.php') ?>">Registrar</a>
                <?php elseif ($usuarioNav->isAdmin()): ?>
                    <a href="<?= url('admin/index.php') ?>" <?= $ativo('index.php') ?>><i class="fas fa-cog"></i> Painel</a>
                    <a href="<?= url('admin/usuarios.php') ?>" <?= in_array($paginaAtual, ['usuarios.php','usuario_form.php']) ? 'class="active"' : '' ?>><i class="fas fa-users"></i> Usuários</a>
                    <a href="<?= url('admin/refeicoes.php') ?>" <?= in_array($paginaAtual, ['refeicoes.php','refeicao_form.php']) ? 'class="active"' : '' ?>><i class="fas fa-utensils"></i> Refeições</a>
                    <a href="<?= url('admin/reservas.php') ?>" <?= $ativo('reservas.php') ?>><i class="fas fa-calendar-check"></i> Reservas</a>
                    <a href="<?= url('logout.php') ?>" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
                <?php elseif ($usuarioNav->isVisitante()): ?>
                    <a href="<?= url('dashboard.php') ?>" <?= $ativo('dashboard.php') ?>><i class="fas fa-home"></i> Início</a>
                    <span class="navbar-badge neutral">👤 Visitante</span>
                    <a href="<?= url('logout.php') ?>" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
                <?php else: ?>
                    <a href="<?= url('dashboard.php') ?>" <?= $ativo('dashboard.php') ?>><i class="fas fa-home"></i> Início</a>
                    <a href="<?= url('tickets.php') ?>" <?= $ativo('tickets.php') ?>><i class="fas fa-ticket-alt"></i> Tickets</a>
                    <a href="<?= url('historico.php') ?>" <?= $ativo('historico.php') ?>><i class="fas fa-history"></i> Histórico</a>
                    <a href="<?= url('perfil.php') ?>" <?= $ativo('perfil.php') ?>><i class="fas fa-user"></i> Perfil</a>
                    <span class="navbar-badge tickets">🎫 <?= $usuarioNav->ticketsDisponiveis() ?></span>
                    <a href="<?= url('logout.php') ?>" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="main-content container" id="mainContent">
