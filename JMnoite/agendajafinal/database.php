
-- Criar a base de dados
CREATE DATABASE IF NOT EXISTS agenda_medicamentos
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Usar a base de dados
USE agenda_medicamentos;

-- =====================================================
-- Tabela: usuarios
-- Descrição: Armazena informações dos utilizadores registados
-- =====================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    nome VARCHAR(100) DEFAULT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE,
    
    -- Índices para melhor performance
    INDEX idx_email (email),
    INDEX idx_ativo (ativo),
    INDEX idx_data_criacao (data_criacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabela: medicamentos
-- Descrição: Armazena informações dos medicamentos dos utilizadores
-- =====================================================
CREATE TABLE IF NOT EXISTS medicamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    dosagem VARCHAR(100) DEFAULT NULL,
    frequencia VARCHAR(100) DEFAULT NULL,
    horarios JSON DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    link_compra VARCHAR(500) DEFAULT NULL,
    data_inicio DATE DEFAULT NULL,
    data_fim DATE DEFAULT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Chave estrangeira
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    
    -- Índices para melhor performance
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_nome (nome),
    INDEX idx_ativo (ativo),
    INDEX idx_data_inicio (data_inicio),
    INDEX idx_data_fim (data_fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabela: lembretes
-- Descrição: Armazena lembretes de medicamentos
-- =====================================================
CREATE TABLE IF NOT EXISTS lembretes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicamento_id INT NOT NULL,
    usuario_id INT NOT NULL,
    data_hora DATETIME NOT NULL,
    tomado BOOLEAN DEFAULT FALSE,
    data_tomada TIMESTAMP NULL DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Chaves estrangeiras
    FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    
    -- Índices para melhor performance
    INDEX idx_medicamento_id (medicamento_id),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_data_hora (data_hora),
    INDEX idx_tomado (tomado),
    INDEX idx_data_tomada (data_tomada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabela: sessoes
-- Descrição: Armazena sessões de utilizadores (opcional)
-- =====================================================
CREATE TABLE IF NOT EXISTS sessoes (
    id VARCHAR(128) PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_expiracao TIMESTAMP NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    
    -- Chave estrangeira
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    
    -- Índices para melhor performance
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_data_expiracao (data_expiracao),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Inserir dados de exemplo (opcional)
-- =====================================================

-- Utilizador de exemplo (senha: 123456)
INSERT INTO usuarios (email, senha_hash, nome) VALUES
('admin@remediosja.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador');

-- =====================================================
-- Views úteis
-- =====================================================

-- View para medicamentos ativos com informações do utilizador
CREATE OR REPLACE VIEW v_medicamentos_ativos AS
SELECT
    m.id,
    m.nome,
    m.dosagem,
    m.frequencia,
    m.horarios,
    m.observacoes,
    m.link_compra,
    m.data_inicio,
    m.data_fim,
    u.email as usuario_email,
    u.nome as usuario_nome
FROM medicamentos m
JOIN usuarios u ON m.usuario_id = u.id
WHERE m.ativo = TRUE AND u.ativo = TRUE;

-- View para próximos lembretes
CREATE OR REPLACE VIEW v_proximos_lembretes AS
SELECT
    l.id,
    l.data_hora,
    l.tomado,
    l.observacoes,
    m.nome as medicamento_nome,
    m.dosagem,
    u.email as usuario_email,
    u.nome as usuario_nome
FROM lembretes l
JOIN medicamentos m ON l.medicamento_id = m.id
JOIN usuarios u ON l.usuario_id = u.id
WHERE l.tomado = FALSE
  AND l.data_hora >= NOW()
  AND m.ativo = TRUE
  AND u.ativo = TRUE
ORDER BY l.data_hora ASC;

-- =====================================================
-- Stored Procedures úteis
-- =====================================================

DELIMITER //

-- Procedure para criar lembretes automáticos
CREATE PROCEDURE CriarLembretesAutomaticos(
    IN p_medicamento_id INT,
    IN p_data_inicio DATE,
    IN p_data_fim DATE
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_horario TIME;
    DECLARE v_data_atual DATE;
    DECLARE v_usuario_id INT;
    
    -- Cursor para horários (assumindo formato JSON simples)
    DECLARE horarios_cursor CURSOR FOR
        SELECT usuario_id FROM medicamentos WHERE id = p_medicamento_id;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Buscar usuario_id
    SELECT usuario_id INTO v_usuario_id
    FROM medicamentos
    WHERE id = p_medicamento_id;
    
    -- Loop através das datas
    SET v_data_atual = p_data_inicio;
    
    WHILE v_data_atual <= p_data_fim DO
        -- Aqui você pode adicionar lógica para criar lembretes
        -- baseado nos horários armazenados no campo JSON
        
        SET v_data_atual = DATE_ADD(v_data_atual, INTERVAL 1 DAY);
    END WHILE;
    
END //

-- Procedure para marcar lembrete como tomado
CREATE PROCEDURE MarcarLembreteTomado(
    IN p_lembrete_id INT,
    IN p_observacoes TEXT
)
BEGIN
    UPDATE lembretes
    SET tomado = TRUE,
        data_tomada = NOW(),
        observacoes = COALESCE(p_observacoes, observacoes)
    WHERE id = p_lembrete_id;
END //

DELIMITER ;

-- =====================================================
-- Triggers para auditoria
-- =====================================================

-- Trigger para atualizar data_atualizacao automaticamente
DELIMITER //

CREATE TRIGGER tr_usuarios_update
    BEFORE UPDATE ON usuarios
    FOR EACH ROW
BEGIN
    SET NEW.data_atualizacao = NOW();
END //

CREATE TRIGGER tr_medicamentos_update 
    BEFORE UPDATE ON medicamentos
    FOR EACH ROW
BEGIN
    SET NEW.data_atualizacao = NOW();
END //

DELIMITER ;

-- =====================================================
-- Configurações de segurança e performance
-- =====================================================

-- Configurar timezone (ajustar conforme necessário)
SET time_zone = '+00:00';

-- Otimizações de performance
SET GLOBAL innodb_buffer_pool_size = 128M;
SET GLOBAL query_cache_size = 32M;
SET GLOBAL query_cache_type = 1;

-- =====================================================
-- Verificação final
-- =====================================================

-- Mostrar tabelas criadas
SHOW TABLES;

-- Mostrar estrutura das tabelas principais
DESCRIBE usuarios;
DESCRIBE medicamentos;
DESCRIBE lembretes;

-- Verificar se as foreign keys foram criadas corretamente
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_SCHEMA = 'agenda_medicamentos'
  AND REFERENCED_TABLE_NAME IS NOT NULL;

-- =====================================================
-- Fim do script
-- =====================================================

SELECT 'Base de dados agenda_medicamentos criada com sucesso!' as status;