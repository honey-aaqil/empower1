<?php
// Database Configuration (TiDB Cloud)
define('DB_HOST', getenv('DB_HOST') ?: 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com');
define('DB_PORT', getenv('DB_PORT') ?: 4000);
define('DB_USER', getenv('DB_USER') ?: 'chsVms76mKp29o2.root');
define('DB_PASS', getenv('DB_PASS') ?: 'VBT2RLeJUjJQJn8F');
define('DB_NAME', getenv('DB_NAME') ?: 'test');


// Database-backed Session Handler (for serverless / Vercel deployment)
class DatabaseSessionHandler implements SessionHandlerInterface
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string|bool
    {
        $stmt = $this->conn->prepare("SELECT data FROM sessions WHERE id = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['data'];
        }
        return '';
    }

    public function write($id, $data): bool
    {
        $expires = date('Y-m-d H:i:s', time() + 86400); // 24 hours
        $stmt = $this->conn->prepare("REPLACE INTO sessions (id, data, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $id, $data, $expires);
        return $stmt->execute();
    }

    public function destroy($id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE id = ?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }

    public function gc($maxlifetime): int|bool
    {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE expires_at < NOW()");
        $stmt->execute();
        return $stmt->affected_rows;
    }
}

// Database Connection
class Database
{
    private $connection;

    public function __construct()
    {
        $this->connection = mysqli_init();

        // Use persistent connection (p: prefix) + SSL for faster serverless reconnects
        $host = 'p:' . DB_HOST;
        $connected = $this->connection->real_connect($host, DB_USER, DB_PASS, DB_NAME, DB_PORT, NULL, MYSQLI_CLIENT_SSL);

        if (!$connected) {
            die("Connection failed: " . mysqli_connect_error());
        }
        $this->connection->set_charset("utf8mb4");
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function query($sql)
    {
        return $this->connection->query($sql);
    }

    public function prepare($sql)
    {
        return $this->connection->prepare($sql);
    }

    public function escape($string)
    {
        return $this->connection->real_escape_string($string);
    }

    public function lastInsertId()
    {
        return $this->connection->insert_id;
    }
}

// Helper Functions
function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url)
{
    header("Location: $url");
    exit();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin()
{
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        redirect('dashboard.php');
    }
}

function isHR()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'hr';
}

function isEmployee()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'employee';
}

function requireManagement()
{
    requireLogin();
    if (isEmployee()) {
        redirect('dashboard.php');
    }
}



// Initialize Database
$db = new Database();

// Register database session handler and start session
$sessionHandler = new DatabaseSessionHandler($db->getConnection());
session_set_save_handler($sessionHandler, true);
session_start();
?>