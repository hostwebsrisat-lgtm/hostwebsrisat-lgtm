<?php
include 'condb.php';

$type_p = $_POST['type_p'];

// ดึงรายการพัสดุที่ตรงกับประเภทพัสดุที่เลือก
$sql = "SELECT name FROM parcel_db WHERE type_p = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $type_p);
$stmt->execute();
$result = $stmt->get_result();

$options = '<option value="">-- เลือกรายการที่รับเข้า --</option>';
while ($row = $result->fetch_assoc()) {
    $options .= '<option value="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</option>';
}

echo $options;

$stmt->close();
$conn->close();
?>

