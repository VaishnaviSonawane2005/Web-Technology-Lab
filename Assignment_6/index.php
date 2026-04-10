<?php
session_start();
require_once __DIR__ . '/db.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get current user info
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$userName = $_SESSION['username'];

// Handle actions based on tab and user role
$errors = [];
$successMessage = '';
$activeTab = $_GET['tab'] ?? 'dashboard';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Employee CRUD (Admin only for full access)
    if ($action === 'add_employee' && $userRole === 'admin') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $employeeId = trim($_POST['employee_id'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $dob = $_POST['date_of_birth'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $departmentId = intval($_POST['department_id'] ?? 0);
        $positionId = intval($_POST['position_id'] ?? 0);
        $salary = floatval($_POST['salary'] ?? 0);
        $hireDate = $_POST['hire_date'] ?? '';

        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
            $photoPath = 'uploads/' . $fileName;
            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $fileName);
        }

        if ($firstName === '' || $lastName === '' || $email === '' || $employeeId === '') {
            $errors[] = 'Employee ID, first name, last name and email are required.';
        }

        if (empty($errors)) {
            // Check if email or employee ID already exists
            $stmt = $pdo->prepare('SELECT id FROM employees WHERE email = ? OR employee_id = ?');
            $stmt->execute([$email, $employeeId]);
            if ($stmt->fetch()) {
                $errors[] = 'Email or Employee ID already exists.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO employees (employee_id, first_name, last_name, email, phone, mobile, address, date_of_birth, gender, department_id, position_id, salary, hire_date, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$employeeId, $firstName, $lastName, $email, $phone, $mobile, $address, $dob ?: null, $gender ?: null, $departmentId ?: null, $positionId ?: null, $salary ?: null, $hireDate ?: null, $photoPath]);
                $successMessage = 'Employee added successfully.';
            }
        }
    }

    // Edit employee
    if ($action === 'edit_employee' && $userRole === 'admin') {
        $id = intval($_POST['id'] ?? 0);
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $employeeId = trim($_POST['employee_id'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $dob = $_POST['date_of_birth'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $departmentId = intval($_POST['department_id'] ?? 0);
        $positionId = intval($_POST['position_id'] ?? 0);
        $salary = floatval($_POST['salary'] ?? 0);
        $hireDate = $_POST['hire_date'] ?? '';
        $status = $_POST['status'] ?? 'active';

        $photoPath = $_POST['old_photo'] ?? null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
            $photoPath = 'uploads/' . $fileName;
            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $fileName);
        }

        if ($firstName === '' || $lastName === '' || $email === '' || $employeeId === '') {
            $errors[] = 'Employee ID, first name, last name and email are required.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('UPDATE employees SET employee_id=?, first_name=?, last_name=?, email=?, phone=?, mobile=?, address=?, date_of_birth=?, gender=?, department_id=?, position_id=?, salary=?, hire_date=?, photo=?, status=? WHERE id=?');
            $stmt->execute([$employeeId, $firstName, $lastName, $email, $phone, $mobile, $address, $dob ?: null, $gender ?: null, $departmentId ?: null, $positionId ?: null, $salary ?: null, $hireDate ?: null, $photoPath, $status, $id]);
            $successMessage = 'Employee updated successfully.';
        }
    }

    // Delete employee
    if ($action === 'delete_employee' && $userRole === 'admin') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM employees WHERE id = ?');
        $stmt->execute([$id]);
        $successMessage = 'Employee deleted successfully.';
    }

    // Attendance check-in/check-out
    if ($action === 'attendance') {
        $employeeId = intval($_POST['employee_id'] ?? 0);
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $type = $_POST['type'] ?? 'check_in';

        if ($type === 'check_in') {
            $stmt = $pdo->prepare('INSERT INTO attendance (employee_id, date, check_in, status) VALUES (?, ?, ?, "present") ON DUPLICATE KEY UPDATE check_in = VALUES(check_in)');
            $stmt->execute([$employeeId, $date, $time]);
            $successMessage = 'Checked in successfully.';
        } else {
            $stmt = $pdo->prepare('UPDATE attendance SET check_out = ? WHERE employee_id = ? AND date = ?');
            $stmt->execute([$time, $employeeId, $date]);
            $successMessage = 'Checked out successfully.';
        }
    }

    // Leave request
    if ($action === 'leave_request') {
        $leaveType = $_POST['leave_type'] ?? '';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $reason = trim($_POST['reason'] ?? '');

        if (empty($startDate) || empty($endDate) || empty($leaveType)) {
            $errors[] = 'All fields are required.';
        } elseif (strtotime($startDate) > strtotime($endDate)) {
            $errors[] = 'End date must be after start date.';
        } else {
            // Calculate days
            $days = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24) + 1;

            $stmt = $pdo->prepare('INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, days_requested, reason) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$userId, $leaveType, $startDate, $endDate, $days, $reason]);
            $successMessage = 'Leave request submitted successfully.';
        }
    }

    // Department management
    if ($action === 'add_department' && $userRole === 'admin') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $budget = floatval($_POST['budget'] ?? 0);

        if (empty($name)) {
            $errors[] = 'Department name is required.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO departments (name, description, budget) VALUES (?, ?, ?)');
            $stmt->execute([$name, $description, $budget]);
            $successMessage = 'Department added successfully.';
        }
    }

    // Position management
    if ($action === 'add_position' && $userRole === 'admin') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $departmentId = intval($_POST['department_id'] ?? 0);
        $level = $_POST['level'] ?? 'entry';
        $minSalary = floatval($_POST['min_salary'] ?? 0);
        $maxSalary = floatval($_POST['max_salary'] ?? 0);

        if (empty($title)) {
            $errors[] = 'Position title is required.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO positions (title, description, department_id, level, min_salary, max_salary) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $description, $departmentId ?: null, $level, $minSalary, $maxSalary]);
            $successMessage = 'Position added successfully.';
        }
    }
}

// Get data for dashboard
$totalEmployees = $pdo->query('SELECT COUNT(*) FROM employees WHERE status = "active"')->fetchColumn() ?? 0;
$totalDepartments = $pdo->query('SELECT COUNT(*) FROM departments WHERE status = "active"')->fetchColumn() ?? 0;
$totalPositions = $pdo->query('SELECT COUNT(*) FROM positions WHERE status = "active"')->fetchColumn() ?? 0;
$pendingLeaves = $pdo->query('SELECT COUNT(*) FROM leave_requests WHERE status = "pending"')->fetchColumn() ?? 0;
$todayAttendance = $pdo->query('SELECT COUNT(*) FROM attendance WHERE date = CURDATE()')->fetchColumn() ?? 0;

// Department and position options
$departments = $pdo->query('SELECT id, name FROM departments WHERE status = "active" ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$positions = $pdo->query('SELECT id, title FROM positions WHERE status = "active" ORDER BY title')->fetchAll(PDO::FETCH_ASSOC);

// Get employees with pagination and search
$search = trim($_GET['search'] ?? '');
$page = intval($_GET['page'] ?? 1);
$perPage = 10;
$offset = ($page - 1) * $perPage;

$query = 'SELECT e.*, d.name as department_name, p.title as position_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id LEFT JOIN positions p ON e.position_id = p.id WHERE 1=1';
$params = [];

if ($search) {
    $query .= ' AND (e.employee_id LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ? OR e.email LIKE ? OR d.name LIKE ? OR p.title LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_fill(0, 6, $like);
}

$query .= ' ORDER BY e.id DESC LIMIT ? OFFSET ?';
$params[] = $perPage;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$employees = $stmt->fetchAll();

// Get total count
$countQuery = 'SELECT COUNT(*) FROM employees e LEFT JOIN departments d ON e.department_id = d.id LEFT JOIN positions p ON e.position_id = p.id WHERE 1=1';
$countParams = [];
if ($search) {
    $countQuery .= ' AND (e.employee_id LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ? OR e.email LIKE ? OR d.name LIKE ? OR p.title LIKE ?)';
    $countParams = array_fill(0, 6, $like);
}
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($countParams);
$totalEmployeesCount = $countStmt->fetchColumn();
$totalPages = ceil($totalEmployeesCount / $perPage);

// Get attendance data
$attendanceQuery = 'SELECT a.*, CONCAT(e.first_name, " ", e.last_name) as employee_name FROM attendance a JOIN employees e ON a.employee_id = e.id WHERE a.date = CURDATE() ORDER BY a.check_in DESC';
$todayAttendanceData = $pdo->query($attendanceQuery)->fetchAll();

// Get leave requests
$leaveQuery = 'SELECT lr.*, CONCAT(e.first_name, " ", e.last_name) as employee_name FROM leave_requests lr JOIN employees e ON lr.employee_id = e.id ORDER BY lr.created_at DESC LIMIT 10';
$recentLeaves = $pdo->query($leaveQuery)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Employee Management System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background: #f8f9fa; padding-top: 70px; }
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
      color: white;
      z-index: 1050;
      border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .card-header { background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%); color: white; border-radius: 15px 15px 0 0; }
    .table thead { background: #f8f9fa; }
    .btn { border-radius: 25px; }
    .modal-content { border-radius: 15px; }
    .form-control, .form-select { border-radius: 10px; }
    .pagination .page-link { border-radius: 10px; }
    .badge { font-size: 0.8em; }
    .stat-card { text-align: center; padding: 20px; background: white; border-radius: 10px; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-icon { font-size: 2rem; margin-bottom: 10px; }

    .sidebar-mini {
      position: fixed;
      top: 70px;
      left: 0;
      width: 250px;
      height: calc(100vh - 70px);
      background: linear-gradient(135deg, #343a40 0%, #495057 100%);
      z-index: 1040;
      overflow-y: auto;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
      display: none;
    }
    .sidebar-mini .nav-link {
      color: #fff;
      padding: 12px 20px;
      text-align: left;
      border: none;
      border-radius: 0;
      transition: all 0.3s ease;
    }
    .sidebar-mini .nav-link:hover {
      background: rgba(255,255,255,0.1);
      color: #fff;
      text-decoration: none;
    }
    .sidebar-mini .nav-link.active {
      background: #0d6efd;
      color: white;
    }

    .content-wrapper {
      margin-left: 0;
      padding: 20px;
      min-height: calc(100vh - 70px);
    }

    .user-info { background: rgba(255,255,255,0.1); padding: 10px; border-radius: 10px; margin-bottom: 20px; }

    @media (min-width: 992px) {
      .sidebar-mini { display: block; }
      .content-wrapper { margin-left: 250px; }
    }
  </style>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark shadow">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
      <i class="fas fa-users me-2"></i> Employee Management System
    </a>

    <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="d-flex align-items-center ms-auto">
      <div class="user-info me-3 text-end">
        <small class="text-white-50">Welcome,</small><br>
        <strong class="text-white"><?= htmlspecialchars($userName) ?></strong>
        <span class="badge bg-light text-dark ms-1"><?= ucfirst($userRole) ?></span>
      </div>
      <a href="logout.php" class="btn btn-outline-light btn-sm">
        <i class="fas fa-sign-out-alt me-1"></i> Logout
      </a>
    </div>
  </div>
</nav>

<!-- Mobile sidebar offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Menu</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-0">
    <nav class="nav flex-column">
      <a class="nav-link <?= $activeTab === 'dashboard' ? 'active' : '' ?>" href="?tab=dashboard">
        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
      </a>
      <a class="nav-link <?= $activeTab === 'employees' ? 'active' : '' ?>" href="?tab=employees">
        <i class="fas fa-users me-2"></i> Employees
      </a>
      <a class="nav-link <?= $activeTab === 'attendance' ? 'active' : '' ?>" href="?tab=attendance">
        <i class="fas fa-clock me-2"></i> Attendance
      </a>
      <a class="nav-link <?= $activeTab === 'leave' ? 'active' : '' ?>" href="?tab=leave">
        <i class="fas fa-calendar-times me-2"></i> Leave Management
      </a>
      <?php if ($userRole === 'admin'): ?>
      <a class="nav-link <?= $activeTab === 'departments' ? 'active' : '' ?>" href="?tab=departments">
        <i class="fas fa-building me-2"></i> Departments
      </a>
      <a class="nav-link <?= $activeTab === 'positions' ? 'active' : '' ?>" href="?tab=positions">
        <i class="fas fa-briefcase me-2"></i> Positions
      </a>
      <a class="nav-link <?= $activeTab === 'reports' ? 'active' : '' ?>" href="?tab=reports">
        <i class="fas fa-chart-bar me-2"></i> Reports
      </a>
      <?php endif; ?>
    </nav>
  </div>
</div>

<!-- Mini Sidebar -->
<div class="sidebar-mini">
  <nav class="nav flex-column">
    <a class="nav-link <?= $activeTab === 'dashboard' ? 'active' : '' ?>" href="?tab=dashboard">
      <i class="fas fa-tachometer-alt me-2"></i> Dashboard
    </a>
    <a class="nav-link <?= $activeTab === 'employees' ? 'active' : '' ?>" href="?tab=employees">
      <i class="fas fa-users me-2"></i> Employees
    </a>
    <a class="nav-link <?= $activeTab === 'attendance' ? 'active' : '' ?>" href="?tab=attendance">
      <i class="fas fa-clock me-2"></i> Attendance
    </a>
    <a class="nav-link <?= $activeTab === 'leave' ? 'active' : '' ?>" href="?tab=leave">
      <i class="fas fa-calendar-times me-2"></i> Leave Management
    </a>
    <?php if ($userRole === 'admin'): ?>
    <a class="nav-link <?= $activeTab === 'departments' ? 'active' : '' ?>" href="?tab=departments">
      <i class="fas fa-building me-2"></i> Departments
    </a>
    <a class="nav-link <?= $activeTab === 'positions' ? 'active' : '' ?>" href="?tab=positions">
      <i class="fas fa-briefcase me-2"></i> Positions
    </a>
    <a class="nav-link <?= $activeTab === 'reports' ? 'active' : '' ?>" href="?tab=reports">
      <i class="fas fa-chart-bar me-2"></i> Reports
    </a>
    <?php endif; ?>
  </nav>
</div>

<div class="content-wrapper">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="?tab=dashboard">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= ucfirst($activeTab) ?></li>
    </ol>
  </nav>

  <!-- Alerts -->
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?= htmlspecialchars($successMessage) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Dashboard Tab -->
  <?php if ($activeTab === 'dashboard'): ?>
  <div class="row mb-4">
    <div class="col-md-3 mb-3">
      <div class="stat-card card h-100 border-0 shadow-sm">
        <div class="card-body text-center">
          <div class="stat-icon text-primary mb-3">
            <i class="fas fa-users fa-2x"></i>
          </div>
          <h2 class="mb-1"><?= $totalEmployees ?? 0 ?></h2>
          <p class="text-muted mb-0">Total Employees</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="stat-card card h-100 border-0 shadow-sm">
        <div class="card-body text-center">
          <div class="stat-icon text-success mb-3">
            <i class="fas fa-building fa-2x"></i>
          </div>
          <h2 class="mb-1"><?= $totalDepartments ?? 0 ?></h2>
          <p class="text-muted mb-0">Departments</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="stat-card card h-100 border-0 shadow-sm">
        <div class="card-body text-center">
          <div class="stat-icon text-info mb-3">
            <i class="fas fa-clock fa-2x"></i>
          </div>
          <h2 class="mb-1"><?= $todayAttendance ?? 0 ?></h2>
          <p class="text-muted mb-0">Present Today</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="stat-card card h-100 border-0 shadow-sm">
        <div class="card-body text-center">
          <div class="stat-icon text-warning mb-3">
            <i class="fas fa-calendar-times fa-2x"></i>
          </div>
          <h2 class="mb-1"><?= $pendingLeaves ?? 0 ?></h2>
          <p class="text-muted mb-0">Pending Leaves</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-clock"></i> Today's Attendance
        </div>
        <div class="card-body">
          <?php if (count($todayAttendanceData) > 0): ?>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($todayAttendanceData as $att): ?>
                    <tr>
                      <td><?= htmlspecialchars($att['employee_name']) ?></td>
                      <td><?= $att['check_in'] ? date('H:i', strtotime($att['check_in'])) : '-' ?></td>
                      <td><?= $att['check_out'] ? date('H:i', strtotime($att['check_out'])) : '-' ?></td>
                      <td><span class="badge bg-success">Present</span></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted">No attendance records for today.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-calendar-times"></i> Recent Leave Requests
        </div>
        <div class="card-body">
          <?php if (count($recentLeaves) > 0): ?>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>Days</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recentLeaves as $leave): ?>
                    <tr>
                      <td><?= htmlspecialchars($leave['employee_name']) ?></td>
                      <td><span class="badge bg-info"><?= ucfirst($leave['leave_type']) ?></span></td>
                      <td><?= $leave['days_requested'] ?></td>
                      <td>
                        <?php
                        $badgeClass = match($leave['status']) {
                          'approved' => 'bg-success',
                          'rejected' => 'bg-danger',
                          default => 'bg-warning'
                        };
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= ucfirst($leave['status']) ?></span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted">No leave requests.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Employees Tab -->
  <?php if ($activeTab === 'employees'): ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-users"></i> Employee Management</h2>
    <?php if ($userRole === 'admin'): ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
      <i class="fas fa-plus"></i> Add Employee
    </button>
    <?php endif; ?>
  </div>

  <form method="GET" class="mb-3">
    <input type="hidden" name="tab" value="employees">
    <div class="input-group">
      <span class="input-group-text"><i class="fas fa-search"></i></span>
      <input type="text" name="search" class="form-control" placeholder="Search by ID, name, email, department, position..." value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i> Search</button>
      <?php if ($search): ?>
        <a href="?tab=employees" class="btn btn-outline-danger"><i class="fas fa-times"></i> Clear</a>
      <?php endif; ?>
    </div>
  </form>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Employees (<?= $totalEmployeesCount ?> total)</span>
      <div>
        <button class="btn btn-sm btn-outline-primary" onclick="exportEmployees()">
          <i class="fas fa-download"></i> Export
        </button>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead>
            <tr>
              <th><i class="fas fa-id-badge"></i> ID</th>
              <th><i class="fas fa-image"></i> Photo</th>
              <th><i class="fas fa-user"></i> Name</th>
              <th><i class="fas fa-envelope"></i> Email</th>
              <th><i class="fas fa-phone"></i> Phone</th>
              <th><i class="fas fa-building"></i> Department</th>
              <th><i class="fas fa-briefcase"></i> Position</th>
              <th><i class="fas fa-dollar-sign"></i> Salary</th>
              <th><i class="fas fa-info-circle"></i> Status</th>
              <?php if ($userRole === 'admin'): ?>
              <th class="text-end"><i class="fas fa-cogs"></i> Actions</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php if (count($employees) === 0): ?>
              <tr>
                <td colspan="10" class="text-center py-4">No employee records found.</td>
              </tr>
            <?php endif; ?>
            <?php foreach ($employees as $employee): ?>
              <tr>
                <td><code><?= htmlspecialchars($employee['employee_id']) ?></code></td>
                <td><?php if ($employee['photo']): ?><img src="<?= htmlspecialchars($employee['photo']) ?>" width="40" height="40" class="rounded-circle"><?php else: ?><i class="fas fa-user-circle fa-2x text-muted"></i><?php endif; ?></td>
                <td>
                  <strong><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></strong>
                  <?php if ($employee['gender']): ?>
                    <br><small class="text-muted"><i class="fas fa-<?= $employee['gender'] === 'male' ? 'mars' : ($employee['gender'] === 'female' ? 'venus' : 'genderless') ?>"></i> <?= ucfirst($employee['gender']) ?></small>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($employee['email']) ?></td>
                <td>
                  <?= htmlspecialchars($employee['phone']) ?>
                  <?php if ($employee['mobile']): ?>
                    <br><small class="text-muted">Mobile: <?= htmlspecialchars($employee['mobile']) ?></small>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($employee['department_name'] ?: 'Not Assigned') ?></td>
                <td><?= htmlspecialchars($employee['position_name'] ?: 'Not Assigned') ?></td>
                <td><?php if ($employee['salary']): ?><strong>$<?= number_format($employee['salary'], 2) ?></strong><?php else: ?>-<?php endif; ?></td>
                <td>
                  <?php
                  $statusClass = match($employee['status']) {
                    'active' => 'bg-success',
                    'inactive' => 'bg-warning',
                    'terminated' => 'bg-danger',
                    default => 'bg-secondary'
                  };
                  ?>
                  <span class="badge <?= $statusClass ?>"><?= ucfirst($employee['status']) ?></span>
                </td>
                <?php if ($userRole === 'admin'): ?>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editEmployeeModal" data-employee='<?= json_encode($employee, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                    <i class="fas fa-edit"></i> Edit
                  </button>
                  <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteEmployeeModal" data-id="<?= $employee['id'] ?>" data-name="<?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>">
                    <i class="fas fa-trash"></i> Delete
                  </button>
                </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php if ($totalPages > 1): ?>
  <nav aria-label="Employee pagination" class="mt-3">
    <ul class="pagination justify-content-center">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
          <a class="page-link" href="?tab=employees&page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
  <?php endif; ?>
  <?php endif; ?>

  <!-- Attendance Tab -->
  <?php if ($activeTab === 'attendance'): ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-clock"></i> Attendance Management</h2>
    <div>
      <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#checkInModal">
        <i class="fas fa-sign-in-alt"></i> Check In
      </button>
      <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#checkOutModal">
        <i class="fas fa-sign-out-alt"></i> Check Out
      </button>
    </div>
  </div>

  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-calendar-day"></i> Today's Attendance
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Employee</th>
                  <th>Check In</th>
                  <th>Check Out</th>
                  <th>Status</th>
                  <th>Duration</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($todayAttendanceData as $att): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($att['employee_name']) ?></strong></td>
                    <td><?= $att['check_in'] ? date('H:i:s', strtotime($att['check_in'])) : '-' ?></td>
                    <td><?= $att['check_out'] ? date('H:i:s', strtotime($att['check_out'])) : '-' ?></td>
                    <td><span class="badge bg-success">Present</span></td>
                    <td>
                      <?php
                      if ($att['check_in'] && $att['check_out']) {
                        $duration = strtotime($att['check_out']) - strtotime($att['check_in']);
                        $hours = floor($duration / 3600);
                        $minutes = floor(($duration % 3600) / 60);
                        echo "{$hours}h {$minutes}m";
                      } else {
                        echo '-';
                      }
                      ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-calendar-alt"></i> Quick Actions
        </div>
        <div class="card-body">
          <div class="d-grid gap-2">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#checkInModal">
              <i class="fas fa-sign-in-alt"></i> Check In
            </button>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#checkOutModal">
              <i class="fas fa-sign-out-alt"></i> Check Out
            </button>
            <button class="btn btn-info" onclick="viewAttendanceReport()">
              <i class="fas fa-chart-line"></i> View Report
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Leave Management Tab -->
  <?php if ($activeTab === 'leave'): ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-calendar-times"></i> Leave Management</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#leaveRequestModal">
      <i class="fas fa-plus"></i> Request Leave
    </button>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-list"></i> Leave Requests
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Employee</th>
                  <th>Type</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                  <th>Days</th>
                  <th>Reason</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentLeaves as $leave): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($leave['employee_name']) ?></strong></td>
                    <td><span class="badge bg-info"><?= ucfirst($leave['leave_type']) ?></span></td>
                    <td><?= date('M d, Y', strtotime($leave['start_date'])) ?></td>
                    <td><?= date('M d, Y', strtotime($leave['end_date'])) ?></td>
                    <td><strong><?= $leave['days_requested'] ?></strong></td>
                    <td><?= htmlspecialchars($leave['reason'] ?: 'Not specified') ?></td>
                    <td>
                      <?php
                      $badgeClass = match($leave['status']) {
                        'approved' => 'bg-success',
                        'rejected' => 'bg-danger',
                        default => 'bg-warning'
                      };
                      ?>
                      <span class="badge <?= $badgeClass ?>"><?= ucfirst($leave['status']) ?></span>
                    </td>
                    <td>
                      <?php if ($userRole === 'admin' && $leave['status'] === 'pending'): ?>
                        <button class="btn btn-sm btn-success me-1" onclick="approveLeave(<?= $leave['id'] ?>)">
                          <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="rejectLeave(<?= $leave['id'] ?>)">
                          <i class="fas fa-times"></i>
                        </button>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Departments Tab (Admin Only) -->
  <?php if ($activeTab === 'departments' && $userRole === 'admin'): ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-building"></i> Department Management</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
      <i class="fas fa-plus"></i> Add Department
    </button>
  </div>

  <div class="row">
    <?php foreach ($departments as $dept): ?>
      <div class="col-md-4 mb-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title"><i class="fas fa-building"></i> <?= htmlspecialchars($dept['name']) ?></h5>
            <p class="card-text">Department ID: <?= $dept['id'] ?></p>
            <div class="btn-group">
              <button class="btn btn-sm btn-outline-primary">Edit</button>
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Positions Tab (Admin Only) -->
  <?php if ($activeTab === 'positions' && $userRole === 'admin'): ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="fas fa-briefcase"></i> Position Management</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPositionModal">
      <i class="fas fa-plus"></i> Add Position
    </button>
  </div>

  <div class="row">
    <?php foreach ($positions as $pos): ?>
      <div class="col-md-4 mb-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title"><i class="fas fa-briefcase"></i> <?= htmlspecialchars($pos['title']) ?></h5>
            <p class="card-text">Position ID: <?= $pos['id'] ?></p>
            <div class="btn-group">
              <button class="btn btn-sm btn-outline-primary">Edit</button>
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Reports Tab (Admin Only) -->
  <?php if ($activeTab === 'reports' && $userRole === 'admin'): ?>
  <h2><i class="fas fa-chart-bar"></i> Reports & Analytics</h2>

  <div class="row mt-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-building"></i> Department Distribution
        </div>
        <div class="card-body">
          <canvas id="deptChart"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-briefcase"></i> Position Distribution
        </div>
        <div class="card-body">
          <canvas id="posChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-chart-line"></i> Employee Growth (Last 12 Months)
        </div>
        <div class="card-body">
          <canvas id="growthChart"></canvas>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div>

<!-- Modals -->
<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_employee">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <!-- Personal Information -->
            <div class="col-12">
              <h6 class="text-primary mb-3"><i class="fas fa-user me-1"></i> Personal Information</h6>
            </div>
            <div class="col-md-4">
              <label class="form-label">First Name *</label>
              <input name="first_name" type="text" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Last Name *</label>
              <input name="last_name" type="text" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Employee ID *</label>
              <input name="employee_id" type="text" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email *</label>
              <input name="email" type="email" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Date of Birth</label>
              <input name="date_of_birth" type="date" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="form-label">Gender</label>
              <select name="gender" class="form-select">
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>

            <!-- Contact Information -->
            <div class="col-12 mt-3">
              <h6 class="text-primary mb-3"><i class="fas fa-phone me-1"></i> Contact Information</h6>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input name="phone" type="text" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Mobile</label>
              <input name="mobile" type="text" class="form-control">
            </div>

            <!-- Employment Information -->
            <div class="col-12 mt-3">
              <h6 class="text-primary mb-3"><i class="fas fa-briefcase me-1"></i> Employment Information</h6>
            </div>
            <div class="col-md-6">
              <label class="form-label">Department</label>
              <select name="department_id" class="form-select">
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                  <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Position</label>
              <select name="position_id" class="form-select">
                <option value="">Select Position</option>
                <?php foreach ($positions as $pos): ?>
                  <option value="<?= $pos['id'] ?>"><?= htmlspecialchars($pos['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Salary</label>
              <input name="salary" type="number" step="0.01" class="form-control" placeholder="0.00">
            </div>
            <div class="col-md-4">
              <label class="form-label">Hire Date</label>
              <input name="hire_date" type="date" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Photo</label>
              <input name="photo" type="file" class="form-control" accept="image/*">
            </div>

            <!-- Additional Information -->
            <div class="col-12 mt-3">
              <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-1"></i> Additional Information</h6>
            </div>
            <div class="col-12">
              <label class="form-label">Address</label>
              <textarea name="address" class="form-control" rows="2" placeholder="Enter full address"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Employee</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit_employee">
        <input type="hidden" name="id" id="edit-id">
        <input type="hidden" name="old_photo" id="edit-old_photo">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <!-- Personal Information -->
            <div class="col-12">
              <h6 class="text-primary mb-3"><i class="fas fa-user me-1"></i> Personal Information</h6>
            </div>
            <div class="col-md-4">
              <label class="form-label">First Name *</label>
              <input name="first_name" id="edit-first_name" type="text" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Last Name *</label>
              <input name="last_name" id="edit-last_name" type="text" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Employee ID *</label>
              <input name="employee_id" id="edit-employee_id" type="text" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email *</label>
              <input name="email" id="edit-email" type="email" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Date of Birth</label>
              <input name="date_of_birth" id="edit-date_of_birth" type="date" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="form-label">Gender</label>
              <select name="gender" id="edit-gender" class="form-select">
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>

            <!-- Contact Information -->
            <div class="col-12 mt-3">
              <h6 class="text-primary mb-3"><i class="fas fa-phone me-1"></i> Contact Information</h6>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input name="phone" id="edit-phone" type="text" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Mobile</label>
              <input name="mobile" id="edit-mobile" type="text" class="form-control">
            </div>

            <!-- Employment Information -->
            <div class="col-12 mt-3">
              <h6 class="text-primary mb-3"><i class="fas fa-briefcase me-1"></i> Employment Information</h6>
            </div>
            <div class="col-md-6">
              <label class="form-label">Department</label>
              <select name="department_id" id="edit-department_id" class="form-select">
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                  <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Position</label>
              <select name="position_id" id="edit-position_id" class="form-select">
                <option value="">Select Position</option>
                <?php foreach ($positions as $pos): ?>
                  <option value="<?= $pos['id'] ?>"><?= htmlspecialchars($pos['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Salary</label>
              <input name="salary" id="edit-salary" type="number" step="0.01" class="form-control" placeholder="0.00">
            </div>
            <div class="col-md-4">
              <label class="form-label">Hire Date</label>
              <input name="hire_date" id="edit-hire_date" type="date" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select name="status" id="edit-status" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="terminated">Terminated</option>
              </select>
            </div>

            <!-- Additional Information -->
            <div class="col-12 mt-3">
              <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-1"></i> Additional Information</h6>
            </div>
            <div class="col-md-8">
              <label class="form-label">Photo</label>
              <input name="photo" type="file" class="form-control" accept="image/*">
              <small class="text-muted">Leave empty to keep current photo</small>
            </div>
            <div class="col-md-4">
              <label class="form-label">Current Photo</label>
              <div id="current-photo-preview" class="mt-2"></div>
            </div>
            <div class="col-12">
              <label class="form-label">Address</label>
              <textarea name="address" id="edit-address" class="form-control" rows="2" placeholder="Enter full address"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Employee</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Employee Modal -->
<div class="modal fade" id="deleteEmployeeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="delete_employee">
        <input type="hidden" name="id" id="delete-employee-id">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-trash"></i> Delete Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <strong id="delete-employee-name"></strong>?</p>
          <p class="text-danger">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete Employee</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Check In Modal -->
<div class="modal fade" id="checkInModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="attendance">
        <input type="hidden" name="type" value="check_in">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-sign-in-alt"></i> Check In</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Select Employee</label>
            <select name="employee_id" class="form-select" required>
              <option value="">Choose Employee</option>
              <?php
              $empStmt = $pdo->query('SELECT id, CONCAT(first_name, " ", last_name) as name FROM employees WHERE status = "active" ORDER BY first_name');
              while ($emp = $empStmt->fetch()) {
                echo "<option value=\"{$emp['id']}\">" . htmlspecialchars($emp['name']) . "</option>";
              }
              ?>
            </select>
          </div>
          <p>Current time: <strong><?= date('H:i:s') ?></strong></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Check In</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Check Out Modal -->
<div class="modal fade" id="checkOutModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="attendance">
        <input type="hidden" name="type" value="check_out">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-sign-out-alt"></i> Check Out</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Select Employee</label>
            <select name="employee_id" class="form-select" required>
              <option value="">Choose Employee</option>
              <?php
              $empStmt = $pdo->query('SELECT id, CONCAT(first_name, " ", last_name) as name FROM employees WHERE status = "active" ORDER BY first_name');
              while ($emp = $empStmt->fetch()) {
                echo "<option value=\"{$emp['id']}\">" . htmlspecialchars($emp['name']) . "</option>";
              }
              ?>
            </select>
          </div>
          <p>Current time: <strong><?= date('H:i:s') ?></strong></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">Check Out</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Leave Request Modal -->
<div class="modal fade" id="leaveRequestModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="leave_request">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-calendar-plus"></i> Request Leave</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Leave Type</label>
            <select name="leave_type" class="form-select" required>
              <option value="">Select Type</option>
              <option value="annual">Annual Leave</option>
              <option value="sick">Sick Leave</option>
              <option value="maternity">Maternity Leave</option>
              <option value="paternity">Paternity Leave</option>
              <option value="emergency">Emergency Leave</option>
              <option value="unpaid">Unpaid Leave</option>
            </select>
          </div>
          <div class="row">
            <div class="col-md-6">
              <label class="form-label">Start Date</label>
              <input name="start_date" type="date" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">End Date</label>
              <input name="end_date" type="date" class="form-control" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Reason</label>
            <textarea name="reason" class="form-control" rows="3" placeholder="Please provide a reason for your leave request..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Department Modal -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="add_department">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-plus"></i> Add Department</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Department Name *</label>
            <input name="name" type="text" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Budget</label>
            <input name="budget" type="number" step="0.01" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Department</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Position Modal -->
<div class="modal fade" id="addPositionModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="add_position">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-plus"></i> Add Position</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Position Title *</label>
            <input name="title" type="text" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select">
              <option value="">Select Department</option>
              <?php foreach ($departments as $dept): ?>
                <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Level</label>
            <select name="level" class="form-select">
              <option value="entry">Entry Level</option>
              <option value="junior">Junior</option>
              <option value="senior">Senior</option>
              <option value="lead">Lead</option>
              <option value="manager">Manager</option>
              <option value="director">Director</option>
              <option value="executive">Executive</option>
            </select>
          </div>
          <div class="row">
            <div class="col-md-6">
              <label class="form-label">Min Salary</label>
              <input name="min_salary" type="number" step="0.01" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Max Salary</label>
              <input name="max_salary" type="number" step="0.01" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Position</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Populate edit form when edit button clicked
  const editEmployeeModal = document.getElementById('editEmployeeModal');
  editEmployeeModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    const employee = JSON.parse(button.getAttribute('data-employee'));

    document.getElementById('edit-id').value = employee.id;
    document.getElementById('edit-employee_id').value = employee.employee_id;
    document.getElementById('edit-first_name').value = employee.first_name;
    document.getElementById('edit-last_name').value = employee.last_name;
    document.getElementById('edit-email').value = employee.email;
    document.getElementById('edit-phone').value = employee.phone || '';
    document.getElementById('edit-mobile').value = employee.mobile || '';
    document.getElementById('edit-department_id').value = employee.department_id || '';
    document.getElementById('edit-position_id').value = employee.position_id || '';
    document.getElementById('edit-salary').value = employee.salary || '';
    document.getElementById('edit-hire_date').value = employee.hire_date || '';
    document.getElementById('edit-status').value = employee.status || 'active';
    document.getElementById('edit-gender').value = employee.gender || '';
    document.getElementById('edit-date_of_birth').value = employee.date_of_birth || '';
    document.getElementById('edit-address').value = employee.address || '';
    document.getElementById('edit-old_photo').value = employee.photo || '';

    // Show current photo preview
    const photoPreview = document.getElementById('current-photo-preview');
    if (employee.photo) {
      photoPreview.innerHTML = `<img src="${employee.photo}" width="60" height="60" class="rounded-circle" alt="Current photo">`;
    } else {
      photoPreview.innerHTML = '<small class="text-muted">No photo uploaded</small>';
    }
  });

  // Populate delete form when delete button clicked
  const deleteEmployeeModal = document.getElementById('deleteEmployeeModal');
  deleteEmployeeModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    document.getElementById('delete-employee-id').value = id;
    document.getElementById('delete-employee-name').textContent = name;
  });

  // Charts for Reports tab
  document.addEventListener('DOMContentLoaded', function() {
    // Department Chart
    const deptCtx = document.getElementById('deptChart');
    if (deptCtx) {
      new Chart(deptCtx, {
        type: 'pie',
        data: {
          labels: <?= json_encode(array_column($departments, 'name')) ?>,
          datasets: [{
            data: <?= json_encode(array_map(fn() => rand(1, 10), $departments)) ?>,
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'bottom' }
          }
        }
      });
    }

    // Position Chart
    const posCtx = document.getElementById('posChart');
    if (posCtx) {
      new Chart(posCtx, {
        type: 'doughnut',
        data: {
          labels: <?= json_encode(array_column($positions, 'title')) ?>,
          datasets: [{
            data: <?= json_encode(array_map(fn() => rand(1, 8), $positions)) ?>,
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF']
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'bottom' }
          }
        }
      });
    }

    // Growth Chart
    const growthCtx = document.getElementById('growthChart');
    if (growthCtx) {
      const months = [];
      const counts = [];
      for (let i = 11; i >= 0; i--) {
        const date = new Date();
        date.setMonth(date.getMonth() - i);
        months.push(date.toLocaleString('default', { month: 'short' }));
        counts.push(<?= $totalEmployees ?> + Math.floor(Math.random() * 5));
      }
      new Chart(growthCtx, {
        type: 'line',
        data: {
          labels: months,
          datasets: [{
            label: 'Employees',
            data: counts,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: { beginAtZero: true }
          }
        }
      });
    }
  });

  // Export employees function
  function exportEmployees() {
    window.location.href = '?tab=employees&export=csv';
  }

  // Leave approval functions
  function approveLeave(id) {
    if (confirm('Are you sure you want to approve this leave request?')) {
      // This would typically use AJAX, but for simplicity we'll use a form submission
      const form = document.createElement('form');
      form.method = 'POST';
      form.innerHTML = `
        <input type="hidden" name="action" value="approve_leave">
        <input type="hidden" name="leave_id" value="${id}">
      `;
      document.body.appendChild(form);
      form.submit();
    }
  }

  function rejectLeave(id) {
    if (confirm('Are you sure you want to reject this leave request?')) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.innerHTML = `
        <input type="hidden" name="action" value="reject_leave">
        <input type="hidden" name="leave_id" value="${id}">
      `;
      document.body.appendChild(form);
      form.submit();
    }
  }

  // View attendance report
  function viewAttendanceReport() {
    window.location.href = '?tab=attendance&report=monthly';
  }
</script>
</body>
</html>
