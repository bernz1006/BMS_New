<?php
include 'db_connect.php'; // Include your database connection

if (isset($_POST['acc_name']) && isset($_POST['office_id'])) {
    $acc_name = $_POST['acc_name'];
    $office_id = $_POST['office_id'];
    
    $acc_code_sql = "SELECT acc_code FROM tbl_budget WHERE acc_name = ? AND identifier = ?";
    $stmt = $conn->prepare($acc_code_sql);
    $stmt->bind_param("ss", $acc_name, $office_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode($row['acc_code']);
    } else {
        echo json_encode(null); // No account code found
    }
}
?>
