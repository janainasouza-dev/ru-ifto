<?php
/** Entidade Ticket — histórico de consumo do auxílio estudantil. */
class Ticket extends Model
{
    protected static string $tabela = 'tickets';

    public int $usuario_id = 0;
    public ?int $reserva_id = null;
    public int $quantidade = 1;
    public string $periodo = '-';
    public string $status = 'utilizada';
    public ?string $criado_em = null;

    /** Registra o consumo de 1 ticket (chamado dentro da transação da reserva). */
    public static function registrar(int $usuarioId, int $reservaId, string $periodo): void
    {
        $st = self::db()->prepare(
            "INSERT INTO tickets (usuario_id, reserva_id, quantidade, periodo, status) VALUES (:u, :r, 1, :p, 'utilizada')"
        );
        $st->execute([':u' => $usuarioId, ':r' => $reservaId, ':p' => $periodo]);
    }

    /** Marca como devolvido o ticket ligado a uma reserva cancelada. */
    public static function devolverDaReserva(int $reservaId): void
    {
        $st = self::db()->prepare("UPDATE tickets SET status = 'devolvida' WHERE reserva_id = :r");
        $st->execute([':r' => $reservaId]);
    }

    /** @return Ticket[] */
    public static function listarPorUsuario(int $usuarioId): array
    {
        $st = self::db()->prepare('SELECT * FROM tickets WHERE usuario_id = :u ORDER BY criado_em DESC, id DESC');
        $st->execute([':u' => $usuarioId]);
        return array_map([self::class, 'fromRow'], $st->fetchAll());
    }
}
