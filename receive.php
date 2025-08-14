<?php
include 'condb.php';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกข้อมูลการรับเข้าพัสดุ</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        body { background-color: #f0f2f5; }
        .card-header-custom { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>
    <div class="container my-4">
        
        <div class="card shadow-sm mb-4">
            <div class="card-header card-header-custom h5">
                <i class="bi bi-box-arrow-in-down"></i> บันทึกข้อมูลการรับเข้าพัสดุ
            </div>
            <div class="card-body">
                <form id="dataForm" class="row g-3">
                    <div class="col-md-6">
                        <label for="ComDropdown" class="form-label">บริษัทที่รับเข้า <span class="text-danger">*</span></label>
                        <select id="ComDropdown" class="form-select" required>
                            <option value="" selected disabled>-- เลือกบริษัทที่รับเข้า --</option>
                            <?php
                            $sqlCom = "SELECT com_name FROM company_db";
                            $resultCom = mysqli_query($conn, $sqlCom);
                            while ($rowcom = mysqli_fetch_assoc($resultCom)) {
                                echo '<option value="' . htmlspecialchars($rowcom["com_name"]) . '">' . htmlspecialchars($rowcom["com_name"]) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="doc_no" class="form-label">เลขที่เอกสาร</label>
                        <input type="text" id="doc_no" name="doc_no" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label for="typeDropdown" class="form-label">ประเภทพัสดุ <span class="text-danger">*</span></label>
                        <select id="typeDropdown" class="form-select" onchange="filterByType(this.value)" required>
                            <option value="" selected disabled>-- เลือกประเภทพัสดุ --</option>
                            <?php
                            $sqlType = "SELECT DISTINCT type_p FROM parcel_db";
                            $resultType = mysqli_query($conn, $sqlType);
                            while ($rowType = mysqli_fetch_assoc($resultType)) {
                                echo '<option value="' . htmlspecialchars($rowType["type_p"]) . '">' . htmlspecialchars($rowType["type_p"]) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="NameDropdown" class="form-label">รายการที่รับเข้า <span class="text-danger">*</span></label>
                        <select id="NameDropdown" class="form-select" required>
                            <option value="" selected disabled>-- กรุณาเลือกประเภทพัสดุก่อน --</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="quantity" class="form-label">จำนวนที่รับเข้า <span class="text-danger">*</span></label>
                        <input type="number" id="quantity" name="quantity" class="form-control" required onchange="calculateTotalPrice()" min="1">
                    </div>

                    <div class="col-md-6">
                        <label for="unit" class="form-label">หน่วยนับ</label>
                        <input type="text" id="unit" name="unit" class="form-control" readonly style="background-color: #e9ecef;">
                    </div>

                    <div class="col-md-6">
                        <label for="remaining_stock" class="form-label">ยอดคงเหลือในสต็อก</label>
                        <input type="number" id="remaining_stock" name="remaining_stock" class="form-control" readonly style="background-color: #e9ecef;">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="price" class="form-label">ราคาต่อหน่วย <span class="text-danger">*</span></label>
                        <input type="number" id="price" name="price" class="form-control" required step="0.01" onchange="calculateTotalPrice()" min="0">
                    </div>

                    <div class="col-md-6">
                        <label for="total_price" class="form-label">ราคารวม</label>
                        <input type="number" id="total_price" name="total_price" class="form-control" readonly style="background-color: #e9ecef;">
                    </div>

                    <div class="col-md-6">
                        <label for="datetime" class="form-label">วันที่และเวลา <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="datetime" name="datetime" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label for="created_by" class="form-label">ผู้บันทึก <span class="text-danger">*</span></label>
                        <input type="text" id="created_by" name="created_by" class="form-control" required>
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
                                <th>บริษัท</th>
                                <th>เลขที่เอกสาร</th>
                                <th>ประเภทพัสดุ</th>
                                <th>รายการพัสดุ</th>
                                <th class="text-end">จำนวน</th>
                                <th>หน่วยนับ</th>
                                <th class="text-end">ยอดคงเหลือ (ใหม่)</th>
                                <th class="text-end">ราคาต่อหน่วย</th>
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
        let notificationModal, confirmationModal;
        let originalStock = 0;

        $(document).ready(function () {
            // Initialize Modals
            notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));
            confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));

            // Initialize Select2
            $('#ComDropdown, #typeDropdown, #NameDropdown').select2({
                theme: "bootstrap-5"
            });
            
            // Set current datetime
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('datetime').value = now.toISOString().slice(0, 16);

            // Event Listeners
            $('#NameDropdown').on('change', function () {
                fetchProductDetails(this.value);
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

        function filterByType(type) {
            if (!type) return;
            $.ajax({
                url: 'fetch_parcels.php',
                type: 'POST',
                data: { type_p: type },
                success: function(response) {
                    $('#NameDropdown').html(response).val('').trigger('change');
                },
                error: function() {
                    showNotification('เกิดข้อผิดพลาด', 'ไม่สามารถโหลดรายการพัสดุได้');
                }
            });
        }

        function fetchProductDetails(productName) {
            if (!productName) {
                $('#unit, #remaining_stock, #price').val('');
                originalStock = 0;
                return;
            }
            $.ajax({
                url: 'fetch_product_details.php',
                type: 'POST',
                data: { productName: productName },
                dataType: 'json',
                success: function(data) {
                    if (data) {
                        originalStock = parseInt(data.qty) || 0;
                        $('#unit').val(data.unit || '');
                        $('#remaining_stock').val(originalStock);
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
            const company = $('#ComDropdown').val();
            const doc_no = $('#doc_no').val() || '-';
            const productType = $('#typeDropdown').val();
            const productName = $('#NameDropdown').find('option:selected').text();
            const quantity = parseInt($('#quantity').val()) || 0;
            const unit = $('#unit').val();
            const unitPrice = parseFloat($('#price').val()) || 0;
            const totalPrice = (quantity * unitPrice).toFixed(2);
            const datetime = $('#datetime').val();
            const createdBy = $('#created_by').val();

            if (!company || !productType || !productName || quantity <= 0 || unitPrice < 0 || !datetime || !createdBy) {
                showNotification('ข้อมูลไม่ครบถ้วน', 'กรุณากรอกข้อมูลที่มีเครื่องหมาย * ให้ครบทุกช่อง');
                return;
            }

            const newRemainingStock = originalStock + quantity;
            const tableBody = document.getElementById('previewTable').querySelector('tbody');

            const newRow = `
                <tr>
                    <td>${company}</td>
                    <td>${doc_no}</td>
                    <td>${productType}</td>
                    <td>${productName}</td>
                    <td class="text-end">${quantity}</td>
                    <td>${unit}</td>
                    <td class="text-end">${newRemainingStock}</td>
                    <td class="text-end">${unitPrice.toFixed(2)}</td>
                    <td class="text-end">${totalPrice}</td>
                    <td>${datetime}</td>
                    <td>${createdBy}</td>
                    <td class="text-center">
                        <button class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            tableBody.insertAdjacentHTML('beforeend', newRow);
            
            // Clear form for next entry, but keep company and createdBy
            $('#doc_no, #typeDropdown, #NameDropdown, #quantity, #unit, #remaining_stock, #price, #total_price').val(null).trigger('change');
            
            // Restore datetime
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('datetime').value = now.toISOString().slice(0, 16);
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
                    productType: cells[2].textContent.trim(),
                    product: cells[3].textContent.trim(),
                    quantity: parseInt(cells[4].textContent.trim(), 10),
                    unit: cells[5].textContent.trim(),
                    remainingStock: parseFloat(cells[6].textContent.trim()),
                    unitPrice: parseFloat(cells[7].textContent.trim()),
                    totalPrice: parseFloat(cells[8].textContent.trim()),
                    datetime: cells[9].textContent.trim(),
                    createdBy: cells[10].textContent.trim()
                };
            });

            $.ajax({
                url: 'save_data.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(dataToSave),
                success: function(response) {
                    if (response.success) {
                        showNotification('บันทึกสำเร็จ', 'ข้อมูลการรับเข้าถูกบันทึกเรียบร้อยแล้ว');
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