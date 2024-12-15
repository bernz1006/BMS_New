<?php
require 'db_connect.php';

if (isset($_GET['department'])) {
    $department = $_GET['department'];

    $acc_title_sql = "SELECT acc_code, acc_name FROM tbl_budget WHERE office = ?";
    $stmt = $conn->prepare($acc_title_sql);
    $stmt->bind_param('s', $department);
    $stmt->execute();
    $result = $stmt->get_result();

    $accounts = [];
    while ($row = $result->fetch_assoc()) {
        $accounts[] = $row;
    }

    // Return JSON response
    echo json_encode($accounts);
}
?>
