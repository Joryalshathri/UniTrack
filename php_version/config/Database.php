<?php
/**
 * Database Connection Class for PostgreSQL
 */

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $connectionString = "host=" . DB_HOST . 
                          " port=" . DB_PORT . 
                          " dbname=" . DB_NAME . 
                          " user=" . DB_USER . 
                          " password=" . DB_PASSWORD;
        
        $this->connection = pg_connect($connectionString);
        
        if (!$this->connection) {
            die("Database Connection Failed: " . pg_last_error());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        if (empty($params)) {
            $result = pg_query($this->connection, $sql);
        } else {
            $result = pg_query_params($this->connection, $sql, $params);
        }

        if (!$result) {
            throw new Exception("Query Error: " . pg_last_error($this->connection));
        }

        return $result;
    }

    public function fetch($sql, $params = []) {
        $result = $this->query($sql, $params);
        return pg_fetch_assoc($result);
    }

    public function fetchAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    public function execute($sql, $params = []) {
        $result = $this->query($sql, $params);
        return pg_affected_rows($result);
    }

    public function lastInsertId($table, $column = 'id') {
        $sql = "SELECT CURRVAL(pg_get_serial_sequence('$table', '$column')) as id";
        $result = $this->fetch($sql);
        return $result['id'] ?? null;
    }

    public function countRows($sql, $params = []) {
        $result = $this->query($sql, $params);
        return pg_num_rows($result);
    }

    public function closeConnection() {
        if ($this->connection) {
            pg_close($this->connection);
        }
    }

    public function __destruct() {
        $this->closeConnection();
    }
}
?>
