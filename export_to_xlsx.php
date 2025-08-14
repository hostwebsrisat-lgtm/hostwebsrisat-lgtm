<?php
// กำหนดให้แสดงข้อผิดพลาดทั้งหมดสำหรับการดีบัก
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';
include 'condb.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';
$product_name = isset($_GET['product_name']) ? $_GET['product_name'] : '';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Stock Card');

// ดึงข้อมูลสินค้าเพื่อใช้ในการแสดงผล
$product_unit = '';
$product_type = '';
$sql_product_info = "SELECT unit, type FROM parcel_db WHERE name = ?";
$stmt_product_info = mysqli_prepare($conn, $sql_product_info);
if ($stmt_product_info) {
    mysqli_stmt_bind_param($stmt_product_info, "s", $product_name);
    mysqli_stmt_execute($stmt_product_info);
    $result_product_info = mysqli_stmt_get_result($stmt_product_info);
    $row_product_info = mysqli_fetch_assoc($result_product_info);
    $product_unit = $row_product_info['unit'] ?? '';
    $product_type = $row_product_info['type'] ?? '';
    mysqli_stmt_close($stmt_product_info);
}

// ตั้งค่าหัวข้อหลัก
$sheet->setCellValue('A1', 'บัญชีวัสดุ');
$sheet->mergeCells('A1:L1');
$sheet->setCellValue('A2', 'Stock Card');
$sheet->mergeCells('A2:L2');
$sheet->setCellValue('A3', 'รายการ: ' . $product_name);
$sheet->mergeCells('A3:F3');
$sheet->setCellValue('G3', 'ประเภท: ' . $product_type);
$sheet->mergeCells('G3:L3');

$sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A3:L3')->getFont()->setBold(true);

// ตั้งค่าหัวตารางแถวแรก (แถวที่ 5)
$headers_row5 = [
    'วันที่', 'เลขที่เอกสาร', 'รับเข้า', '', '', '', 'จ่ายออก', '', '', '', 'คงเหลือ', ''
];
$sheet->fromArray($headers_row5, NULL, 'A5');
$sheet->mergeCells('A5:A6'); // วันที่
$sheet->mergeCells('B5:B6'); // เลขที่เอกสาร
$sheet->mergeCells('C5:F5'); // รับเข้า
$sheet->mergeCells('G5:J5'); // จ่ายออก
$sheet->mergeCells('K5:L5'); // คงเหลือ

// ตั้งค่าหัวตารางแถวสอง (แถวที่ 6)
$headers_row6 = [
    '', '', 'จำนวน', 'ราคาต่อหน่วย', 'รวมเงิน', 'หน่วย', 'จำนวน', 'ราคาต่อหน่วย', 'รวมเงิน', 'หน่วย', 'จำนวน', 'หน่วย'
];
$sheet->fromArray($headers_row6, NULL, 'A6');
$sheet->getStyle('A5:L6')->getFont()->setBold(true);
$sheet->getStyle('A5:L6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// กำหนด Border ให้กับหัวตาราง
$styleArray = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
];
$sheet->getStyle('A5:L6')->applyFromArray($styleArray);

// ดึงข้อมูลจากฐานข้อมูลและจัดกลุ่ม
$sql = "SELECT h_date, h_name, h_bill, h_status, h_qty, h_unit, h_price FROM history_db WHERE h_name = ? AND h_date BETWEEN ? AND ? ORDER BY h_date ASC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sss", $product_name, $date_start, $date_end);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$grouped_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $date = $row['h_date'];
    if (!isset($grouped_data[$date])) {
        $grouped_data[$date] = [
            'h_bill' => $row['h_bill'],
            'in_qty' => 0,
            'out_qty' => 0,
            'in_total_price' => 0,
            'out_total_price' => 0,
            'unit' => $row['h_unit'],
        ];
    }
    
    // ตรวจสอบสถานะเพื่อรวมยอด
    if ($row['h_status'] == 'รับเข้า') {
        $grouped_data[$date]['in_qty'] += $row['h_qty'];
        $grouped_data[$date]['in_total_price'] += ($row['h_qty'] * $row['h_price']);
    } else {
        $grouped_data[$date]['out_qty'] += $row['h_qty'];
        $grouped_data[$date]['out_total_price'] += ($row['h_qty'] * $row['h_price']);
    }
    // ใช้ราคาต่อหน่วยจากรายการล่าสุดในวันนั้น
    $grouped_data[$date]['in_price'] = $row['h_price'];
    $grouped_data[$date]['out_price'] = $row['h_price'];
}
mysqli_stmt_close($stmt);

// ดึงยอดคงเหลือ ณ สิ้นสุดวันก่อนหน้า
$sql_prev_stock = "SELECT SUM(CASE WHEN h_status = 'รับเข้า' THEN h_qty ELSE -h_qty END) as prev_balance FROM history_db WHERE h_name = ? AND h_date < ?";
$stmt_prev = mysqli_prepare($conn, $sql_prev_stock);
mysqli_stmt_bind_param($stmt_prev, "ss", $product_name, $date_start);
mysqli_stmt_execute($stmt_prev);
$result_prev = mysqli_stmt_get_result($stmt_prev);
$row_prev = mysqli_fetch_assoc($result_prev);
$remaining_qty = $row_prev['prev_balance'] ?? 0;
mysqli_stmt_close($stmt_prev);

$rowIndex = 7;
// แสดงยอดคงเหลือยกมา
$sheet->setCellValue('A' . $rowIndex, 'ยอดคงเหลือยกมา');
$sheet->mergeCells('A' . $rowIndex . ':J' . $rowIndex);
$sheet->getStyle('A' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('A' . $rowIndex)->getFont()->setBold(true);
$sheet->setCellValue('K' . $rowIndex, $remaining_qty);
$sheet->setCellValue('L' . $rowIndex, $product_unit);
$rowIndex++;

foreach ($grouped_data as $date => $data) {
    $sheet->setCellValue('A' . $rowIndex, $date);
    $sheet->setCellValue('B' . $rowIndex, $data['h_bill']);
    
    // รับเข้า
    if ($data['in_qty'] > 0) {
        $sheet->setCellValue('C' . $rowIndex, $data['in_qty']);
        $sheet->setCellValue('D' . $rowIndex, $data['in_total_price'] / $data['in_qty']);
        $sheet->setCellValue('E' . $rowIndex, $data['in_total_price']);
    }
    $sheet->setCellValue('F' . $rowIndex, $data['unit']);
    
    // จ่ายออก
    if ($data['out_qty'] > 0) {
        $sheet->setCellValue('G' . $rowIndex, $data['out_qty']);
        $sheet->setCellValue('H' . $rowIndex, $data['out_total_price'] / $data['out_qty']);
        $sheet->setCellValue('I' . $rowIndex, $data['out_total_price']);
    }
    $sheet->setCellValue('J' . $rowIndex, $data['unit']);

    // คงเหลือ
    $remaining_qty += ($data['in_qty'] - $data['out_qty']);
    $sheet->setCellValue('K' . $rowIndex, $remaining_qty);
    $sheet->setCellValue('L' . $rowIndex, $product_unit);

    // กำหนด Border ให้กับข้อมูลแต่ละแถว
    $sheet->getStyle('A' . $rowIndex . ':L' . $rowIndex)->applyFromArray($styleArray);
    $rowIndex++;
}

// ตั้งค่าความกว้างคอลัมน์อัตโนมัติ
foreach (range('A', 'L') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// กำหนด Header เพื่อให้เบราว์เซอร์ดาวน์โหลดไฟล์
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="stock_card.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>