<?php
// db_config.php

date_default_timezone_set('Asia/Jakarta');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$host = "127.0.0.1"; 
$user = "user";      
$pass = "euShiV3UE0q0BU5dsynr";          
$db   = "ujiansmp4"; 


$conn = new mysqli($host, $user, $pass, $db);


if ($conn->connect_error) {
   
    header('Content-Type: application/json');
    http_response_code(500); 
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal: " . $conn->connect_error]);
    die();
}

$conn->set_charset("utf8mb4");


function sendResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    die();
}