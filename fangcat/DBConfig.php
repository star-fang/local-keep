<?php
$dsn = "mysql:dbname=$db;host=localhost";
$user = 'dynamic';
$password = $apiKey;


try {
    $conn = new PDO($dsn, $user, $password,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
} catch (PDOException $e) {
    die("{\"status\":\"fail\",\"message\":\"Connection failed: $e->getMessage()\"}");
}

  
 
?> 
