<?php
$db_host = "localhost";
$username = "root";
$password = "";
$dbname = "petsocialnetwork";

// Create connection
$conn = new mysqli($db_host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>