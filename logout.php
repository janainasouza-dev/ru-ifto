<?php
require_once __DIR__ . '/config/config.php';
Auth::logout();
session_start();
flash('Logout realizado!', 'info');
redirecionar('login.php');
