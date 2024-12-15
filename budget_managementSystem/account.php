<?php
require 'db_connect.php';

$rowsPerPage = isset($_GET['rowsPerPage']) && is_numeric($_GET['rowsPerPage']) ? (int)$_GET['rowsPerPage'] : 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $rowsPerPage;

// Check if rowsPerPage is 'all' and handle the query accordingly
if ($rowsPerPage === 'all') {
    $sql = "SELECT role, fullname, user_id, email, status FROM users";
} else {
    $sql = "SELECT role, fullname, user_id, email, status FROM users LIMIT ?, ?";
}

$stmt = $conn->prepare($sql);

// If rowsPerPage is not 'all', bind parameters for pagination
if ($rowsPerPage !== 'all') {
    $stmt->bind_param("ii", $offset, $rowsPerPage);
}

$stmt->execute();
$result = $stmt->get_result();

// Count total rows
$totalRowsResult = $conn->query("SELECT COUNT(*) AS total FROM users");
$totalRows = $totalRowsResult->fetch_assoc()['total'];
$totalPages = $rowsPerPage === 'all' ? 1 : ceil($totalRows / $rowsPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="assets/css/style.css">
	<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
   <link rel="icon" type="image/png" href="assets/img/laur.png">
	<title>BMS</title>
    <style>
  table tbody tr td {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  table th {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
</style>
</head>
<body>


	<!-- SIDEBAR -->
	<?php require("./sidemenu.php"); ?>
	<!-- SIDEBAR -->

	<!-- CONTENT -->
    <section id="content" style="background: transparent;">
  <!-- MAIN -->
  <main>
    <div class="head-title">
      <div class="left">
        <h1>Account Management</h1>
      </div>
    </div>
    <!-- Success/Error message placement -->
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success">
          <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
    <?php elseif (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger">
          <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>
    <!-- Button to trigger modal -->
    <div class="container mt-4">
   <div class="d-flex justify-content-end" style="padding-left:30px;">
  <button type="button" class="btn btn-primary col-md-2 mb-3" data-bs-toggle="modal" data-bs-target="#addDataModal">
    Add New Users
  </button>
  </div>
      <!-- Table with pagination -->
      <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
        <table class="table table-bordered table-striped table-hover">
          <thead>
            <tr>
              <th>NO.</th>
              <th>USER ID</th>
              <th>USER NAME</th>
              <th>ROLE</th>
              <th>EMAIL</th>
              <th>STATUS</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($result->num_rows > 0) {
                $i = $offset + 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                          <td>{$i}</td>
                          <td>{$row['user_id']}</td>
                          <td>{$row['fullname']}</td>
                          <td>{$row['role']}</td>
                          <td>{$row['email']}</td>
                          <td>{$row['status']}</td>
                        </tr>";
                    $i++;
                }
            } else {
                echo "<tr><td colspan='9'>No records found</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination and rows per page controls -->
      <div class="d-flex justify-content-between">
    <div class="form-group">
        <label for="rowsPerPage">Rows per page:</label>
        <select id="rowsPerPage" class="form-control" style="width: auto; display: inline-block;" onchange="changeRowsPerPage(this.value)">
            <option value="10" <?php if ($rowsPerPage == 10) echo "selected"; ?>>10</option>
            <option value="50" <?php if ($rowsPerPage == 50) echo "selected"; ?>>50</option>
            <option value="100" <?php if ($rowsPerPage == 100) echo "selected"; ?>>100</option>
            <option value="all" <?php if ($rowsPerPage == 'all') echo "selected"; ?>>All</option>
        </select>
    </div>

    <?php if ($rowsPerPage !== 'all'): ?>
    <nav aria-label="Table pagination">
        <ul class="pagination">
            <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&rowsPerPage=<?php echo $rowsPerPage; ?>">Previous</a></li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&rowsPerPage=<?php echo $rowsPerPage; ?>"><?php echo $i; ?></a></li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&rowsPerPage=<?php echo $rowsPerPage; ?>">Next</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
    </div>
    <div class="d-flex justify-content-center align-items-center" style="height: 10vh;">
        <div class="col-4">
            <button class="btn btn-primary fw-bold btn-block" id="generate-report-account">GENERATE REPORT</button>
        </div>
    </div>
   <!-- Modal Structure -->
   <div class="modal fade" id="addDataModal" tabindex="-1" aria-labelledby="addDataModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#1560BD;">
        <h5 class="modal-title" id="addDataModalLabel" style="color:white;">Add New Users</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size:12pt; width:25px; background-color:red;">X</button>
      </div>
      <div class="modal-body">
        <form id="addAccountForm" action="addAccount.php" method="POST">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="fullname" class="form-label">Fullname</label>
              <input type="text" class="form-control" id="fullname" name="fullname" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control" id="username" name="username" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="confirm_password" class="form-label">Confirm Password</label>
              <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="role" class="form-label">Role</label> <br>
              <select class="form-select" id="role" name="role" required>
                <option value="Administrator">Administrator</option>
                <option value="Encoder">Encoder</option>
                <option value="Operator">Operator</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" required>
              <div id="email-error" class="text-danger" style="display:none;">Invalid email. Only @gmail.com is allowed.</div>
            </div>
          </div>

          <div class="text-center">
            <button type="submit" class="btn btn-primary">Save Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
  </main>
  <!-- MAIN -->
</section>
	<!-- CONTENT -->
	

	<script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-..." crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" integrity="sha384-..." crossorigin="anonymous"></script>
    <script>
  function changeRowsPerPage(rows) {
    window.location.href = "?rowsPerPage=" + rows;
  }
  document.getElementById('addAccountForm').addEventListener('submit', function (event) {
    const emailField = document.getElementById('email');
    const emailError = document.getElementById('email-error');
    if (!emailField.value.endsWith('@gmail.com')) {
      emailError.style.display = 'block';
      event.preventDefault(); // Prevent form submission
    } else {
      emailError.style.display = 'none';
    }
  });
  document.getElementById('generate-report-account').addEventListener('click', function() {
                window.location.href = `pdf/account_pdf.php`;
        });
</script>
</body>
</html>