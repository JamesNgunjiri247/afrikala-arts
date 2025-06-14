<?php
$host = "sql101.infinityfree.com";
$user = "if0_39216815";
$password = "dDWxroW0KwqH";
$dbname = "if0_39216815_afrikala_arts"; // Replace XXX with your actual database name

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>