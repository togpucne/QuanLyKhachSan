<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý nhân viên - ABC Resort</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { background-color: #F3EEEA; font-family: "Segoe UI", sans-serif; }
    .sidebar { background-color: #292D32; color: white; height: 100vh; width: 250px; position: fixed; padding-top: 20px; display: flex; flex-direction: column; justify-content: space-between; }
    .sidebar h4 { text-align: center; color: #E8DFCA; font-weight: bold; }
    .sidebar a { color: #E8DFCA; text-decoration: none; display: block; padding: 10px 20px; transition: 0.3s; font-size: 15px; }
    .sidebar a:hover { background-color: #3b4148; border-radius: 5px; }
    .sidebar .logout { color: #ff6b6b; margin: 15px 20px; border-top: 1px solid #444; padding-top: 10px; }
    .content { margin-left: 260px; padding: 20px; }
    .topbar { background: #A9907E; color: #fff; padding: 10px 20px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
    .table thead { background-color: #E8DFCA; }
    .btn-action { border: none; background: none; cursor: pointer; color: #A9907E; font-size: 18px; }
    .btn-action:hover { color: #6d5e51; }
    .filter-bar { background: #E8DFCA; padding: 12px; border-radius: 8px; margin-bottom: 15px; }
    .footer { text-align: center; color: #777; margin-top: 30px; font-size: 14px; }
    .btn-custom { background-color: #A9907E; color: white; border: none; }
    .btn-custom:hover { background-color: #8b7b6c; }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <h4>🏨 ABC Resort</h4>
      <hr class="text-light mx-3">
      <a href="dashboard.html"><i class="fa-solid fa-chart-line me-2"></i>Tổng quan</a>
      <a href="quanlynhanvien.html" class="fw-bold bg-dark rounded-2"><i class="fa-solid fa-user-tie me-2"></i>Quản lý nhân viên</a>
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
      <h5><i class="fa-solid fa-user-tie me-2"></i>Quản lý nhân viên</h5>
      <div>
        Xin chào, <b id="usernameDisplay">admin</b>!
        <button class="btn btn-light btn-sm" onclick="logout()">Đăng xuất</button>
      </div>
    </div>

    <!-- Thanh lọc & tìm kiếm -->
    <div class="filter-bar mt-4">
      <div class="row g-2 align-items-center">
        <div class="col-md-3">
          <input type="text" class="form-control" id="searchInput" placeholder="🔍 Tìm theo tên, mã NV...">
        </div>
        <div class="col-md-3">
          <select class="form-select" id="filterDept">
            <option value="">-- Bộ phận --</option>
            <option>Buồng phòng</option>
            <option>Lễ tân</option>
            <option>Kế toán</option>
            <option>Thu ngân</option>
            <option>Kinh doanh</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select" id="filterStatus">
            <option value="">-- Trạng thái --</option>
            <option>Đang làm</option>
            <option>Đã nghỉ</option>
          </select>
        </div>
        <div class="col-md-3 text-end">
          <button class="btn btn-custom" id="addBtn"><i class="fa-solid fa-plus me-1"></i>Thêm nhân viên</button>
          <button class="btn btn-danger" id="deleteSelected"><i class="fa-solid fa-trash me-1"></i>Xóa nhiều</button>
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
            <th>Mã NV</th>
            <th>Họ tên</th>
            <th>Bộ phận</th>
            <th>Ngày vào làm</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody id="employeeTable"></tbody>
      </table>
    </div>

    <div class="footer">
      © 2025 - Nhóm Tỏa Sáng | ABC Resort Management System
    </div>
  </div>

  <!-- Modal thêm/sửa -->
  <div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title" id="modalTitle">Thêm nhân viên</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="detailForm">
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">Mã NV</label><input type="text" id="empId" class="form-control"></div>
              <div class="col-md-6"><label class="form-label">Họ tên</label><input type="text" id="empName" class="form-control"></div>
              <div class="col-md-6"><label class="form-label">Giới tính</label><select id="empGender" class="form-select"><option>Nam</option><option>Nữ</option></select></div>
              <div class="col-md-6"><label class="form-label">Ngày sinh</label><input type="date" id="empBirth" class="form-control"></div>
              <div class="col-md-6"><label class="form-label">SĐT</label><input type="text" id="empPhone" class="form-control"></div>
              <div class="col-md-6"><label class="form-label">Email</label><input type="email" id="empEmail" class="form-control"></div>
              <div class="col-md-12"><label class="form-label">Địa chỉ</label><input type="text" id="empAddress" class="form-control"></div>
              <div class="col-md-6"><label class="form-label">Bộ phận</label><select id="empDept" class="form-select"><option>Buồng phòng</option><option>Lễ tân</option><option>Kế toán</option><option>Thu ngân</option><option>Kinh doanh</option></select></div>
              <div class="col-md-6"><label class="form-label">Ngày vào làm</label><input type="date" id="empJoin" class="form-control"></div>
              <div class="col-md-6"><label class="form-label">Trạng thái</label><select id="empStatus" class="form-select"><option>Đang làm</option><option>Đã nghỉ</option></select></div>
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
  // ====== DỮ LIỆU MẪU ======
  let employees = JSON.parse(localStorage.getItem("employees")) || [
    { id: "NV001", name: "Nguyễn Văn A", gender: "Nam", birth: "2000-08-12", phone: "0393833244", email: "pike81204@gmail.com", address: "13 Lê Lợi, Gò Vấp", dept: "Lễ tân", join: "2022-03-12", status: "Đang làm" },
    { id: "NV002", name: "Trần Thị B", gender: "Nữ", birth: "1999-05-08", phone: "0901234567", email: "b@gmail.com", address: "12 Nguyễn Văn Nghi", dept: "Buồng phòng", join: "2021-11-05", status: "Đang làm" },
    { id: "NV003", name: "Phạm Văn C", gender: "Nam", birth: "1997-03-05", phone: "0911111111", email: "c@gmail.com", address: "Đà Nẵng", dept: "Kế toán", join: "2020-01-12", status: "Đã nghỉ" }
  ];

  const tbody = document.getElementById("employeeTable");
  const modal = new bootstrap.Modal("#detailModal");
  let editIndex = null;

  // ====== HIỂN THỊ BẢNG ======
  function renderTable(list = employees) {
    tbody.innerHTML = list.map((e, i) => `
      <tr>
        <td><input type="checkbox" class="selectEmp" data-index="${i}"></td>
        <td>${i + 1}</td>
        <td>${e.id}</td>
        <td>${e.name}</td>
        <td>${e.dept}</td>
        <td>${e.join}</td>
        <td><span class="badge ${e.status === "Đang làm" ? "bg-success" : "bg-secondary"}">${e.status}</span></td>
        <td>
          <button class="btn-action text-info btn-view" data-index="${i}" title="Chỉnh sửa"><i class="fa-solid fa-pen-to-square"></i></button>
          <button class="btn-action text-danger btn-del" data-index="${i}" title="Xóa"><i class="fa-solid fa-trash"></i></button>
        </td>
      </tr>`).join("");
  }
  renderTable();

  // ====== LỌC & TÌM KIẾM ======
  const searchInput = document.getElementById("searchInput");
  const filterDept = document.getElementById("filterDept");
  const filterStatus = document.getElementById("filterStatus");

  function applyFilters() {
    const keyword = searchInput.value.toLowerCase();
    const dept = filterDept.value;
    const status = filterStatus.value;

    const filtered = employees.filter(e => {
      const matchSearch =
        e.name.toLowerCase().includes(keyword) ||
        e.id.toLowerCase().includes(keyword);
      const matchDept = dept === "" || e.dept === dept;
      const matchStatus = status === "" || e.status === status;
      return matchSearch && matchDept && matchStatus;
    });

    renderTable(filtered);
  }

  searchInput.addEventListener("input", applyFilters);
  filterDept.addEventListener("change", applyFilters);
  filterStatus.addEventListener("change", applyFilters);

  // ====== TẠO MÃ NV MỚI ======
  function generateNewId() {
    const maxNum = employees.reduce((max, e) => {
      const num = parseInt(e.id.replace("NV", ""));
      return num > max ? num : max;
    }, 0);
    return "NV" + String(maxNum + 1).padStart(3, "0");
  }

  // ====== THÊM NHÂN VIÊN ======
  document.getElementById("addBtn").onclick = () => {
    editIndex = null;
    document.getElementById("modalTitle").textContent = "Thêm nhân viên";
    document.getElementById("detailForm").reset();

    const today = new Date().toISOString().split("T")[0];
    empId.value = generateNewId();
    empJoin.value = today;
    empStatus.value = "Đang làm";

    modal.show();
  };

  // ====== LƯU THAY ĐỔI ======
  document.getElementById("saveChanges").onclick = () => {
    const newEmp = {
      id: empId.value, name: empName.value, gender: empGender.value,
      birth: empBirth.value, phone: empPhone.value, email: empEmail.value,
      address: empAddress.value, dept: empDept.value, join: empJoin.value,
      status: empStatus.value
    };

    if (editIndex !== null) employees[editIndex] = newEmp;
    else employees.push(newEmp);

    localStorage.setItem("employees", JSON.stringify(employees));
    renderTable();
    modal.hide();
    alert(editIndex !== null ? "✅ Cập nhật thành công!" : "✅ Thêm nhân viên mới thành công!");
  };

  // ====== XÓA 1 NHÂN VIÊN ======
  tbody.addEventListener("click", e => {
    if (e.target.closest(".btn-del")) {
      const i = e.target.closest(".btn-del").dataset.index;
      if (confirm(`Xóa nhân viên ${employees[i].name}?`)) {
        employees.splice(i, 1);
        localStorage.setItem("employees", JSON.stringify(employees));
        renderTable();
      }
    }
  });

  // ====== CHỈNH SỬA ======
  tbody.addEventListener("click", e => {
    if (e.target.closest(".btn-view")) {
      editIndex = e.target.closest(".btn-view").dataset.index;
      const emp = employees[editIndex];
      document.getElementById("modalTitle").textContent = "Chỉnh sửa nhân viên";
      empId.value = emp.id; empName.value = emp.name; empGender.value = emp.gender;
      empBirth.value = emp.birth; empPhone.value = emp.phone; empEmail.value = emp.email;
      empAddress.value = emp.address; empDept.value = emp.dept; empJoin.value = emp.join;
      empStatus.value = emp.status;
      modal.show();
    }
  });

  // ====== XÓA NHIỀU ======
  document.getElementById("deleteSelected").onclick = () => {
    const checked = document.querySelectorAll(".selectEmp:checked");
    if (checked.length === 0) return alert("⚠️ Vui lòng chọn ít nhất 1 nhân viên để xóa!");
    if (!confirm(`Xóa ${checked.length} nhân viên đã chọn?`)) return;

    const indexes = Array.from(checked).map(c => parseInt(c.dataset.index));
    employees = employees.filter((_, i) => !indexes.includes(i));
    localStorage.setItem("employees", JSON.stringify(employees));
    renderTable();
  };

  // ====== CHỌN TẤT CẢ ======
  document.getElementById("selectAll").onchange = e => {
    document.querySelectorAll(".selectEmp").forEach(cb => cb.checked = e.target.checked);
  };

  // ====== ĐĂNG NHẬP / ĐĂNG XUẤT ======
  const user = localStorage.getItem("user");
  if (user) document.getElementById("usernameDisplay").textContent = user;
  else window.location.href = "log-in.html";

  function logout() {
    localStorage.removeItem("user");
    window.location.href = "log-in.html";
  }
</script>
