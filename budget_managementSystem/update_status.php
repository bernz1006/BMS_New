<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<?php
session_start();
ob_start();

if (isset($_SESSION['fullname']) && isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $fullname = $_SESSION['fullname'];
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
} else {
    $fullname = "Guest";
    $user_id = "Unknown";
    $role = "Unknown";
}
require 'db_connect.php';
// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     echo '<pre>';
//     print_r($_POST);
//     echo '</pre>';
//     exit;
// }
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id']) && isset($_POST['action']) && isset($_POST['reason'])) {
    $id = intval($_POST['id']); 
    $action = $_POST['action'];
    if (empty($_POST['reason'])) {
        $reason = 'N/A';
    } else {
        $reason = $_POST['reason'];
    }
    if ($action == 'reject') {
        $status = 'REJECTED';
    } elseif ($action == 'release') {
        $status = 'RELEASE';
    } else {
        die("Invalid action");
    }

    
    $sql_fetch = "SELECT budget, balance, identifier FROM tbl_budget WHERE id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    if (!$stmt_fetch) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_fetch->bind_param("i", $id);
    $stmt_fetch->execute();
    $stmt_fetch->bind_result($budget, $balance, $identifier);
    $stmt_fetch->fetch();
    $stmt_fetch->close();

    if ($budget != $balance && $status == 'REJECTED') {
        echo '
        <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="errorModalLabel">Cannot Reject</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Cannot reject because the budget is already used.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <a href="office.php" class="btn btn-primary">OK</a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                $("#errorModal").modal("show");
            });
        </script>
        ';

        ob_flush(); 
        exit; 
    }
    if ($status == 'REJECTED') {
        $sql_update_status = "UPDATE tbl_budget SET status = ?, reason = ? WHERE id = ?";
        $stmt_status = $conn->prepare($sql_update_status);
        if (!$stmt_status) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt_status->bind_param("ssi", $status, $reason, $id);
    } else {
        $sql_update_status = "UPDATE tbl_budget SET status = ?, reason = ? WHERE id = ?";
        $stmt_status = $conn->prepare($sql_update_status);
        if (!$stmt_status) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt_status->bind_param("ssi", $status, $reason, $id);
    }
    
   if ($status == 'REJECTED') {
    $sql_update_balance = "UPDATE tbl_departments SET balance = balance + ? WHERE identifier = ?";
    $stmt_balance = $conn->prepare($sql_update_balance);
    if (!$stmt_balance) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_balance->bind_param("ds", $budget, $identifier);

    if (!$stmt_balance->execute()) {
        echo "Error updating balance: " . $conn->error;
    }
    // $sql_update_status = "UPDATE tbl_budget SET status = ?, reason = ? WHERE id = ?";
    // $stmt_status = $conn->prepare($sql_update_status);
    // if (!$stmt_status) {
    //     die("Prepare failed: " . $conn->error);
    // }
    // $stmt_status->bind_param("ssi", $status, $reason, $id);    
}

    // $sql_update_status = "UPDATE tbl_budget SET status = ? WHERE id = ?";
    // $stmt_status = $conn->prepare($sql_update_status);
    // if (!$stmt_status) {
    //     die("Prepare failed: " . $conn->error);
    // }
    // $stmt_status->bind_param("si", $status, $id);

    date_default_timezone_set('Asia/Manila'); 
    $activity = "{$action} id no {$id}"; 

    $sql_log = "INSERT INTO tbl_activity (user_id, user, role, activity, date_activity) VALUES (?, ?, ?, ?, NOW())";
    $stmt_log = $conn->prepare($sql_log);
    if (!$stmt_log) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_log->bind_param("ssss", $user_id, $fullname, $role, $activity);

    if ($status == 'REJECTED' && $stmt_balance->execute() && $stmt_status->execute() && $stmt_log->execute()) {
        header('Location: office.php?message=Status updated');
        exit;
    } else if ($status == 'RELEASE' && $stmt_status->execute() && $stmt_log->execute()) {
        header('Location: office.php?message=Status updated');
        exit;
    } else {
        echo "Error updating record or inserting log: " . $conn->error;
    }

    $stmt_balance->close();
    $stmt_status->close();
    $stmt_log->close();
}

$conn->close();
?>
