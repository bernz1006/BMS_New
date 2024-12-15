<?php
require 'db_connect.php';

// Initialize variables for pagination and filtering data
$rowsPerPage = isset($_GET['rowsPerPage']) ? $_GET['rowsPerPage'] : 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $rowsPerPage;

$user_id = isset($_GET['userSelect']) ? $_GET['userSelect'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Check if filters have been applied
$dataFiltered = !empty($user_id) || (!empty($date_from) && !empty($date_to));

// SQL query for retrieving data, default is all data
$sql = "SELECT user_id, user, role, activity, date_activity FROM tbl_activity WHERE 1=1";

// Modify query if filters are applied
if ($dataFiltered) {
    if (!empty($user_id)) {
        $sql .= " AND user_id = '$user_id'";
    }

    if (!empty($date_from) && !empty($date_to)) {
        $sql .= " AND date_activity BETWEEN '$date_from' AND DATE_ADD('$date_to', INTERVAL 1 DAY)";
    }    
}

// Add pagination to the query
$sql .= " LIMIT $offset, $rowsPerPage";
$result = $conn->query($sql);
$rowCount = $result->num_rows; 

// Fetch total rows count for pagination
$totalRowsQuery = "SELECT COUNT(*) AS total FROM tbl_activity WHERE 1=1";
if (!empty($user_id)) {
    $totalRowsQuery .= " AND user_id = '$user_id'";
}
if (!empty($date_from) && !empty($date_to)) {
    $totalRowsQuery .= " AND date_activity BETWEEN '$date_from' AND DATE_ADD('$date_to', INTERVAL 1 DAY)";
}

$totalRows = $conn->query($totalRowsQuery)->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $rowsPerPage);

// Fetch user data for the dropdown
$user_sql = "SELECT user_id, fullname FROM users";
$user = $conn->query($user_sql);
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
                    <h1>Activity Logs</h1>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="container mt-4">
                <form method="GET" action="">
                    <div class="form-row">
                        <!-- User Dropdown -->
                        <div class="col-md-4 mb-3">
                        <label for="userSelect">User:</label>
                            <select class="form-control" id="userSelect" name="userSelect">
                                <option value="">-- Select User Name --</option>
                                <?php
                                if ($user->num_rows > 0) {
                                    while ($row = $user->fetch_assoc()) {
                                        $selected = ($user_id == $row['user_id']) ? "selected" : "";
                                        echo "<option value='{$row['user_id']}' $selected>{$row['fullname']}</option>";
                                    }
                                } else {
                                    echo "<option value=''>No Users available</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Date Range Pickers -->
                        <div class="col-md-3 mb-3">
                        <label for="date_from">Date From:</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" placeholder="From" value="<?= $date_from ?>">
                        </div>

                        <div class="col-md-3 mb-3">
                        <label for="date_to">Date To:</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" placeholder="To" value="<?= $date_to ?>">
                        </div>

                        <!-- View Button -->
                        <button type="submit" class="btn btn-success col-md-2 mb-3" id="viewReport">View</button>
                    </div>
                </form>
            </div>

            <!-- Table showing data -->
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>NO.</th>
                            <th>USER ID</th>
                            <th>USER'S NAME</th>
                            <th>ROLE</th>
                            <th>ACTIVITY</th>
                            <th>DATE OF ACTIVITY</th>
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
                                      <td>{$row['user']}</td>
                                      <td>{$row['role']}</td>
                                      <td>{$row['activity']}</td>
                                      <td>{$row['date_activity']}</td>
                                    </tr>";
                                $i++;
                            }
                        } else {
                            echo "<tr><td colspan='6'>No records found</td></tr>";
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
                        <option value="10" <?= ($rowsPerPage == 10) ? "selected" : "" ?>>10</option>
                        <option value="50" <?= ($rowsPerPage == 50) ? "selected" : "" ?>>50</option>
                        <option value="100" <?= ($rowsPerPage == 100) ? "selected" : "" ?>>100</option>
                        <option value="all" <?= ($rowsPerPage == 'all') ? "selected" : "" ?>>All</option>
                    </select>
                </div>

                <nav aria-label="Table pagination">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&rowsPerPage=<?= $rowsPerPage ?>">Previous</a></li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&rowsPerPage=<?= $rowsPerPage ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&rowsPerPage=<?= $rowsPerPage ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <div class="d-flex justify-content-center align-items-center" style="height: 10vh;">
        <div class="col-4">
            <button class="btn btn-primary fw-bold btn-block" id="generate-report-activity">GENERATE REPORT</button>
        </div>
    </div>
        </main>
        <!-- MAIN -->
    </section>
	<!-- CONTENT -->

    <script>
          var rowCount = <?php echo $rowCount; ?>;
            document.addEventListener('DOMContentLoaded', function() {
        const generateReportButton = document.getElementById('generate-report-activity');
        if (rowCount === 0) {
            generateReportButton.disabled = true;
        } else {
            generateReportButton.disabled = false;
        }
    });
        function changeRowsPerPage(rows) {
            window.location.href = "?rowsPerPage=" + rows;
        }
        document.getElementById('generate-report-activity').addEventListener('click', function() {
            var userSelect = document.getElementById('userSelect').value;
            var dateFrom = document.getElementById('date_from').value;
            var dateTo = document.getElementById('date_to').value;

            if (!userSelect || !dateFrom || !dateTo) {
                alert('Please select a user and date range');
            } else {
                window.location.href = `pdf/generate_report.php?userSelect=${userSelect}&date_from=${dateFrom}&date_to=${dateTo}`;
            }
        });
    </script>
</body>
</html>
