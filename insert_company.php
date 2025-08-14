<?php
include 'condb.php';

// ตรวจสอบและทำความสะอาดข้อมูลที่รับเข้ามา
$com_name = mysqli_real_escape_string($conn, $_POST['com_name']);

// ใช้ Prepared Statement เพื่อป้องกัน SQL Injection
$sql = "INSERT INTO company_db (com_name) VALUES (?)";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    // ผูกค่าตัวแปรกับคำสั่ง SQL
    mysqli_stmt_bind_param($stmt, "s", $com_name);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        echo "<script>alert('บันทึกข้อมูลสำเร็จ');</script>";
        echo "<script>window.location='home.php';</script>";
    } else {
        echo "<script>alert('ไม่สามารถบันทึกข้อมูลได้: " . mysqli_stmt_error($stmt) . "');</script>";
    }

    // ปิด Statement
    mysqli_stmt_close($stmt);
} else {
    echo "<script>alert('เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL');</script>";
}

// ปิดการเชื่อมต่อ
mysqli_close($conn);
?>
