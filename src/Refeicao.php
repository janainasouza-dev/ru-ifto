<?php
/** Entidade Refeicao (almoço do dia) — CRUD completo. */
class Refeicao extends Model
{
    protected static string $tabela = 'refeicoes';

    public string $tipo = 'almoco';
    public string $data = '';
    public string $cardapio_principal = '';
    public ?string $cardapio_acompanhamento = null;
    public ?string $cardapio_salada = null;
    public ?string $cardapio_sobremesa = null;
    public ?string $cardapio_suco = null;
    public int $vagas_totais = 100;
    public int $vagas_disponiveis = 100;
    public string $status = 'disponivel';

    public function estaDisponivel(): bool
    {
        return $this->status === 'disponivel' && $this->vagas_disponiveis > 0;
    }

    public function validar(): array
    {
        $erros = [];
        $d = DateTime::createFromFormat('Y-m-d', $this->data);
        if (!$d || $d->format('Y-m-d') !== $this->data) {
            $erros[] = 'Data inválida.';
        } elseif (self::existeNaData($this->data, $this->id)) {
            $erros[] = 'Já existe um almoço cadastrado para esta data.';
        }
        if (trim($this->cardapio_principal) === '') {
            $erros[] = 'Informe o prato principal.';
        }
        if ($this->vagas_totais < 1) {
            $erros[] = 'O total de vagas deve ser maior que zero.';
        }
        if ($this->vagas_disponiveis < 0 || $this->vagas_disponiveis > $this->vagas_totais) {
            $erros[] = 'Vagas disponíveis deve estar entre 0 e o total de vagas.';
        }
        return $erros;
    }

    public static function existeNaData(string $data, ?int $ignorarId = null): bool
    {
        $st = self::db()->prepare("SELECT COUNT(*) FROM refeicoes WHERE data = :d AND tipo = 'almoco' AND id <> :id");
        $st->execute([':d' => $data, ':id' => $ignorarId ?? 0]);
        return (int) $st->fetchColumn() > 0;
    }

    /** @return Refeicao[] */
    public static function doDia(?string $data = null): array
    {
        $st = self::db()->prepare("SELECT * FROM refeicoes WHERE tipo = 'almoco' AND data = :d");
        $st->execute([':d' => $data ?? date('Y-m-d')]);
        return array_map([self::class, 'fromRow'], $st->fetchAll());
    }

    /** @return Refeicao[] */
    public static function listar(): array
    {
        $linhas = self::db()->query('SELECT * FROM refeicoes ORDER BY data DESC')->fetchAll();
        return array_map([self::class, 'fromRow'], $linhas);
    }

    public function salvar(): bool
    {
        $this->status = $this->vagas_disponiveis > 0 ? 'disponivel' : 'lotado';
        $dados = [
            ':data' => $this->data, ':p' => trim($this->cardapio_principal),
            ':a' => trim((string) $this->cardapio_acompanhamento) ?: null,
            ':sa' => trim((string) $this->cardapio_salada) ?: null,
            ':so' => trim((string) $this->cardapio_sobremesa) ?: null,
            ':su' => trim((string) $this->cardapio_suco) ?: null,
            ':vt' => $this->vagas_totais, ':vd' => $this->vagas_disponiveis, ':st' => $this->status,
        ];
        if ($this->id === null) {
            $st = self::db()->prepare(
                "INSERT INTO refeicoes (tipo,data,cardapio_principal,cardapio_acompanhamento,cardapio_salada,cardapio_sobremesa,cardapio_suco,vagas_totais,vagas_disponiveis,status)
                 VALUES ('almoco',:data,:p,:a,:sa,:so,:su,:vt,:vd,:st)"
            );
            $ok = $st->execute($dados);
            $this->id = (int) self::db()->lastInsertId();
            return $ok;
        }
        $dados[':id'] = $this->id;
        $st = self::db()->prepare(
            'UPDATE refeicoes SET data=:data,cardapio_principal=:p,cardapio_acompanhamento=:a,cardapio_salada=:sa,
             cardapio_sobremesa=:so,cardapio_suco=:su,vagas_totais=:vt,vagas_disponiveis=:vd,status=:st WHERE id=:id'
        );
        return $st->execute($dados);
    }
}
