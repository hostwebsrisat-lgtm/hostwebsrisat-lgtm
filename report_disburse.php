<?php
include 'condb.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานเบิกจ่ายพัสดุ</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* Custom Styles for a modern look */
        body {
            background-color: #f8f9fa;
        }
        .header-card {
            background: linear-gradient(to right, #0d6efd, #0dcaf0);
            color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .filter-card {
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .form-label i {
            margin-right: 8px;
        }
        .table-hover tbody tr:hover {
            background-color: #e9ecef;
        }
        #no-results {
            display: none; /* ซ่อนไว้ก่อนเป็นค่าเริ่มต้น */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="header-card text-center p-4 mb-4">
            <h1 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> รายงานเบิกจ่ายพัสดุ</h1>
        </div>

        <div class="card filter-card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="fas fa-filter"></i> ตัวกรองข้อมูล</h5>
                <div class="row g-3">
                    <div class="col-lg-4 col-md-6">
                        <label for="searchParcel" class="form-label"><i class="fas fa-search"></i>ค้นหาชื่อพัสดุ:</label>
                        <input type="text" id="searchParcel" class="form-control" placeholder="พิมพ์ชื่อพัสดุ..." onkeyup="filterTable()">
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <label for="ComDropdown" class="form-label"><i class="fas fa-building"></i>หน่วยงาน:</label>
                        <select id="ComDropdown" class="form-select" onchange="filterTable()">
                            <option value="">-- ทั้งหมด --</option>
                            <?php
                            $sqlCom = "SELECT DISTINCT h_sec FROM history_db WHERE h_status = 'จ่ายออก'";
                            $resultCom = mysqli_query($conn, $sqlCom);
                            while ($rowCom = mysqli_fetch_assoc($resultCom)) {
                                echo '<option value="' . htmlspecialchars($rowCom["h_sec"]) . '">' . htmlspecialchars($rowCom["h_sec"]) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <label for="typeDropdown" class="form-label"><i class="fas fa-tags"></i>ประเภทพัสดุ:</label>
                        <select id="typeDropdown" class="form-select" onchange="filterTable()">
                            <option value="">-- ทั้งหมด --</option>
                            <?php
                            $sqlType = "SELECT DISTINCT h_type FROM history_db WHERE h_status = 'จ่ายออก'";
                            $resultType = mysqli_query($conn, $sqlType);
                            while ($rowType = mysqli_fetch_assoc($resultType)) {
                                echo '<option value="' . htmlspecialchars($rowType["h_type"]) . '">' . htmlspecialchars($rowType["h_type"]) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <label for="masterDropdown" class="form-label"><i class="fas fa-user-edit"></i>ผู้บันทึก:</label>
                        <select id="masterDropdown" class="form-select" onchange="filterTable()">
                            <option value="">-- ทั้งหมด --</option>
                            <?php
                            $sqlMaster = "SELECT DISTINCT h_master FROM history_db WHERE h_status = 'จ่ายออก'";
                            $resultMaster = mysqli_query($conn, $sqlMaster);
                            while ($rowMaster = mysqli_fetch_assoc($resultMaster)) {
                                echo '<option value="' . htmlspecialchars($rowMaster["h_master"]) . '">' . htmlspecialchars($rowMaster["h_master"]) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <label for="startDate" class="form-label"><i class="fas fa-calendar-alt"></i>วันที่เริ่มต้น:</label>
                        <input type="date" id="startDate" class="form-control" onchange="filterTable()">
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <label for="endDate" class="form-label"><i class="fas fa-calendar-check"></i>วันที่สิ้นสุด:</label>
                        <input type="date" id="endDate" class="form-control" onchange="filterTable()">
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>ชื่อพัสดุ</th>
                        <th>ประเภท</th>
                        <th>จำนวน</th>
                        <th>หน่วยนับ</th>
                        <th>ราคารวม (บาท)</th>
                        <th>หน่วยงาน</th>
                        <th>วันที่</th>
                        <th>สถานะ</th>
                        <th>เลขที่เอกสาร</th>
                        <th>ผู้บันทึก</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody id="historyTable">
                <?php
                    $sql = "SELECT * FROM history_db WHERE h_status = 'จ่ายออก' ORDER BY h_id DESC";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['h_name']) . "</td>
                            <td class='text-center'>" . htmlspecialchars($row['h_type']) . "</td>
                            <td class='text-center'>" . htmlspecialchars($row['h_qty']) . "</td>
                            <td class='text-center'>" . htmlspecialchars($row['h_unit']) . "</td>
                            <td class='text-end'>" . number_format($row['h_price'], 2) . "</td>
                            <td>" . htmlspecialchars($row['h_sec']) . "</td>
                            <td class='text-center'>" . date("d/m/Y", strtotime($row['h_date'])) . "</td>
                            <td class='text-center'><span class='badge bg-warning text-dark'>" . htmlspecialchars($row['h_status']) . "</span></td>
                            <td>" . htmlspecialchars($row['h_bill']) . "</td>
                            <td>" . htmlspecialchars($row['h_master']) . "</td>
                            <td class='text-center'>
                                <a href='delete_report_d.php?h_id=" . htmlspecialchars($row['h_id']) . "' class='btn btn-outline-danger btn-sm'
                                onclick='return confirm(\"คุณต้องการลบข้อมูลนี้หรือไม่?\");' title='ลบข้อมูล'>
                                <i class='fas fa-trash-alt'></i>
                                </a>
                            </td>
                        </tr>";
                    }
                    mysqli_close($conn);
                ?>
                </tbody>
            </table>
            <div id="no-results" class="text-center p-5">
                <i class="fas fa-search-minus fa-3x text-muted mb-3"></i>
                <h4>ไม่พบข้อมูล</h4>
                <p class="text-muted">ไม่พบข้อมูลที่ตรงกับเงื่อนไขการค้นหาของคุณ</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterTable() {
            // ดึงค่าจากฟิลเตอร์ทั้งหมด
            const comFilter = document.getElementById("ComDropdown").value.toLowerCase();
            const typeFilter = document.getElementById("typeDropdown").value.toLowerCase();
            const masterFilter = document.getElementById("masterDropdown").value.toLowerCase();
            const searchFilter = document.getElementById("searchParcel").value.toLowerCase();
            const startDateFilter = document.getElementById("startDate").value;
            const endDateFilter = document.getElementById("endDate").value;
            
            const tableBody = document.getElementById("historyTable");
            const rows = tableBody.getElementsByTagName("tr");
            const noResultsDiv = document.getElementById("no-results");
            
            let visibleRowCount = 0;

            // วนลูปเพื่อตรวจสอบทีละแถว
            for (const row of rows) {
                // อ้างอิงคอลัมน์ให้ถูกต้อง (index เริ่มที่ 0)
                const nameCell = row.cells[0].textContent.toLowerCase();
                const typeCell = row.cells[1].textContent.toLowerCase();
                const secCell = row.cells[5].textContent.toLowerCase();
                const dateCellRaw = row.cells[6].textContent; // format: dd/mm/yyyy
                const masterCell = row.cells[9].textContent.toLowerCase();

                // แปลง format วันที่จาก dd/mm/yyyy เป็น yyyy-mm-dd เพื่อให้เปรียบเทียบได้
                const dateParts = dateCellRaw.split('/');
                const dateCellFormatted = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;

                // ตรวจสอบเงื่อนไขทั้งหมด
                const matchesSearch = nameCell.includes(searchFilter);
                const matchesCom = comFilter === "" || secCell.includes(comFilter);
                const matchesType = typeFilter === "" || typeCell.includes(typeFilter);
                const matchesMaster = masterFilter === "" || masterCell.includes(masterFilter);
                const matchesStartDate = startDateFilter === "" || dateCellFormatted >= startDateFilter;
                const matchesEndDate = endDateFilter === "" || dateCellFormatted <= endDateFilter;

                // แสดง/ซ่อนแถวตามผลลัพธ์
                if (matchesSearch && matchesCom && matchesType && matchesMaster && matchesStartDate && matchesEndDate) {
                    row.style.display = "";
                    visibleRowCount++;
                } else {
                    row.style.display = "none";
                }
            }
            
            // แสดง/ซ่อนข้อความ "ไม่พบข้อมูล"
            noResultsDiv.style.display = visibleRowCount === 0 ? "block" : "none";
        }
    </script>
</body>
</html>