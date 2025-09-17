<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // XAMPP/WAMP default
$dbname = 'lwb_management';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
