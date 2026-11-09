<?php
/** Entidade Usuario (admin, aluno ou visitante) — CRUD completo. */
class Usuario extends Model
{
    protected static string $tabela = 'usuarios';

    public string $nome = '';
    public string $email = '';
    public string $senha_hash = '';
    public string $perfil = 'aluno';
    public string $matricula = '-';
    public string $curso = '-';
    public string $periodo = '-';
    public int $auxilio_ativo = 0;
    public int $tickets_totais = 0;
    public int $tickets_usados = 0;
    public float|string $valor_refeicao = 0;
    public ?string $criado_em = null;

    // ---------- Regras ----------
    public function ticketsDisponiveis(): int
    {
        return max(0, (int) $this->tickets_totais - (int) $this->tickets_usados);
    }

    public function isAdmin(): bool     { return $this->perfil === 'admin'; }
    public function isAluno(): bool     { return $this->perfil === 'aluno'; }
    public function isVisitante(): bool { return $this->perfil === 'visitante'; }

    public function definirSenha(string $senha): void
    {
        $this->senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    }

    public function verificarSenha(string $senha): bool
    {
        return password_verify($senha, $this->senha_hash);
    }

    /** Valida os dados; retorna lista de erros (vazia = ok). */
    public function validar(?string $senhaNova = null, bool $senhaObrigatoria = true): array
    {
        $erros = [];
        if (strlen(trim($this->nome)) < 3) {
            $erros[] = 'Informe o nome (mínimo 3 caracteres).';
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'E-mail inválido.';
        } elseif (self::emailEmUso($this->email, $this->id)) {
            $erros[] = 'Este e-mail já está cadastrado.';
        }
        if (!in_array($this->perfil, ['admin', 'aluno', 'visitante'], true)) {
            $erros[] = 'Perfil inválido.';
        }
        if ($senhaObrigatoria && ($senhaNova === null || strlen($senhaNova) < 6)) {
            $erros[] = 'A senha deve ter no mínimo 6 caracteres.';
        } elseif (!$senhaObrigatoria && $senhaNova !== null && $senhaNova !== '' && strlen($senhaNova) < 6) {
            $erros[] = 'A nova senha deve ter no mínimo 6 caracteres.';
        }
        if ($this->tickets_usados > $this->tickets_totais) {
            $erros[] = 'Tickets usados não pode ser maior que o total de tickets.';
        }
        return $erros;
    }

    // ---------- Persistência ----------
    public static function emailEmUso(string $email, ?int $ignorarId = null): bool
    {
        $st = self::db()->prepare('SELECT COUNT(*) FROM usuarios WHERE email = :e AND id <> :id');
        $st->execute([':e' => $email, ':id' => $ignorarId ?? 0]);
        return (int) $st->fetchColumn() > 0;
    }

    public static function buscarPorEmail(string $email): ?Usuario
    {
        $st = self::db()->prepare('SELECT * FROM usuarios WHERE email = :e');
        $st->execute([':e' => $email]);
        $linha = $st->fetch();
        return $linha ? self::fromRow($linha) : null;
    }

    public static function primeiroVisitante(): ?Usuario
    {
        $linha = self::db()->query("SELECT * FROM usuarios WHERE perfil = 'visitante' ORDER BY id LIMIT 1")->fetch();
        return $linha ? self::fromRow($linha) : null;
    }

    /** @return Usuario[] */
    public static function listar(?string $perfil = null, string $busca = ''): array
    {
        $sql = 'SELECT * FROM usuarios WHERE 1=1';
        $params = [];
        if ($perfil) {
            $sql .= ' AND perfil = :p';
            $params[':p'] = $perfil;
        }
        if ($busca !== '') {
            $sql .= ' AND (nome LIKE :b OR email LIKE :b OR matricula LIKE :b)';
            $params[':b'] = '%' . $busca . '%';
        }
        $sql .= ' ORDER BY nome';
        $st = self::db()->prepare($sql);
        $st->execute($params);
        return array_map([self::class, 'fromRow'], $st->fetchAll());
    }

    public static function contar(string $where = '1=1'): int
    {
        // $where é sempre uma string fixa escrita no código (nunca vem do usuário)
        return (int) self::db()->query("SELECT COUNT(*) FROM usuarios WHERE $where")->fetchColumn();
    }

    /** Insere (id nulo) ou atualiza. */
    public function salvar(): bool
    {
        $dados = [
            ':nome' => trim($this->nome), ':email' => trim($this->email), ':senha' => $this->senha_hash,
            ':perfil' => $this->perfil, ':mat' => $this->matricula ?: '-', ':curso' => $this->curso ?: '-',
            ':per' => $this->periodo ?: '-', ':aux' => (int) $this->auxilio_ativo,
            ':tt' => (int) $this->tickets_totais, ':tu' => (int) $this->tickets_usados,
            ':val' => (float) $this->valor_refeicao,
        ];
        if ($this->id === null) {
            $st = self::db()->prepare(
                'INSERT INTO usuarios (nome,email,senha_hash,perfil,matricula,curso,periodo,auxilio_ativo,tickets_totais,tickets_usados,valor_refeicao)
                 VALUES (:nome,:email,:senha,:perfil,:mat,:curso,:per,:aux,:tt,:tu,:val)'
            );
            $ok = $st->execute($dados);
            $this->id = (int) self::db()->lastInsertId();
            return $ok;
        }
        $dados[':id'] = $this->id;
        $st = self::db()->prepare(
            'UPDATE usuarios SET nome=:nome,email=:email,senha_hash=:senha,perfil=:perfil,matricula=:mat,curso=:curso,
             periodo=:per,auxilio_ativo=:aux,tickets_totais=:tt,tickets_usados=:tu,valor_refeicao=:val WHERE id=:id'
        );
        return $st->execute($dados);
    }
}
