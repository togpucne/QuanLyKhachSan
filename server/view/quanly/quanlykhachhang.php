<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khách hàng - ABC Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #F3EEEA;
            font-family: "Segoe UI", sans-serif;
        }

        .sidebar {
            background-color: #292D32;
            color: white;
            height: 100vh;
            width: 250px;
            position: fixed;
            padding-top: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar h4 {
            text-align: center;
            color: #E8DFCA;
            font-weight: bold;
        }

        .sidebar a {
            color: #E8DFCA;
            text-decoration: none;
            display: block;
            padding: 10px 20px;
            transition: 0.3s;
            font-size: 15px;
        }

        .sidebar a:hover {
            background-color: #3b4148;
            border-radius: 5px;
        }

        .sidebar .logout {
            color: #ff6b6b;
            margin: 15px 20px;
            border-top: 1px solid #444;
            padding-top: 10px;
        }

        .content {
            margin-left: 260px;
            padding: 20px;
        }

        .topbar {
            background: #A9907E;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table thead {
            background-color: #E8DFCA;
        }

        .btn-action {
            border: none;
            background: none;
            cursor: pointer;
            color: #A9907E;
            font-size: 18px;
        }

        .btn-action:hover {
            color: #6d5e51;
        }

        .filter-bar {
            background: #E8DFCA;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .footer {
            text-align: center;
            color: #777;
            margin-top: 30px;
            font-size: 14px;
        }

        .btn-custom {
            background-color: #A9907E;
            color: white;
            border: none;
        }

        .btn-custom:hover {
            background-color: #8b7b6c;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <h4>🏨 ABC Resort</h4>
            <hr class="text-light mx-3">
            <a href="dashboard.html"><i class="fa-solid fa-chart-line me-2"></i>Tổng quan</a>
            <a href="quanlynhanvien.html" class="fw-bold bg-dark rounded-2"><i
                    class="fa-solid fa-user-tie me-2"></i>Quản lý nhân viên</a>
            <a href="quanlykhachhang.html"><i class="fa-solid fa-users me-2"></i>Quản lý khách hàng</a>
            <a href="quanlydichvu.html"><i class="fa-solid fa-concierge-bell me-2"></i>Quản lý dịch vụ</a>
            <a href="quanlyphong.html"><i class="fa-solid fa-bed me-2"></i>Quản lý phòng</a>
        </div>

        <div class="logout">
            <a href="log-in.html"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</a>
        </div>
    </div>

    <!-- Nội dung -->
    <div class="content">
        <div class="topbar">
            <h5><i class="fa-solid fa-users me-2"></i>Quản lý khách hàng</h5>
            <div>
                Xin chào, <b id="usernameDisplay">admin</b>!
                <button class="btn btn-light btn-sm" onclick="logout()">Đăng xuất</button>
            </div>
        </div>

        <!-- Thanh tìm kiếm -->
        <div class="filter-bar mt-4">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" class="form-control" id="searchInput" placeholder="🔍 Tìm theo tên, mã KH...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterType">
                        <option value="">-- Loại khách --</option>
                        <option>VIP</option>
                        <option>Thường</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterStatus">
                        <option value="">-- Trạng thái --</option>
                        <option>Hoạt động</option>
                        <option>Ngừng</option>
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <button class="btn btn-custom" id="addBtn"><i class="fa-solid fa-plus me-1"></i>Thêm khách
                        hàng</button>
                    <button class="btn btn-danger" id="deleteSelected"><i class="fa-solid fa-trash me-1"></i>Xóa
                        nhiều</button>
                </div>
            </div>
        </div>

        <!-- Bảng danh sách -->
        <div class="table-responsive shadow-sm bg-white rounded p-3">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>STT</th>
                        <th>Mã KH</th>
                        <th>Họ tên</th>
                        <th>Đoàn khách</th> <!-- ✅ thêm cột đoàn -->
                        <th>Loại KH</th>
                        <th>Ngày đăng ký</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="customerTable"></tbody>
            </table>
        </div>


        <div class="footer">© 2025 - Nhóm Tỏa Sáng | ABC Resort Management System</div>
    </div>

    <!-- Modal thêm/sửa -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalTitle">Thêm khách hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="detailForm">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Mã KH</label><input type="text" id="cusId"
                                    class="form-control" readonly></div>
                            <div class="col-md-6"><label class="form-label">Họ tên</label><input type="text"
                                    id="cusName" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Giới tính</label><select id="cusGender"
                                    class="form-select">
                                    <option>Nam</option>
                                    <option>Nữ</option>
                                </select></div>
                            <div class="col-md-6"><label class="form-label">Ngày sinh</label><input type="date"
                                    id="cusBirth" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">SĐT</label><input type="text" id="cusPhone"
                                    class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Email</label><input type="email"
                                    id="cusEmail" class="form-control"></div>
                            <div class="col-md-12"><label class="form-label">Địa chỉ</label><input type="text"
                                    id="cusAddress" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Đoàn khách</label><input type="text"
                                    id="cusGroup" class="form-control" placeholder="VD: Đoàn Hà Nội"></div>
                            <!-- ✅ thêm trường đoàn -->
                            <div class="col-md-6"><label class="form-label">Loại khách</label><select id="cusType"
                                    class="form-select">
                                    <option>VIP</option>
                                    <option>Thường</option>
                                </select></div>
                            <div class="col-md-6"><label class="form-label">Ngày đăng ký</label><input type="date"
                                    id="cusJoin" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Trạng thái</label><select id="cusStatus"
                                    class="form-select">
                                    <option>Hoạt động</option>
                                    <option>Ngừng</option>
                                </select></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-success" id="saveChanges">Lưu</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ===== DỮ LIỆU MẪU =====
        let customers = JSON.parse(localStorage.getItem("customers")) || [
            { id: "KH001", name: "Phạm Minh Khang", group: "Đoàn Hà Nội", gender: "Nam", birth: "1998-06-12", phone: "0912345678", email: "khang@gmail.com", address: "Hà Nội", type: "VIP", join: "2023-03-01", status: "Hoạt động" },
            { id: "KH002", name: "Nguyễn Thị Hạnh", group: "Đoàn FPT", gender: "Nữ", birth: "2001-01-10", phone: "0987654321", email: "hanh@gmail.com", address: "TP.HCM", type: "Thường", join: "2024-04-12", status: "Hoạt động" }
        ];
        const tbody = document.getElementById("customerTable");
        const modal = new bootstrap.Modal("#detailModal");
        let editIndex = null;

        // ===== HIỂN THỊ BẢNG =====
        function renderTable() {
            tbody.innerHTML = customers.map((c, i) => `
      <tr>
        <td><input type="checkbox" class="selectCus" data-index="${i}"></td>
        <td>${i + 1}</td>
        <td>${c.id}</td>
        <td>${c.name}</td>
        <td>${c.group || ""}</td> <!-- ✅ hiển thị đoàn -->
        <td>${c.type}</td>
        <td>${c.join}</td>
        <td><span class="badge ${c.status === "Hoạt động" ? "bg-success" : "bg-secondary"}">${c.status}</span></td>
        <td>
          <button class="btn-action text-info btn-edit" data-index="${i}"><i class="fa-solid fa-pen-to-square"></i></button>
          <button class="btn-action text-danger btn-del" data-index="${i}"><i class="fa-solid fa-trash"></i></button>
        </td>
      </tr>`).join("");
        }
        renderTable();

        // Tạo mã KH mới (VD: KH003)
        function generateNewId() {
            const maxNum = customers.reduce((max, c) => Math.max(max, parseInt(c.id.replace("KH", ""))), 0);
            return "KH" + String(maxNum + 1).padStart(3, "0");
        }

        // ===== THÊM KH =====
        document.getElementById("addBtn").onclick = () => {
            editIndex = null;
            document.getElementById("modalTitle").textContent = "Thêm khách hàng";
            document.getElementById("detailForm").reset();
            cusId.value = generateNewId();
            cusJoin.value = new Date().toISOString().split("T")[0];
            cusStatus.value = "Hoạt động";
            modal.show();
        };

        // ===== THÊM / SỬA =====
        document.getElementById("saveChanges").onclick = () => {
            const newCus = {
                id: cusId.value, name: cusName.value, group: cusGroup.value, gender: cusGender.value, birth: cusBirth.value,
                phone: cusPhone.value, email: cusEmail.value, address: cusAddress.value,
                type: cusType.value, join: cusJoin.value, status: cusStatus.value
            };
            if (editIndex !== null) customers[editIndex] = newCus;
            else customers.push(newCus);
            localStorage.setItem("customers", JSON.stringify(customers));
            renderTable();
            modal.hide();
            alert(editIndex !== null ? "✅ Cập nhật thành công!" : "✅ Thêm khách hàng mới thành công!");
        };

        // ===== XÓA / SỬA =====
        tbody.addEventListener("click", e => {
            if (e.target.closest(".btn-del")) {
                const i = e.target.closest(".btn-del").dataset.index;
                if (confirm(`Xóa khách hàng ${customers[i].name}?`)) {
                    customers.splice(i, 1);
                    localStorage.setItem("customers", JSON.stringify(customers));
                    renderTable();
                }
            }
            if (e.target.closest(".btn-edit")) {
                editIndex = e.target.closest(".btn-edit").dataset.index;
                const c = customers[editIndex];
                document.getElementById("modalTitle").textContent = "Chỉnh sửa khách hàng";
                Object.keys(c).forEach(k => {
                    const el = document.getElementById("cus" + k.charAt(0).toUpperCase() + k.slice(1));
                    if (el) el.value = c[k];
                });
                modal.show();
            }
        });

        // ===== XÓA NHIỀU =====
        document.getElementById("deleteSelected").onclick = () => {
            const checked = document.querySelectorAll(".selectCus:checked");
            if (checked.length === 0) return alert("⚠️ Chọn ít nhất 1 khách hàng!");
            if (!confirm(`Xóa ${checked.length} khách hàng đã chọn?`)) return;
            const indexes = Array.from(checked).map(c => parseInt(c.dataset.index));
            customers = customers.filter((_, i) => !indexes.includes(i));
            localStorage.setItem("customers", JSON.stringify(customers));
            renderTable();
        };

        document.getElementById("selectAll").onchange = e => {
            document.querySelectorAll(".selectCus").forEach(cb => cb.checked = e.target.checked);
        };

        // ===== USER LOGIN =====
        const user = localStorage.getItem("user");
        if (user) document.getElementById("usernameDisplay").textContent = user;
        else window.location.href = "log-in.html";

        function logout() {
            localStorage.removeItem("user");
            window.location.href = "log-in.html";
        }
        // ===== TÌM KIẾM & BỘ LỌC =====
        function applyFilters() {
            const keyword = document.getElementById("searchInput").value.toLowerCase().trim();
            const typeFilter = document.getElementById("filterType").value;
            const statusFilter = document.getElementById("filterStatus").value;

            const filtered = customers.filter(c => {
                const matchKeyword =
                    c.name.toLowerCase().includes(keyword) ||
                    c.id.toLowerCase().includes(keyword) ||
                    (c.group && c.group.toLowerCase().includes(keyword));

                const matchType = !typeFilter || c.type === typeFilter;
                const matchStatus = !statusFilter || c.status === statusFilter;

                return matchKeyword && matchType && matchStatus;
            });

            tbody.innerHTML = filtered.map((c, i) => `
      <tr>
        <td><input type="checkbox" class="selectCus"></td>
        <td>${i + 1}</td>
        <td>${c.id}</td>
        <td>${c.name}</td>
        <td>${c.group || ""}</td>
        <td>${c.type}</td>
        <td>${c.join}</td>
        <td><span class="badge ${c.status === "Hoạt động" ? "bg-success" : "bg-secondary"}">${c.status}</span></td>
        <td>
          <button class="btn-action text-info btn-edit" data-index="${customers.indexOf(c)}"><i class="fa-solid fa-pen-to-square"></i></button>
          <button class="btn-action text-danger btn-del" data-index="${customers.indexOf(c)}"><i class="fa-solid fa-trash"></i></button>
        </td>
      </tr>
    `).join("");
        }

        // Gắn sự kiện
        document.getElementById("searchInput").oninput = applyFilters;
        document.getElementById("filterType").onchange = applyFilters;
        document.getElementById("filterStatus").onchange = applyFilters;
    </script>
</body>

</html>