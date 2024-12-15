<?php

include('db_connect.php');

$department_id = isset($_GET['department_id']) ? $_GET['department_id'] : '';
$acc_id = isset($_GET['acc_id']) ? $_GET['acc_id'] : '';

if ($department_id) {
    // Use 's' for acc_id since it's a string (assuming acc_name is a string)
    $acc_title_sql = "SELECT acc_name FROM tbl_budget WHERE identifier = ? and acc_name != ? and status = 'ACTIVE' ORDER BY acc_name ASC";
    $stmt = $conn->prepare($acc_title_sql);
    $stmt->bind_param("is", $department_id, $acc_id);  // 'i' for integer, 's' for string
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<option value=''></option>";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['acc_name']}'>{$row['acc_name']}</option>";
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
