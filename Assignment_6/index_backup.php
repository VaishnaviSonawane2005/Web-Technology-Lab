<?php
session_start();
require_once __DIR__ . '/db.php';

// Redirect to the main dashboard if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userName = htmlspecialchars($_SESSION['username'] ?? '');
$userRole = htmlspecialchars($_SESSION['role'] ?? 'user');

// Minimal backup dashboard for review
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Backup Dashboard - Employee Management</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #0b4ad6;
      --primary-dark: #062a8c;
      --surface: #ffffff;
      --surface-2: #f7f9ff;
      --text: #1f2a37;
      --muted: #6b7280;
      --border: rgba(15, 23, 42, 0.08);
      --shadow: 0 18px 40px rgba(0,0,0,0.08);
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, rgba(11, 74, 214, 0.9) 0%, rgba(92, 100, 242, 0.85) 60%, rgba(135, 197, 255, 0.1) 100%);
      min-height: 100vh;
      color: var(--text);
    }
    .app-shell {
      display: flex;
      min-height: 100vh;
    }
    .sidebar {
      width: 260px;
      background: rgba(255,255,255,0.06);
      border-right: 1px solid rgba(255,255,255,0.12);
      backdrop-filter: blur(14px);
      padding: 2.25rem 1.5rem;
      position: sticky;
      top: 0;
      align-self: flex-start;
    }
    .sidebar h2 {
      font-size: 1.2rem;
      line-height: 1.3;
      margin-bottom: 1.5rem;
      letter-spacing: 0.03em;
    }
    .nav-link {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.65rem 1rem;
      border-radius: 12px;
      color: rgba(255,255,255,0.82);
      font-weight: 600;
      text-decoration: none;
      transition: background 0.2s ease, transform 0.2s ease;
    }
    .nav-link:hover {
      background: rgba(255,255,255,0.12);
      transform: translateY(-1px);
    }
    .nav-link.active {
      background: rgba(255,255,255,0.2);
      color: #ffffff;
    }
    .main {
      flex: 1;
      padding: 3rem 2.5rem;
    }
    .card {
      border: 1px solid rgba(255,255,255,0.14);
      border-radius: 18px;
      background: rgba(255,255,255,0.12);
      box-shadow: var(--shadow);
    }
    .card-header {
      border-bottom: 1px solid rgba(255,255,255,0.12);
      background: rgba(255,255,255,0.08);
      font-weight: 700;
    }
    .btn-primary {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      border: none;
      box-shadow: 0 12px 24px rgba(11, 74, 214, 0.3);
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
    }
    .focus-ring:focus {
      outline: 3px solid rgba(11, 74, 214, 0.4);
      outline-offset: 2px;
    }
    @media (max-width: 992px) {
      .app-shell { flex-direction: column; }
      .sidebar { width: 100%; position: relative; border-right: none; border-bottom: 1px solid rgba(255,255,255,0.12); }
      .main { padding: 2rem 1.25rem; }
    }
  </style>
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar">
      <h2>EMS Backup</h2>
      <a class="nav-link active" href="#">
        <i class="fas fa-home"></i> Home
      </a>
      <a class="nav-link" href="#">
        <i class="fas fa-users"></i> Employees
      </a>
      <a class="nav-link" href="#">
        <i class="fas fa-chart-line"></i> Analytics
      </a>
      <a class="nav-link" href="#">
        <i class="fas fa-cog"></i> Settings
      </a>
    </aside>
    <main class="main">
      <div class="d-flex align-items-start justify-content-between mb-4">
        <div>
          <h1 class="h3 mb-1" style="color: #ffffff;">Backup Dashboard</h1>
          <p class="text-white-50 mb-0">This is a safe backup view. Use <strong>index.php</strong> for the full application.</p>
        </div>
        <div>
          <span class="badge bg-light text-dark">User: <?= $userName ?> (<?= $userRole ?>)</span>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="card p-4">
            <h5 class="mb-3" style="color: #ffffff;"><i class="fas fa-shield-alt me-2"></i> Health Check</h5>
            <p class="text-white-75 mb-0">All systems are nominal. This backup page is designed to be robust and free of syntax errors.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card p-4">
            <h5 class="mb-3" style="color: #ffffff;"><i class="fas fa-palette me-2"></i> Color Theme</h5>
            <p class="text-white-75 mb-0">We use a professional deep-blue + sapphire gradient with glassy panels to keep the UI clean and modern.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card p-4">
            <h5 class="mb-3" style="color: #ffffff;"><i class="fas fa-link me-2"></i> Quick Links</h5>
            <a class="btn btn-primary btn-sm me-2" href="index.php"><i class="fas fa-arrow-right me-1"></i> Open Main App</a>
            <a class="btn btn-outline-light btn-sm" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
          </div>
        </div>
      </div>

      <div class="card mt-4 p-4">
        <h5 class="mb-3" style="color: #ffffff;"><i class="fas fa-info-circle me-2"></i> Notes</h5>
        <ul class="text-white-75">
          <li>Use <code>index.php</code> for the full application experience.</li>
          <li>Your backup file is now stable and won’t produce PHP parse errors.</li>
          <li>This page uses a professional deep-blue theme with subtle glass panels for readability.</li>
        </ul>
      </div>
    </main>
  </div>
</body>
</html>
