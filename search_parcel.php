<?php
include 'condb.php';

$query = isset($_GET['query']) ? mysqli_real_escape_string($conn, $_GET['query']) : '';
$type = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';

// สร้างเงื่อนไขสำหรับการค้นหาชื่อพัสดุและประเภทพัสดุ
$sql = "SELECT * FROM parcel_db WHERE name LIKE '%$query%'";

if ($type != '') {
    $sql .= " AND type_p = '$type'";
}

$result = mysqli_query($conn, $sql);

// สร้างตารางผลลัพธ์
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['name']}</td>
                <td>{$row['type_p']}</td>
                <td>{$row['price']}</td>
                <td>{$row['qty']}</td>
                <td>{$row['unit']}</td>
                <td>{$row['limit_value']}</td>
                <td><a href='edit_member.php?id={$row['id']}' class='btn btn-secondary'>Edit</a></td>
                <td><a href='delete_parcel.php?id={$row['id']}' class='btn btn-danger' onclick='Del(this.href); return false;'>Delete</a></td>
            </tr>";
    }
} else {
    echo "<tr><td colspan='9' class='text-center'>ไม่พบข้อมูล</td></tr>";
}

mysqli_close($conn);
?>
