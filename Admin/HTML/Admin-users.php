<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
  header("Location: ZE-Electronics.php");
  exit();
}

require_once '../PHP/db.php'; // Adjust path if needed

// Fetch all users - including username
$users_query = "SELECT id, firstname, lastname, middlename, username, email, role, gender 
                FROM users 
                ORDER BY id DESC";
$users_result = $conn->query($users_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Management — Procurement System</title>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <!-- SweetAlert2 CDN -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Your custom CSS with cache-busting -->
  <link rel="stylesheet" href="../CSS/user-management.css?v=<?= time(); ?>" />
</head>

<body>
  <div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="profile">
        <img src="../Assets/Avatar.jpg" alt="Admin" />
        <span class="role">ADMIN</span>
      </div>
      <nav class="nav-menu">
        <ul>
          <li><a href="AdminZE.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
          <li><a href="Admin-users.php" class="active"><i class="fas fa-users"></i> User Management</a></li>
          <li><a href="Pending-approvals.php"><i class="fas fa-clock"></i> Pending Approvals</a></li>
          <li><a href="suppliers.php"><i class="fas fa-truck-field"></i> Suppliers</a></li>
          <li><a href="admin_price_prediction.php"><i class="fas fa-chart-line"></i> Price Prediction</a></li>
          <li><a href="admin_returns.php"><i class="fas fa-rotate-left"></i> Item Returns</a></li>
      <li><a href="HistoryZE.php"><i class="fas fa-history"></i> History</a></li>
        </ul>
      </nav>
      <a href="../PHP/logout.php" class="logout-btn">LOGOUT</a>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div class="page-header">
        <h1>User Management</h1>
        <button class="add-btn" onclick="openAddModal()">
          <i class="fas fa-plus"></i> Add Employee
        </button>
      </div>

      <div class="search-bar">
        <div class="search-input">
          <input type="text" placeholder="Search by ID, Username, Name or Email" id="searchInput"
            onkeyup="searchTable()" />
          <i class="fas fa-search"></i>
        </div>
      </div>

      <div class="table-card">
        <table class="employee-table" id="employeeTable">
          <thead>
            <tr>
              <th>Employee ID</th>
              <th>Username</th>
              <th>Last Name</th>
              <th>First Name</th>
              <th>Middle Name</th>
              <th>Role</th>
              <th>Gender</th>
              <th>Email</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="employeeTableBody">
            <?php if ($users_result->num_rows > 0): ?>
              <?php while ($user = $users_result->fetch_assoc()): ?>
                <tr data-id="<?= $user['id'] ?>">
                  <td><?= htmlspecialchars($user['id']) ?></td>
                  <td><?= htmlspecialchars($user['username'] ?: '—') ?></td>
                  <td><?= htmlspecialchars($user['lastname'] ?: '—') ?></td>
                  <td><?= htmlspecialchars($user['firstname'] ?: '—') ?></td>
                  <td><?= htmlspecialchars($user['middlename'] ?: '—') ?></td>
                  <td><?= htmlspecialchars($user['role'] ?: '—') ?></td>
                  <td><?= htmlspecialchars($user['gender'] ?: '—') ?></td>
                  <td><?= htmlspecialchars($user['email'] ?: '—') ?></td>
                  <td class="actions">
                    <button class="btn-edit" onclick="openEditModal(
                      <?= $user['id'] ?>,
                      '<?= htmlspecialchars(addslashes($user['firstname'])) ?>',
                      '<?= htmlspecialchars(addslashes($user['lastname'])) ?>',
                      '<?= htmlspecialchars(addslashes($user['middlename'] ?: '')) ?>',
                      '<?= htmlspecialchars(addslashes($user['username'] ?: '')) ?>',
                      '<?= htmlspecialchars(addslashes($user['email'])) ?>',
                      '<?= htmlspecialchars($user['gender'] ?: '') ?>',
                      '<?= htmlspecialchars($user['role'] ?: '') ?>'
                    )">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-delete" onclick="deleteUser(<?= $user['id'] ?>)">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="9" class="no-data">No users found</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <!-- ADD MODAL -->
  <div id="addModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeAddModal()">×</span>
      <h2 class="modalAdd">Add Employee</h2>
      <form id="addForm">
        <div class="form-grid">
          <div class="form-group">
            <label>First Name</label>
            <input type="text" id="addFirstName" required />
          </div>
          <div class="form-group">
            <label>Last Name</label>
            <input type="text" id="addLastName" required />
          </div>
          <div class="form-group">
            <label>Middle Name</label>
            <input type="text" id="addMiddleName" />
          </div>
          <div class="form-group">
            <label>Username</label>
            <input type="text" id="addUsername" required />
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" id="addEmail" required />
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" id="addPassword" required />
          </div>
          <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" id="addConfirmPassword" required />
          </div>
          <div class="form-group">
            <label>Gender</label>
            <select id="addGender" required>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
          <div class="form-group">
            <label>Role</label>
            <select id="addRole" required>
              <option value="ADMIN">Admin</option>
              <option value="REQUESTOR">Requestor</option>
              <option value="FINANCE">Finance</option>
              <option value="BUYER">Buyer</option>
            </select>
          </div>
        </div>

        <!-- Password Requirements -->
        <div class="password-requirements" id="passwordRequirements">
          <p><strong>Password Requirements:</strong></p>
          <ul>
            <li id="length">At least 8 characters</li>
            <li id="capital">At least 1 uppercase letter</li>
            <li id="number">At least 3 numbers</li>
            <li id="special">At least 1 special character (!@#$%^&*)</li>
            <li id="match">Passwords must match</li>
          </ul>
        </div>

        <div class="modal-actions">
          <button type="button" class="cancel-btn" onclick="closeAddModal()">Cancel</button>
          <button type="submit" class="save-btn" id="addSubmitBtn" disabled>Add Employee</button>
        </div>
      </form>
    </div>
  </div>

  <!-- EDIT MODAL -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeEditModal()">×</span>
      <h2 class="modalEdit">Edit User</h2>
      <form id="editForm">
        <input type="hidden" id="editId" />
        <div class="form-grid">
          <div class="form-group">
            <label>First Name</label>
            <input type="text" id="editFirstName" required />
          </div>
          <div class="form-group">
            <label>Last Name</label>
            <input type="text" id="editLastName" required />
          </div>
          <div class="form-group">
            <label>Middle Name</label>
            <input type="text" id="editMiddleName" />
          </div>
          <div class="form-group">
            <label>Username</label>
            <input type="text" id="editUsername" required />
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" id="editEmail" required />
          </div>
          <div class="form-group">
            <label>Gender</label>
            <select id="editGender" required>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
          <div class="form-group">
            <label>Role</label>
            <select id="editRole" required>
              <option value="ADMIN">Admin</option>
              <option value="REQUESTOR">Requestor</option>
              <option value="FINANCE">Finance</option>
              <option value="BUYER">Buyer</option>
            </select>
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
          <button type="submit" class="save-btn">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Your JavaScript -->
  <script src="../JS/user-management.js?v=<?= time(); ?>"></script>
</body>

</html>