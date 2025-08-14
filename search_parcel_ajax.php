<?php
include 'condb.php';

// รับค่าจาก GET request และป้องกัน SQL Injection
$query = isset($_GET['query']) ? mysqli_real_escape_string($conn, $_GET['query']) : '';
$type = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : ''; // รับค่า status ที่เพิ่มเข้ามา

// เริ่มต้นสร้าง SQL query
$sql = "SELECT * FROM parcel_db WHERE name LIKE '%$query%'";

// เพิ่มเงื่อนไขการกรองตามประเภท
if (!empty($type)) {
    $sql .= " AND type_p = '$type'";
}

// เพิ่มเงื่อนไขการกรองตามสถานะ (Status)
if ($status === 'out') {
    $sql .= " AND qty = 0";
} elseif ($status === 'low') {
    $sql .= " AND qty < limit_value AND qty > 0";
} elseif ($status === 'normal') {
    $sql .= " AND qty >= limit_value";
}

$sql .= " ORDER BY name";

$result = mysqli_query($conn, $sql);
$output = '';

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // --- ส่วนของการกำหนดค่าสถานะและสไตล์ ---
        $status_text = 'ปกติ';
        $status_class = 'bg-success-subtle text-success-emphasis';
        $stock_highlight_class = ''; // คลาสสำหรับไฮไลต์ช่อง 'คงเหลือ'

        if ($row['qty'] == 0) {
            $status_text = 'หมดสต็อก';
            $status_class = 'bg-danger-subtle text-danger-emphasis';
            $stock_highlight_class = 'table-danger'; // ใช้คลาสของ Bootstrap
        } elseif ($row['qty'] < $row['limit_value']) {
            $status_text = 'พัสดุเหลือน้อย';
            $status_class = 'bg-warning-subtle text-warning-emphasis';
            $stock_highlight_class = 'table-warning'; // ใช้คลาสของ Bootstrap
        }

        // --- สร้าง HTML ของแถวที่แยกคอลัมน์แล้ว ---
        $output .= "<tr>
                        <td><strong>" . htmlspecialchars($row['name']) . "</strong></td>
                        <td>" . htmlspecialchars($row['type_p']) . "</td>
                        <td class='text-center fw-bold {$stock_highlight_class}'>" . $row['qty'] . "</td>
                        <td class='text-center'>" . $row['limit_value'] . "</td>
                        <td>" . htmlspecialchars($row['unit']) . "</td>
                        <td class='text-center'><span class='badge {$status_class} status-badge'>{$status_text}</span></td>
                        <td class='text-end'>" . number_format($row['price'], 2) . " ฿</td>
                        <td class='text-center'>
                            <a href='edit_parcel.php?id={$row['id']}' class='btn btn-sm btn-outline-primary' title='แก้ไข'><i class='bi bi-pencil-square'></i></a>
                            <a href='delete_parcel.php?id={$row['id']}' class='btn btn-sm btn-outline-danger' title='ลบ' onclick='return confirmDelete(event, this.href);'><i class='bi bi-trash'></i></a>
                        </td>
                    </tr>";
    }
} else {
    // ปรับ colspan ให้ตรงกับจำนวนคอลัมน์ใหม่ (8 คอลัมน์)
    $output = '<tr><td colspan="8" class="text-center p-4">ไม่พบข้อมูลพัสดุที่ตรงกับเงื่อนไข</td></tr>';
}

echo $output;

mysqli_close($conn);
?>