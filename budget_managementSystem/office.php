<?php
require 'db_connect.php';
session_start();
if(isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
} else {
    $role = "Unknown";
}
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
    $sql = "SELECT a.id, b.department_name, a.date_created, a.office, a.acc_name, a.acc_code, a.budget, a.supplemental, a.realignment, a.reprogram, a.expense, a.balance, a.aro, a.release, a.status 
            FROM tbl_budget a 
            INNER JOIN tbl_departments b ON a.identifier = b.identifier";
    
    if (!empty($department)) {
        $sql .= " WHERE b.identifier = '$department' and a.status = 'active'";
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

    $totalResult = $conn->query("SELECT COUNT(*) AS total FROM tbl_budget");
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
                    <h1>Departments/Offices</h1>
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
                        <button type="submit" name="view" class="btn btn-success w-100" id="onchange">
                            View
                        </button>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <?php  
                        if (strcasecmp($role, 'Operator') === 0) {
                          echo '';
                      }  
                      else{
                        echo '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDataModal">Allocate Budget</button>';
                      }                    
                        ?>
                    </div>
                    <!-- Add New Record Button aligned to the right -->
                    <div class="col-md-2 mb-3  d-flex justify-content-end">
                    <?php  
                        if (strcasecmp($role, 'Operator') === 0) {
                          echo '';
                      }  
                      else{
                        echo '<button type="button" class="btn btn-primary w-100"  data-bs-toggle="modal" data-bs-target="#addBudgetModal">Create Budget</button>';
                      }                    
                        ?>                                                 
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
            <th>OFFICE</th>
            <th>ACCOUNT NAME</th>
            <th>ACCT. CODE</th>
            <th>BUDGET</th>
            <th>EXPENSE</th>
            <th>UNUSED FUNDS</th>
            <th>ARO</th>
            <th>STATUS</th>
            <?php  
                        if (strcasecmp($role, 'Operator') === 0) {
                          echo '';
                      }  
                      else{
                        echo '<th>ACTIONS</th>';
                      }                    
                        ?>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($dataLoaded && $result->num_rows > 0) {
            $i = $offset + 1;
            while ($row = $result->fetch_assoc()) {
                if ($row['status'] == 'ACTIVE') {
                    echo "<tr style='background-color: lightgreen;'>";
                } else {
                    echo "<tr>"; 
                }
                if (strcasecmp($role, 'Operator') === 0) {
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
                </tr>";
              }  
              else{
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
                    <td>
                        <!-- Edit Button 
                        <button class='btn btn-warning btn-sm editButton'
                                data-bs-toggle='modal'
                                data-bs-target='#editModal'
                                data-id='{$row['id']}'
                                data-office='{$row['office']}'
                                data-accname='{$row['acc_name']}'
                                data-acccode='{$row['acc_code']}'
                                data-budget='{$row['budget']}'
                                data-expense='{$row['expense']}'
                                data-balance='{$row['balance']}'
                                data-aro='{$row['aro']}'>
                            <i class='bx bx-edit'></i> Edit
                        </button> -->
                        
    <!-- Reject Button with Modal -->
    <button type='button' class='btn btn-danger btn-sm rejectButton' 
            data-bs-toggle='modal' 
            data-bs-target='#confirmationModalReject' 
            data-id='{$row['id']}' 
            data-action='reject'>
        Reject
    </button>

    <!-- Release Button with Modal -->
    <button type='button' class='btn btn-primary btn-sm releaseButton' 
            data-bs-toggle='modal' 
            data-bs-target='#confirmationModal' 
            data-id='{$row['id']}' 
            data-action='release'>
        Release
    </button>
                    </td>
                </tr>";
              }                    
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
   <!-- Modal Create Budget Structure -->
   <div class="modal fade" id="addBudgetModal" tabindex="-1" aria-labelledby="addBudgetModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#1560BD;">
        <h5 class="modal-title" id="addBudgetModalLabel" style="color:white;">Add New Budget of Department/Office</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size:12pt; width:25px; background-color:red;">X</button>
      </div>
      <div class="modal-body">
        <form action="add_overallBudget.php" method="POST">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="officeName" class="form-label">Office</label>
              <select type="text" class="form-control" id="officeName" name="officeName" required onchange="updateOfficeIdentifier()">
                                        <option value="">-- Select Department --</option>
                                        <?php
                                        $department_sql = "
                                        SELECT a.identifier, a.department_name 
                                        FROM tbl_departments a
                                        LEFT JOIN tbl_budget b ON a.identifier = b.identifier 
                                        WHERE a.budget = '0' 
                                        AND (b.status NOT IN ('ACTIVE') OR b.status IS NULL) order by a.department_name asc";                                    
                                        $departments = $conn->query($department_sql);
                                        if ($departments->num_rows > 0) {
                                            while ($row = $departments->fetch_assoc()) {
                                                echo "<option value='{$row['department_name']}' data-acc-code='{$row['identifier']}'>{$row['department_name']}</option>";
                                            }
                                        } else {
                                            echo "<option value=''>No departments available</option>";
                                        }
                                        
                                        ?>
                                    </select>
                                    <input type="text" class="form-control" id="officeidentifier" name="officeidentifier" hidden>    
            </div>
            <div class="col-md-6">
            <label for="setbudget" class="form-label">Budget</label>
              <input type="text" class="form-control" id="setbudget" name="setbudget" required>          
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
   <!-- Modal Structure -->
   <div class="modal fade" id="addDataModal" tabindex="-1" aria-labelledby="addDataModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#1560BD;">
        <h5 class="modal-title" id="addDataModalLabel" style="color:white;">Add New Department/Office Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size:12pt; width:25px; background-color:red;">X</button>
      </div>
      <div class="modal-body">
        <form action="add_officeBudget.php" method="POST">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="office" class="form-label">Office</label>
              <select type="text" class="form-control" id="office" name="office" required onchange="updateIdentifier()">
                                        <option value="">-- Select Department --</option>
                                        <?php
                                        // $department_sql = "SELECT a.identifier, a.department_name 
                                        // FROM tbl_departments a 
                                        // LEFT JOIN tbl_budget b 
                                        // ON a.identifier = b.identifier 
                                        // WHERE b.identifier IS NULL";
                                        $department_sql = "SELECT identifier, department_name 
                                        FROM tbl_departments where budget != '0' order by department_name asc";
                                        $departments = $conn->query($department_sql);
                                        if ($departments->num_rows > 0) {
                                            while ($row = $departments->fetch_assoc()) {
                                                echo "<option value='{$row['department_name']}' data-acc-code='{$row['identifier']}'>{$row['department_name']}</option>";
                                            }
                                        } else {
                                            echo "<option value=''>No departments available</option>";
                                        }
                                        
                                        ?>
                                    </select>
                                    <input type="text" class="form-control" id="identifier" name="identifier" hidden>    
            </div>
            <div class="col-md-6">
            <label for="budget" class="form-label">Budget</label>
              <input type="text" class="form-control" id="budget" name="budget" required>          
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
            <label for="acc_name" class="form-label">Account Name</label>
              <select type="text" class="form-control" id="acc_name" name="acc_name" required onchange="updateAccountCode()">
                                        <option value="">-- Select Account Name --</option>
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
            <label for="acc_code" class="form-label">Account Code</label>
              <input type="text" class="form-control" id="acc_code" name="acc_code" required>
            </div>
          </div>
          <div class="text-center">
          <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addAccountModal">Add Account</button>
            <button type="submit" class="btn btn-primary">Save Record</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#1560BD;">
        <h5 class="modal-title" id="addAccountModalLabel" style="color:white;">Add New Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size:12pt; width:25px; background-color:red;">X</button>
      </div>
      <div class="modal-body">
        <form action="add_account.php" method="POST">
          <div class="mb-3">
            <label for="newAccName" class="form-label">Account Name</label>
            <input type="text" class="form-control" id="newAccName" name="newAccName" required>
          </div>
          <div class="mb-3">
            <label for="newAccCode" class="form-label">Account Code</label>
            <input type="text" class="form-control" id="newAccCode" name="newAccCode" required>
          </div>
          <div class="text-center">
            <button type="submit" class="btn btn-primary">Add Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require("./office_edit.php"); ?>
<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
      </div>
      <div class="modal-body">
        Are you sure you want to <span id="actionType"></span> this record?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <form id="confirmationForm" action="update_status.php" method="POST">
          <input type="hidden" name="id" id="recordId">
          <input type="hidden" name="action" id="actionTypeInput">
          <input type="hidden" name="reason" value="N/A">
          <button type="submit" class="btn btn-primary">Yes, proceed</button>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="confirmationModalReject" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel2" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmationModalLabel2">Confirm Action</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to <span id="actionType2"></span> this record?
      </div>
      <div class="col-md-12">
        <label for="reason2" class="form-label">Reason</label>
        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <form id="confirmationForm2" action="update_status.php" method="POST">
          <input type="hidden" name="id" id="recordId2">
          <input type="hidden" name="action" id="actionTypeInput2">
          <input type="hidden" name="reason" id="hiddenReason">
          <button type="submit" class="btn btn-primary" id="confirmButton" disabled>Yes, proceed</button>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-..." crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" integrity="sha384-..." crossorigin="anonymous"></script>
    <script>
  function changeRowsPerPage(rows) {
    window.location.href = "?rowsPerPage=" + rows;
  }
  function updateAccountCode() {
        var accNameSelect = document.getElementById("acc_name");
        var selectedOption = accNameSelect.options[accNameSelect.selectedIndex];
        var accountCodeInput = document.getElementById("acc_code");

        accountCodeInput.value = selectedOption.getAttribute("data-acc-code");
    }

    function updateOfficeIdentifier() {
        var accNameSelect = document.getElementById("officeName");
        var selectedOption = accNameSelect.options[accNameSelect.selectedIndex];
        var accountCodeInput = document.getElementById("officeidentifier");
        accountCodeInput.value = selectedOption.getAttribute("data-acc-code");
    }
    function updateIdentifier() {
        var accNameSelect = document.getElementById("office");
        var selectedOption = accNameSelect.options[accNameSelect.selectedIndex];
        var accountCodeInput = document.getElementById("identifier");
        accountCodeInput.value = selectedOption.getAttribute("data-acc-code");
    }
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.editButton');

    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Get data from button's data-* attributes
            const id = this.getAttribute('data-id');
            const office = this.getAttribute('data-office');
            const accName = this.getAttribute('data-accname');
            const accCode = this.getAttribute('data-acccode');
            const budget = this.getAttribute('data-budget');
            const reprogram = this.getAttribute('data-reprogram');
            const expense = this.getAttribute('data-expense');
            const balance = this.getAttribute('data-balance');
            const aro = this.getAttribute('data-aro');

            // Update the modal's input fields
            document.getElementById('editId').value = id;

            document.getElementById('editOffice').value = office;
            document.getElementById('editAccName').value = accName;
            document.getElementById('editAccCode').value = accCode;
            document.getElementById('editBudget').value = budget;
            document.getElementById('editExpense').value = expense;
            document.getElementById('editBalance').value = balance;
            document.getElementById('editARO').value = aro;
            document.getElementById('editReprogram').value = reprogram;

        });
    });
});
$('#confirmationModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id'); 
        var action = button.data('action');
        
        var modal = $(this);
        modal.find('#actionType').text(action);
        modal.find('#actionTypeInput').val(action);
        modal.find('#recordId').val(id);
    });
    $('#confirmationModalReject').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); 
        var id = button.data('id'); 
        var action = button.data('action');

        var modal = $(this);
        modal.find('#actionType2').text(action);
        modal.find('#actionTypeInput2').val(action);
        modal.find('#recordId2').val(id);
    });
    document.getElementById('generate-report-office').addEventListener('click', function() {
            var department = document.getElementById('department').value;

            if (!department) {
                alert('Please select a department');
            } else {
                window.location.href = `pdf/office_pdf.php?department=${department}`;
            }
        });
        document.getElementById('reason').addEventListener('input', function() {
    //const reasonInput = this.value.trim();
    //document.getElementById('confirmButton').disabled = reasonInput.length === 0;
  });
  $('#reason').on('input', function () {
    const reason = $(this).val().trim();
    $('#hiddenReason').val(reason);
    $('#confirmButton').prop('disabled', reason.length === 0);
});

$('#confirmButton').on('click', function () {
    $('#hiddenReason').val($('#reason').val().trim());
    document.getElementById('confirmationForm2').submit();
});

  $("#department").on("change", function() {
$("#onchange").click();
  });
</script>

</body>
</html>
