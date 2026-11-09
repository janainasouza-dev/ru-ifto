<?php
// Funções utilitárias globais

/** Escapa texto para HTML (proteção contra XSS). */
function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** Monta uma URL absoluta a partir da raiz do projeto. */
function url(string $caminho = ''): string
{
    return BASE_URL . '/' . ltrim($caminho, '/');
}

function redirecionar(string $caminho): never
{
    header('Location: ' . url($caminho));
    exit;
}

/** Mensagens "flash" (toast) mostradas na próxima página. */
function flash(string $mensagem, string $tipo = 'info'): void
{
    $_SESSION['flash'] = ['msg' => $mensagem, 'tipo' => $tipo];
}

function pegarFlash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function dinheiro(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function dataHora(?string $data): string
{
    return $data ? date('d/m/Y H:i', strtotime($data)) : '-';
}

function dataBR(?string $data): string
{
    return $data ? date('d/m/Y', strtotime($data)) : '-';
}

/** Token CSRF: gera e valida (formulários POST). */
function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfCampo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

function validarCsrf(): void
{
    $enviado = $_POST['csrf'] ?? '';
    if (!is_string($enviado) || !hash_equals($_SESSION['csrf'] ?? '', $enviado)) {
        http_response_code(419);
        exit('Requisição inválida (token CSRF).');
    }
}

function ehPost(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}
