<?php
include 'condb.php';
$id = $_POST['id_p'];
$type_p = $_POST['type_p'];
$name = $_POST['name'];
$unit = $_POST['unit'];
$price = $_POST['price'];
$qty = $_POST['qty'];
$limit_value = $_POST['limit_value'];

$sql = "UPDATE parcel_db SET name='$name', type_p='$type_p', price='$price', qty='$qty', unit='$unit', limit_value='$limit_value' WHERE id='$id'";

$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<script>alert('บันทึกข้อมูลสำเร็จ');</script>";
    echo "<script>window.location='home.php';</script>";
} else {
    echo "<script>alert('ไม่สามารถบันทึกข้อมูลได้: " . mysqli_error($conn) . "');</script>";
}

mysqli_close($conn);

?>
