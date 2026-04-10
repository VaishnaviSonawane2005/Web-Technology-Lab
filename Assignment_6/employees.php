<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db.php';

// Handle actions: add, edit, delete
$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $position = trim($_POST['position'] ?? '');

        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
            $photoPath = 'uploads/' . $fileName;
            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $fileName);
        }

        if ($firstName === '' || $lastName === '' || $email === '') {
            $errors[] = 'First name, last name and email are required.';
        }

        if (empty($errors)) {
            // Check if email already exists
            $stmt = $pdo->prepare('SELECT id FROM employees WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already exists.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO employees (first_name, last_name, email, phone, department, position, photo) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$firstName, $lastName, $email, $phone, $department, $position, $photoPath]);
                $successMessage = 'Employee added successfully.';
            }
        }
    }

    if ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $oldPhoto = trim($_POST['old_photo'] ?? '');

        $photoPath = $oldPhoto;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
            $photoPath = 'uploads/' . $fileName;
            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $fileName);
            // Optionally delete old photo
            if ($oldPhoto && file_exists(__DIR__ . '/' . $oldPhoto)) unlink(__DIR__ . '/' . $oldPhoto);
        }

        if ($id <= 0) {
            $errors[] = 'Invalid employee selected.';
        }
        if ($firstName === '' || $lastName === '' || $email === '') {
            $errors[] = 'First name, last name and email are required.';
        }

        if (empty($errors)) {
            // Check if email already exists for another employee
            $stmt = $pdo->prepare('SELECT id FROM employees WHERE email = ? AND id != ?');
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already exists.';
            } else {
                $stmt = $pdo->prepare('UPDATE employees SET first_name = ?, last_name = ?, email = ?, phone = ?, department = ?, position = ?, photo = ? WHERE id = ?');
                $stmt->execute([$firstName, $lastName, $email, $phone, $department, $position, $photoPath, $id]);
                $successMessage = 'Employee updated successfully.';
            }
        }
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            $errors[] = 'Invalid employee selected.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('DELETE FROM employees WHERE id = ?');
            $stmt->execute([$id]);
            $successMessage = 'Employee deleted successfully.';
        }
    }
}

// Search and pagination
$search = trim($_GET['search'] ?? '');
$page = intval($_GET['page'] ?? 1);
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Build query
$query = 'SELECT * FROM employees WHERE 1=1';
$params = [];
if ($search) {
    $query .= ' AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR department LIKE ? OR position LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_fill(0, 5, $like);
}
$query .= ' ORDER BY id DESC LIMIT ? OFFSET ?';
$params[] = $perPage;
$params[] = $offset;

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$employees = $stmt->fetchAll();

// Get total count for pagination
$countQuery = 'SELECT COUNT(*) FROM employees WHERE 1=1';
$countParams = [];
if ($search) {
    $countQuery .= ' AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR department LIKE ? OR position LIKE ?)';
    $countParams = array_fill(0, 5, $like);
}
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($countParams);
$totalEmployees = $countStmt->fetchColumn();
$totalPages = ceil($totalEmployees / $perPage);

// Department and position options
$departments = ['HR', 'Sales', 'IT', 'Finance', 'Marketing', 'Operations'];
$positions = ['Recruiter', 'Account Manager', 'Developer', 'Analyst', 'Manager', 'Intern', 'Consultant'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Employees</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <style>
    body { background: #f8f9fa; }
    .sidebar { background: linear-gradient(135deg, #343a40 0%, #495057 100%); min-height: 100vh; position: fixed; width: 250px; }
    .sidebar .nav-link { color: #fff; padding: 15px 20px; }
    .sidebar .nav-link:hover { background: rgba(255,255,255,0.1); }
    .content { margin-left: 250px; padding: 20px; }
    .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .table thead { background: #f8f9fa; }
    .btn { border-radius: 25px; }
    .modal-content { border-radius: 15px; }
    .form-control, .form-select { border-radius: 10px; }
    .pagination .page-link { border-radius: 10px; }
    .badge { font-size: 0.8em; }
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
        <a class="nav-link active" href="employees.php"><i class="fas fa-users"></i> Employees</a>
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
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h1 class="h3 mb-0"><i class="fas fa-user-plus"></i> Manage Employees</h1>
      <p class="text-muted mb-0">Add, update, delete and view employee records dynamically.</p>
    </div>
    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus"></i> Add Employee</button>
  </div>

  <form method="GET" class="mb-3">
    <div class="input-group">
      <span class="input-group-text"><i class="fas fa-search"></i></span>
      <input type="text" name="search" class="form-control" placeholder="Search by name, email, department, position..." value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i> Search</button>
      <?php if ($search): ?>
        <a href="?" class="btn btn-outline-danger"><i class="fas fa-times"></i> Clear</a>
      <?php endif; ?>
    </div>
  </form>

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
    <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between">
      <span>Employees</span>
      <span class="badge bg-white text-dark">Total: <?= $totalEmployees ?></span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead>
            <tr>
              <th><i class="fas fa-hashtag"></i> ID</th>
              <th><i class="fas fa-image"></i> Photo</th>
              <th><i class="fas fa-user"></i> Name</th>
              <th><i class="fas fa-envelope"></i> Email</th>
              <th><i class="fas fa-phone"></i> Phone</th>
              <th><i class="fas fa-building"></i> Department</th>
              <th><i class="fas fa-briefcase"></i> Position</th>
              <th><i class="fas fa-calendar"></i> Created</th>
              <th class="text-end"><i class="fas fa-cogs"></i> Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($employees) === 0): ?>
              <tr>
                <td colspan="8" class="text-center py-4">No employee records found. Add a new employee to get started.</td>
              </tr>
            <?php endif; ?>
            <?php foreach ($employees as $employee): ?>
              <tr>
                <td><?= htmlspecialchars($employee['id']) ?></td>
                <td><?php if ($employee['photo']): ?><img src="<?= htmlspecialchars($employee['photo']) ?>" width="50" height="50" class="rounded-circle"><?php else: ?><i class="fas fa-user-circle fa-2x text-muted"></i><?php endif; ?></td>
                <td><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></td>
                <td><?= htmlspecialchars($employee['email']) ?></td>
                <td><?= htmlspecialchars($employee['phone']) ?></td>
                <td><?= htmlspecialchars($employee['department']) ?></td>
                <td><?= htmlspecialchars($employee['position']) ?></td>
                <td><?= htmlspecialchars($employee['created_at']) ?></td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editModal" data-employee='<?= json_encode($employee, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>'><i class="fas fa-edit"></i> Edit</button>
                  <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $employee['id'] ?>" data-name="<?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>"><i class="fas fa-trash"></i> Delete</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php if ($totalPages > 1): ?>
  <nav aria-label="Employee pagination">
    <ul class="pagination justify-content-center">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
  <?php endif; ?>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <div class="modal-header">
          <h5 class="modal-title" id="addModalLabel">Add New Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label">First Name</label>
              <input name="first_name" type="text" class="form-control" required>
            </div>
            <div class="col-6">
              <label class="form-label">Last Name</label>
              <input name="last_name" type="text" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Email</label>
              <input name="email" type="email" class="form-control" required>
            </div>
            <div class="col-6">
              <label class="form-label">Phone</label>
              <input name="phone" type="text" class="form-control">
            </div>
            <div class="col-6">
              <label class="form-label">Department</label>
              <select name="department" class="form-select">
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                  <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Position</label>
              <select name="position" class="form-select">
                <option value="">Select Position</option>
                <?php foreach ($positions as $pos): ?>
                  <option value="<?= htmlspecialchars($pos) ?>"><?= htmlspecialchars($pos) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Photo</label>
              <input name="photo" type="file" class="form-control" accept="image/*">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit-id">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Edit Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label">First Name</label>
              <input id="edit-first_name" name="first_name" type="text" class="form-control" required>
            </div>
            <div class="col-6">
              <label class="form-label">Last Name</label>
              <input id="edit-last_name" name="last_name" type="text" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Email</label>
              <input id="edit-email" name="email" type="email" class="form-control" required>
            </div>
            <div class="col-6">
              <label class="form-label">Phone</label>
              <input id="edit-phone" name="phone" type="text" class="form-control">
            </div>
            <div class="col-6">
              <label class="form-label">Department</label>
              <select id="edit-department" name="department" class="form-select">
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                  <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Position</label>
              <select id="edit-position" name="position" class="form-select">
                <option value="">Select Position</option>
                <?php foreach ($positions as $pos): ?>
                  <option value="<?= htmlspecialchars($pos) ?>"><?= htmlspecialchars($pos) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Photo</label>
              <input name="photo" type="file" class="form-control" accept="image/*">
              <input type="hidden" name="old_photo" id="edit-old_photo">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="delete-id">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Delete Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <strong id="delete-name"></strong>?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
          <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<footer class="text-center text-light mt-5 py-3">
  <p>&copy; 2026 Employee Management System. Built with PHP & MySQL.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></footer>
<script>
  // Populate edit form when edit button clicked
  const editModal = document.getElementById('editModal');
  editModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    const employee = JSON.parse(button.getAttribute('data-employee'));

    document.getElementById('edit-id').value = employee.id;
    document.getElementById('edit-first_name').value = employee.first_name;
    document.getElementById('edit-last_name').value = employee.last_name;
    document.getElementById('edit-email').value = employee.email;
    document.getElementById('edit-phone').value = employee.phone;
    document.getElementById('edit-department').value = employee.department;
    document.getElementById('edit-position').value = employee.position;
    document.getElementById('edit-old_photo').value = employee.photo;
  });

  // Populate delete form when delete button clicked
  const deleteModal = document.getElementById('deleteModal');
  deleteModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-name').textContent = name;
  });
</script>
</body>
</html>
