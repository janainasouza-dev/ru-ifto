<?php
require_once __DIR__ . '/config/config.php';
redirecionar(Auth::usuario() ? 'dashboard.php' : 'login.php');
