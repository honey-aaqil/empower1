<?php
// Database Configuration (TiDB Cloud)
define('DB_HOST', 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com');
define('DB_PORT', 4000);
define('DB_USER', 'chsVms76mKp29o2.root');
define('DB_PASS', 'VBT2RLeJUjJQJn8F');
define('DB_NAME', 'test');

// Google AI Studio API Configuration
define('GOOGLE_AI_API_KEY', 'AIzaSyCOUEXmc-k82Pgv48VBATeotWj7Mg_RFdo');
define('GOOGLE_AI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent');

// Session Configuration
session_start();

// Database Connection
class Database
{
    private $connection;

    public function __construct()
    {
        $this->connection = mysqli_init();

        // Connect with SSL flag (MYSQLI_CLIENT_SSL)
        $connected = $this->connection->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, NULL, MYSQLI_CLIENT_SSL);

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

// Google AI Studio API Integration
class GoogleAI
{
    private $apiKey;
    private $apiUrl;

    public function __construct()
    {
        $this->apiKey = GOOGLE_AI_API_KEY;
        $this->apiUrl = GOOGLE_AI_API_URL;
    }

    public function generateContent($prompt)
    {
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($this->apiUrl . '?key=' . $this->apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function analyzeEmployeeSentiment($feedback)
    {
        $prompt = "Analyze the sentiment of this employee feedback and provide insights. Rate positivity from 0-100 and suggest improvements:\n\n$feedback";
        return $this->generateContent($prompt);
    }

    public function predictPerformance($employeeData)
    {
        $prompt = "Based on this employee data, predict performance trends and provide recommendations:\n\n" . json_encode($employeeData);
        return $this->generateContent($prompt);
    }

    public function generateJobDescription($role, $requirements)
    {
        $prompt = "Generate a professional job description for: $role\nRequirements: $requirements";
        return $this->generateContent($prompt);
    }

    public function analyzeTeamDynamics($teamData)
    {
        $prompt = "Analyze this team composition and suggest improvements for better collaboration:\n\n" . json_encode($teamData);
        return $this->generateContent($prompt);
    }
}

// Initialize Database
$db = new Database();
$googleAI = new GoogleAI();
?>