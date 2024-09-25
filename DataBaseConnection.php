<?php
// Database connection parameters
// Hostname of the database server
$db_hostname = "studdb.csc.liv.ac.uk";
// Name of the database
$db_database = "sgschuri";
// Database username
$db_username = "sgschuri";
// Database password
$db_password = "Manoj";
// Character set for the connection
$db_charset = "utf8mb4";

// Construct the Data Source Name (DSN) string
$dsn = "mysql:host=$db_hostname;dbname=$db_database;charset=$db_charset";

// PDO options for error handling and fetching
$opt = array(
// Throw exceptions for errors
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
// Fetch results as associative arrays
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
// Use real prepared statements
PDO::ATTR_EMULATE_PREPARES => false
);
try {
// Attempt to create a new PDO connection
  $pdo = new PDO($dsn,$db_username,$db_password,$opt);
}catch (PDOException $e) {
// If connection fails, output an error message
  echo 'Connection failed', $e ->getMessage();
}

?>