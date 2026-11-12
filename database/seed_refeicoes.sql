-- ============================================
-- RU IFTO — Cadastro de almoços em lote (DEMONSTRAÇÃO)
-- Cadastra um almoço por dia, de HOJE até os próximos 30 dias,
-- com 7 cardápios que se repetem em rotação.
-- ============================================

SET NAMES utf8mb4;
USE ruifto;

INSERT IGNORE INTO refeicoes
(tipo, data, cardapio_principal, cardapio_acompanhamento, cardapio_salada, cardapio_sobremesa, cardapio_suco, vagas_totais, vagas_disponiveis, status)
SELECT 'almoco', d.dia, c.principal, c.acomp, c.salada, c.sobremesa, c.suco, 100, 100, 'disponivel'
FROM (
    -- gera os dias: hoje (n = 0) até hoje + 29
    SELECT n, DATE_ADD(CURDATE(), INTERVAL n DAY) AS dia
    FROM (
        SELECT a.i + b.i * 10 AS n
        FROM (SELECT 0 AS i UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
              UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a
        CROSS JOIN
             (SELECT 0 AS i UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
              UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b
    ) nums
    WHERE n < 30
) d
JOIN (
    -- 7 cardápios em rotação
    SELECT 0 AS idx, 'Arroz, feijão, bife acebolado' AS principal, 'Batata frita' AS acomp, 'Salada verde com tomate' AS salada, 'Pudim de leite' AS sobremesa, 'Suco de maracujá' AS suco
    UNION ALL SELECT 1, 'Frango grelhado com legumes',     'Purê de batata',   'Salada de beterraba', 'Gelatina',          'Suco de limão'
    UNION ALL SELECT 2, 'Arroz, feijão, carne de panela',  'Farofa',           'Salada de repolho',   'Banana',            'Suco de acerola'
    UNION ALL SELECT 3, 'Arroz, feijão, frango assado',    'Macarrão ao alho', 'Salada de alface',    'Doce de leite',     'Suco de caju'
    UNION ALL SELECT 4, 'Arroz, feijão, peixe ao molho',   'Pirão',            'Salada de pepino',    'Melancia',          'Suco de goiaba'
    UNION ALL SELECT 5, 'Arroz, feijão, estrogonofe',      'Batata palha',     'Salada de cenoura',   'Mousse de maracujá','Suco de abacaxi'
    UNION ALL SELECT 6, 'Arroz, feijão, carne moída',      'Legumes refogados','Salada de tomate',    'Laranja',           'Suco de manga'
) c ON c.idx = MOD(d.n, 7);
