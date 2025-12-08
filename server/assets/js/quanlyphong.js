function suaPhong(maPhong) {
  console.log("Đang lấy thông tin phòng:", maPhong);

  // Hiển thị loading
  document.getElementById("suaSoPhongInfo").textContent =
    "Đang tải thông tin...";

  // Reset form
  document.getElementById("formSuaPhong").reset();
  document.getElementById("currentAvatar").innerHTML = "";
  document.getElementById("currentImages").innerHTML = "";
  document.getElementById("suaTienNghiKhac").style.display = "none";
  document.getElementById("suaTienNghiKhacCheck").checked = false;

  // GỌI CHÍNH FILE HIỆN TẠI với action lay_thong_tin
  // URL: quanlyphong.php?action=lay_thong_tin&ma_phong=XXX
  const url = `quanlyphong.php?action=lay_thong_tin&ma_phong=${maPhong}`;
  console.log("URL gọi:", url);

  fetch(url)
    .then((response) => {
      console.log("Response status:", response.status);
      console.log("Response type:", response.headers.get("content-type"));

      // Kiểm tra nếu không phải JSON
      const contentType = response.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        return response.text().then((text) => {
          console.error(
            "Server trả về không phải JSON:",
            text.substring(0, 200)
          );
          throw new Error("Server trả về HTML thay vì JSON");
        });
      }

      return response.json();
    })
    .then((data) => {
      console.log("Dữ liệu nhận được:", data);

      if (data.error) {
        throw new Error(data.error);
      }

      if (!data) {
        throw new Error("Không tìm thấy thông tin phòng");
      }

      // Điền dữ liệu vào form
      document.getElementById("suaMaPhong").value = maPhong;
      document.getElementById(
        "suaSoPhongInfo"
      ).textContent = `Số phòng: ${data.SoPhong}`;
      document.getElementById("suaTang").value = data.Tang;
      document.getElementById("suaMaLoaiPhong").value = data.MaLoaiPhong;
      document.getElementById("suaGiaPhong").value = data.GiaPhong;
      document.getElementById("suaTrangThai").value = data.TrangThai;
      document.getElementById("suaDienTich").value = data.DienTich;
      document.getElementById("suaSoKhachToiDa").value = data.SoKhachToiDa;
      document.getElementById("suaHuongNha").value = data.HuongNha || "";
      document.getElementById("suaRoomName").value = data.roomName;
      document.getElementById("suaMoTaChiTiet").value = data.MoTaChiTiet || "";

      // Tính tổng giá
      calculateTongGiaSua();

      // Xử lý tiện nghi
      const tienNghi = data.TienNghi || [];
      document.querySelectorAll(".sua-tien-nghi").forEach((checkbox) => {
        checkbox.checked = tienNghi.includes(checkbox.value);
      });

      // Xử lý tiện nghi khác
      const tienNghiCoSan = [
        "Điều hòa",
        "TV màn hình phẳng",
        "Minibar",
        "Ban công",
        "Bồn tắm",
        "Vòi sen",
        "Wifi miễn phí",
        "Bếp nhỏ",
      ];
      const tienNghiKhac = tienNghi.filter((tn) => !tienNghiCoSan.includes(tn));

      if (tienNghiKhac.length > 0) {
        document.getElementById("suaTienNghiKhacCheck").checked = true;
        document.getElementById("suaTienNghiKhac").style.display = "block";
        document.getElementById("suaTienNghiKhac").value =
          tienNghiKhac.join("\n");
      }

      // === HIỂN THỊ ẢNH AVATAR ===
      if (data.Avatar) {
        console.log("Avatar path từ DB:", data.Avatar);

        // Avatar từ DB: "room203/avatar1"
        const avatarUrl = `../../client/assets/images/rooms/${data.Avatar}.jpeg`;

        // Lấy tên file đẹp hơn (room203/avatar1 → Avatar 1 - Room 203)
        const parts = data.Avatar.split("/");
        const roomName = parts[0] ? parts[0].replace("room", "Phòng ") : "";
        const avatarName = parts[1] ? parts[1].replace("avatar", "Ảnh ") : "";

        const avatarHTML = `
        <div class="alert alert-light border">
            <small class="text-muted d-block mb-2">Ảnh đại diện hiện tại</small>
            <div class="text-center">
                <img src="${avatarUrl}" 
                     style="width: 120px; height: 120px; object-fit: cover;" 
                     class="img-thumbnail border rounded"
                     onerror="this.onerror=null; this.src='../../assets/images/no-image.jpg'">
                <div class="mt-2">
                    <small class="text-muted">${avatarName} • ${roomName}</small>
                </div>
            </div>
        </div>
    `;

        document.getElementById("currentAvatar").innerHTML = avatarHTML;
      } else {
        document.getElementById("currentAvatar").innerHTML =
          '<div class="alert alert-light border"><small class="text-muted">Không có ảnh đại diện</small></div>';
      }

      // === HIỂN THỊ DANH SÁCH ẢNH ===
      let danhSachAnh = [];

      // Xử lý DanhSachPhong
      if (data.DanhSachPhong) {
        if (typeof data.DanhSachPhong === "string") {
          try {
            danhSachAnh = JSON.parse(data.DanhSachPhong);
          } catch (e) {
            console.error("Lỗi parse JSON:", e);
            danhSachAnh = [];
          }
        } else if (Array.isArray(data.DanhSachPhong)) {
          danhSachAnh = data.DanhSachPhong;
        }
      }

      console.log("Danh sách ảnh sau xử lý:", danhSachAnh);

      // Lọc ra chỉ ảnh chi tiết (không phải avatar)
      const anhChiTiet = danhSachAnh.filter((imgPath) => {
        return (
          imgPath && imgPath !== data.Avatar && !imgPath.includes("avatar")
        );
      });

      console.log("Ảnh chi tiết sau lọc:", anhChiTiet);

      if (anhChiTiet.length > 0) {
        let imagesHTML = '<div class="alert alert-light border">';
        imagesHTML +=
          '<small class="text-muted d-block mb-3">📸 Ảnh chi tiết hiện tại</small>';
        imagesHTML += '<div class="row g-2">';

        anhChiTiet.forEach((imgPath, index) => {
          const fullImageUrl = `../../client/assets/images/rooms/${imgPath}.jpeg`;

          // Tạo tên hiển thị đẹp
          const fileName = imgPath.split("/").pop() || "";
          const displayName = fileName.replace("chitiet", "Chi tiết ");

          imagesHTML += `
            <div class="col-6 col-md-4">
                <div class="card border position-relative">
                    <img src="${fullImageUrl}" 
                         style="height: 100px; object-fit: cover;" 
                         class="card-img-top"
                         onerror="this.onerror=null; this.src='../../assets/images/no-image.jpg'">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">${displayName}</small>
                            <button type="button" 
                                    class="btn btn-sm btn-outline-danger btn-sm py-0 px-2"
                                    style="font-size: 11px;"
                                    onclick="xoaAnhChiTiet(${maPhong}, '${imgPath}', this)">
                                <small>Xóa</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        });

        imagesHTML += "</div></div>";
        document.getElementById("currentImages").innerHTML = imagesHTML;
      } else {
        document.getElementById("currentImages").innerHTML =
          '<div class="alert alert-light border">' +
          '<small class="text-muted">📷 Chưa có ảnh chi tiết nào</small>' +
          "</div>";
      }
      // Hiển thị modal
      const modal = new bootstrap.Modal(
        document.getElementById("suaPhongModal")
      );
      modal.show();
    })
    .catch((error) => {
      console.error("Lỗi:", error);
      alert("Lỗi khi tải thông tin phòng: " + error.message);
    });
}
// HÀM ĐIỀN DỮ LIỆU RIÊNG
function fillFormData(phong, maPhong) {
  // Điền dữ liệu vào form
  document.getElementById("suaMaPhong").value = maPhong;
  document.getElementById(
    "suaSoPhongInfo"
  ).textContent = `Số phòng: ${phong.SoPhong}`;
  document.getElementById("suaTang").value = phong.Tang;
  document.getElementById("suaMaLoaiPhong").value = phong.MaLoaiPhong;
  document.getElementById("suaGiaPhong").value = phong.GiaPhong;
  document.getElementById("suaTrangThai").value = phong.TrangThai;
  document.getElementById("suaDienTich").value = phong.DienTich;
  document.getElementById("suaSoKhachToiDa").value = phong.SoKhachToiDa;
  document.getElementById("suaHuongNha").value = phong.HuongNha || "";
  document.getElementById("suaRoomName").value = phong.roomName;
  document.getElementById("suaMoTaChiTiet").value = phong.MoTaChiTiet || "";

  // Tính tổng giá
  calculateTongGiaSua();

  // Xử lý tiện nghi
  const tienNghi = phong.TienNghi || [];
  document.querySelectorAll(".sua-tien-nghi").forEach((checkbox) => {
    checkbox.checked = tienNghi.includes(checkbox.value);
  });

  // Xử lý tiện nghi khác
  const tienNghiCoSan = [
    "Điều hòa",
    "TV màn hình phẳng",
    "Minibar",
    "Ban công",
    "Bồn tắm",
    "Vòi sen",
    "Wifi miễn phí",
    "Bếp nhỏ",
  ];
  const tienNghiKhac = tienNghi.filter((tn) => !tienNghiCoSan.includes(tn));

  if (tienNghiKhac.length > 0) {
    document.getElementById("suaTienNghiKhacCheck").checked = true;
    document.getElementById("suaTienNghiKhac").style.display = "block";
    document.getElementById("suaTienNghiKhac").value = tienNghiKhac.join("\n");
  }

  // Trong hàm fillFormData hoặc suaPhong, sửa phần hiển thị ảnh:
  if (phong.Avatar) {
    const avatarPath = phong.Avatar.includes(".jpeg")
      ? phong.Avatar
      : phong.Avatar + ".jpeg";
    document.getElementById("currentAvatar").innerHTML = `
        <small class="text-muted">Ảnh đại diện hiện tại:</small><br>
        <img src="../../client/assets/images/rooms/${avatarPath}" 
             style="max-width: 120px; max-height: 120px;" 
             class="img-thumbnail mt-1 border"
             onerror="this.onerror=null; this.src='../../assets/images/default.jpg'">
    `;
  }

  if (phong.DanhSachPhong && phong.DanhSachPhong.length > 0) {
    let imagesHTML =
      '<small class="text-muted">Ảnh chi tiết hiện tại:</small><br>';
    phong.DanhSachPhong.forEach((img) => {
      if (img && img !== phong.Avatar) {
        const imgPath = img.includes(".jpeg") ? img : img + ".jpeg";
        imagesHTML += `
                <div class="position-relative d-inline-block m-1">
                    <img src="../../client/assets/images/rooms/${imgPath}" 
                         style="max-width: 80px; max-height: 80px;" 
                         class="img-thumbnail border"
                         onerror="this.onerror=null; this.style.display='none'">
                </div>
            `;
      }
    });
    document.getElementById("currentImages").innerHTML = imagesHTML;
  }
}
// Tính tổng giá cho form sửa
function calculateTongGiaSua() {
  const selectLoaiPhong = document.getElementById("suaMaLoaiPhong");
  const giaPhong =
    parseFloat(document.getElementById("suaGiaPhong").value) || 0;
  const selectedOption = selectLoaiPhong.options[selectLoaiPhong.selectedIndex];
  const donGiaLoaiPhong =
    parseFloat(selectedOption.getAttribute("data-dongia")) || 0;

  const tongGia = giaPhong + donGiaLoaiPhong;
  document.getElementById("suaTongGia").value =
    tongGia.toLocaleString("vi-VN") + " đ";
}

// Thêm event listeners cho form sửa
document
  .getElementById("suaMaLoaiPhong")
  .addEventListener("change", calculateTongGiaSua);
document
  .getElementById("suaGiaPhong")
  .addEventListener("input", calculateTongGiaSua);

// Tiện nghi khác cho form sửa
document
  .getElementById("suaTienNghiKhacCheck")
  .addEventListener("change", function () {
    const textarea = document.getElementById("suaTienNghiKhac");
    textarea.style.display = this.checked ? "block" : "none";
    if (!this.checked) {
      textarea.value = "";
    }
  });

// Preview ảnh cho form sửa
document
  .getElementById("suaAvatarUpload")
  .addEventListener("change", function (e) {
    const preview = document.getElementById("suaAvatarPreview");
    preview.innerHTML = "";

    if (this.files && this.files[0]) {
      const reader = new FileReader();
      reader.onload = function (e) {
        const img = document.createElement("img");
        img.src = e.target.result;
        img.style.maxWidth = "120px";
        img.style.maxHeight = "120px";
        img.className = "img-thumbnail mt-2 border";
        preview.appendChild(img);
      };
      reader.readAsDataURL(this.files[0]);
    }
  });

// Xử lý form submit sửa
document
  .getElementById("formSuaPhong")
  .addEventListener("submit", function (e) {
    const selectedTienNghi = Array.from(
      document.querySelectorAll(".sua-tien-nghi:checked")
    ).map((checkbox) => checkbox.value);

    const tienNghiKhac = document.getElementById("suaTienNghiKhac").value;
    if (tienNghiKhac) {
      const lines = tienNghiKhac
        .split("\n")
        .map((line) => line.trim())
        .filter((line) => line !== "");
      selectedTienNghi.push(...lines);
    }

    const hiddenInput = document.createElement("input");
    hiddenInput.type = "hidden";
    hiddenInput.name = "tien_nghi_json";
    hiddenInput.value = JSON.stringify(selectedTienNghi);
    this.appendChild(hiddenInput);
  });
// Chọn tất cả
document.getElementById("checkAll").addEventListener("change", function () {
  const checkboxes = document.querySelectorAll(".checkPhong");
  checkboxes.forEach((checkbox) => {
    checkbox.checked = this.checked;
  });
  updateXoaNhieuButton();
});

// Cập nhật nút xóa nhiều
function updateXoaNhieuButton() {
  const checkedCount = document.querySelectorAll(".checkPhong:checked").length;
  const btn = document.getElementById("xoaNhieuPhong");
  btn.disabled = checkedCount === 0;
  btn.textContent = `Xóa (${checkedCount})`;
}

// Xóa nhiều phòng
document.getElementById("xoaNhieuPhong").addEventListener("click", function () {
  const checkedCount = document.querySelectorAll(".checkPhong:checked").length;
  if (
    checkedCount > 0 &&
    confirm(`Bạn có chắc muốn xóa ${checkedCount} phòng đã chọn?`)
  ) {
    document.getElementById("formXoaNhieu").submit();
  }
});

// Xóa từng phòng
function xoaPhong(maPhong, soPhong) {
  if (confirm(`Bạn có chắc muốn xóa phòng ${soPhong}?`)) {
    document.getElementById("maPhongXoa").value = maPhong;
    document.getElementById("formXoaPhong").submit();
  }
}

// Tính tổng giá
document
  .getElementById("selectLoaiPhong")
  .addEventListener("change", calculateTongGia);
document.getElementById("giaPhong").addEventListener("input", calculateTongGia);

function calculateTongGia() {
  const selectLoaiPhong = document.getElementById("selectLoaiPhong");
  const giaPhong = parseFloat(document.getElementById("giaPhong").value) || 0;
  const selectedOption = selectLoaiPhong.options[selectLoaiPhong.selectedIndex];
  const donGiaLoaiPhong =
    parseFloat(selectedOption.getAttribute("data-dongia")) || 0;

  const tongGia = giaPhong + donGiaLoaiPhong;
  document.getElementById("tongGia").value =
    tongGia.toLocaleString("vi-VN") + " đ";
}

// Tiện nghi khác
document
  .getElementById("tienNghiKhacCheck")
  .addEventListener("change", function () {
    const textarea = document.getElementById("tienNghiKhac");
    textarea.style.display = this.checked ? "block" : "none";
  });

// Preview ảnh
document
  .getElementById("avatarUpload")
  .addEventListener("change", function (e) {
    const preview = document.getElementById("avatarPreview");
    preview.innerHTML = "";

    if (this.files && this.files[0]) {
      const reader = new FileReader();
      reader.onload = function (e) {
        const img = document.createElement("img");
        img.src = e.target.result;
        img.style.maxWidth = "120px";
        img.style.maxHeight = "120px";
        img.className = "img-thumbnail mt-2 border";
        preview.appendChild(img);
      };
      reader.readAsDataURL(this.files[0]);
    }
  });

// Xử lý form submit
document
  .getElementById("formThemPhong")
  .addEventListener("submit", function (e) {
    const selectedTienNghi = Array.from(
      document.querySelectorAll('input[name="tien_nghi[]"]:checked')
    ).map((checkbox) => checkbox.value);

    const tienNghiKhac = document.getElementById("tienNghiKhac").value;
    if (tienNghiKhac) {
      const lines = tienNghiKhac
        .split("\n")
        .map((line) => line.trim())
        .filter((line) => line !== "");
      selectedTienNghi.push(...lines);
    }

    const hiddenInput = document.createElement("input");
    hiddenInput.type = "hidden";
    hiddenInput.name = "tien_nghi_json";
    hiddenInput.value = JSON.stringify(selectedTienNghi);
    this.appendChild(hiddenInput);
  });

// Cập nhật trạng thái checkbox khi click
document.addEventListener("DOMContentLoaded", function () {
  const checkboxes = document.querySelectorAll(".checkPhong");
  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", updateXoaNhieuButton);
  });
});
// THÊM HÀM XÓA ẢNH CHI TIẾT
function xoaAnhChiTiet(maPhong, imgPath, element) {
  if (!confirm("Bạn có chắc muốn xóa ảnh này?")) {
    return;
  }

  fetch("quanlyphong.php?action=xoa_anh", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `ma_phong=${maPhong}&img_path=${encodeURIComponent(imgPath)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Xóa ảnh khỏi giao diện
        element.parentElement.remove();
        alert("Đã xóa ảnh thành công!");
      } else {
        alert("Lỗi: " + data.error);
      }
    })
    .catch((error) => {
      console.error("Lỗi:", error);
      alert("Lỗi khi xóa ảnh!");
    });
}

// Thêm nút xóa cho ảnh chi tiết (sửa phần hiển thị ảnh)
// TÌM DÒNG 80-95 và SỬA THÀNH:
if (phong.DanhSachPhong && phong.DanhSachPhong.length > 0) {
  let imagesHTML = '<small class="text-muted">Ảnh hiện tại:</small><br>';
  phong.DanhSachPhong.forEach((img) => {
    if (img && img !== phong.Avatar) {
      imagesHTML += `
                <div style="position: relative; display: inline-block; margin: 5px;">
                    <img src="../../client/assets/images/rooms/${img}" 
                         style="max-width: 80px; max-height: 80px;" 
                         class="img-thumbnail m-1 border"
                         onerror="this.style.display='none'">
                    <button type="button" 
                            style="position: absolute; top: 0; right: 0; background: red; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; cursor: pointer;"
                            onclick="xoaAnhChiTiet(${maPhong}, '${img}', this)">
                        ×
                    </button>
                </div>
            `;
    }
  });
  document.getElementById("currentImages").innerHTML = imagesHTML;
}
