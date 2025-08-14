<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "warehouse_db";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
//echo "Connected successfully";
// ตั้งค่าเชื่อมต่อให้รองรับ UTF-8
$conn->set_charset("utf8mb4");

?>