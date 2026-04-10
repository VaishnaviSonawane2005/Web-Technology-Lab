<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

// Get employee count by department
$deptStats = $pdo->query('SELECT department, COUNT(*) as count FROM employees WHERE department IS NOT NULL AND department != "" GROUP BY department ORDER BY count DESC')->fetchAll(PDO::FETCH_ASSOC);

// Get employee count by position
$posStats = $pdo->query('SELECT position, COUNT(*) as count FROM employees WHERE position IS NOT NULL AND position != "" GROUP BY position ORDER BY count DESC')->fetchAll(PDO::FETCH_ASSOC);

// Total employees
$total = $pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Employee Reports</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background: #f8f9fa; }
    .sidebar { background: linear-gradient(135deg, #343a40 0%, #495057 100%); min-height: 100vh; position: fixed; width: 250px; }
    .sidebar .nav-link { color: #fff; padding: 15px 20px; }
    .sidebar .nav-link:hover { background: rgba(255,255,255,0.1); }
    .content { margin-left: 250px; padding: 20px; }
    .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .card-header { background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%); color: white; border-radius: 15px 15px 0 0; }
    .btn { border-radius: 25px; }
  </style>
</head>
<body>
<div class="sidebar">
  <div class="p-3">
    <h5 class="text-white"><i class="fas fa-users"></i> EMS</h5>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="employees.php"><i class="fas fa-users"></i> Employees</a>
      </li>
      <li class="nav-item">
        <a class="nav-link active" href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </li>
    </ul>
  </div>
</div>

<div class="content">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h3 mb-0"><i class="fas fa-chart-pie"></i> Employee Reports</h1>
      <p class="text-muted mb-0">Statistics and insights on employee data.</p>
    </div>
    <a href="employees.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left"></i> Back to Employees</a>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="card shadow-sm mb-4">
        <div class="card-header">
          Employees by Department
        </div>
        <div class="card-body">
          <?php if (count($deptStats) > 0): ?>
            <ul class="list-group list-group-flush">
              <?php foreach ($deptStats as $stat): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <?= htmlspecialchars($stat['department']) ?>
                  <span class="badge bg-primary rounded-pill"><?= $stat['count'] ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-muted">No department data available.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow-sm mb-4">
        <div class="card-header">
          Employees by Position
        </div>
        <div class="card-body">
          <?php if (count($posStats) > 0): ?>
            <ul class="list-group list-group-flush">
              <?php foreach ($posStats as $stat): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <?= htmlspecialchars($stat['position']) ?>
                  <span class="badge bg-success rounded-pill"><?= $stat['count'] ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-muted">No position data available.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header">
          Department Distribution
        </div>
        <div class="card-body">
          <canvas id="deptChart" width="400" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header">
      Summary
    </div>
    <div class="card-body">
      <div class="row text-center">
        <div class="col-4">
          <h3 class="text-primary"><?= $total ?></h3>
          <p>Total Employees</p>
        </div>
        <div class="col-4">
          <h3 class="text-success"><?= count($deptStats) ?></h3>
          <p>Departments</p>
        </div>
        <div class="col-4">
          <h3 class="text-info"><?= count($posStats) ?></h3>
          <p>Positions</p>
        </div>
      </div>
    </div>
  </div>
</div>

<footer class="text-center text-light mt-5 py-3">
  <p>&copy; 2026 Employee Management System. Built with PHP & MySQL.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const deptData = <?= json_encode($deptStats) ?>;
  const labels = deptData.map(item => item.department);
  const data = deptData.map(item => item.count);

  const ctx = document.getElementById('deptChart').getContext('2d');
  new Chart(ctx, {
    type: 'pie',
    data: {
      labels: labels,
      datasets: [{
        label: 'Employees',
        data: data,
        backgroundColor: [
          '#FF6384',
          '#36A2EB',
          '#FFCE56',
          '#4BC0C0',
          '#9966FF',
          '#FF9F40'
        ],
        hoverOffset: 4
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom',
        }
      }
    }
  });
</script>
</body>
</html>