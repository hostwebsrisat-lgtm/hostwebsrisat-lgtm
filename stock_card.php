<?php
include 'condb.php';

// --- [แก้ไข] 1. ตั้งค่าการเชื่อมต่อเป็น UTF8 ---
// เพื่อให้แน่ใจว่าข้อมูลที่ดึงจาก Database และแสดงผลเป็นภาษาไทยถูกต้อง
if ($conn) {
    mysqli_set_charset($conn, "utf8");
}

$start = $_GET['start_date'] ?? '';
$end   = $_GET['end_date'] ?? '';
$itemFilter = $_GET['item_name'] ?? '';
$secFilter  = $_GET['section'] ?? '';

// --- [แก้ไข] 2. ตรวจสอบและเรียกใช้ฟังก์ชัน Export ---
if (isset($_GET['export']) && $start && $end) {
    // แก้ไข Header ให้รองรับ UTF-8
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=stock_card.xls");
    
    exportExcel($conn, $start, $end, $itemFilter, $secFilter);
    exit;
}

// --- [แก้ไข] 3. แก้ไขฟังก์ชัน Export Excel ---
function exportExcel($conn, $start, $end, $itemFilter, $secFilter) {
    // เพิ่ม BOM (Byte Order Mark) เพื่อให้ Excel รู้ว่าเป็น UTF-8
    echo "\xEF\xBB\xBF";

    // ส่วนหัวของตาราง
    echo "วันที่\tชื่อพัสดุ\tหน่วย\tหน่วยงาน\tสถานะ\tจำนวน\tคงเหลือ\n";
    
    $rows = getStockCard($conn, $start, $end, $itemFilter, $secFilter);
    foreach ($rows as $r) {
        // ทำความสะอาดข้อมูลก่อนแสดงผล ป้องกันอักขระพิเศษที่อาจทำให้แถวใน Excel เลื่อน
        $h_date = str_replace(["\t", "\n", "\r"], '', $r['h_date']);
        $h_name = str_replace(["\t", "\n", "\r"], '', $r['h_name']);
        $h_unit = str_replace(["\t", "\n", "\r"], '', $r['h_unit']);
        $h_sec = str_replace(["\t", "\n", "\r"], '', $r['h_sec']);
        $h_status = str_replace(["\t", "\n", "\r"], '', $r['h_status']);
        $h_qty = str_replace(["\t", "\n", "\r"], '', $r['h_qty']);
        $balance = str_replace(["\t", "\n", "\r"], '', $r['balance']);

        echo "{$h_date}\t{$h_name}\t{$h_unit}\t{$h_sec}\t{$h_status}\t{$h_qty}\t{$balance}\n";
    }
}

function getOpeningBalance($conn, $name, $sec, $start) {
    $secSql = $sec ? "AND h_sec='$sec'" : "";
    $sql = "SELECT 
                SUM(CASE WHEN h_status='รับเข้า' THEN h_qty ELSE 0 END) -
                SUM(CASE WHEN h_status='จ่ายออก' THEN h_qty ELSE 0 END) AS bal
            FROM history_db
            WHERE h_name='$name' $secSql AND h_date < '$start'";
    $res = mysqli_fetch_assoc(mysqli_query($conn, $sql));
    return $res['bal'] ?? 0;
}

function getStockCard($conn, $start, $end, $itemFilter, $secFilter) {
    $where = "WHERE h_date BETWEEN '$start' AND '$end'";
    if ($itemFilter) $where .= " AND h_name='$itemFilter'";
    if ($secFilter)  $where .= " AND h_sec='$secFilter'";
    $sql = "SELECT * FROM history_db $where ORDER BY h_name, h_date, h_id";
    $query = mysqli_query($conn, $sql);

    $data = [];
    $currentItem = '';
    $balance = 0;

    while ($row = mysqli_fetch_assoc($query)) {
        if ($row['h_name'] !== $currentItem) {
            // สินค้าใหม่ → คำนวณยอดยกมา
            $balance = getOpeningBalance($conn, $row['h_name'], $secFilter, $start);
            $currentItem = $row['h_name'];
        }
        // ปรับคงเหลือตามสถานะ
        if ($row['h_status'] === 'รับเข้า') {
            $balance += $row['h_qty'];
        } elseif ($row['h_status'] === 'จ่ายออก') {
            $balance -= $row['h_qty'];
        }
        $row['balance'] = $balance;
        $data[] = $row;
    }
    return $data;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Stock Card</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container .select2-selection--single {
    height: 38px;
    padding: 4px 10px;
}
</style>
</head>
<body class="bg-light p-4">

<div class="container">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
            <h4 class="mb-0">📦 รายงาน Stock Card</h4>
            <a href="home.php" class="btn btn-light btn-sm">⬅ กลับหน้า Home</a>
        </div>
        <div class="card-body">

            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">วันที่เริ่มต้น</label>
                    <input type="date" name="start_date" class="form-control" required value="<?= htmlspecialchars($start) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">วันที่สิ้นสุด</label>
                    <input type="date" name="end_date" class="form-control" required value="<?= htmlspecialchars($end) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">ชื่อพัสดุ</label>
                    <select name="item_name" class="form-select select2">
                        <option value="">-- ทั้งหมด --</option>
                        <?php
                        $items = mysqli_query($conn, "SELECT DISTINCT h_name FROM history_db ORDER BY h_name");
                        while ($i = mysqli_fetch_assoc($items)) {
                            $selected = ($itemFilter == $i['h_name']) ? "selected" : "";
                            echo "<option value=\"".htmlspecialchars($i['h_name'])."\" $selected>".htmlspecialchars($i['h_name'])."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">หน่วยงาน</label>
                    <select name="section" class="form-select select2">
                        <option value="">-- ทั้งหมด --</option>
                        <?php
                        $secs = mysqli_query($conn, "SELECT DISTINCT h_sec FROM history_db ORDER BY h_sec");
                        while ($s = mysqli_fetch_assoc($secs)) {
                            $selected = ($secFilter == $s['h_sec']) ? "selected" : "";
                            echo "<option value=\"".htmlspecialchars($s['h_sec'])."\" $selected>".htmlspecialchars($s['h_sec'])."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">ดูรายงาน</button>
                    <?php if ($start && $end): ?>
                        <a href="?start_date=<?= urlencode($start) ?>&end_date=<?= urlencode($end) ?>&item_name=<?= urlencode($itemFilter) ?>&section=<?= urlencode($secFilter) ?>&export=1" class="btn btn-success">
                            Export to Excel
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($start && $end): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>วันที่</th>
                            <th>ชื่อพัสดุ</th>
                            <th>หน่วย</th>
                            <th>หน่วยงาน</th>
                            <th>สถานะ</th>
                            <th>จำนวน</th>
                            <th>คงเหลือ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rows = getStockCard($conn, $start, $end, $itemFilter, $secFilter);
                        foreach ($rows as $r) {
                            echo "<tr>
                                    <td>".htmlspecialchars($r['h_date'])."</td>
                                    <td>".htmlspecialchars($r['h_name'])."</td>
                                    <td>".htmlspecialchars($r['h_unit'])."</td>
                                    <td>".htmlspecialchars($r['h_sec'])."</td>
                                    <td>".htmlspecialchars($r['h_status'])."</td>
                                    <td class='text-end'>".number_format($r['h_qty'])."</td>
                                    <td class='text-end fw-bold'>".number_format($r['balance'])."</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        width: '100%',
        placeholder: "พิมพ์เพื่อค้นหา",
        allowClear: true
    });
});
</script>

</body>
</html>