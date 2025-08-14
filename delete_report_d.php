<?php
include 'condb.php';

if (isset($_GET['h_id'])) {
    $h_id = intval($_GET['h_id']);

    // ดึงข้อมูลรายการที่ต้องการลบ
    $sqlSelect = "SELECT h_name, h_qty FROM history_db WHERE h_id = ?";
    $stmtSelect = $conn->prepare($sqlSelect);
    $stmtSelect->bind_param("i", $h_id);
    $stmtSelect->execute();
    $resultSelect = $stmtSelect->get_result();

    if ($resultSelect->num_rows > 0) {
        $row = $resultSelect->fetch_assoc();
        $h_name = $row['h_name'];
        $h_qty = $row['h_qty'];

        // อัปเดตจำนวนในตาราง Parcel_db
        $sqlUpdate = "UPDATE Parcel_db SET qty = qty + ? WHERE name = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("is", $h_qty, $h_name);
        $stmtUpdate->execute();

        // ลบรายการใน history_db
        $sqlDelete = "DELETE FROM history_db WHERE h_id = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $h_id);
        $stmtDelete->execute();

        if ($stmtDelete->affected_rows > 0) {
            echo "<script>
                    alert('ลบรายการสำเร็จ');
                    window.location.href = 'home.php';
                  </script>";
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการลบรายการ');</script>";
        }
    } else {
        echo "<script>alert('ไม่พบข้อมูลรายการนี้');</script>";
    }
} else {
    echo "<script>alert('ไม่มี ID รายการที่ระบุ');</script>";
}

$conn->close();
?>
