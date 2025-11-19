<?php

$host = "localhost";        
$user = "root";           
$password = "";            
$database = "tanglaw_lms";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['loggedUser'])) {
    $_SESSION['loggedUser'] = [
        'id' => 1,
        'name' => 'Test Student',
        'grade_level' => 'Grade 10'
    ];
}
$loggedUser = $_SESSION['loggedUser'];
?>