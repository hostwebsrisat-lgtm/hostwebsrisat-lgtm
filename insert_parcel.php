<?php
include 'condb.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $type_p = $_POST['type_p'] ?? '';
    $price = $_POST['price'] ?? 0;
    $qty = $_POST['qty'] ?? 0;
    $unit = $_POST['unit'] ?? '';
    $limit_value = $_POST['limit_value'] ?? 0;

    if ($name && $type_p && $price) {
        $sql = "INSERT INTO parcel_db (name, type_p, price, qty, unit, limit_value) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssdiis", $name, $type_p, $price, $qty, $unit, $limit_value);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('บันทึกข้อมูลสำเร็จ');</script>";
            echo "<script>window.location='home.php';</script>";
        } else {
            echo "<script>alert('ไม่สามารถบันทึกข้อมูลได้: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        echo "<script>alert('กรุณากรอกข้อมูลให้ครบถ้วน');</script>";
    }
}
mysqli_close($conn);
?>
