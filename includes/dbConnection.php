<?php
class dbConnection{
    private $connection;
    private $db_type;
    private $db_host;
    private $db_port;
    private $db_user;
    private $db_pass;
    private $db_name;

    public function __construct($db_type, $db_host, $db_port, $db_user, $db_pass, $db_name){
        $this->db_type = $db_type;
        $this->db_port = $db_port;
        $this->db_host = $db_host;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->db_name = $db_name;
        
        $this->connect();
    }

    // Establishes a connection based on the type (PDO or MySQLi)
    private function connect(){
        switch($this->db_type){
            case 'PDO':
                try {
                    $dsn = "mysql:host={$this->db_host};port={$this->db_port};dbname={$this->db_name}";
                    $this->connection = new PDO($dsn, $this->db_user, $this->db_pass);
                    $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (PDOException $e) {
                    throw new Exception("Connection failed: " . $e->getMessage());
                }
                break;
            
                
                case 'MySQLi':
                    $this->connection = new mysqli($this->db_host, $this->db_user, $this->db_pass, $this->db_name, $this->db_port);
                    if ($this->connection->connect_error) {
                        throw new Exception("Connection failed: " . $this->connection->connect_error);
                    }
                    break;
                
        }
    }

    // Escapes values based on connection type
    public function escape_values($posted_values): string {
        switch ($this->db_type) {
            case 'PDO':
                return addslashes($posted_values);
            case 'MySQLi':
                return $this->connection->real_escape_string($posted_values);
        }
    }

    // Counts results from a SQL query
    public function count_results($sql) {
        switch ($this->db_type) {
            case 'PDO':
                $stmt = $this->connection->prepare($sql);
                $stmt->execute();
                return $stmt->rowCount();
            case 'MySQLi':
                $result = $this->connection->query($sql);
                return $result->num_rows;
        }
    }

    // Inserts data securely into the database using prepared statements
    public function insert($table, $data) {
        ksort($data);
        $fieldNames = implode('`, `', array_keys($data));
        $fieldValues = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO $table (`$fieldNames`) VALUES ($fieldValues)";
        $stmt = $this->connection->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        return $stmt->execute();
    }

    // Retrieves a single result from a query
    public function select($sql) {
        switch ($this->db_type) {
            case 'PDO':
                $stmt = $this->connection->prepare($sql);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            case 'MySQLi':
                $result = $this->connection->query($sql);
                return $result->fetch_assoc();
        }
    }

    // Retrieves multiple results in a loop
    public function select_while($sql) {
        switch ($this->db_type) {
            case 'PDO':
                $stmt = $this->connection->prepare($sql);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            case 'MySQLi':
                $result = $this->connection->query($sql);
                $res = [];
                while ($row = $result->fetch_assoc()) {
                    $res[] = $row;
                }
                return $res;
        }
    }

    // Updates data in the database securely
    public function update($table, $data, $where) {
        ksort($data);
        $fieldDetails = implode('=?, ', array_keys($data)) . '=?';

        $sql = "UPDATE $table SET $fieldDetails WHERE $where";
        $stmt = $this->connection->prepare($sql);

        $values = array_values($data);
        return $stmt->execute($values);
    }

    // Deletes data from the database
    public function delete($table, $where) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->extracted($sql);
    }

    // Truncates a table
    public function truncate($table) {
        $sql = "TRUNCATE TABLE $table";
        return $this->extracted($sql);
    }

    // Gets the ID of the last inserted row
    public function last_id() {
        switch ($this->db_type) {
            case 'PDO':
                return $this->connection->lastInsertId();
            case 'MySQLi':
                return $this->connection->insert_id;
        }
    }

    // Executes a query (for both insert, update, delete, etc.)
    public function extracted(string $sql) {
        switch ($this->db_type) {
            case 'PDO':
                try {
                    $stmt = $this->connection->prepare($sql);
                    return $stmt->execute();
                } catch (PDOException $e) {
                    throw new Exception($e->getMessage());
                }
            case 'MySQLi':
                if ($this->connection->query($sql) === true) {
                    return true;
                } else {
                    throw new Exception("Error: " . $this->connection->error);
                }
        }
    }

    // Closes the connection
    public function close() {
        switch ($this->db_type) {
            case 'PDO':
                $this->connection = null;
                break;
            case 'MySQLi':
                $this->connection->close();
                break;
        }
    }
}
