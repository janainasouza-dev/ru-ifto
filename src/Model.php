<?php
/**
 * Classe base das entidades: concentra conexão e operações
 * genéricas (buscar por id, excluir) para reaproveitar código.
 */
abstract class Model
{
    protected static string $tabela;

    public ?int $id = null;

    protected static function db(): PDO
    {
        return Database::conectar();
    }

    /** Cria o objeto da entidade a partir de uma linha do banco. */
    public static function fromRow(array $linha): static
    {
        $obj = new static();
        foreach ($linha as $campo => $valor) {
            if (property_exists($obj, $campo)) {
                $obj->$campo = $valor;
            }
        }
        $obj->id = (int) $linha['id'];
        return $obj;
    }

    public static function buscar(int $id): ?static
    {
        $st = static::db()->prepare('SELECT * FROM ' . static::$tabela . ' WHERE id = :id');
        $st->execute([':id' => $id]);
        $linha = $st->fetch();
        return $linha ? static::fromRow($linha) : null;
    }

    public static function excluir(int $id): bool
    {
        $st = static::db()->prepare('DELETE FROM ' . static::$tabela . ' WHERE id = :id');
        return $st->execute([':id' => $id]);
    }
}
