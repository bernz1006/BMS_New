<?php
require 'db_connect.php';
session_start();
if(isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
} else {
    $role = "Unknown";
}
// Get the current page and rows per page
$rowsPerPage = isset($_GET['rowsPerPage']) ? $_GET['rowsPerPage'] : 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $rowsPerPage;

// Fetch the department, date_from, and date_to from the URL parameters
$selectedDepartment = isset($_GET['department']) ? $_GET['department'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Only run the query if filters are applied
$filterApplied = $selectedDepartment && $dateFrom && $dateTo;

if ($filterApplied) {
    $sql = "SELECT date_start, obr_no, payee, office, acc_name, acc_code, details, amount 
            FROM tbl_office 
            WHERE office = ? AND date_start BETWEEN ? AND ? 
            LIMIT $offset, $rowsPerPage";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $selectedDepartment, $dateFrom, $dateTo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $acc_title_sql = "SELECT acc_code, acc_name FROM tbl_budget WHERE office = ? and status = 'ACTIVE' order by acc_name asc";
    $stmt = $conn->prepare($acc_title_sql);
    $stmt->bind_param('s', $selectedDepartment);
    $stmt->execute();
    $resultAcc = $stmt->get_result();

    $totalRows = $conn->query("SELECT COUNT(*) AS total FROM tbl_office WHERE office = '$selectedDepartment' AND date_start BETWEEN '$dateFrom' AND '$dateTo'")->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $rowsPerPage);
} else {
    // If no filter applied, initialize variables
    $result = null;
    $totalRows = 0;
    $totalPages = 1;
}

// Get departments for filter
$department_sql = "SELECT distinct(identifier), office FROM tbl_budget order by office asc";
$departments = $conn->query($department_sql);
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
                    <h1>Budget Management</h1>
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
        <div class="col-md-4 mb-3">
        <label for="department">Office:</label>
            <select class="form-control" id="department" name="department" required>
                <option value="">-- Select Office --</option>
                <?php
                if ($departments->num_rows > 0) {
                    while ($row = $departments->fetch_assoc()) {
                        $selected = ($row['office'] == $selectedDepartment) ? 'selected' : ''; // Set selected
                        echo "<option value='{$row['office']}' $selected>{$row['office']}</option>";
                    }
                } else {
                    echo "<option value=''>No departments available</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label for="date_from">Date From:</label>
            <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $dateFrom; ?>" required>
        </div>
        <div class="col-md-3 mb-3">
        <label for="date_to">Date To:</label>
            <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $dateTo; ?>" required>
        </div>
        <button type="submit" class="btn btn-success col-md-2 mb-3" id="viewButton" <?php echo ($filterApplied) ? '' : 'disabled'; ?>>View</button>
    </div>
    
    <div class="form-row hiddenUntil <?php echo $filterApplied ? '' : 'd-none'; ?>" id="hiddenUntil">
        <div class="col-md-4 mb-3">
            <select class="form-control" id="acc_name2" name="acc_name2" required onchange="updateAccountDetails()">
                <option value="">-- Select Account Name --</option>
                <?php              
                if ($resultAcc->num_rows > 0) {
                    while ($row = $resultAcc->fetch_assoc()) {
                        echo "<option value='{$row['acc_name']}' data-acc-code='{$row['acc_code']}'>{$row['acc_name']}</option>";
                    }
                } else {
                    echo "<option value=''>No available</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-3 mb-3">
            <input type="text" class="form-control" id="acc_code2" name="acc_code2" readonly>
        </div>
        <div class="col-md-3 mb-3">
            <input type="text" class="form-control" id="budget2" name="budget2" readonly>
        </div>
        <div class="col-md-2 mb-3">
        <?php  
                        if (strcasecmp($role, 'Operator') === 0) {
                          echo '';
                      }  
                      else{
                        echo '<button type="button" class="btn btn-primary col-12" id="addRecordButton" data-toggle="modal" data-target="#addDataModal2" disabled>Allocate Budget</button>';
                      }                    
                        ?> 
        </div> 
    </div>
</div>
<div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>NO.</th>
                                <th>DATE</th>
                                <th>OBR NO.</th>
                                <th>PAYEE</th>
                                <th>OFFICE</th>
                                <th>ACCOUNT NAME</th>
                                <th>ACCT. CODE</th>
                                <th>DETAILS</th>
                                <th>AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result && $result->num_rows > 0) {
                                $i = $offset + 1;
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                        <td>{$i}</td>
                                        <td>{$row['date_start']}</td>
                                        <td>{$row['obr_no']}</td>
                                        <td>{$row['payee']}</td>
                                        <td>{$row['office']}</td>
                                        <td>{$row['acc_name']}</td>
                                        <td>{$row['acc_code']}</td>
                                        <td>{$row['details']}</td>
                                        <td>{$row['amount']}</td>
                                    </tr>";
                                    $i++;
                                }
                            } else {
                                echo "<tr><td colspan='9' style='text-align:center;'>No records found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

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
                    <button class="btn btn-primary fw-bold btn-block" id="generate-report-budget" <?php echo ($filterApplied) ? '' : 'disabled'; ?>>GENERATE REPORT</button>
                </div>
            </div>

            <!-- Modal Structure -->
            <div class="modal fade" id="addDataModal2" tabindex="-1" aria-labelledby="addDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#1560BD;">
                <h5 class="modal-title" id="addDataModalLabel" style="color:white;">Add New Department/Office Record</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close" style="font-size:12pt; width:25px; background-color:red;">X</button>
            </div>
            <div class="modal-body">
                <form action="budget_addrecord.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="office" class="form-label">Office</label>
                            <input type="text" class="form-control text-center" id="office" name="office" required readonly>
                            <input type="text" class="form-control text-center" id="ident" name="ident" required hidden>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="budget" class="form-label">Balance</label>
                            <input type="text" class="form-control text-center" id="budget" name="budget" readonly>
                        </div>                      
                    </div>
                    <div class="row justify-content-center">
                         <div class="col-md-12 mb-3 text-center">
                            <label for="acc_name" class="form-label">Account Name</label>
                            <input type="text" class="form-control text-center" id="acc_name" name="acc_name" required readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="acc_code" class="form-label">Account Code</label>
                            <input type="text" class="form-control" id="acc_code" name="acc_code" required readonly>
                        </div>          
                        <div class="col-md-6 mb-3">
                            <label for="date_start" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date_start" name="date_start" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payee" class="form-label">Payee</label>
                            <input type="text" class="form-control" id="payee" name="payee" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="obr_no" class="form-label">OBR No.</label>
                            <input type="text" class="form-control" id="obr_no" name="obr_no" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="details" class="form-label">Details</label>
                        <textarea class="form-control" id="details" name="details" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" id="amount" name="amount" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
        </main>
    </section>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
function changeRowsPerPage(value) {
    window.location.href = '?page=1&rowsPerPage=' + value;
}
$(document).ready(function() {
            $('#department, #date_from, #date_to').on('change', function() {
                if ($('#department').val() && $('#date_from').val() && $('#date_to').val()) {
                    $('#viewButton').prop('disabled', false);
                    $('#generate-report-budget').prop('disabled', false);
                } else {
                    $('#viewButton').prop('disabled', true);
                    $('#generate-report-budget').prop('disabled', true);
                }
            });

            // Handle view button click
            $('#viewButton').click(function() {
                var department = $('#department').val();
                var dateFrom = $('#date_from').val();
                var dateTo = $('#date_to').val();
                window.location.href = "?department=" + department + "&date_from=" + dateFrom + "&date_to=" + dateTo + "&rowsPerPage=<?php echo $rowsPerPage; ?>";
            });
        });

function updateAccountDetails() {
    var accName = document.getElementById("acc_name2").value;
    var department = document.getElementById("department").value;

    if (accName && department) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "get_account_details.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                document.getElementById("acc_code2").value = response.acc_code;
                document.getElementById("budget2").value = response.budget;
                document.getElementById("ident").value = response.ident;
            }
        };

        xhr.send("acc_name=" + encodeURIComponent(accName) + "&department=" + encodeURIComponent(department));
    }
}

function fetchinModal() {
 $("#addRecordButton").on("click", function () {
    var dep = $("#department").val();
    var accCode = $("#acc_code2").val();
    var budget = $("#budget2").val();
    var accName = $("#acc_name2").val();
    $("#office").val(dep);
    $("#acc_code").val(accCode);
    $("#budget").val(budget);
    $("#acc_name").val(accName);
 })
}
fetchinModal();

document.getElementById('acc_name2').addEventListener('change', function() {
    const accName = document.getElementById('acc_name2').value;
    document.getElementById('addRecordButton').disabled = accName === '';
});
document.getElementById('generate-report-budget').addEventListener('click', function() {
            var department = document.getElementById('department').value;
            var dateFrom = document.getElementById('date_from').value;
            var dateTo = document.getElementById('date_to').value;

            if (!department || !dateFrom || !dateTo) {
                alert('Please select a user and date range');
            } else {
                window.location.href = `pdf/budget_pdf.php?department=${department}&date_from=${dateFrom}&date_to=${dateTo}`;
            }
        });
    </script>
</body>
</html>
