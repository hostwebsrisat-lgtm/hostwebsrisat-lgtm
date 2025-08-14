<?php
include 'condb.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกข้อมูลการเบิกจ่ายพัสดุ</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        body { background-color: #f0f2f5; }
        .card-header-custom { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="container my-4">
        
        <div class="card shadow-sm mb-4">
            <div class="card-header card-header-custom h5">
                <i class="bi bi-box-arrow-up"></i> บันทึกข้อมูลการเบิกจ่ายพัสดุ
            </div>
            <div class="card-body">
                <form id="dataForm" class="row g-3">
                    <div class="col-md-6">
                        <label for="sectionDropdown" class="form-label">หน่วยงานที่เบิก <span class="text-danger">*</span></label>
                        <select id="sectionDropdown" class="form-select" required>
                            <option value="" selected disabled>-- เลือกหน่วยงาน --</option>
                            <?php
                            $sqlSec = "SELECT sec_name FROM section_db ORDER BY sec_name";
                            $resultSec = mysqli_query($conn, $sqlSec);
                            while ($rowSec = mysqli_fetch_assoc($resultSec)) {
                                echo '<option value="' . htmlspecialchars($rowSec["sec_name"]) . '">' . htmlspecialchars($rowSec["sec_name"]) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="doc_no" class="form-label">เลขที่เอกสาร</label>
                        <input type="text" id="doc_no" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label for="typeDropdown" class="form-label">ประเภทพัสดุ <span class="text-danger">*</span></label>
                        <select id="typeDropdown" class="form-select" onchange="filterParcelsByType(this.value)" required>
                            <option value="" selected disabled>-- เลือกประเภทพัสดุ --</option>
                            <?php
                            $sqlType = "SELECT DISTINCT type_p FROM parcel_db ORDER BY type_p";
                            $resultType = mysqli_query($conn, $sqlType);
                            while ($rowType = mysqli_fetch_assoc($resultType)) {
                                echo '<option value="' . htmlspecialchars($rowType["type_p"]) . '">' . htmlspecialchars($rowType["type_p"]) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="parcelNameDropdown" class="form-label">รายการที่จ่ายออก <span class="text-danger">*</span></label>
                        <select id="parcelNameDropdown" class="form-select" required>
                            <option value="" selected disabled>-- กรุณาเลือกประเภทพัสดุก่อน --</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="quantity" class="form-label">จำนวนที่จ่ายออก <span class="text-danger">*</span></label>
                        <input type="number" id="quantity" class="form-control" min="1" required oninput="calculateTotalPrice()">
                    </div>
                    <div class="col-md-6">
                        <label for="unit" class="form-label">หน่วยนับ</label>
                        <input type="text" id="unit" class="form-control" readonly style="background-color: #e9ecef;">
                    </div>

                    <div class="col-md-6">
                        <label for="stock_before" class="form-label">ยอดคงเหลือในสต็อก</label>
                        <input type="number" id="stock_before" class="form-control" readonly style="background-color: #e9ecef;">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="price" class="form-label">ราคาต่อหน่วย <span class="text-danger">*</span></label>
                        <input type="number" id="price" class="form-control" step="0.01" min="0" required oninput="calculateTotalPrice()">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="total_price" class="form-label">ราคารวม</label>
                        <input type="number" id="total_price" class="form-control" readonly style="background-color: #e9ecef;">
                    </div>

                    <div class="col-md-6">
                        <label for="datetime" class="form-label">วันที่และเวลา <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="datetime" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="created_by" class="form-label">ผู้บันทึก <span class="text-danger">*</span></label>
                        <input type="text" id="created_by" class="form-control" required>
                    </div>

                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-primary" onclick="addRowToPreview()">
                            <i class="bi bi-plus-circle"></i> เพิ่มรายการลงตาราง
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header h5"><i class="bi bi-table"></i> ตารางแสดงตัวอย่างข้อมูล (รอการบันทึก)</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="previewTable" class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>หน่วยงาน</th>
                                <th>เลขที่เอกสาร</th>
                                <th>รายการพัสดุ</th>
                                <th class="text-end">จำนวนจ่าย</th>
                                <th>หน่วยนับ</th>
                                <th class="text-end">คงเหลือ (ใหม่)</th>
                                <th class="text-end">ราคารวม</th>
                                <th>วันที่</th>
                                <th>ผู้บันทึก</th>
                                <th class="text-center">ลบ</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <button id="saveAllBtn" class="btn btn-success btn-lg">
                        <i class="bi bi-save"></i> บันทึกข้อมูลทั้งหมด
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="notificationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalTitle">แจ้งเตือน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="notificationModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalTitle">ยืนยันการบันทึก</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="confirmationModalBody">
                    คุณต้องการบันทึกข้อมูลทั้งหมดที่อยู่ในตารางใช่หรือไม่?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-success" id="confirmSaveBtn">ยืนยันการบันทึก</button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Store original fetched stock to prevent calculation errors
        let originalStock = 0;
        let originalProductType = '';
        let notificationModal, confirmationModal;

        $(document).ready(function () {
            // Initialize Modals
            notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));
            confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));

            // Initialize Select2
            $('#sectionDropdown, #typeDropdown, #parcelNameDropdown').select2({
                theme: "bootstrap-5"
            });
            
            // Set current datetime
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('datetime').value = now.toISOString().slice(0, 16);

            // Event Listeners
            $('#parcelNameDropdown').on('change', function () {
                fetchParcelDetails(this.value);
            });

            $('#saveAllBtn').on('click', function () {
                if (document.getElementById('previewTable').querySelector('tbody').rows.length === 0) {
                    showNotification('ไม่มีข้อมูลให้บันทึก', 'กรุณาเพิ่มรายการลงในตารางก่อน');
                    return;
                }
                confirmationModal.show();
            });

            $('#confirmSaveBtn').on('click', function() {
                saveAllData();
                confirmationModal.hide();
            });
        });

        function showNotification(title, body) {
            document.getElementById('notificationModalTitle').textContent = title;
            document.getElementById('notificationModalBody').textContent = body;
            notificationModal.show();
        }

        function filterParcelsByType(type) {
            if (!type) return;
            $.ajax({
                url: 'fetch_parcels.php',
                type: 'POST',
                data: { type_p: type },
                success: function(response) {
                    $('#parcelNameDropdown').html(response).val('').trigger('change');
                },
                error: function() {
                     showNotification('เกิดข้อผิดพลาด', 'ไม่สามารถโหลดรายการพัสดุได้');
                }
            });
        }

        function fetchParcelDetails(parcelName) {
            if (!parcelName) {
                $('#unit, #stock_before, #price, #quantity').val('');
                originalStock = 0;
                originalProductType = '';
                calculateTotalPrice();
                return;
            };

            $.ajax({
                url: 'fetch_product_details.php',
                type: 'POST',
                data: { productName: parcelName },
                dataType: 'json',
                success: function(data) {
                    if (data) {
                        originalStock = parseInt(data.qty) || 0;
                        originalProductType = data.type_p || '';
                        $('#unit').val(data.unit || '');
                        $('#stock_before').val(originalStock);
                        $('#price').val(data.price || '');
                        calculateTotalPrice();
                    } else {
                        showNotification('ไม่พบข้อมูล', 'ไม่พบรายละเอียดของพัสดุที่เลือก');
                    }
                },
                error: function() {
                    showNotification('เกิดข้อผิดพลาด', 'ไม่สามารถโหลดรายละเอียดพัสดุได้');
                }
            });
        }

        function calculateTotalPrice() {
            const quantity = parseInt($('#quantity').val()) || 0;
            const unitPrice = parseFloat($('#price').val()) || 0;
            $('#total_price').val((quantity * unitPrice).toFixed(2));
        }
        
        function addRowToPreview() {
            const requiredFields = ['#sectionDropdown', '#typeDropdown', '#parcelNameDropdown', '#quantity', '#datetime', '#created_by', '#price'];
            for (const field of requiredFields) {
                if (!$(field).val()) {
                    showNotification('ข้อมูลไม่ครบถ้วน', 'กรุณากรอกข้อมูลที่มีเครื่องหมาย * ให้ครบทุกช่อง');
                    return;
                }
            }

            const quantity = parseInt($('#quantity').val(), 10);
            if (quantity <= 0) {
                 showNotification('ข้อมูลผิดพลาด', 'จำนวนที่จ่ายออกต้องมากกว่า 0');
                 return;
            }

            if (quantity > originalStock) {
                showNotification('จำนวนไม่เพียงพอ', `สต็อกมีเพียง ${originalStock} หน่วย แต่ต้องการเบิก ${quantity} หน่วย`);
                return;
            }

            const newRemainingStock = originalStock - quantity;
            const unitPrice = parseFloat($('#price').val()) || 0;
            const totalPrice = (quantity * unitPrice).toFixed(2);
            const tableBody = document.getElementById('previewTable').querySelector('tbody');

            const rowData = {
                company: $('#sectionDropdown').val(),
                doc_no: $('#doc_no').val() || '-',
                product: $('#parcelNameDropdown').val(),
                productType: $('#typeDropdown').val(),
                quantity: quantity,
                unit: $('#unit').val(),
                totalPrice: totalPrice,
                datetime: $('#datetime').val(),
                createdBy: $('#created_by').val(),
            };

            const newRow = `
                <tr>
                    <td data-field="company">${rowData.company}</td>
                    <td data-field="doc_no">${rowData.doc_no}</td>
                    <td data-field="product" data-name="${$('#parcelNameDropdown').find('option:selected').text()}" data-type="${rowData.productType}">${rowData.product}</td>
                    <td data-field="quantity" class="text-end">${rowData.quantity}</td>
                    <td data-field="unit">${rowData.unit}</td>
                    <td data-field="newStock" class="text-end">${newRemainingStock}</td>
                    <td data-field="totalPrice" class="text-end">${rowData.totalPrice}</td>
                    <td data-field="datetime">${rowData.datetime}</td>
                    <td data-field="createdBy">${rowData.createdBy}</td>
                    <td class="text-center">
                        <button class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            tableBody.insertAdjacentHTML('beforeend', newRow);
            
            $('#parcelNameDropdown, #typeDropdown').val(null).trigger('change');
            $('#quantity, #unit, #stock_before, #price, #total_price').val('');
            $('#doc_no').val('');
        }

        function saveAllData() {
            const saveBtn = document.getElementById('confirmSaveBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> กำลังบันทึก...';
            
            const tableBody = document.getElementById('previewTable').querySelector('tbody');
            const dataToSave = Array.from(tableBody.rows).map(row => {
                const cells = row.cells;
                return {
                    company: cells[0].textContent.trim(),
                    doc_no: cells[1].textContent.trim(),
                    product: cells[2].dataset.name,
                    productType: cells[2].dataset.type,
                    quantity: parseInt(cells[3].textContent.trim(), 10),
                    unit: cells[4].textContent.trim(),
                    totalPrice: parseFloat(cells[6].textContent.trim()),
                    datetime: cells[7].textContent.trim(),
                    createdBy: cells[8].textContent.trim()
                };
            });
            
            console.log(dataToSave);

            $.ajax({
                url: 'save_data_d.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(dataToSave),
                success: function(response) {
                    if (response.success) {
                        showNotification('บันทึกสำเร็จ', 'ข้อมูลการเบิกจ่ายถูกบันทึกเรียบร้อยแล้ว');
                        tableBody.innerHTML = ''; 
                        setTimeout(() => window.location.href = 'home.php', 2000);
                    } else {
                        showNotification('เกิดข้อผิดพลาดในการบันทึก', response.message || 'ไม่สามารถบันทึกข้อมูลลงฐานข้อมูลได้');
                    }
                },
                error: function() {
                    showNotification('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'ไม่สามารถติดต่อเซิร์ฟเวอร์เพื่อบันทึกข้อมูลได้');
                },
                complete: function() {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = 'ยืนยันการบันทึก';
                }
            });
        }
    </script>
</body>
</html>