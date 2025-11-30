
    // Sửa phòng - Hiển thị modal với dữ liệu
    function suaPhong(maPhong) {
        // Hiển thị loading
        document.getElementById('suaSoPhongInfo').textContent = 'Đang tải thông tin...';

        // Reset form trước khi load dữ liệu mới
        document.getElementById('formSuaPhong').reset();
        document.getElementById('currentAvatar').innerHTML = '';
        document.getElementById('currentImages').innerHTML = '';
        document.getElementById('suaTienNghiKhac').style.display = 'none';
        document.getElementById('suaTienNghiKhacCheck').checked = false;

        fetch(`quanlyphong.php?action=lay_thong_tin&ma_phong=${maPhong}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Lỗi kết nối: ' + response.status);
                }
                return response.json();
            })
            .then(phong => {
                if (!phong) {
                    throw new Error('Không tìm thấy thông tin phòng');
                }

                // Điền dữ liệu vào form
                document.getElementById('suaMaPhong').value = maPhong;
                document.getElementById('suaSoPhongInfo').textContent = `Số phòng: ${phong.SoPhong}`;
                document.getElementById('suaTang').value = phong.Tang;
                document.getElementById('suaMaLoaiPhong').value = phong.MaLoaiPhong;
                document.getElementById('suaGiaPhong').value = phong.GiaPhong;
                document.getElementById('suaTrangThai').value = phong.TrangThai;
                document.getElementById('suaDienTich').value = phong.DienTich;
                document.getElementById('suaSoKhachToiDa').value = phong.SoKhachToiDa;
                document.getElementById('suaHuongNha').value = phong.HuongNha || '';
                document.getElementById('suaRoomName').value = phong.roomName;
                document.getElementById('suaMoTaChiTiet').value = phong.MoTaChiTiet || '';

                // Tính tổng giá
                calculateTongGiaSua();

                // Xử lý tiện nghi
                const tienNghi = phong.TienNghi || [];
                document.querySelectorAll('.sua-tien-nghi').forEach(checkbox => {
                    checkbox.checked = tienNghi.includes(checkbox.value);
                });

                // Xử lý tiện nghi khác
                const tienNghiCoSan = ['Điều hòa', 'TV màn hình phẳng', 'Minibar', 'Ban công',
                    'Bồn tắm', 'Vòi sen', 'Wifi miễn phí', 'Bếp nhỏ'
                ];
                const tienNghiKhac = tienNghi.filter(tn => !tienNghiCoSan.includes(tn));

                if (tienNghiKhac.length > 0) {
                    document.getElementById('suaTienNghiKhacCheck').checked = true;
                    document.getElementById('suaTienNghiKhac').style.display = 'block';
                    document.getElementById('suaTienNghiKhac').value = tienNghiKhac.join('\n');
                }

                // Hiển thị ảnh hiện tại
                if (phong.Avatar) {
                    document.getElementById('currentAvatar').innerHTML = `
                    <small class="text-muted">Ảnh hiện tại:</small><br>
                    <img src="../../client/assets/images/rooms/${phong.Avatar}.jpeg" 
                         style="max-width: 120px; max-height: 120px;" 
                         class="img-thumbnail mt-1 border"
                         onerror="this.src='../../client/assets/images/no-image.jpg'">
                `;
                }

                // Hiển thị danh sách ảnh hiện tại
                if (phong.DanhSachPhong && phong.DanhSachPhong.length > 0) {
                    let imagesHTML = '<small class="text-muted">Ảnh hiện tại:</small><br>';
                    phong.DanhSachPhong.forEach(img => {
                        if (img && img !== phong.Avatar) { // Không hiển thị avatar trùng
                            imagesHTML += `
                            <img src="../../client/assets/images/rooms/${img}.jpeg" 
                                 style="max-width: 80px; max-height: 80px;" 
                                 class="img-thumbnail m-1 border"
                                 onerror="this.style.display='none'">
                        `;
                        }
                    });
                    document.getElementById('currentImages').innerHTML = imagesHTML;
                }

                // Hiển thị modal
                const modal = new bootstrap.Modal(document.getElementById('suaPhongModal'));
                modal.show();
            })
            .catch(error => {
                console.error('Lỗi:', error);
                alert('Lỗi khi tải thông tin phòng: ' + error.message);
            });
    }

    // Tính tổng giá cho form sửa
    function calculateTongGiaSua() {
        const selectLoaiPhong = document.getElementById('suaMaLoaiPhong');
        const giaPhong = parseFloat(document.getElementById('suaGiaPhong').value) || 0;
        const selectedOption = selectLoaiPhong.options[selectLoaiPhong.selectedIndex];
        const donGiaLoaiPhong = parseFloat(selectedOption.getAttribute('data-dongia')) || 0;

        const tongGia = giaPhong + donGiaLoaiPhong;
        document.getElementById('suaTongGia').value = tongGia.toLocaleString('vi-VN') + ' đ';
    }

    // Thêm event listeners cho form sửa
    document.getElementById('suaMaLoaiPhong').addEventListener('change', calculateTongGiaSua);
    document.getElementById('suaGiaPhong').addEventListener('input', calculateTongGiaSua);

    // Tiện nghi khác cho form sửa
    document.getElementById('suaTienNghiKhacCheck').addEventListener('change', function() {
        const textarea = document.getElementById('suaTienNghiKhac');
        textarea.style.display = this.checked ? 'block' : 'none';
        if (!this.checked) {
            textarea.value = '';
        }
    });

    // Preview ảnh cho form sửa
    document.getElementById('suaAvatarUpload').addEventListener('change', function(e) {
        const preview = document.getElementById('suaAvatarPreview');
        preview.innerHTML = '';

        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '120px';
                img.style.maxHeight = '120px';
                img.className = 'img-thumbnail mt-2 border';
                preview.appendChild(img);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Xử lý form submit sửa
    document.getElementById('formSuaPhong').addEventListener('submit', function(e) {
        const selectedTienNghi = Array.from(document.querySelectorAll('.sua-tien-nghi:checked'))
            .map(checkbox => checkbox.value);

        const tienNghiKhac = document.getElementById('suaTienNghiKhac').value;
        if (tienNghiKhac) {
            const lines = tienNghiKhac.split('\n')
                .map(line => line.trim())
                .filter(line => line !== '');
            selectedTienNghi.push(...lines);
        }

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'tien_nghi_json';
        hiddenInput.value = JSON.stringify(selectedTienNghi);
        this.appendChild(hiddenInput);
    });
    // Chọn tất cả
    document.getElementById('checkAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.checkPhong');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateXoaNhieuButton();
    });

    // Cập nhật nút xóa nhiều
    function updateXoaNhieuButton() {
        const checkedCount = document.querySelectorAll('.checkPhong:checked').length;
        const btn = document.getElementById('xoaNhieuPhong');
        btn.disabled = checkedCount === 0;
        btn.textContent = `Xóa (${checkedCount})`;
    }

    // Xóa nhiều phòng
    document.getElementById('xoaNhieuPhong').addEventListener('click', function() {
        const checkedCount = document.querySelectorAll('.checkPhong:checked').length;
        if (checkedCount > 0 && confirm(`Bạn có chắc muốn xóa ${checkedCount} phòng đã chọn?`)) {
            document.getElementById('formXoaNhieu').submit();
        }
    });

    // Xóa từng phòng
    function xoaPhong(maPhong, soPhong) {
        if (confirm(`Bạn có chắc muốn xóa phòng ${soPhong}?`)) {
            document.getElementById('maPhongXoa').value = maPhong;
            document.getElementById('formXoaPhong').submit();
        }
    }


    // Tính tổng giá
    document.getElementById('selectLoaiPhong').addEventListener('change', calculateTongGia);
    document.getElementById('giaPhong').addEventListener('input', calculateTongGia);

    function calculateTongGia() {
        const selectLoaiPhong = document.getElementById('selectLoaiPhong');
        const giaPhong = parseFloat(document.getElementById('giaPhong').value) || 0;
        const selectedOption = selectLoaiPhong.options[selectLoaiPhong.selectedIndex];
        const donGiaLoaiPhong = parseFloat(selectedOption.getAttribute('data-dongia')) || 0;

        const tongGia = giaPhong + donGiaLoaiPhong;
        document.getElementById('tongGia').value = tongGia.toLocaleString('vi-VN') + ' đ';
    }

    // Tiện nghi khác
    document.getElementById('tienNghiKhacCheck').addEventListener('change', function() {
        const textarea = document.getElementById('tienNghiKhac');
        textarea.style.display = this.checked ? 'block' : 'none';
    });

    // Preview ảnh
    document.getElementById('avatarUpload').addEventListener('change', function(e) {
        const preview = document.getElementById('avatarPreview');
        preview.innerHTML = '';

        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '120px';
                img.style.maxHeight = '120px';
                img.className = 'img-thumbnail mt-2 border';
                preview.appendChild(img);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Xử lý form submit
    document.getElementById('formThemPhong').addEventListener('submit', function(e) {
        const selectedTienNghi = Array.from(document.querySelectorAll('input[name="tien_nghi[]"]:checked'))
            .map(checkbox => checkbox.value);

        const tienNghiKhac = document.getElementById('tienNghiKhac').value;
        if (tienNghiKhac) {
            const lines = tienNghiKhac.split('\n')
                .map(line => line.trim())
                .filter(line => line !== '');
            selectedTienNghi.push(...lines);
        }

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'tien_nghi_json';
        hiddenInput.value = JSON.stringify(selectedTienNghi);
        this.appendChild(hiddenInput);
    });

    // Cập nhật trạng thái checkbox khi click
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.checkPhong');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateXoaNhieuButton);
        });
    });

