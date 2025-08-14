<?php
include 'condb.php';

// เปิดการแสดงผลข้อผิดพลาดสำหรับ Debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// รับข้อมูล JSON จาก request body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// ตรวจสอบ JSON error
if (json_last_error() !== JSON_ERROR_NONE) {
    $response = [
        "success" => false,
        "message" => "Invalid JSON data: " . json_last_error_msg()
    ];
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// ตรวจสอบว่ามีข้อมูลส่งมาหรือไม่
if (empty($data)) {
    $response = [
        "success" => false,
        "message" => "No data received"
    ];
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// เริ่ม transaction
mysqli_begin_transaction($conn);

try {
    foreach ($data as $row) {
        // ตรวจสอบ key ที่จำเป็น
        $requiredKeys = ['company', 'doc_no', 'productType', 'product', 'quantity', 'unit', 'remainingStock', 'unitPrice', 'totalPrice', 'datetime', 'createdBy'];
        if (count(array_intersect_key(array_flip($requiredKeys), $row)) !== count($requiredKeys)) {
            $missingKeys = array_diff($requiredKeys, array_keys($row));
            throw new Exception("Missing required data: " . implode(", ", $missingKeys));
        }
        
        $company = $row['company'];
        $doc_no = $row['doc_no'];
        $productType = $row['productType'];
        $productName = $row['product'];
        $quantity = $row['quantity'];
        $unit = $row['unit'];
        $total_price = $row['totalPrice'];
        $datetime = $row['datetime'];
        $created_by = $row['createdBy'];

        // อัปเดตจำนวนใน parcel_db
        $updateQtySQL = "UPDATE parcel_db SET qty = qty + ? WHERE name = ?";
        $stmt = mysqli_prepare($conn, $updateQtySQL);
        if (!$stmt) {
            throw new Exception("Error preparing update statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, "is", $quantity, $productName);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error updating quantity for product '{$productName}': " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);

        // บันทึกข้อมูลใน history_db
        $insertHistorySQL = "INSERT INTO history_db (h_name, h_type, h_qty, h_unit, h_price, h_sec, h_date, h_status, h_bill, h_master) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtHistory = mysqli_prepare($conn, $insertHistorySQL);
        if (!$stmtHistory) {
            throw new Exception("Error preparing insert statement: " . mysqli_error($conn));
        }
        $status = 'รับเข้า';
        mysqli_stmt_bind_param($stmtHistory, "ssisssssss", $productName, $productType, $quantity, $unit, $total_price, $company, $datetime, $status, $doc_no, $created_by);
        if (!mysqli_stmt_execute($stmtHistory)) {
            throw new Exception("Error inserting into history_db for product '{$productName}': " . mysqli_stmt_error($stmtHistory));
        }
        mysqli_stmt_close($stmtHistory);
    }

    // ถ้าทุกอย่างสำเร็จ ให้ commit transaction
    mysqli_commit($conn);
    $response = ["success" => true, "message" => "บันทึกข้อมูลสำเร็จ"];
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // ถ้ามีข้อผิดพลาด ให้ rollback transaction
    mysqli_rollback($conn);
    $response = ["success" => false, "message" => "Error: " . $e->getMessage()];
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} finally {
    mysqli_close($conn);
}
?>