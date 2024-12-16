<?php
require 'db_connect.php';
session_start();
if(isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
} else {
    $role = "Unknown";
}
$rowsPerPage = isset($_GET['rowsPerPage']) ? $_GET['rowsPerPage'] : 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $rowsPerPage;

$department = isset($_GET['department']) ? $_GET['department'] : '';
$dataLoaded = false;
$totalPages = 1;

if (isset($_GET['view'])) {
    $dataLoaded = true;
    $sql = "SELECT a.id, b.department_name, a.date_created, a.office, a.acc_name, a.acc_code, a.budget, a.reprogram, a.expense, a.balance, a.aro, a.release, a.status, a.reason 
            FROM tbl_budget a 
            INNER JOIN tbl_departments b ON a.identifier = b.identifier";
    
    if (!empty($department)) {
        $sql .= " WHERE b.identifier = '$department' and (a.status = 'rejected' or a.status = 'release')";
    }
    
    $sql .= " LIMIT $offset, $rowsPerPage";
    $result = $conn->query($sql);
    
    if (!$result) {
        die("Error in query: " . $conn->error);
    }

    $totalResult = $conn->query("SELECT COUNT(*) AS total FROM tbl_budget");
    if (!$totalResult) {
        die("Error in counting total rows: " . $conn->error);
    }
    $totalRows = $totalResult->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $rowsPerPage);
}

$department_sql = "SELECT identifier, department_name FROM tbl_departments";
$departments = $conn->query($department_sql);

if (!$departments) {
    die("Error fetching departments: " . $conn->error);
}
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
                    <h1>ARCHIVES</h1>
                </div>
            </div>

            <div class="container mt-4">
                <div class="form-row">
                    <!-- Department Dropdown -->
                    <div class="col-md-4 mb-3">
                        <form method="GET" action="">
                            <select class="form-control ondept" id="department" name="department" required>
                                <option value="">-- Select Department --</option>
                                <?php
                                if ($departments->num_rows > 0) {
                                    while ($row = $departments->fetch_assoc()) {
                                        $selected = ($department == $row['identifier']) ? 'selected' : '';
                                        echo "<option value='{$row['identifier']}' {$selected}>{$row['department_name']}</option>";
                                    }
                                } else {
                                    echo "<option value=''>No departments available</option>";
                                }
                                ?>
                            </select>
                    </div>

                    <!-- View Button -->
                    <div class="col-md-2 mb-3">
                        <button type="submit" name="view" class="btn btn-success w-100" id="clicka">
                            View
                        </button>
                    </div>
                    <!-- <div class="col-md-2 mb-3">
                        <button type="submit" name="view" class="btn btn-success w-100">
                           Create Budget
                        </button>
                    </div> -->
                    </form>
                </div>

                <!-- Display "No records found" before fetching data -->
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-bordered table-striped table-hover">
    <thead>
        <tr>
            <th>No.</th>
            <th>DATE</th>
            <th>OFFICE</th>
            <th>ACCOUNT NAME</th>
            <th>ACCT. CODE</th>
            <th>BUDGET</th>
            <th>EXPENSE</th>
            <th>BALANCE</th>
            <th>ARO</th>
            <th>STATUS</th>
            <th>REASON</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if ($dataLoaded && $result->num_rows > 0) {
        $i = $offset + 1;
        while ($row = $result->fetch_assoc()) {
            if ($row['status'] == 'REJECTED') {
                echo "<tr style='background-color: #f69697;'>";
            } elseif ($row['status'] == 'RELEASE') {
                echo "<tr style='background-color: lightblue;'>";
            } else {
                echo "<tr>";
            }

            echo "<td>{$i}</td>
                <td>{$row['date_created']}</td>
                <td>{$row['office']}</td>
                <td>{$row['acc_name']}</td>
                <td>{$row['acc_code']}</td>
                <td>{$row['budget']}</td>
                <td>{$row['expense']}</td>
                <td>{$row['balance']}</td>              
                <td>{$row['aro']}</td>
                <td>{$row['status']}</td>  
                <td>{$row['reason']}</td>                                  
            </tr>";
            $i++;
        }
    } else {
        echo "<tr><td colspan='15' style='text-align:center;'>No records found</td></tr>";
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
                </div>
            </div>

            <div class="d-flex justify-content-center align-items-center" style="height: 10vh;">
                <div class="col-4">
                <?php
                            if ($dataLoaded && $result->num_rows > 0) {
                              echo "<button class='btn btn-primary fw-bold btn-block' id='generate-report-office'>GENERATE REPORT</button>";
                            } else {
                                echo "<button class='btn btn-primary fw-bold btn-block' id='generate-report-office' disabled>GENERATE REPORT</button>";
                            }
                            ?>                    
                </div>
            </div>

        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

	<script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-..." crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" integrity="sha384-..." crossorigin="anonymous"></script>
    <script>
        $(".ondept").on("change", function() {
        $("#clicka").click();
        });
  function changeRowsPerPage(rows) {
    window.location.href = "?rowsPerPage=" + rows;
  }
  function updateAccountCode() {
        var accNameSelect = document.getElementById("acc_name");
        var selectedOption = accNameSelect.options[accNameSelect.selectedIndex];
        var accountCodeInput = document.getElementById("acc_code");

        accountCodeInput.value = selectedOption.getAttribute("data-acc-code");
    }

    function updateIdentifier() {
        var accNameSelect = document.getElementById("office");
        var selectedOption = accNameSelect.options[accNameSelect.selectedIndex];
        var accountCodeInput = document.getElementById("identifier");
        accountCodeInput.value = selectedOption.getAttribute("data-acc-code");
    }
    document.getElementById('generate-report-office').addEventListener('click', function() {
            var department = document.getElementById('department').value;

            if (!department) {
                alert('Please select a department');
            } else {
                window.location.href = `pdf/archive_pdf.php?department=${department}`;
            }
        });

</script>

</body>
</html>
