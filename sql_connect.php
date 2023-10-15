<?php

// Note, it is possible to prepare multiple queries before execute, 
// see https://stackoverflow.com/a/11635679

if (!isset($GLOBALS["CONFIG"]))
    require_once "config/sql_config.php";
require_once "logs/logger.php";

class SqlConnection {
    private $conn;
    private $logger;

    // Creates a new mysqli object. Use null as parameter when 
    // you want to create a database-independent connection:
    function __construct($db=null) {
        $this->logger = new Logger();
        $this->conn = new mysqli($GLOBALS["CONFIG"]["SQL_SERVER"], $GLOBALS["CONFIG"]["SQL_USERNAME"], $GLOBALS["CONFIG"]["SQL_PASSWORD"], ($db ? $db : null), $GLOBALS["CONFIG"]["SQL_PORT"]);
        if ($this->conn->connect_error) {
            $this->logger->error("SQL connection failed.", ["db" => $db, "error" => $this->conn->connect_error]);
            exit("SQL connection failed.");
        }
        $this->logger->info("SQL connection created.");
        $this->conn->set_charset("utf8");
    }

    function get_connection() {
        return $this->conn;
    }

    // Use multi_query to execute multiple queries with one call
    public function multi_query($query) {
        try {
            if (!$this->conn->multi_query($query)) {
                $this->logger->error("SQL multi_query failed.", ["query" => $query, "msg" => $this->conn->error]);
                return ["success" => false, "value" => $this->conn->error];
            }
            $results = [];
            do {
                if ($result = $this->conn->store_result())
                    $results[] = $result;
            } while ($this->conn->next_result());
            $this->logger->debug("SQL multi_query success.", ["query" => $query]);
            return ["success" => true, "value" => $results];  // success
        } catch (mysqli_sql_exception $e) {
            $this->logger->error("SQL multi_query caused mysqli_sql_exception.", ["query" => $query, "exception" => $e->getMessage()]);
            return ["success" => false, "value" => $e->getMessage()];
        } catch (ArgumentCountError $e) {
            $this->logger->error("SQL multi_query caused ArgumentCountError.", ["query" => $query, "exception" => $e->getMessage()]);
            return ["success" => false, "value" => $e->getMessage()];
        }
    }

    // Method for safe parameter substitution into SQL statements using 
    // prepared statements and placeholders. 
    // Returns array with keys "success" and "value". Success is true iff no problems occured, 
    // value is return value of statement on success or error message on failure.
    public function substitute_and_execute($query) {
        $params = array_slice(func_get_args(), 1);
        $n = count($params);
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                $this->logger->error("SQL query failed to prepare.", ["query" => $query]);
                return ["success" => false, "value" => "Statement failed to prepare."];
            }
            if ($n > 0) {
                $refs = [];
                foreach ($params as $key => $value) 
                    $refs[$key] = &$params[$key];
                $s_params = array_merge(array(str_repeat('s', $n)), $refs);
                call_user_func_array([$stmt, 'bind_param'], $s_params);
            }
            if (!$stmt->execute()) {
                $this->logger->error("SQL query failed to execute.", compact("query"));
                return ["success" => false, "value" => "Statement failed to execute."];
            }
            $result = $stmt->get_result();
            $affected_rows = $this->conn->affected_rows;
            $stmt->close();
            $this->logger->debug("SQL query success.", ["query" => $query, "affected_rows" => $affected_rows, "params" => var_export($params, true)]);
            return ["success" => true, "value" => $result];  // success
        } catch (mysqli_sql_exception $e) {
            $this->logger->error("SQL query caused mysqli_sql_exception.", ["query" => $query, "exception" => $e->getMessage()]);
            return ["success" => false, "value" => $e->getMessage()];
        } catch (ArgumentCountError $e) {
            $this->logger->error("SQL query caused ArgumentCountError.", ["query" => $query, "exception" => $e->getMessage()]);
            return ["success" => false, "value" => $e->getMessage()];
        }
    }

    // Used to extract all possible values of ENUM or SET from a field
    public function extract_range($table, $field) {
        // cannot use substitution with table or field names so just use plain old:
        $stmt = "SHOW COLUMNS FROM $table WHERE Field='$field'";
        $result = $this->substitute_and_execute($stmt);
        $range = [];
        if ($result['success']) { 
            $cleaner_pattern = '/\'([^\']*)\'/';   // matches content that starts and ends with '
            $row = $result['value']->fetch_assoc();
            $dirty_range = explode(",", $row['Type']);
            foreach ($dirty_range as $dirty) {
                if (preg_match($cleaner_pattern, $dirty, $matches)) 
                    $range[] = $matches[1];  // [1] for only stuff inside first capture group ()
            }
        }
        return $range;
    }
}

?>