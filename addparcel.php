<?php
include 'condb.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มรายการพัสดุ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-box-seam"></i> เพิ่มรายการพัสดุ</h4>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="insert_parcel.php">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">ชื่อพัสดุ</label>
                                        <input type="text" id="name" name="name" class="form-control" placeholder="ระบุชื่อพัสดุ" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="type_p" class="form-label">ประเภทพัสดุ</label>
                                        <input list="typeOptions" id="type_p" name="type_p" class="form-control" placeholder="เลือกหรือพิมพ์ประเภท" required>
                                        <datalist id="typeOptions">
                                            <?php
                                            // ดึงข้อมูลประเภทพัสดุมาแสดงใน datalist
                                            $sqlType = "SELECT DISTINCT type_p FROM parcel_db";
                                            $resultType = mysqli_query($conn, $sqlType);
                                            while ($rowType = mysqli_fetch_assoc($resultType)) {
                                                echo '<option value="' . htmlspecialchars($rowType["type_p"]) . '">';
                                            }
                                            ?>
                                        </datalist>
                                    </div>
                                    <div class="mb-3">
                                        <label for="price" class="form-label">ราคาต่อหน่วย</label>
                                        <input type="number" step="0.01" id="price" name="price" class="form-control" placeholder="ระบุราคา" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="qty" class="form-label">จำนวน</label>
                                        <input type="number" id="qty" name="qty" class="form-control" placeholder="ระบุจำนวน" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="unit" class="form-label">หน่วยนับ</label>
                                        <input type="text" id="unit" name="unit" class="form-control" placeholder="เช่น ชิ้น, กล่อง, อัน" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="limit_value" class="form-label">แจ้งเตือนเมื่อต่ำกว่า (Minimum Stock)</label>
                                        <input type="number" id="limit_value" name="limit_value" class="form-control" placeholder="ระบุจำนวนขั้นต่ำ" required>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="home.php" class="btn btn-secondary me-md-2"><i class="bi bi-x-circle"></i> ยกเลิก</a>
                                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>