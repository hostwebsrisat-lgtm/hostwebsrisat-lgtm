<?php
include 'condb.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มข้อมูลหน่วยงาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 960px;
        }
        .card-header {
            background-color: #198754; /* Green color for section */
            color: white;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-people-fill"></i> เพิ่มข้อมูลหน่วยงาน</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="insert_sec.php">
                            <div class="mb-3">
                                <label for="sec_name" class="form-label">ชื่อหน่วยงาน</label>
                                <input type="text" id="sec_name" name="sec_name" class="form-control" placeholder="กรอกชื่อหน่วยงาน" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
                                <a href="home.php" class="btn btn-outline-secondary">กลับหน้าหลัก</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header">
                         <h4 class="mb-0"><i class="bi bi-list-ul"></i> รายชื่อหน่วยงาน</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ชื่อหน่วยงาน</th>
                                        <th scope="col" class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM section_db ORDER BY sec_name ASC";
                                    $result = mysqli_query($conn, $sql);

                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $id = isset($row['sec_id']) ? $row['sec_id'] : '';
                                            $sec_name = isset($row['sec_name']) ? htmlspecialchars($row['sec_name']) : 'ไม่มีชื่อหน่วยงาน';

                                            echo "<tr>
                                                    <td>{$sec_name}</td>
                                                    <td class='text-center'>
                                                        <a href='delete_sec.php?id={$id}' class='btn btn-danger btn-sm' onclick='return Del(this.href); return false;'>
                                                            <i class='bi bi-trash'></i>
                                                        </a>
                                                    </td>
                                                </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='2' class='text-center text-muted'>ยังไม่มีข้อมูลหน่วยงาน</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function Del(url) {
        // Confirmation dialog before deleting
        if (confirm('คุณต้องการลบข้อมูลนี้ใช่หรือไม่?')) {
            window.location.href = url;
        }
    }
    </script>
</body>
</html>