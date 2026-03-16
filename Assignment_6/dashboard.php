<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

// Stats
$totalEmployees = $pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();
$totalDepartments = $pdo->query('SELECT COUNT(DISTINCT department) FROM employees WHERE department IS NOT NULL')->fetchColumn();
$recentEmployees = $pdo->query('SELECT COUNT(*) FROM employees WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
$deptStats = $pdo->query('SELECT department, COUNT(*) as count FROM employees WHERE department IS NOT NULL GROUP BY department ORDER BY count DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <style>
    body { background: #f8f9fa; }
    .sidebar { background: linear-gradient(135deg, #343a40 0%, #495057 100%); min-height: 100vh; position: fixed; width: 250px; }
    .sidebar .nav-link { color: #fff; padding: 15px 20px; }
    .sidebar .nav-link:hover { background: rgba(255,255,255,0.1); }
    .content { margin-left: 250px; padding: 20px; }
    .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .stat-card { text-align: center; padding: 20px; }
    .stat-icon { font-size: 2rem; margin-bottom: 10px; }
  </style>
</head>
<body>
<div class="sidebar">
  <div class="p-3">
    <h5 class="text-white"><i class="fas fa-tachometer-alt"></i> Dashboard</h5>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link active" href="dashboard.php"><i class="fas fa-home"></i> Overview</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="employees.php"><i class="fas fa-users"></i> Employees</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </li>
    </ul>
  </div>
</div>

<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
    <span>Welcome, Admin</span>
  </div>

  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card stat-card">
        <div class="stat-icon text-primary"><i class="fas fa-users"></i></div>
        <h3><?= $totalEmployees ?></h3>
        <p>Total Employees</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card">
        <div class="stat-icon text-success"><i class="fas fa-building"></i></div>
        <h3><?= $totalDepartments ?></h3>
        <p>Departments</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card">
        <div class="stat-icon text-info"><i class="fas fa-calendar-plus"></i></div>
        <h3><?= $recentEmployees ?></h3>
        <p>New This Month</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card stat-card">
        <div class="stat-icon text-warning"><i class="fas fa-chart-pie"></i></div>
        <h3>Reports</h3>
        <p>View Analytics</p>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-building"></i> Top Departments
        </div>
        <div class="card-body">
          <?php if (count($deptStats) > 0): ?>
            <ul class="list-group list-group-flush">
              <?php foreach ($deptStats as $stat): ?>
                <li class="list-group-item d-flex justify-content-between">
                  <?= htmlspecialchars($stat['department']) ?>
                  <span class="badge bg-primary"><?= $stat['count'] ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p>No data</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-bolt"></i> Quick Actions
        </div>
        <div class="card-body">
          <a href="employees.php" class="btn btn-primary me-2"><i class="fas fa-plus"></i> Add Employee</a>
          <a href="reports.php" class="btn btn-secondary"><i class="fas fa-chart-bar"></i> View Reports</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>