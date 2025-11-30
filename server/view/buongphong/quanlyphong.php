<?php
$dsPhong = array();
require_once '../../model/buongphongQLPhong.model.php';
$model = new PhongModel();
$dsPhong = $model->getDanhSachPhong();

if ($dsPhong === null) {
    $dsPhong = array();
}

$hangPhongList = array_unique(array_column($dsPhong, 'HangPhong'));
$tangList = array_unique(array_column($dsPhong, 'Tang'));
sort($tangList);
?>

<?php include '../layouts/header.php'; ?>
<style>
    .btn-icon {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
        transition: all 0.3s ease;
    }

    .btn-icon:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-group .btn {
        border: 1px solid #dee2e6;
    }

    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }

    .btn-outline-warning:hover {
        background-color: #ffc107;
        color: black;
    }

    .btn-outline-info:hover {
        background-color: #0dcaf0;
        color: white;
    }

    .table th {
        border-top: none;
        font-weight: 600;
    }

    .badge {
        font-size: 0.8em;
        padding: 0.5em 0.75em;
    }

    .card {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border: 1px solid #e3e6f0;
    }

    .card-header {
        background: linear-gradient(180deg, #f8f9fc 0%, #e2e6f0 100%);
        border-bottom: 1px solid #e3e6f0;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mt-4">Quản Lý Buồng Phòng</h1>

            <!-- Bộ lọc và tìm kiếm -->
            <div class="card mb-4">
                <div class="card-body">
                    <form id="formFilter">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Tìm kiếm:</label>
                                <input type="text" class="form-control" id="searchKeyword" placeholder="Nhập từ khóa và Enter...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Hạng phòng:</label>
                                <select class="form-select" id="filterHangPhong">
                                    <option value="">Tất cả hạng</option>
                                    <?php foreach ($hangPhongList as $hang): ?>
                                        <option value="<?php echo $hang; ?>"><?php echo $hang; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tầng:</label>
                                <select class="form-select" id="filterTang">
                                    <option value="">Tất cả tầng</option>
                                    <?php foreach ($tangList as $tang): ?>
                                        <option value="<?php echo $tang; ?>">Tầng <?php echo $tang; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Trạng thái:</label>
                                <select class="form-select" id="filterTrangThai">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="Trống">Trống</option>
                                    <option value="Đang sử dụng">Đang sử dụng</option>
                                    <option value="Đang dọn dẹp">Đang dọn dẹp</option>
                                    <option value="Bảo trì">Bảo trì</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12 text-end">
                                <button type="button" class="btn btn-secondary" id="btnResetFilter">
                                    <i class="fas fa-redo"></i> Hiển thị tất cả
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>



            <!-- Bảng danh sách phòng -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Danh sách phòng (<span id="roomCount"><?php echo count($dsPhong); ?></span> phòng)</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tablePhong">
                            <thead class="table-dark">
                                <tr>
                                    <th>Mã phòng</th>
                                    <th>Số phòng</th>
                                    <th>Tên phòng</th>
                                    <th>Tầng</th>
                                    <th>Hạng phòng</th>
                                    <th>Diện tích</th>
                                    <th>Số khách</th>
                                    <th>Trạng thái</th>
                                    <th width="150px" class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($dsPhong) > 0): ?>
                                    <?php foreach ($dsPhong as $phong): ?>
                                        <tr>
                                            <td><?php echo $phong['MaPhong']; ?></td>
                                            <td>
                                                <strong><?php echo $phong['SoPhong']; ?></strong>
                                            </td>
                                            <td>
                                                <?php echo !empty($phong['roomName']) ? $phong['roomName'] : 'Chưa đặt tên'; ?>
                                            </td>
                                            <td>Tầng <?php echo $phong['Tang']; ?></td>
                                            <td><?php echo $phong['HangPhong']; ?></td>
                                            <td>
                                                <?php if ($phong['DienTich'] > 0): ?>
                                                    <?php echo $phong['DienTich']; ?> m²
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($phong['SoKhachToiDa'] > 0): ?>
                                                    <?php echo $phong['SoKhachToiDa']; ?> người
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge 
                                        <?php
                                        switch ($phong['TrangThai']) {
                                            case 'Trống':
                                                echo 'bg-success';
                                                break;
                                            case 'Đang sử dụng':
                                                echo 'bg-primary';
                                                break;
                                            case 'Đang bảo trì':
                                                echo 'bg-danger';
                                                break;
                                            default:
                                                echo 'bg-secondary';
                                        }
                                        ?>">
                                                    <?php echo $phong['TrangThai']; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-danger btn-action btn-icon"
                                                        data-action="suCo"
                                                        data-maphong="<?php echo $phong['MaPhong']; ?>"
                                                        data-sophong="<?php echo $phong['SoPhong']; ?>"
                                                        title="Ghi nhận sự cố">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-warning btn-action btn-icon"
                                                        data-action="chiPhi"
                                                        data-maphong="<?php echo $phong['MaPhong']; ?>"
                                                        data-sophong="<?php echo $phong['SoPhong']; ?>"
                                                        title="Ghi nhận chi phí">
                                                        <i class="fas fa-money-bill-wave"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info btn-action btn-icon"
                                                        data-action="trangThai"
                                                        data-maphong="<?php echo $phong['MaPhong']; ?>"
                                                        data-sophong="<?php echo $phong['SoPhong']; ?>"
                                                        data-trangthai="<?php echo $phong['TrangThai']; ?>"
                                                        title="Cập nhật trạng thái">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">Không có dữ liệu phòng</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ghi nhận sự cố -->
<div class="modal fade" id="modalSuCo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ghi nhận sự cố</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSuCo">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Phòng:</label>
                        <div id="selectedRoomSuCo" class="alert alert-info">
                            <strong id="roomInfoSuCo"></strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả sự cố:</label>
                        <textarea class="form-control" name="moTaSuCo" rows="3" required placeholder="Mô tả chi tiết sự cố..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Chi phí sửa chữa (VNĐ):</label>
                        <input type="number" class="form-control" name="chiPhi" step="1000" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Ghi nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ghi nhận chi phí -->
<div class="modal fade" id="modalChiPhi" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ghi nhận chi phí</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formChiPhi">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Phòng:</label>
                        <div id="selectedRoomChiPhi" class="alert alert-info">
                            <strong id="roomInfoChiPhi"></strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Loại chi phí:</label>
                        <input type="text" class="form-control" name="loaiChiPhi" required placeholder="Ví dụ: Vệ sinh, Bảo trì...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số tiền (VNĐ):</label>
                        <input type="number" class="form-control" name="soTien" step="1000" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú:</label>
                        <textarea class="form-control" name="ghiChu" rows="2" placeholder="Ghi chú thêm..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Ghi nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cập nhật trạng thái -->
<div class="modal fade" id="modalTrangThai" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật trạng thái phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTrangThai">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Phòng:</label>
                        <div id="selectedRoomTrangThai" class="alert alert-info">
                            <strong id="roomInfoTrangThai"></strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái mới:</label>
                        <select class="form-select" name="trangThaiMoi" required>
                            <option value="Trống">Trống</option>
                            <option value="Đang sử dụng">Đang sử dụng</option>
                            <option value="Đang bảo trì">Đang bảo trì</option>
                        </select>
                    </div>
                    <!-- THÊM TRƯỜNG GHI CHÚ KỸ THUẬT -->
                    <div class="mb-3">
                        <label class="form-label">Ghi chú kỹ thuật:</label>
                        <textarea class="form-control" name="ghiChuKyThuat" rows="3"
                            placeholder="Nhập ghi chú về tình trạng phòng (nếu có)..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lý do thay đổi:</label>
                        <input type="text" class="form-control" name="lyDo"
                            placeholder="Lý do thay đổi trạng thái...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Tự động tìm kiếm khi nhập
        $('#searchKeyword').on('input', function() {
            filterRooms();
        });

        // Tự động lọc khi thay đổi select
        $('#filterHangPhong, #filterTang, #filterTrangThai').change(function() {
            filterRooms();
        });

        // Nút hiển thị tất cả
        $('#btnResetFilter').click(function() {
            $('#searchKeyword').val('');
            $('#filterHangPhong').val('');
            $('#filterTang').val('');
            $('#filterTrangThai').val('');
            filterRooms();
        });

        // Hành động cho từng phòng
        $('.btn-action').click(function() {
            const action = $(this).data('action');
            const maPhong = $(this).data('maphong');
            const soPhong = $(this).data('sophong');
            const trangThai = $(this).data('trangthai');

            if (action === 'suCo') {
                $('#roomInfoSuCo').text(soPhong + ' (Mã: ' + maPhong + ')');
                $('#modalSuCo').modal('show');
            } else if (action === 'chiPhi') {
                $('#roomInfoChiPhi').text(soPhong + ' (Mã: ' + maPhong + ')');
                $('#modalChiPhi').modal('show');
            } else if (action === 'trangThai') {
                $('#roomInfoTrangThai').text(soPhong + ' (Mã: ' + maPhong + ') - Hiện tại: ' + trangThai);
                $('#modalTrangThai').modal('show');
            }
        });

        // Hàm lọc phòng - CẬP NHẬT CHO BẢNG MỚI
        function filterRooms() {
            const keyword = $('#searchKeyword').val().toLowerCase();
            const hangPhong = $('#filterHangPhong').val();
            const tang = $('#filterTang').val();
            const trangThai = $('#filterTrangThai').val();

            let visibleCount = 0;

            $('tbody tr').each(function() {
                const $row = $(this);
                const soPhong = $row.find('td:eq(1)').text().toLowerCase();
                const tenPhong = $row.find('td:eq(2)').text().toLowerCase();
                const hang = $row.find('td:eq(4)').text();
                const tangText = $row.find('td:eq(3)').text();
                const trangthai = $row.find('td:eq(7) .badge').text().trim();

                let showRow = true;

                // Lọc theo keyword (tìm trong số phòng và tên phòng)
                if (keyword && !soPhong.includes(keyword) && !tenPhong.includes(keyword)) {
                    showRow = false;
                }

                // Lọc theo hạng phòng
                if (hangPhong && hang !== hangPhong) {
                    showRow = false;
                }

                // Lọc theo tầng
                if (tang) {
                    const currentTang = tangText.replace('Tầng ', '').trim();
                    if (currentTang !== tang) {
                        showRow = false;
                    }
                }

                // Lọc theo trạng thái
                if (trangThai) {
                    const trangThaiFromTable = trangthai.replace(/\s+/g, ' ').trim();
                    const trangThaiFromFilter = trangThai.replace(/\s+/g, ' ').trim();

                    if (trangThaiFromTable !== trangThaiFromFilter) {
                        showRow = false;
                    }
                }

                if (showRow) {
                    $row.show();
                    visibleCount++;
                } else {
                    $row.hide();
                }
            });

            // Cập nhật số lượng phòng hiển thị
            $('#roomCount').text(visibleCount);

            // Hiển thị thông báo nếu không có kết quả
            if (visibleCount === 0) {
                if ($('#noResults').length === 0) {
                    $('tbody').append('<tr id="noResults"><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-search me-2"></i>Không tìm thấy phòng nào phù hợp</td></tr>');
                }
            } else {
                $('#noResults').remove();
            }
        }

        // Xử lý submit form CẬP NHẬT TRẠNG THÁI
        $('#formTrangThai').submit(function(e) {
            e.preventDefault();

            const formData = $(this).serializeArray();
            const trangThaiMoi = formData.find(f => f.name === 'trangThaiMoi').value;
            const ghiChuKyThuat = formData.find(f => f.name === 'ghiChuKyThuat')?.value || '';
            const lyDo = formData.find(f => f.name === 'lyDo')?.value || '';

            const roomInfo = $('#roomInfoTrangThai').text();
            const maPhongMatch = roomInfo.match(/Mã:\s*(\d+)/);
            const maPhong = maPhongMatch ? maPhongMatch[1] : '';

            console.log("Gửi request cập nhật:", {
                maPhong,
                trangThaiMoi,
                ghiChuKyThuat,
                lyDo
            });

            // Hiển thị loading
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');
            submitBtn.prop('disabled', true);

            // Gửi request đến server
            $.ajax({
                url: '../../controller/buongphongQLPhong.controller.php', // ĐƯỜNG DẪN ĐÚNG
                type: 'POST',
                data: {
                    action: 'capNhatTrangThai',
                    maPhong: maPhong,
                    trangThai: trangThaiMoi,
                    ghiChuKyThuat: ghiChuKyThuat,
                    lyDo: lyDo
                },
                success: function(response) {
                    console.log("Nhận response:", response);

                    // Khôi phục button
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);

                    try {
                        const result = typeof response === 'object' ? response : JSON.parse(response);

                        if (result.success) {
                            $('#modalTrangThai').modal('hide');
                            alert('✅ ' + result.message);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            alert('❌ ' + result.message);
                        }
                    } catch (e) {
                        console.error("Lỗi parse JSON:", e);
                        alert('❌ Lỗi xử lý dữ liệu từ server');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Lỗi AJAX:", error);
                    console.log("Response text:", xhr.responseText);
                    submitBtn.html(originalText);
                    submitBtn.prop('disabled', false);
                    alert('❌ Lỗi kết nối server: ' + error);
                }
            });
        });
        // Xử lý submit form GHI NHẬN CHI PHÍ
        $('#formChiPhi').submit(function(e) {
            e.preventDefault();
            const formData = $(this).serializeArray();
            const roomInfo = $('#roomInfoChiPhi').text();
            const maPhong = $('#roomInfoChiPhi').text().match(/Mã: (\d+)/)[1];
            const loaiChiPhi = formData.find(f => f.name === 'loaiChiPhi').value;
            const soTien = formData.find(f => f.name === 'soTien').value;
            const ghiChu = formData.find(f => f.name === 'ghiChu').value;

            // Gửi request đến server
            $.ajax({
                url: '../controller/phongController.php',
                type: 'POST',
                data: {
                    action: 'ghiNhanChiPhi',
                    maPhong: maPhong,
                    loaiChiPhi: loaiChiPhi,
                    soTien: soTien,
                    ghiChu: ghiChu
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            alert('Ghi nhận chi phí thành công!');
                        } else {
                            alert('Lỗi: ' + result.message);
                        }
                    } catch (e) {
                        alert('Lỗi xử lý dữ liệu');
                    }
                },
                error: function() {
                    alert('Lỗi kết nối server');
                }
            });

            $('#modalChiPhi').modal('hide');
            $(this)[0].reset();
        });
    });
</script>

<?php include '../layouts/footer.php'; ?>