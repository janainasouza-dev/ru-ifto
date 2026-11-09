<?php
/**
 * Entidade Reserva — concentra as regras de negócio do RU:
 * vagas, tickets do auxílio, regras do visitante e prazo de cancelamento.
 */
class Reserva extends Model
{
    protected static string $tabela = 'reservas';

    public int $usuario_id = 0;
    public int $refeicao_id = 0;
    public string $status = 'confirmada';
    public string $tipo_usuario = 'aluno';
    public float|string $valor = 0;
    public ?string $data_reserva = null;
    public ?string $data_utilizacao = null;

    public static function dentroDoPrazoCancelamento(): bool
    {
        $h = (int) date('G');
        $m = (int) date('i');
        return $h < CANCELAMENTO_HORA || ($h === CANCELAMENTO_HORA && $m <= CANCELAMENTO_MINUTO);
    }

    // ---------- CREATE ----------
    /** @throws RuntimeException com a mensagem de regra violada */
    public static function criar(int $usuarioId, int $refeicaoId): void
    {
        $db = self::db();
        $db->beginTransaction();
        try {
            // Bloqueia as linhas para evitar reservas simultâneas além das vagas
            $st = $db->prepare('SELECT * FROM usuarios WHERE id = :id FOR UPDATE');
            $st->execute([':id' => $usuarioId]);
            $u = ($l = $st->fetch()) ? Usuario::fromRow($l) : null;

            $st = $db->prepare('SELECT * FROM refeicoes WHERE id = :id FOR UPDATE');
            $st->execute([':id' => $refeicaoId]);
            $r = ($l = $st->fetch()) ? Refeicao::fromRow($l) : null;

            if (!$u || !$r) {
                throw new RuntimeException('Refeição ou usuário não encontrado.');
            }
            if ($u->isAdmin()) {
                throw new RuntimeException('Administradores não fazem reservas.');
            }
            if ($r->data !== date('Y-m-d')) {
                throw new RuntimeException('Só é possível reservar o almoço do dia.');
            }
            if (!$r->estaDisponivel()) {
                throw new RuntimeException('Não há vagas disponíveis!');
            }

            $st = $db->prepare("SELECT COUNT(*) FROM reservas WHERE usuario_id = :u AND refeicao_id = :r AND status = 'confirmada'");
            $st->execute([':u' => $u->id, ':r' => $r->id]);
            if ((int) $st->fetchColumn() > 0) {
                throw new RuntimeException('Você já reservou esta refeição!');
            }

            $valor = 0.0;
            if ($u->isVisitante()) {
                $st = $db->prepare("SELECT COUNT(*) FROM reservas WHERE usuario_id = :u AND status = 'confirmada'");
                $st->execute([':u' => $u->id]);
                if ((int) $st->fetchColumn() > 0) {
                    throw new RuntimeException('Visitante só pode ter 1 reserva ativa por vez!');
                }
                $valor = (float) ($u->valor_refeicao ?: PRECO_REFEICAO_VISITANTE);
            } else {
                if (!$u->auxilio_ativo) {
                    throw new RuntimeException('Você não tem auxílio alimentação ativo!');
                }
                if ($u->ticketsDisponiveis() <= 0) {
                    throw new RuntimeException('Você não tem tickets disponíveis!');
                }
            }

            $st = $db->prepare(
                'INSERT INTO reservas (usuario_id, refeicao_id, status, tipo_usuario, valor) VALUES (:u, :r, :s, :t, :v)'
            );
            $st->execute([':u' => $u->id, ':r' => $r->id, ':s' => 'confirmada', ':t' => $u->perfil, ':v' => $valor]);
            $reservaId = (int) $db->lastInsertId();

            $db->prepare(
                "UPDATE refeicoes SET vagas_disponiveis = vagas_disponiveis - 1,
                 status = IF(vagas_disponiveis - 1 <= 0, 'lotado', 'disponivel') WHERE id = :id"
            )->execute([':id' => $r->id]);

            if ($u->isAluno()) {
                $db->prepare('UPDATE usuarios SET tickets_usados = tickets_usados + 1 WHERE id = :id')->execute([':id' => $u->id]);
                Ticket::registrar($u->id, $reservaId, $u->periodo);
            }
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    // ---------- READ ----------
    private const SELECT_BASE =
        'SELECT r.*, f.data AS refeicao_data, f.cardapio_principal, u.nome AS usuario_nome, u.email AS usuario_email
         FROM reservas r
         JOIN refeicoes f ON f.id = r.refeicao_id
         JOIN usuarios  u ON u.id = r.usuario_id';

    public static function listarPorUsuario(int $usuarioId, ?string $status = null): array
    {
        $sql = self::SELECT_BASE . ' WHERE r.usuario_id = :u';
        $p = [':u' => $usuarioId];
        if ($status) {
            $sql .= ' AND r.status = :s';
            $p[':s'] = $status;
        }
        $st = self::db()->prepare($sql . ' ORDER BY r.data_reserva DESC, r.id DESC');
        $st->execute($p);
        return $st->fetchAll();
    }

    public static function listarTodas(?string $status = null): array
    {
        $sql = self::SELECT_BASE;
        $p = [];
        if ($status) {
            $sql .= ' WHERE r.status = :s';
            $p[':s'] = $status;
        }
        $st = self::db()->prepare($sql . ' ORDER BY r.data_reserva DESC, r.id DESC');
        $st->execute($p);
        return $st->fetchAll();
    }

    public static function contarHoje(string $status, ?string $tipoUsuario = null): int
    {
        $sql = 'SELECT COUNT(*) FROM reservas WHERE status = :s AND DATE(data_reserva) = :d';
        $p = [':s' => $status, ':d' => date('Y-m-d')];
        if ($tipoUsuario) {
            $sql .= ' AND tipo_usuario = :t';
            $p[':t'] = $tipoUsuario;
        }
        $st = self::db()->prepare($sql);
        $st->execute($p);
        return (int) $st->fetchColumn();
    }

    public static function contarPorStatus(string $status): int
    {
        $st = self::db()->prepare('SELECT COUNT(*) FROM reservas WHERE status = :s');
        $st->execute([':s' => $status]);
        return (int) $st->fetchColumn();
    }

    public static function receitaAvulsaHoje(): float
    {
        $st = self::db()->prepare(
            "SELECT COALESCE(SUM(valor),0) FROM reservas
             WHERE tipo_usuario = 'visitante' AND status IN ('confirmada','utilizada') AND DATE(data_reserva) = :d"
        );
        $st->execute([':d' => date('Y-m-d')]);
        return (float) $st->fetchColumn();
    }

    // ---------- UPDATE ----------
    /** Cancelamento feito pelo próprio usuário (respeita o prazo). */
    public static function cancelar(int $reservaId, int $usuarioId): void
    {
        $r = self::buscar($reservaId);
        if (!$r || $r->usuario_id !== $usuarioId) {
            throw new RuntimeException('Você não pode cancelar esta reserva!');
        }
        if (!self::dentroDoPrazoCancelamento()) {
            throw new RuntimeException('Prazo de cancelamento expirado (até 9:30h)!');
        }
        if ($r->status !== 'confirmada') {
            throw new RuntimeException('Esta reserva já foi utilizada ou cancelada!');
        }
        self::reverter($r, 'cancelada');
    }

    /** Alteração de status feita pelo administrador (sem prazo). */
    public static function alterarStatus(int $reservaId, string $novo): void
    {
        $r = self::buscar($reservaId);
        if (!$r) {
            throw new RuntimeException('Reserva não encontrada.');
        }
        if ($r->status !== 'confirmada') {
            throw new RuntimeException('Só reservas confirmadas podem ser alteradas.');
        }
        if ($novo === 'cancelada') {
            self::reverter($r, 'cancelada');
        } elseif ($novo === 'utilizada') {
            $st = self::db()->prepare("UPDATE reservas SET status = 'utilizada', data_utilizacao = NOW() WHERE id = :id");
            $st->execute([':id' => $r->id]);
        } else {
            throw new RuntimeException('Status inválido.');
        }
    }

    // ---------- DELETE ----------
    public static function excluir(int $id): bool
    {
        $r = self::buscar($id);
        if (!$r) {
            return false;
        }
        $db = self::db();
        $db->beginTransaction();
        try {
            if ($r->status === 'confirmada') {
                self::devolverRecursos($r); // libera vaga e devolve ticket
            }
            $db->prepare('DELETE FROM reservas WHERE id = :id')->execute([':id' => $id]);
            $db->commit();
            return true;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    // ---------- internos ----------
    private static function reverter(Reserva $r, string $novoStatus): void
    {
        $db = self::db();
        $db->beginTransaction();
        try {
            self::devolverRecursos($r);
            $db->prepare('UPDATE reservas SET status = :s WHERE id = :id')->execute([':s' => $novoStatus, ':id' => $r->id]);
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /** Devolve a vaga à refeição e o ticket ao aluno. */
    private static function devolverRecursos(Reserva $r): void
    {
        $db = self::db();
        $db->prepare(
            "UPDATE refeicoes SET vagas_disponiveis = LEAST(vagas_totais, vagas_disponiveis + 1), status = 'disponivel' WHERE id = :id"
        )->execute([':id' => $r->refeicao_id]);

        if ($r->tipo_usuario === 'aluno') {
            $db->prepare('UPDATE usuarios SET tickets_usados = GREATEST(0, tickets_usados - 1) WHERE id = :id')
               ->execute([':id' => $r->usuario_id]);
            Ticket::devolverDaReserva($r->id);
        }
    }
}
