<?php
  class MysqlConnector {

    private $connection;
  
    /* Constructors */
    function __construct() {
      if (method_exists($this, $func = "__construct" . func_num_args())) {
        call_user_func_array(array($this, $func), func_get_args());
      }
    }

    function __construct3($host, $user, $password) {
      $this->connection = new mysqli($host, $user, $password);

      if ($this->connection->connect_errno) {
        throw new Exception("Connection failed: " . $this->connection->connect_error);
      }
    }

    function __construct4($host, $user, $password, $database) {
      $this->connection = new mysqli($host, $user, $password, $database);

      if ($this->connection->connect_errno) {
        throw new Exception("Connection failed: " . $this->connection->connect_error);
      }
    }
    
    /* Destructor */
    function __destruct() {
      $this->connection->close();
    }

    /* MySQLi-based methods */
    function selectDatabase($database) {
      if (!$this->connection->select_db($database)) {
        throw new Exception("Select database failed");
      }
    }

    function query($statement) {
      $result = $this->connection->query($statement, MYSQLI_USE_RESULT);
  
      if (!$result) {
        throw new Exception("Error in query " . $statement . ": " . $this->connection->error);
      }

      if (is_bool($result)) {
        return $result;
      }
      
      else {
        $resultArray = array();
  
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
          array_push($resultArray, $row);
        }

        $result->free();
  
        return $resultArray;
      }
    }

    function execute($statements) {
      $this->connection->autocommit(FALSE);
  
      $error = "";
  
      foreach ($statements as $statement) {
        $result = $this->connection->query($statement);

        if (!$result) {
          $error = "$error \nError in query " . $statement . ": " . $this->connection->error;
        }
      }
  
      if (empty($error)) {
        $this->connection->commit();
        $this->connection->autocommit(TRUE);
      } else {
        $this->connection->rollback();
        $this->connection->autocommit(TRUE);
  
        throw new Exception($error);
      }
    }

    /* Database-based methods */
    function listDatabases() {
      $results = $this->query("SHOW DATABASES");

      $results = array_map(function ($db) { return $db["Database"]; }, $results);

      return array_filter($results, function ($db) {
        return 
          $db !== "information_schema" && 
          $db !== "performance_schema" && 
          $db !== "mysql" && 
          $db !== "sys";
      });
    }

    function createDatabase($database) {
      return $this->query("CREATE DATABASE `$database`");
    }

    function dropDatabase($database) {
      return $this->query("DROP DATABASE `$database`");
    }

    function copyDatabase($from, $to) {
      $tables = $this->listTables($from);

      $queries = array();

      array_push($queries, "CREATE DATABASE `$to`");
    
      foreach ($tables as $table) {
        array_push($queries, "CREATE TABLE `$to`.`$table` LIKE `$from`.`$table`");
        array_push($queries, "INSERT `$to`.`$table` SELECT * FROM `$from`.`$table`");
      }
  
      return $this->execute($queries);
    }

    function clearDatabase($database) {
      $tables = $this->listTables($database);

      $queries = array();
  
      foreach ($tables as $table) {
        array_push($queries, "TRUNCATE TABLE `$table`");
      }
  
      return $this->execute($queries);
    }

    /* Table-based methods */
    function listTables($database) {
      $this->selectDatabase($database);
      
      $results = $this->query("SHOW TABLES");

      return array_map(function ($table) use ($database) {
        return $table["Tables_in_$database"];
      }, $results);
    }

    function createTable($database, $table, $columns) {
      $this->selectDatabase($database);
  
      $columnString = join(", ", $columns);
      
      return $this->query("CREATE TABLE `$table` ($columnString)");
    }

    function dropTable($database, $table) {
      $this->selectDatabase($database);
      
      return $this->query("DROP TABLE `$table`");
    }

    function copyTable($fromDatabase, $fromTable, $toDatabase, $toTable) {
      $queries = array();
    
      array_push($queries, "CREATE TABLE `$toDatabase`.`$toTable` LIKE `$fromDatabase`.`$fromTable`");
      array_push($queries, "INSERT `$toDatabase`.`$toTable` SELECT * FROM `$fromDatabase`.`$fromTable`");
  
      return $this->execute($queries);
    }
  
    function clearTable($database, $table) {
      $this->selectDatabase($database);

      return $this->query("TRUNCATE TABLE `$table`");
    }

    /* Column-based methods */
    function listColumns($database, $table) {
      $this->selectDatabase($database);

      $results = $this->query("DESCRIBE `$table`");

      return array_map(function ($column) {
        return $column["Field"];
      }, $results);
    }

    function createColumn($database, $table, $column, $attributes) {
      $this->selectDatabase($database);
      
      return $this->query("ALTER TABLE `$table` ADD $column $attributes");
    }
  
    function updateColumn($database, $table, $column, $attributes) {
      $this->selectDatabase($database);

      return $this->query("ALTER TABLE `$table` MODIFY COLUMN $column, $attributes");
    }
  
    function dropColumn($database, $table, $column) {
      $this->selectDatabase($database);

      return $this->query("ALTER TABLE `$table` DROP COLUMN $column");
    }
  }
?>