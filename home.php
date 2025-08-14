<?php
// Always start sessions for potential future use (e.g., user authentication)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'condb.php'; // Include database connection

// Fetch distinct parcel types for the filter dropdown
$types = [];
$sqlType = "SELECT DISTINCT type_p FROM parcel_db ORDER BY type_p";
if ($resultType = mysqli_query($conn, $sqlType)) {
    while ($rowType = mysqli_fetch_assoc($resultType)) {
        $types[] = $rowType['type_p'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Inventory Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* Custom Styles */
        body {
            background-color: #eef2f5; /* Lighter, modern background */
            font-family: 'Sarabun', sans-serif; /* A modern Thai font */
        }
        .card {
            border: none; /* Remove default card border */
            border-radius: 0.75rem; /* Softer corners */
        }
        .card-header-custom {
            background-color: #1a237e; /* Darker, more professional blue */
            color: white;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
        }
        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.8rem 1rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
            color: #333;
        }
        .nav-link-custom:hover, .nav-link-custom.active {
            background-color: #e8eaf6;
            color: #1a237e;
            font-weight: 600;
        }
        .nav-link-custom i {
            font-size: 1.2rem;
        }
        .status-badge {
            padding: 0.4em 0.8em;
            font-size: 0.85rem;
            border-radius: 1rem;
        }
        .table > thead {
            background-color: #f1f3f5; /* Light grey for table header */
        }
        /* Use Bootstrap's contextual classes for stock status */
        .table-warning { /* low stock */
            --bs-table-bg: #fff3cd;
            --bs-table-color: #664d03;
        }
        .table-danger { /* out of stock */
            --bs-table-bg: #f8d7da;
            --bs-table-color: #842029;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="card shadow-sm mb-4">
                    <div class="card-header card-header-custom h5">
                        <i class="bi bi-compass"></i> เมนูนำทาง
                    </div>
                    <div class="card-body">
                        <h6 class="text-muted">การจัดการพัสดุ</h6>
                        <nav class="nav flex-column">
                            <a class="nav-link-custom" href="receive.php"><i class="bi bi-box-arrow-in-down"></i> รับเข้าพัสดุ</a>
                            <a class="nav-link-custom" href="disburse.php"><i class="bi bi-box-arrow-up"></i> เบิกจ่ายพัสดุ</a>
                        </nav>
                        <hr>
                        <h6 class="text-muted">รายงาน</h6>
                        <nav class="nav flex-column">
                            <a class="nav-link-custom" href="stock_card.php"><i class="bi bi-card-checklist"></i> Stock Card</a>
                            <a class="nav-link-custom" href="report_receive.php"><i class="bi bi-file-earmark-text"></i> รายงานรับเข้า</a>
                            <a class="nav-link-custom" href="report_disburse.php"><i class="bi bi-file-earmark-bar-graph"></i> รายงานเบิกจ่าย</a>
                        </nav>
                        <hr>
                        <h6 class="text-muted">ตั้งค่าระบบ</h6>
                        <nav class="nav flex-column">
                            <a class="nav-link-custom" href="addparcel.php"><i class="bi bi-plus-circle"></i> เพิ่มรายการพัสดุ</a>
                            <a class="nav-link-custom" href="addcompany.php"><i class="bi bi-building"></i> เพิ่มบริษัท</a>
                            <a class="nav-link-custom" href="addsection.php"><i class="bi bi-people"></i> เพิ่มหน่วยงาน</a>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="card shadow-sm">
                    <div class="card-header h5 bg-white d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-table me-2"></i> ภาพรวมพัสดุคงคลัง</span>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success-subtle text-success-emphasis me-2">ปกติ</span>
                            <span class="badge bg-warning-subtle text-warning-emphasis me-2">เหลือน้อย</span>
                            <span class="badge bg-danger-subtle text-danger-emphasis">หมดสต็อก</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-4 p-3 bg-light border rounded">
                             <div class="col-md-4">
                                <label for="searchParcel" class="form-label">ค้นหาชื่อพัสดุ</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" id="searchParcel" class="form-control" placeholder="พิมพ์เพื่อค้นหา..." onkeyup="filterData()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="typeDropdown" class="form-label">ประเภทพัสดุ</label>
                                <select id="typeDropdown" class="form-select" onchange="filterData()">
                                    <option value="">-- ทุกประเภท --</option>
                                    <?php foreach ($types as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="statusFilter" class="form-label">สถานะ</label>
                                <select id="statusFilter" class="form-select" onchange="filterData()">
                                    <option value="">-- ทุกสถานะ --</option>
                                    <option value="normal">ปกติ</option>
                                    <option value="low">พัสดุเหลือน้อย</option>
                                    <option value="out">หมดสต็อก</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ชื่อพัสดุ</th>
                                        <th>ประเภท</th>
                                        <th class="text-center">คงเหลือ</th>
                                        <th class="text-center">ขั้นต่ำ</th>
                                        <th>หน่วยนับ</th>
                                        <th class="text-center">สถานะ</th>
                                        <th class="text-end">ราคา/หน่วย</th>
                                        <th class="text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="parcelTable">
                                    <?php
                                    $sql = "SELECT * FROM parcel_db ORDER BY name";
                                    $result = mysqli_query($conn, $sql);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        // Determine status text, badge class, and row highlight class
                                        $status_text = 'ปกติ';
                                        $status_class = 'bg-success-subtle text-success-emphasis';
                                        $stock_highlight_class = '';

                                        if ($row['qty'] == 0) {
                                            $status_text = 'หมดสต็อก';
                                            $status_class = 'bg-danger-subtle text-danger-emphasis';
                                            $stock_highlight_class = 'table-danger';
                                        } elseif ($row['qty'] < $row['limit_value']) {
                                            $status_text = 'พัสดุเหลือน้อย';
                                            $status_class = 'bg-warning-subtle text-warning-emphasis';
                                            $stock_highlight_class = 'table-warning';
                                        }
                                        
                                        echo "<tr>
                                                <td><strong>" . htmlspecialchars($row['name']) . "</strong></td>
                                                <td>" . htmlspecialchars($row['type_p']) . "</td>
                                                <td class='text-center fw-bold {$stock_highlight_class}'>" . $row['qty'] . "</td>
                                                <td class='text-center'>" . $row['limit_value'] . "</td>
                                                <td>" . htmlspecialchars($row['unit']) . "</td>
                                                <td class='text-center'><span class='badge {$status_class} status-badge'>{$status_text}</span></td>
                                                <td class='text-end'>" . number_format($row['price'], 2) . " ฿</td>
                                                <td class='text-center'>
                                                    <a href='edit_parcel.php?id={$row['id']}' class='btn btn-sm btn-outline-primary'><i class='bi bi-pencil-square'></i></a>
                                                    <a href='delete_parcel.php?id={$row['id']}' class='btn btn-sm btn-outline-danger' onclick='return confirmDelete(event, this.href);'><i class='bi bi-trash'></i></a>
                                                </td>
                                            </tr>";
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
        // Debounce function to limit the rate at which a function gets called.
        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
        }

        // Main function to filter data via AJAX
        function filterData() {
            const query = document.getElementById("searchParcel").value;
            const type = document.getElementById("typeDropdown").value;
            const status = document.getElementById("statusFilter").value;
            
            // Show a loading indicator in the table body
            const tableBody = document.getElementById("parcelTable");
            tableBody.innerHTML = `<tr><td colspan="8" class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>`;

            const xhr = new XMLHttpRequest();
            // Pass all filter values to the AJAX script
            const url = `search_parcel_ajax.php?query=${encodeURIComponent(query)}&type=${encodeURIComponent(type)}&status=${encodeURIComponent(status)}`;
            
            xhr.open("GET", url, true);
            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                    tableBody.innerHTML = xhr.responseText;
                } else {
                    tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>`;
                }
            };
            xhr.onerror = function () {
                 tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">เกิดข้อผิดพลาดเครือข่าย</td></tr>`;
            };
            xhr.send();
        }

        // Improved delete confirmation
        function confirmDelete(event, url) {
            event.preventDefault(); // Prevent the link from navigating immediately
            if (confirm("คุณต้องการลบข้อมูลนี้ใช่หรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้")) {
                window.location.href = url;
            }
        }

        // Attach debounced event listener to the search input
        document.getElementById('searchParcel').addEventListener('keyup', debounce(filterData, 300));

    </script>
</body>
</html>