<?php
/** Autenticação por sessão e proteção de páginas. */
class Auth
{
    public static function login(string $email, string $senha): bool
    {
        $u = Usuario::buscarPorEmail(trim($email));
        // Visitante não entra por senha: usa o acesso rápido
        if (!$u || $u->isVisitante() || !$u->verificarSenha($senha)) {
            return false;
        }
        self::iniciarSessao($u);
        return true;
    }

    public static function loginVisitante(): bool
    {
        $u = Usuario::primeiroVisitante();
        if (!$u) {
            return false;
        }
        self::iniciarSessao($u);
        return true;
    }

    private static function iniciarSessao(Usuario $u): void
    {
        session_regenerate_id(true); // evita fixação de sessão
        $_SESSION['usuario_id'] = $u->id;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    /** Usuário logado (sempre relido do banco, para refletir tickets/auxílio atuais). */
    public static function usuario(): ?Usuario
    {
        static $cache = false;
        if ($cache !== false) {
            return $cache;
        }
        $cache = isset($_SESSION['usuario_id']) ? Usuario::buscar((int) $_SESSION['usuario_id']) : null;
        if (!$cache) {
            unset($_SESSION['usuario_id']);
        }
        return $cache;
    }

    public static function exigirLogin(): Usuario
    {
        $u = self::usuario();
        if (!$u) {
            flash('Faça login para continuar.', 'error');
            redirecionar('login.php');
        }
        return $u;
    }

    public static function exigirAdmin(): Usuario
    {
        $u = self::exigirLogin();
        if (!$u->isAdmin()) {
            flash('Acesso negado!', 'error');
            redirecionar('dashboard.php');
        }
        return $u;
    }

    /** Páginas do aluno/visitante: admin é redirecionado ao painel. */
    public static function exigirUsuarioComum(): Usuario
    {
        $u = self::exigirLogin();
        if ($u->isAdmin()) {
            redirecionar('admin/index.php');
        }
        return $u;
    }
}
