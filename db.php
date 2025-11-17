
<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'pharmacy_management';
    private $username = 'root';
    private $password = '';
    private $port = '3306';  // Add this line
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            echo "<br>Please check: MySQL service, database name, username/password";
        }

        return $this->conn;
    }
}

$database = new Database();
$db = $database->getConnection();
?>
