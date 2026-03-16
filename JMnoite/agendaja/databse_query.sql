-- 1. Listar todos os utilizadores ativos
SELECT 
    id,
    email,
    nome,
    data_criacao,
    data_atualizacao
FROM usuarios 
WHERE ativo = TRUE
ORDER BY data_criacao DESC;

-- 2. Buscar utilizador por email
SELECT * FROM usuarios 
WHERE email = 'exemplo@email.com' AND ativo = TRUE;

-- 3. Contar total de utilizadores registados
SELECT COUNT(*) as total_usuarios FROM usuarios WHERE ativo = TRUE;

-- 4. Utilizadores registados nos últimos 30 dias
SELECT 
    email,
    nome,
    data_criacao
FROM usuarios 
WHERE data_criacao >= DATE_SUB(NOW(), INTERVAL 30 DAY)
  AND ativo = TRUE
ORDER BY data_criacao DESC;

-- =====================================================
-- CONSULTAS PARA MEDICAMENTOS
-- =====================================================

-- 5. Listar medicamentos de um utilizador específico
SELECT 
    m.id,
    m.nome,
    m.dosagem,
    m.frequencia,
    m.data_inicio,
    m.data_fim,
    m.ativo
FROM medicamentos m
WHERE m.usuario_id = 1 AND m.ativo = TRUE
ORDER BY m.nome;

-- 6. Medicamentos que estão a terminar (próximos 7 dias)
SELECT 
    m.nome,
    m.dosagem,
    m.data_fim,
    u.email,
    u.nome as usuario_nome
FROM medicamentos m
JOIN usuarios u ON m.usuario_id = u.id
WHERE m.data_fim BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
  AND m.ativo = TRUE
  AND u.ativo = TRUE
ORDER BY m.data_fim;

-- 7. Medicamentos mais utilizados (por número de utilizadores)
SELECT 
    nome,
    COUNT(DISTINCT usuario_id) as num_usuarios
FROM medicamentos 
WHERE ativo = TRUE
GROUP BY nome
ORDER BY num_usuarios DESC
LIMIT 10;

-- 8. Buscar medicamentos por nome (pesquisa parcial)
SELECT 
    m.id,
    m.nome,
    m.dosagem,
    u.email
FROM medicamentos m
JOIN usuarios u ON m.usuario_id = u.id
WHERE m.nome LIKE '%paracetamol%' 
  AND m.ativo = TRUE
  AND u.ativo = TRUE;

-- =====================================================
-- CONSULTAS PARA LEMBRETES
-- =====================================================

-- 9. Lembretes pendentes para hoje
SELECT 
    l.id,
    l.data_hora,
    m.nome as medicamento,
    m.dosagem,
    u.email,
    u.nome as usuario_nome
FROM lembretes l
JOIN medicamentos m ON l.medicamento_id = m.id
JOIN usuarios u ON l.usuario_id = u.id
WHERE DATE(l.data_hora) = CURDATE()
  AND l.tomado = FALSE
  AND m.ativo = TRUE
  AND u.ativo = TRUE
ORDER BY l.data_hora;

-- 10. Próximos lembretes (próximas 24 horas)
SELECT 
    l.id,
    l.data_hora,
    m.nome as medicamento,
    m.dosagem,
    u.email
FROM lembretes l
JOIN medicamentos m ON l.medicamento_id = m.id
JOIN usuarios u ON l.usuario_id = u.id
WHERE l.data_hora BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
  AND l.tomado = FALSE
  AND m.ativo = TRUE
  AND u.ativo = TRUE
ORDER BY l.data_hora;

-- 11. Lembretes em atraso
SELECT 
    l.id,
    l.data_hora,
    m.nome as medicamento,
    m.dosagem,
    u.email,
    TIMESTAMPDIFF(HOUR, l.data_hora, NOW()) as horas_atraso
FROM lembretes l
JOIN medicamentos m ON l.medicamento_id = m.id
JOIN usuarios u ON l.usuario_id = u.id
WHERE l.data_hora < NOW()
  AND l.tomado = FALSE
  AND m.ativo = TRUE
  AND u.ativo = TRUE
ORDER BY l.data_hora;

-- 12. Histórico de medicamentos tomados (últimos 30 dias)
SELECT 
    l.data_tomada,
    m.nome as medicamento,
    m.dosagem,
    l.observacoes
FROM lembretes l
JOIN medicamentos m ON l.medicamento_id = m.id
WHERE l.usuario_id = 1
  AND l.tomado = TRUE
  AND l.data_tomada >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY l.data_tomada DESC;

-- =====================================================
-- CONSULTAS ESTATÍSTICAS
-- =====================================================

-- 13. Taxa de adesão por utilizador (últimos 30 dias)
SELECT 
    u.email,
    u.nome,
    COUNT(l.id) as total_lembretes,
    SUM(CASE WHEN l.tomado = TRUE THEN 1 ELSE 0 END) as lembretes_tomados,
    ROUND(
        (SUM(CASE WHEN l.tomado = TRUE THEN 1 ELSE 0 END) * 100.0) / COUNT(l.id), 
        2
    ) as taxa_adesao_percent
FROM usuarios u
LEFT JOIN lembretes l ON u.id = l.usuario_id 
    AND l.data_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
WHERE u.ativo = TRUE
GROUP BY u.id, u.email, u.nome
HAVING COUNT(l.id) > 0
ORDER BY taxa_adesao_percent DESC;

-- 14. Medicamentos com maior taxa de esquecimento
SELECT 
    m.nome,
    COUNT(l.id) as total_lembretes,
    SUM(CASE WHEN l.tomado = FALSE AND l.data_hora < NOW() THEN 1 ELSE 0 END) as esquecidos,
    ROUND(
        (SUM(CASE WHEN l.tomado = FALSE AND l.data_hora < NOW() THEN 1 ELSE 0 END) * 100.0) / COUNT(l.id), 
        2
    ) as taxa_esquecimento_percent
FROM medicamentos m
JOIN lembretes l ON m.id = l.medicamento_id
WHERE m.ativo = TRUE
  AND l.data_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY m.id, m.nome
HAVING COUNT(l.id) >= 5
ORDER BY taxa_esquecimento_percent DESC;

-- 15. Horários mais comuns para medicamentos
SELECT 
    HOUR(l.data_hora) as hora,
    COUNT(*) as quantidade_lembretes
FROM lembretes l
JOIN medicamentos m ON l.medicamento_id = m.id
WHERE m.ativo = TRUE
  AND l.data_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY HOUR(l.data_hora)
ORDER BY quantidade_lembretes DESC;

-- =====================================================
-- CONSULTAS PARA RELATÓRIOS
-- =====================================================

-- 16. Relatório mensal de utilizador
SELECT 
    DATE(l.data_hora) as data,
    COUNT(CASE WHEN l.tomado = TRUE THEN 1 END) as medicamentos_tomados,
    COUNT(CASE WHEN l.tomado = FALSE AND l.data_hora < NOW() THEN 1 END) as medicamentos_esquecidos,
    COUNT(*) as total_lembretes
FROM lembretes l
JOIN medicamentos m ON l.medicamento_id = m.id
WHERE l.usuario_id = 1
  AND l.data_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
  AND m.ativo = TRUE
GROUP BY DATE(l.data_hora)
ORDER BY data DESC;

-- 17. Resumo geral do sistema
SELECT 
    (SELECT COUNT(*) FROM usuarios WHERE ativo = TRUE) as total_usuarios,
    (SELECT COUNT(*) FROM medicamentos WHERE ativo = TRUE) as total_medicamentos,
    (SELECT COUNT(*) FROM lembretes WHERE data_hora >= CURDATE()) as lembretes_hoje,
    (SELECT COUNT(*) FROM lembretes WHERE tomado = FALSE AND data_hora < NOW()) as lembretes_atrasados;

-- =====================================================
-- CONSULTAS PARA MANUTENÇÃO
-- =====================================================

-- 18. Limpar lembretes antigos (mais de 1 ano)
DELETE FROM lembretes 
WHERE data_hora < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- 19. Limpar sessões expiradas
DELETE FROM sessoes 
WHERE data_expiracao < NOW();

-- 20. Encontrar utilizadores inativos (sem login há mais de 6 meses)
SELECT 
    u.id,
    u.email,
    u.nome,
    u.data_atualizacao
FROM usuarios u
WHERE u.data_atualizacao < DATE_SUB(NOW(), INTERVAL 6 MONTH)
  AND u.ativo = TRUE
ORDER BY u.data_atualizacao;

-- =====================================================
-- CONSULTAS PARA BACKUP E RESTAURO
-- =====================================================

-- 21. Backup de dados de utilizador específico
SELECT 
    'usuarios' as tabela,
    JSON_OBJECT(
        'id', u.id,
        'email', u.email,
        'nome', u.nome,
        'data_criacao', u.data_criacao
    ) as dados
FROM usuarios u
WHERE u.id = 1

UNION ALL

SELECT 
    'medicamentos' as tabela,
    JSON_OBJECT(
        'id', m.id,
        'nome', m.nome,
        'dosagem', m.dosagem,
        'frequencia', m.frequencia,
        'horarios', m.horarios,
        'observacoes', m.observacoes,
        'link_compra', m.link_compra,
        'data_inicio', m.data_inicio,
        'data_fim', m.data_fim
    ) as dados
FROM medicamentos m
WHERE m.usuario_id = 1 AND m.ativo = TRUE

UNION ALL

SELECT 
    'lembretes' as tabela,
    JSON_OBJECT(
        'id', l.id,
        'medicamento_id', l.medicamento_id,
        'data_hora', l.data_hora,
        'tomado', l.tomado,
        'data_tomada', l.data_tomada,
        'observacoes', l.observacoes
    ) as dados
FROM lembretes l
WHERE l.usuario_id = 1;

-- =====================================================
-- ÍNDICES ADICIONAIS PARA PERFORMANCE
-- =====================================================

-- Índices compostos para consultas frequentes
CREATE INDEX idx_lembretes_usuario_data ON lembretes(usuario_id, data_hora);
CREATE INDEX idx_lembretes_tomado_data ON lembretes(tomado, data_hora);
CREATE INDEX idx_medicamentos_usuario_ativo ON medicamentos(usuario_id, ativo);
CREATE INDEX idx_medicamentos_data_fim ON medicamentos(data_fim) WHERE data_fim IS NOT NULL;

-- =====================================================
-- FUNÇÕES ÚTEIS
-- =====================================================

DELIMITER //

-- Função para calcular próximo lembrete
CREATE FUNCTION ProximoLembrete(p_usuario_id INT) 
RETURNS DATETIME
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_proximo_lembrete DATETIME;
    
    SELECT MIN(data_hora) INTO v_proximo_lembrete
    FROM lembretes l
    JOIN medicamentos m ON l.medicamento_id = m.id
    WHERE l.usuario_id = p_usuario_id
      AND l.tomado = FALSE
      AND l.data_hora > NOW()
      AND m.ativo = TRUE;
    
    RETURN v_proximo_lembrete;
END //

-- Função para contar medicamentos ativos de um utilizador
CREATE FUNCTION ContarMedicamentosAtivos(p_usuario_id INT) 
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_count INT;
    
    SELECT COUNT(*) INTO v_count
    FROM medicamentos
    WHERE usuario_id = p_usuario_id AND ativo = TRUE;
    
    RETURN v_count;
END //

DELIMITER ;

-- =====================================================
-- Fim das consultas
-- =====================================================

