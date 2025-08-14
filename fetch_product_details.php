<?php
include 'condb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['productName'] ?? '';

    if ($productName) {
        // ดึงข้อมูลจากตาราง parcel_db โดยใช้ชื่อในคอลัมน์ name
        $sql = "SELECT unit, qty, price FROM parcel_db WHERE name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $productName);
        $stmt->execute();
        $result = $stmt->get_result();

        // กำหนด header ว่าตอบกลับเป็น JSON
        header('Content-Type: application/json');

        if ($row = $result->fetch_assoc()) {
            echo json_encode([
                'unit' => $row['unit'],
                'qty' => $row['qty'],
                'price' => $row['price']
            ]);
        } else {
            echo json_encode([
                'unit' => '',
                'qty' => '',
                'price' => ''
            ]);
        }
    } else {
        echo json_encode(['error' => 'No product name provided']);
    }

    $stmt->close();
    $conn->close();
}
?>
