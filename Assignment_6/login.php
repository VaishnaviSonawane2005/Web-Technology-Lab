<?php
session_start();
require_once __DIR__ . '/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors[] = 'Please enter both username and password.';
    } else {
        // Check user credentials
        $stmt = $pdo->prepare('SELECT id, username, email, password, role, status FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $username]);

        if ($user = $stmt->fetch()) {
            if ($user['status'] !== 'active') {
                $errors[] = 'Your account is inactive. Please contact administrator.';
            } elseif (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Update last login
                $stmt = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
                $stmt->execute([$user['id']]);

                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Invalid username or password.';
            }
        } else {
            $errors[] = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Employee Management System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      margin: 0;
    }

    .login-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 18px;
      box-shadow: 0 25px 50px rgba(0,0,0,0.18);
      overflow: hidden;
      max-width: 420px;
      width: 100%;
      border: 1px solid rgba(255,255,255,0.2);
    }

    .login-header {
      background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
      color: white;
      padding: 32px 24px;
      text-align: center;
    }

    .login-header h4 {
      margin-bottom: 0.25rem;
      font-weight: 700;
    }

    .login-body {
      padding: 28px 24px;
    }

    .form-control {
      border-radius: 14px;
      border: 1px solid rgba(0,0,0,0.12);
      padding: 14px 16px;
    }

    .btn-login {
      background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
      border: none;
      border-radius: 28px;
      padding: 14px;
      font-weight: 700;
      width: 100%;
      box-shadow: 0 15px 25px rgba(0,0,0,0.2);
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 18px 35px rgba(0,0,0,0.25);
    }

    .right-panel {
      background: linear-gradient(135deg, rgba(13, 110, 253, 0.95) 0%, rgba(102, 16, 242, 0.95) 100%);
      color: rgba(255,255,255,0.95);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 60px 40px;
    }

    .right-panel .login-cta {
      max-width: 520px;
      width: 100%;
      text-align: center;
    }

    .right-panel .login-cta .big-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      padding: 22px 32px;
      font-size: 1.25rem;
      font-weight: 700;
      border-radius: 35px;
      background: rgba(255,255,255,0.15);
      border: 1px solid rgba(255,255,255,0.25);
      color: #fff;
      text-decoration: none;
      width: 100%;
      max-width: 420px;
    }

    .right-panel .login-cta .big-btn:hover {
      background: rgba(255,255,255,0.25);
      text-decoration: none;
    }

    @media (max-width: 991px) {
      .right-panel {
        display: none;
      }
    }
  </style>
</head>
<body>
  <div class="container-fluid min-vh-100">
    <div class="row g-0 min-vh-100">
      <!-- Left: Login Form -->
      <div class="col-lg-5 d-flex align-items-center justify-content-center px-4 py-5">
        <div class="login-card">
          <div class="login-header">
            <i class="fas fa-users fa-3x mb-3"></i>
            <h4>Employee Management System</h4>
            <p>Sign in to your account</p>
          </div>
          <div class="login-body">
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <?php if ($successMessage): ?>
              <div class="alert alert-success">
                <?= htmlspecialchars($successMessage) ?>
              </div>
            <?php endif; ?>

            <form method="POST" action="">
              <div class="mb-3">
                <label for="username" class="form-label">
                  <i class="fas fa-user"></i> Username or Email
                </label>
                <input type="text" class="form-control" id="username" name="username" required
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">
                  <i class="fas fa-lock"></i> Password
                </label>
                <input type="password" class="form-control" id="password" name="password" required>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-login">
                  <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
              </div>
            </form>

            <div class="text-center mt-3">
              <small class="text-muted">
                Default admin login: <strong>admin@example.com</strong> / <strong>admin123</strong><br>
                Sample user login: <strong>user@example.com</strong> / <strong>user123</strong>
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Info Panel -->
      <div class="col-lg-7 right-panel d-none d-lg-flex">
        <div class="login-cta">
          <div class="big-btn mb-4">
            <i class="fas fa-sign-in-alt fa-lg"></i>
            <span>LOGIN</span>
          </div>
          <h2 class="fw-bold">Welcome Back!</h2>
          <p class="lead text-white-75">
            Access your dashboard to manage employees, attendance, leave requests, reports, and settings.
          </p>
          <p class="text-white-50 mt-4">
            Use your assigned credentials to sign in securely.
          </p>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>