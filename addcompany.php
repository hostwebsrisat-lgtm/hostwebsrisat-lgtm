<?php
include 'condb.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มข้อมูลบริษัท</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* Optional: Custom styles for better spacing */
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 960px;
        }
        .card-header {
            background-color: #0d6efd;
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
                        <h4 class="mb-0"><i class="bi bi-building-add"></i> เพิ่มข้อมูลบริษัท</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="insert_company.php">
                            <div class="mb-3">
                                <label for="com_name" class="form-label">ชื่อบริษัท</label>
                                <input type="text" id="com_name" name="com_name" class="form-control" placeholder="กรอกชื่อบริษัท" required>
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
                         <h4 class="mb-0"><i class="bi bi-list-ul"></i> รายชื่อบริษัท</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ชื่อบริษัท</th>
                                        <th scope="col" class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM company_db ORDER BY com_name ASC";
                                    $result = mysqli_query($conn, $sql);

                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $id = isset($row['com_id']) ? $row['com_id'] : '';
                                            $com_name = isset($row['com_name']) ? htmlspecialchars($row['com_name']) : 'ไม่มีชื่อบริษัท';

                                            echo "<tr>
                                                    <td>{$com_name}</td>
                                                    <td class='text-center'>
                                                        <a href='delete_com.php?id={$id}' class='btn btn-danger btn-sm' onclick='return confirm(\"คุณต้องการลบข้อมูลนี้ใช่หรือไม่?\");'>
                                                            <i class='bi bi-trash'></i>
                                                        </a>
                                                    </td>
                                                </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='2' class='text-center text-muted'>ยังไม่มีข้อมูลบริษัท</td></tr>";
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
</body>
</html>