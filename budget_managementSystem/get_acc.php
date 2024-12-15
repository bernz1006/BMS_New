<?php

include('db_connect.php');

$department_id = isset($_GET['department_id']) ? $_GET['department_id'] : '';

if ($department_id) {
    $acc_title_sql = "SELECT acc_name, balance FROM tbl_budget WHERE identifier = ? and status = 'ACTIVE' ORDER BY acc_name ASC";
    $stmt = $conn->prepare($acc_title_sql);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<option value=''></option>";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            echo "<option value='{$row['acc_name']}' data-acc-code='{$row['balance']}'>{$row['acc_name']}</option>";
        }
    } else {
        echo "<option value=''>No accounts available</option>";
    }
    $stmt->close();
} else {
    echo "<option value=''>Select Department First</option>";
}

$conn->close();
?>
