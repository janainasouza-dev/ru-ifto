<?php
/**
 * Conexão única (Singleton) com o banco via PDO.
 * Todas as consultas do sistema usam prepared statements.
 */
class Database
{
    private static ?PDO $instancia = null;

    private function __construct() {}

    public static function conectar(): PDO
    {
        if (self::$instancia === null) {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
            try {
                self::$instancia = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                exit('Erro ao conectar ao banco de dados. Verifique config/config.php e se o script database/ruifto.sql foi importado.');
            }
        }
        return self::$instancia;
    }
}
