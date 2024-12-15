<?php
require 'db_connect.php';

$rowsPerPage = isset($_GET['rowsPerPage']) ? $_GET['rowsPerPage'] : 10; // Default 10
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $rowsPerPage;

$department = isset($_GET['department']) ? $_GET['department'] : ''; // Selected department filter
$dataLoaded = false;
$totalPages = 1;  // Initialize $totalPages to avoid undefined variable warning

// Fetch data only after clicking the 'View' button
if (isset($_GET['view'])) {
    $dataLoaded = true;
    // SQL query with department filter
    $sql = "SELECT a.office_from, a.office_to, a.amount, a.date_transfer, a.type_of_transfer
            FROM tbl_transfer a 
            INNER JOIN tbl_budget b ON (a.office_from = b.office or a.office_from = b.acc_name)";
    
    if (!empty($department)) {
        $sql .= " WHERE b.identifier = '$department' and b.status = 'active'";
    }
    
    $sql .= " LIMIT $offset, $rowsPerPage";
    $result = $conn->query($sql);
    // Check if the query failed
    if (!$result) {
        die("Error in query: " . $conn->error);
    }

    $sql2 = "SELECT budget, balance FROM tbl_departments";
    if (!empty($department)) {
        $sql2 .= " WHERE identifier = '$department'";
    }
    $result2 = $conn->query($sql2);   
    // Check if the query failed
    if (!$result2) {
        die("Error in query: " . $conn->error);
    }

    $totalResult = $conn->query("SELECT COUNT(*) AS total FROM tbl_transfer");
    if (!$totalResult) {
        die("Error in counting total rows: " . $conn->error);
    }
    $totalRows = $totalResult->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $rowsPerPage);
}

$department_sql = "SELECT identifier, department_name FROM tbl_departments order by department_name asc";
$departments = $conn->query($department_sql);

// Check for errors in department query
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
                    <h1>Supplimental/Realignment</h1>
                </div>
            </div>
            <?php
            if (isset($_SESSION['message'])) {
                echo "<div class='alert alert-success'>{$_SESSION['message']}</div>";
                unset($_SESSION['message']);
            }
            ?>
            <div class="container mt-4">
                <div class="form-row">
                    <!-- Department Dropdown -->
                    <div class="col-md-4 mb-3">
                        <form method="GET" action="">
                            <select class="form-control" id="department" name="department" required>
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
                    <div class="col-md-2 mb-3" hidden>
                        <button type="submit" name="view" class="btn btn-success w-100" id="autofetch">
                            View
                        </button>
                    </div>
                    
                    <div class="col-md-3 mb-3 d-flex justify-content-end"  style="margin-left:165px;">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRealignmentModal">
                        Add Realignment
                        </button>
                    </div>
                    <!-- Add New Record Button aligned to the right -->
                    <div class="col-md-3 mb-3  d-flex justify-content-end">
                        <button type="button" class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#addSupplementalModal">
                           Add Supplemental
                        </button>
                    </div>         
                    </form>
                </div>                
                <div class="form-row">
                <div class="col-md-4">
                    <span for="budget" class="form-label">Unused Funds:</span>
                    <?php
                    // Display budget only if $result2 has been set
                    if ($dataLoaded && $result2 && $result2->num_rows > 0) {
                        while ($row = $result2->fetch_assoc()) {
                            echo "<input type='text' class='form-control' value='{$row['balance']}' required disabled>";
                        }
                    } else {
                        echo "<input type='text' class='form-control' value='0' required disabled>";
                    }
                    ?>
                </div>
            </div>
            <br>
                <!-- Display "No records found" before fetching data -->
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-bordered table-striped table-hover">
    <thead>
        <tr>
            <th>No.</th>
            <th>DATE</th>
            <th>OFFICE/ACCOUNT FROM</th>
            <th>OFFICE/ACCOUNT TO</th>
            <th>AMOUNT</th>
            <th>TYPE OF TRANSACTION</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($dataLoaded && $result->num_rows > 0) {
            $i = $offset + 1;
            while ($row = $result->fetch_assoc()) {
                echo "<td>{$i}</td>
                    <td>{$row['date_transfer']}</td>
                    <td>{$row['office_from']}</td>
                    <td>{$row['office_to']}</td>
                    <td>{$row['amount']}</td>
                    <td>{$row['type_of_transfer']}</td>
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
                              echo "<button class='btn btn-primary fw-bold btn-block' id='generate-report-supre'>GENERATE REPORT</button>";
                            } else {
                                echo "<button class='btn btn-primary fw-bold btn-block' id='generate-report-supre' disabled>GENERATE REPORT</button>";
                            }
                            ?>                    
                </div>
            </div>
                        <!--modal supplemental-->
                        <div class="modal fade" id="addSupplementalModal" tabindex="-1" aria-labelledby="addDataModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #1560BD;">
        <h5 class="modal-title" id="addDataModalLabel" style="color: white;">Add New Supplemental Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="add_supplemental.php" method="POST">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="officeFrom" class="form-label">Department From:</label>
              <select class="form-control" id="officeFrom" name="officeFrom" required onchange="getBudget()">
                <option value="">-- Select Department From --</option>
                <?php
                $department_sql = "SELECT identifier, department_name, balance 
                                   FROM tbl_departments WHERE balance != 0 ORDER BY department_name ASC";
                $departments = $conn->query($department_sql);
                if ($departments->num_rows > 0) {
                  while ($row = $departments->fetch_assoc()) {
                    echo "<option value='{$row['department_name']}' data-acc-code='{$row['balance']}'>{$row['department_name']}</option>";
                  }
                } else {
                  echo "<option value=''>No departments available</option>";
                }
                ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="budget" class="form-label">Budget</label>
              <input type="text" class="form-control" id="budget" name="budget" required disabled>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="officeTo" class="form-label">Department To:</label>
              <select class="form-control" id="officeTo" name="officeTo" required>
                <option value="">-- Select Department To --</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="amount" class="form-label">Transfer Amount</label>
              <input type="number" class="form-control" id="amount" name="amount" required>
            </div>
          </div>
          <div class="mb-3">
            <label for="date_transfer" class="form-label">Date</label>
            <input type="date" class="form-control" id="date_transfer" name="date_transfer" required>
          </div>
          <div class="text-center">
            <button type="submit" class="btn btn-primary">Save Record</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

 <!--modal realignment-->
 <div class="modal fade" id="addRealignmentModal" tabindex="-1" aria-labelledby="addDataModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #1560BD;">
        <h5 class="modal-title" id="addDataModalLabel" style="color: white;">Add New Realignment Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="add_realignment.php" method="POST">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="office" class="form-label">Department From:</label>
              <select class="form-control" id="office" name="office" required onchange="getAccount()">
                <option value="">-- Select Department From --</option>
                <?php
                $department_sql = "SELECT office, identifier, balance 
                FROM tbl_budget GROUP BY identifier ORDER BY office ASC";

                $departments = $conn->query($department_sql);
                if ($departments->num_rows > 0) {
                  while ($row = $departments->fetch_assoc()) {
                    echo "<option value='{$row['identifier']}'>{$row['office']}</option>";
                  }
                } else {
                  echo "<option value=''>No departments available</option>";
                }
                ?>
              </select>
              <input type="hidden" id="identifier" name="identifier">
            </div>
            <div class="col-md-6">
            <label for="redate_transfer" class="form-label">Date</label>
            <input type="date" class="form-control" id="redate_transfer" name="redate_transfer" required>
          </div>
          </div>
          <div class="row mb-3">
          <div class="col-md-6">
            <label for="acc_name_from" class="form-label">Account Name From:</label>
              <select type="text" class="form-control" id="acc_name_from" name="acc_name_from" required onchange="getAccBudget()">
                                        <option value="">-- Select Account Name From --</option>
                                    </select>
            </div>
          <div class="col-md-6">
              <label for="budgetRe" class="form-label">Budget</label>
              <input type="text" class="form-control" id="budgetRe" name="budgetRe" required disabled>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="acc_name_to" class="form-label">Account Name To:</label>
              <select class="form-control" id="acc_name_to" name="acc_name_to" required>
                <option value="">-- Select Account Name To --</option>
                <?php
                                        $acc_title_sql = "SELECT acc_code, acc_title FROM tbl_list
                                        WHERE acc_code NOT IN ('000 00 000') order by acc_title asc";
                                        $acc_title = $conn->query($acc_title_sql);
                                        if ($acc_title->num_rows > 0) {
                                            while ($row = $acc_title->fetch_assoc()) {
                                                echo "<option value='{$row['acc_title']}' data-acc-code='{$row['acc_code']}'>{$row['acc_title']}</option>";
                                            }
                                        } else {
                                            echo "<option value=''>No available</option>";
                                        }
                                        ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="amount" class="form-label">Transfer Amount</label>
              <input type="number" class="form-control" id="amount" name="amount" required>
            </div>
          </div> 

          <div class="text-center">
            <button type="submit" class="btn btn-primary">Save Record</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
        </main>
        <!-- MAIN 
        -->
    </section>
    <!-- CONTENT -->

	<script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-..." crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" integrity="sha384-..." crossorigin="anonymous"></script>
    <script>
  function changeRowsPerPage(rows) {
    window.location.href = "?rowsPerPage=" + rows;
  }

  function getBudget() {
  const officeFrom = document.getElementById('officeFrom');
  const selectedOption = officeFrom.options[officeFrom.selectedIndex];
  const budget = selectedOption.getAttribute('data-acc-code');
  const budgetField = document.getElementById('budget');
  const officeTo = document.getElementById('officeTo');

  if (budget) {
    budgetField.value = budget;
  } else {
    budgetField.value = '';
  }

  const selectedIdentifier = officeFrom.value;
  if (!selectedIdentifier) {
    officeTo.innerHTML = '<option value="">-- Select Department To --</option>';
    officeTo.disabled = true;
    return;
  }

  officeTo.disabled = true;
  fetch(`get_departments.php?exclude=${selectedIdentifier}`)
    .then(response => response.json())
    .then(data => {
      officeTo.innerHTML = '<option value="">-- Select Department To --</option>';
      data.forEach(department => {
        const option = document.createElement('option');
        option.value = department.department_name;
        option.textContent = department.department_name;
        officeTo.appendChild(option);
      });
      officeTo.disabled = false;
    })
    .catch(error => {
      console.error('Error fetching departments:', error);
      officeTo.innerHTML = '<option value="">Error loading options</option>';
      officeTo.disabled = false;
    });
}

  document.addEventListener("DOMContentLoaded", function () {
    const departmentSelect = document.getElementById("department");
    const autofetchButton = document.getElementById("autofetch");

    if (departmentSelect && autofetchButton) {
        departmentSelect.addEventListener("change", function () {
            autofetchButton.click();
        });
    } else {
        console.error("Required elements not found: department or autofetch.");
    }
});

function getAccount() {
    var departmentId = document.getElementById("office").value;

    if (departmentId) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "get_acc.php?department_id=" + departmentId, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById("acc_name_from").innerHTML = xhr.responseText; 
            }
        };
        xhr.send();
    } else {
        document.getElementById("acc_name_from").innerHTML = "<option value=''>-- Select Account Name --</option>";
    }
}
function getAccBudget() {
  const accFrom = document.getElementById('acc_name_from');
  const selectedOption = accFrom.options[accFrom.selectedIndex];
  const budget = selectedOption.getAttribute('data-acc-code');
  const budgetField = document.getElementById('budgetRe');

  if (budget) {
    budgetField.value = budget;
  } else {
    budgetField.value = '';
  }

var depId = document.getElementById("office").value;
var accName = document.getElementById("acc_name_from").value;

if (accName) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "get_accTo.php?department_id=" + depId + "&acc_id=" + accName, true); // Added "&"
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById("acc_name_to").innerHTML = xhr.responseText; 
        }
    };
    xhr.send();
} else {
    document.getElementById("acc_name_to").innerHTML = "<option value=''>-- Select Account Name --</option>";
}

}

document.getElementById('generate-report-supre').addEventListener('click', function() {
            var department = document.getElementById('department').value;

            if (!department) {
                alert('Please select a department');
            } else {
                window.location.href = `pdf/transfer_pdf.php?department=${department}`;
            }
        });
</script>

</body>
</html>
