<?php
// Configurações da base de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'agenda_medicamentos');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configurações de conexão PDO
define('PDO_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
]);

// Configurações de sessão
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 dias
define('SESSION_NAME', 'remediosja_session');

// Configurações de segurança
define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, PDO_OPTIONS);
            
            // Configurar timezone
            $this->pdo->exec("SET time_zone = '+00:00'");
            
        } catch (PDOException $e) {
            error_log("Erro na conexão com a base de dados: " . $e->getMessage());
            die("Erro na conexão com a base de dados. Tente novamente mais tarde.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    // Método para executar consultas preparadas
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Erro na consulta SQL: " . $e->getMessage());
            throw new Exception("Erro na operação da base de dados.");
        }
    }
    
    // Método para buscar um único registo
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    // Método para buscar múltiplos registos
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    // Método para inserir dados e retornar o ID
    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->pdo->lastInsertId();
    }
    
    // Método para atualizar dados
    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    // Método para eliminar dados
    public function delete($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    // Método para iniciar transação
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    // Método para confirmar transação
    public function commit() {
        return $this->pdo->commit();
    }
    
    // Método para reverter transação
    public function rollback() {
        return $this->pdo->rollback();
    }
}

// Classe para gestão de utilizadores
class UserManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Registar novo utilizador
    public function register($email, $password, $name = null) {
        // Verificar se o email já existe
        if ($this->emailExists($email)) {
            throw new Exception("Este e-mail já está registado.");
        }
        
        // Validar senha
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            throw new Exception("A senha deve ter pelo menos " . PASSWORD_MIN_LENGTH . " caracteres.");
        }
        
        // Hash da senha
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Inserir utilizador
        $sql = "INSERT INTO usuarios (email, senha_hash, nome) VALUES (?, ?, ?)";
        return $this->db->insert($sql, [$email, $passwordHash, $name]);
    }
    
    // Fazer login
    public function login($email, $password) {
        $sql = "SELECT * FROM usuarios WHERE email = ? AND ativo = TRUE";
        $user = $this->db->fetchOne($sql, [$email]);
        
        if ($user && password_verify($password, $user['senha_hash'])) {
            // Atualizar última atividade
            $this->updateLastActivity($user['id']);
            return $user;
        }
        
        return false;
    }
    
    // Verificar se email existe
    public function emailExists($email) {
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $result = $this->db->fetchOne($sql, [$email]);
        return $result !== false;
    }
    
    // Atualizar última atividade
    public function updateLastActivity($userId) {
        $sql = "UPDATE usuarios SET data_atualizacao = NOW() WHERE id = ?";
        return $this->db->update($sql, [$userId]);
    }
    
    // Buscar utilizador por ID
    public function getUserById($userId) {
        $sql = "SELECT * FROM usuarios WHERE id = ? AND ativo = TRUE";
        return $this->db->fetchOne($sql, [$userId]);
    }
    
    // Atualizar perfil do utilizador
    public function updateProfile($userId, $name, $email = null) {
        if ($email) {
            // Verificar se o novo email já existe (exceto para o próprio utilizador)
            $sql = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
            $existing = $this->db->fetchOne($sql, [$email, $userId]);
            if ($existing) {
                throw new Exception("Este e-mail já está em uso por outro utilizador.");
            }
            
            $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
            return $this->db->update($sql, [$name, $email, $userId]);
        } else {
            $sql = "UPDATE usuarios SET nome = ? WHERE id = ?";
            return $this->db->update($sql, [$name, $userId]);
        }
    }
    
    // Alterar senha
    public function changePassword($userId, $currentPassword, $newPassword) {
        // Verificar senha atual
        $user = $this->getUserById($userId);
        if (!$user || !password_verify($currentPassword, $user['senha_hash'])) {
            throw new Exception("Senha atual incorreta.");
        }
        
        // Validar nova senha
        if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            throw new Exception("A nova senha deve ter pelo menos " . PASSWORD_MIN_LENGTH . " caracteres.");
        }
        
        // Atualizar senha
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET senha_hash = ? WHERE id = ?";
        return $this->db->update($sql, [$passwordHash, $userId]);
    }
}

// Classe para gestão de medicamentos
class MedicationManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Adicionar medicamento
    public function addMedication($userId, $name, $dosage = null, $frequency = null, $schedules = null, $notes = null, $purchaseLink = null, $startDate = null, $endDate = null) {
        $sql = "INSERT INTO medicamentos (usuario_id, nome, dosagem, frequencia, horarios, observacoes, link_compra, data_inicio, data_fim) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $schedulesJson = $schedules ? json_encode($schedules) : null;
        
        return $this->db->insert($sql, [
            $userId, $name, $dosage, $frequency, $schedulesJson, $notes, $purchaseLink, $startDate, $endDate
        ]);
    }
    
    // Listar medicamentos do utilizador
    public function getUserMedications($userId, $activeOnly = true) {
        $sql = "SELECT * FROM medicamentos WHERE usuario_id = ?";
        $params = [$userId];
        
        if ($activeOnly) {
            $sql .= " AND ativo = TRUE";
        }
        
        $sql .= " ORDER BY nome";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    // Buscar medicamento por ID
    public function getMedicationById($medicationId, $userId = null) {
        $sql = "SELECT * FROM medicamentos WHERE id = ?";
        $params = [$medicationId];
        
        if ($userId) {
            $sql .= " AND usuario_id = ?";
            $params[] = $userId;
        }
        
        return $this->db->fetchOne($sql, $params);
    }
    
    // Atualizar medicamento
    public function updateMedication($medicationId, $userId, $name, $dosage = null, $frequency = null, $schedules = null, $notes = null, $purchaseLink = null, $startDate = null, $endDate = null) {
        $sql = "UPDATE medicamentos 
                SET nome = ?, dosagem = ?, frequencia = ?, horarios = ?, observacoes = ?, link_compra = ?, data_inicio = ?, data_fim = ?
                WHERE id = ? AND usuario_id = ?";
        
        $schedulesJson = $schedules ? json_encode($schedules) : null;
        
        return $this->db->update($sql, [
            $name, $dosage, $frequency, $schedulesJson, $notes, $purchaseLink, $startDate, $endDate, $medicationId, $userId
        ]);
    }
    
    // Eliminar medicamento (soft delete)
    public function deleteMedication($medicationId, $userId) {
        $sql = "UPDATE medicamentos SET ativo = FALSE WHERE id = ? AND usuario_id = ?";
        return $this->db->update($sql, [$medicationId, $userId]);
    }
}

// Classe para gestão de lembretes
class ReminderManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Adicionar lembrete
    public function addReminder($medicationId, $userId, $dateTime, $notes = null) {
        $sql = "INSERT INTO lembretes (medicamento_id, usuario_id, data_hora, observacoes) VALUES (?, ?, ?, ?)";
        return $this->db->insert($sql, [$medicationId, $userId, $dateTime, $notes]);
    }
    
    // Listar lembretes do utilizador
    public function getUserReminders($userId, $dateFrom = null, $dateTo = null, $pendingOnly = false) {
        $sql = "SELECT l.*, m.nome as medicamento_nome, m.dosagem 
                FROM lembretes l 
                JOIN medicamentos m ON l.medicamento_id = m.id 
                WHERE l.usuario_id = ? AND m.ativo = TRUE";
        
        $params = [$userId];
        
        if ($dateFrom) {
            $sql .= " AND l.data_hora >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND l.data_hora <= ?";
            $params[] = $dateTo;
        }
        
        if ($pendingOnly) {
            $sql .= " AND l.tomado = FALSE";
        }
        
        $sql .= " ORDER BY l.data_hora";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    // Marcar lembrete como tomado
    public function markReminderTaken($reminderId, $userId, $notes = null) {
        $sql = "UPDATE lembretes 
                SET tomado = TRUE, data_tomada = NOW(), observacoes = COALESCE(?, observacoes)
                WHERE id = ? AND usuario_id = ?";
        return $this->db->update($sql, [$notes, $reminderId, $userId]);
    }
    
    // Buscar próximos lembretes
    public function getUpcomingReminders($userId, $hours = 24) {
        $sql = "SELECT l.*, m.nome as medicamento_nome, m.dosagem 
                FROM lembretes l 
                JOIN medicamentos m ON l.medicamento_id = m.id 
                WHERE l.usuario_id = ? 
                  AND l.tomado = FALSE 
                  AND l.data_hora BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? HOUR)
                  AND m.ativo = TRUE
                ORDER BY l.data_hora";
        
        return $this->db->fetchAll($sql, [$userId, $hours]);
    }
    
    // Buscar lembretes em atraso
    public function getOverdueReminders($userId) {
        $sql = "SELECT l.*, m.nome as medicamento_nome, m.dosagem 
                FROM lembretes l 
                JOIN medicamentos m ON l.medicamento_id = m.id 
                WHERE l.usuario_id = ? 
                  AND l.tomado = FALSE 
                  AND l.data_hora < NOW()
                  AND m.ativo = TRUE
                ORDER BY l.data_hora";
        
        return $this->db->fetchAll($sql, [$userId]);
    }
}

// Inicializar sessão de forma segura
function initSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        session_start();
    }
}

// Verificar se utilizador está logado
function isLoggedIn() {
    return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
}

// Obter ID do utilizador logado
function getLoggedUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Fazer logout
function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

// Sanitizar entrada de dados
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Gerar token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verificar token CSRF
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Inicializar sessão automaticamente
initSecureSession();

// Instância global da base de dados (compatibilidade com código existente)
try {
    $pdo = Database::getInstance()->getConnection();
} catch (Exception $e) {
    error_log("Erro ao inicializar base de dados: " . $e->getMessage());
    die("Erro na conexão com a base de dados.");
}
?>

