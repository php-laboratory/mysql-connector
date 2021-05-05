![Last Commit][github-last-commit-image]
[![Issues][issues-image]][issues-url]

# MySQL Connector

Connector for MySQL databases.

<!-- TABLE OF CONTENTS -->
<details open="open">
  <summary>Table of contents</summary>
  <ol>
    <li>
      <a href="#built-with">Built with</a>
    </li>
    <li>
      <a href="#prerequisite">Prerequisite</a>
    </li>
    <li>
      <a href="#usage">Usage</a>
      <ul>
        <li><a href="#create-a-connection">Create a connection</a></li>
        <li><a href="#querying">Querying</a></li>
        <li><a href="#database-functions">Database functions</a></li>
        <li><a href="#table-functions">Table functions</a></li>
        <li><a href="#column-functions">Column functions</a></li>
      </ul>
    </li>
    <li>
      <a href="#contact">Contact</a>
    </li>
  </ol>
</details>

## Built with

- [MySQLi (MySQL Improved Extension)](https://www.php.net/manual/en/book.mysqli.php)

## Prerequisite

This connector does not control or handle user permissions within the database server. Therefore, amongst other errors (such as connection error), all functions will also throw an exception if the user has insufficient permissions.

## Usage

### Create a connection

```php
include_once "mysql-connector.php";

// Creates a connection with host, username and password
$mysql = new MysqlConnector("<address>:<port>", "<username>", "<password>");

// Creates a connection with host, username, password and database
$mysql = new MysqlConnector("<address>:<port>", "<username>", "<password>", "<database>");
```

### Querying

```php
// Query database
$result = $mysql->query("SELECT * from `table1`");
/* OUTPUT:
  [
    { col1: value11, col2: value12 ... },
    { col1: value21, col2: value22 ... },
    ...
  ]
*/

// Execute multiple queries to the database (rolls back all queries when fails)
$mysql->execute([
  "CREATE TABLE `table1` (
    `col1` INT(12)       NOT NULL AUTO_INCREMENT,
    `col2` VARCHAR(30)   NOT NULL,
    `col3` VARCHAR(50)   DEFAULT \"\",
    PRIMARY KEY (`col1`),
    UNIQUE KEY `key1` (`col2`)
  );",
  "INSERT INTO `table1` (`col2`, `col3`) VALUES ('a', '1'), ('b', '2');",
  "INSERT INTO `table1` (`col2`, `col3`) VALUES ('c', '3'), ('d', '4');"
]);
```

### Database functions

```php
// List database names
$result = $mysql->listDatabases();
/* OUTPUT:
  [ "database1", "database2", "database3", ... ]
*/

// Select or switch database
$mysql->selectDatabase("database1");

// Create a new database
$mysql->createDatabase("database1");

// Drop a database
$mysql->dropDatabase("database1");

// Copy a database
$mysql->copyDatabase("database1", "new_database");

// Remove all entries from all tables of a database
$mysql->clearDatabase("database1");
```

### Table functions

```php
// List table names
$result = $mysql->listTables("database1");
/* OUTPUT:
  [ "table1", "table2", "table3", ... ]
*/

// Create a new table
$mysql->createTable("database1", "table1");

// Drop a table
$mysql->dropTable("database1", "table1");

// Copy a table
$mysql->copyTable("database1", "table1", "database2", "new_table");

// Remove all entries from a table
$mysql->clearTable("table1");
```

### Column functions

```php
// List column names
$result = $mysql->listColumns("database1", "table1");
/* OUTPUT:
  [ "col1", "col2", "col3", ... ]
*/

// Create a new column
$mysql->createColumn("database1", "table1", "col1", "INT(12) NOT NULL AUTO_INCREMENT");

// Update a column
$mysql->updateColumn("database1", "table1", "col1", "INT(12) NOT NULL AUTO_INCREMENT");

// Drop a column
$mysql->dropColumn("database1", "table1", "col1");
```

## Contact

Wai Chung Wong - [Github](https://github.com/WaiChungWong) | [johnwongwwc@gmail.com](mailto:johnwongwwc@gmail.com)

[github-last-commit-image]: https://img.shields.io/github/last-commit/php-laboratory/mysql-connector?style=for-the-badge
[issues-image]: https://img.shields.io/github/issues/php-laboratory/mysql-connector.svg?style=for-the-badge
[issues-url]: https://github.com/php-laboratory/mysql-connector/issues
