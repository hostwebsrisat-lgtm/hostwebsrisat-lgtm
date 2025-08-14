<?php
// Set header to return JSON response
header('Content-Type: application/json; charset=utf-8');

// 1. เชื่อมต่อฐานข้อมูล
include 'condb.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit();
}

// 2. รับข้อมูล JSON ที่ถูกส่งมา
$json_data = file_get_contents('php://input');
$data_to_save = json_decode($json_data, true);

// ตรวจสอบว่ามีข้อมูลส่งมาหรือไม่
if (empty($data_to_save)) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit();
}

// 3. เริ่ม Transaction เพียงครั้งเดียว ก่อนเริ่ม Loop
// เพื่อให้แน่ใจว่าข้อมูลทั้งหมดจะถูกบันทึกพร้อมกัน (All or Nothing)
mysqli_begin_transaction($conn);

try {
    // 4. เตรียม SQL Statements ไว้ล่วงหน้า (ใช้ชื่อตารางและคอลัมน์จากโค้ดเดิมของคุณ)
    $stmt_update = mysqli_prepare($conn, 
        "UPDATE parcel_db SET qty = qty - ? WHERE name = ?"
    );

    $stmt_insert = mysqli_prepare($conn, 
        "INSERT INTO history_db (h_name, h_type, h_qty, h_unit, h_price, h_sec, h_date, h_status, h_bill, h_master) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    // 5. วนลูปเพื่อประมวลผลข้อมูลทุกรายการ
    foreach ($data_to_save as $row) {
        // --- ส่วนของการ UPDATE สต็อก ---
        mysqli_stmt_bind_param($stmt_update, "is", $row['quantity'], $row['product']);
        if (!mysqli_stmt_execute($stmt_update)) {
            throw new Exception("Error updating stock for " . $row['product'] . ": " . mysqli_stmt_error($stmt_update));
        }

        // --- ส่วนของการ INSERT ประวัติ ---
        $status = 'จ่ายออก';
        mysqli_stmt_bind_param($stmt_insert, "ssisdsssss", 
            $row['product'], 
            $row['productType'], 
            $row['quantity'], 
            $row['unit'], 
            $row['totalPrice'], 
            $row['company'], 
            $row['datetime'], 
            $status, 
            $row['doc_no'], 
            $row['createdBy']
        );
        if (!mysqli_stmt_execute($stmt_insert)) {
            throw new Exception("Error inserting history for " . $row['product'] . ": " . mysqli_stmt_error($stmt_insert));
        }
    }

    // 6. หากทุกอย่างสำเร็จ (ไม่เกิด Exception) ให้ Commit Transaction
    mysqli_commit($conn);
    $response = ['success' => true, 'message' => 'บันทึกข้อมูลสำเร็จ'];

} catch (Exception $e) {
    // 7. หากเกิดข้อผิดพลาดใดๆ ใน Loop ให้ Rollback (ยกเลิกทุกอย่างที่ทำไป)
    mysqli_rollback($conn);
    $response = ['success' => false, 'message' => $e->getMessage()];

} finally {
    // 8. ปิด Statements และ Connection
    if (isset($stmt_update)) mysqli_stmt_close($stmt_update);
    if (isset($stmt_insert)) mysqli_stmt_close($stmt_insert);
    mysqli_close($conn);
}

// 9. ส่ง Response กลับไปหา Frontend เพียงครั้งเดียว
echo json_encode($response, JSON_UNESCAPED_UNICODE);

?>