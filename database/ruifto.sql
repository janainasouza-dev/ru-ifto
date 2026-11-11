-- ============================================
-- RU IFTO — Script de criação e dados iniciais
-- Banco: MySQL / MariaDB
--
-- Este script é SEGURO para rodar mais de uma vez:
--   * não apaga tabelas nem dados existentes
--   * cria tabelas só se ainda não existirem
--   * ignora dados iniciais que já foram inseridos
--
-- Para zerar o banco durante o desenvolvimento, use reset_dev.sql
-- ============================================

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS ruifto
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ruifto;

-- --------------------------------------------
-- USUÁRIOS (admin, aluno, visitante)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome           VARCHAR(120) NOT NULL,
    email          VARCHAR(150) NOT NULL UNIQUE,
    senha_hash     VARCHAR(255) NOT NULL,              -- password_hash() (bcrypt)
    perfil         ENUM('admin','aluno','visitante') NOT NULL DEFAULT 'aluno',
    matricula      VARCHAR(30)  NOT NULL DEFAULT '-',
    curso          VARCHAR(100) NOT NULL DEFAULT '-',
    periodo        VARCHAR(10)  NOT NULL DEFAULT '-',
    auxilio_ativo  TINYINT(1)   NOT NULL DEFAULT 0,
    tickets_totais INT UNSIGNED NOT NULL DEFAULT 0,
    tickets_usados INT UNSIGNED NOT NULL DEFAULT 0,
    valor_refeicao DECIMAL(6,2) NOT NULL DEFAULT 0.00, -- usado por visitantes
    criado_em      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_tickets CHECK (tickets_usados <= tickets_totais)
) ENGINE=InnoDB;

-- --------------------------------------------
-- REFEIÇÕES (almoço de cada dia)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS refeicoes (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo                    ENUM('almoco') NOT NULL DEFAULT 'almoco',
    data                    DATE         NOT NULL,
    cardapio_principal      VARCHAR(200) NOT NULL,
    cardapio_acompanhamento VARCHAR(200) NULL,
    cardapio_salada         VARCHAR(200) NULL,
    cardapio_sobremesa      VARCHAR(200) NULL,
    cardapio_suco           VARCHAR(200) NULL,
    vagas_totais            INT UNSIGNED NOT NULL DEFAULT 100,
    vagas_disponiveis       INT UNSIGNED NOT NULL DEFAULT 100,
    status                  ENUM('disponivel','lotado') NOT NULL DEFAULT 'disponivel',
    UNIQUE KEY uk_refeicao_dia (tipo, data),
    CONSTRAINT chk_vagas CHECK (vagas_disponiveis <= vagas_totais)
) ENGINE=InnoDB;

-- --------------------------------------------
-- RESERVAS (usuário x refeição)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS reservas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT UNSIGNED NOT NULL,
    refeicao_id     INT UNSIGNED NOT NULL,
    status          ENUM('confirmada','utilizada','cancelada') NOT NULL DEFAULT 'confirmada',
    tipo_usuario    ENUM('admin','aluno','visitante') NOT NULL,
    valor           DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    data_reserva    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_utilizacao DATETIME NULL,
    CONSTRAINT fk_reserva_usuario  FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)  ON DELETE CASCADE,
    CONSTRAINT fk_reserva_refeicao FOREIGN KEY (refeicao_id) REFERENCES refeicoes(id) ON DELETE CASCADE,
    INDEX idx_reserva_usuario (usuario_id, status)
) ENGINE=InnoDB;

-- --------------------------------------------
-- TICKETS (histórico de consumo do auxílio)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS tickets (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    reserva_id INT UNSIGNED NULL,
    quantidade INT UNSIGNED NOT NULL DEFAULT 1,
    periodo    VARCHAR(10) NOT NULL,
    status     ENUM('utilizada','devolvida') NOT NULL DEFAULT 'utilizada',
    criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ticket_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_ticket_reserva FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- DADOS INICIAIS (somente para desenvolvimento/demonstração)
-- Senhas (bcrypt):  admin123 | 123456 | visitante
-- INSERT IGNORE: se o id já existir, a linha é ignorada
-- ============================================
INSERT IGNORE INTO usuarios
(id, nome, email, senha_hash, perfil, matricula, curso, periodo, auxilio_ativo, tickets_totais, tickets_usados, valor_refeicao) VALUES
(1, 'Administrador', 'admin@ru.com',               '$2y$10$to9obC3.OqoHf/Z..fOTeub5qEmgnJbx5pJ.7UM8BoYik7f78D1Ze', 'admin',     'ADMIN001',     'Administração',         '2024.2', 0, 0,  0, 0.00),
(2, 'João Silva',     'joao@email.com',            '$2y$10$vyUiszK2J5IAMb5VW6S7XOAKVUjWB0zAkteIO24ThQNVrZVkE22.O', 'aluno',     '202512170015', 'Engenharia Agronômica', '2024.2', 1, 20, 2, 0.00),
(3, 'Maria Santos',   'maria@email.com',           '$2y$10$vyUiszK2J5IAMb5VW6S7XOAKVUjWB0zAkteIO24ThQNVrZVkE22.O', 'aluno',     '202512170013', 'Análise de Sistemas',   '2024.2', 1, 20, 5, 0.00),
(4, 'Visitante',      'visitante@ru.ifto.edu.br',  '$2y$10$sSuWUC0gibf3xXlwA9.utOGQInFlliXBYsY7wUfvqrz43ekCpBdZC', 'visitante',  '-',            '-',                     '-',      0, 0,  0, 13.00),
(5, 'Janaina',        'janaina@email.com',         '$2y$10$vyUiszK2J5IAMb5VW6S7XOAKVUjWB0zAkteIO24ThQNVrZVkE22.O', 'aluno',     '202512170016', 'Análise de Sistemas',   '2024.2', 1, 20, 5, 0.00);

INSERT IGNORE INTO refeicoes
(id, tipo, data, cardapio_principal, cardapio_acompanhamento, cardapio_salada, cardapio_sobremesa, cardapio_suco, vagas_totais, vagas_disponiveis, status) VALUES
(1, 'almoco', CURDATE(),                           'Arroz, feijão, bife acebolado',  'Batata frita',  'Salada verde com tomate', 'Pudim de leite', 'Suco de maracujá', 100, 32, 'disponivel'),
(2, 'almoco', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Frango grelhado com legumes',    'Purê de batata','Salada de beterraba',     'Gelatina',       'Suco de limão',    100, 50, 'disponivel'),
(3, 'almoco', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Arroz, feijão, carne de panela', 'Farofa',        'Salada de repolho',       'Banana',         'Suco de acerola',  100, 98, 'disponivel');

INSERT IGNORE INTO reservas (id, usuario_id, refeicao_id, status, tipo_usuario, valor, data_reserva, data_utilizacao) VALUES
(1, 2, 3, 'utilizada', 'aluno', 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 3, 3, 'utilizada', 'aluno', 0.00, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY));

-- id fixo para evitar duplicar os registros ao rodar o script de novo
INSERT IGNORE INTO tickets (id, usuario_id, reserva_id, quantidade, periodo, status, criado_em) VALUES
(1, 2, 1, 1, '2024.2', 'utilizada', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 3, 2, 1, '2024.2', 'utilizada', DATE_SUB(NOW(), INTERVAL 1 DAY));
