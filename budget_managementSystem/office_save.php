<?php
// edit_record.php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $department = $_POST['department'];
    $balance = $_POST['balance'];
    $acc_name = $_POST['acc_name2'];
    $supplemental = isset($_POST['supplemental']) ? $_POST['supplemental'] : 0;
    $realignment = isset($_POST['realignment']) ? $_POST['realignment'] : 0;

    if (isset($_POST['submitSupplemental'])) {
        $sql1 = "UPDATE tbl_budget SET 
                    supplemental = supplemental + '$supplemental', 
                    balance = balance - '$supplemental'
                 WHERE id = '$id'";

        $sql2 = "UPDATE tbl_budget SET 
                    balance = balance + '$supplemental',
                    realignment = realignment + '$realignment'
                 WHERE department = '$department' AND status = 'ACTIVE'";
    } elseif (isset($_POST['submitRealignment'])) {
        $sql1 = "UPDATE tbl_budget SET 
                    realignment = realignment + '$realignment', 
                    balance = balance - '$realignment'
                 WHERE id = '$id'";

        $sql2 = "UPDATE tbl_budget SET 
                    balance = balance + '$realignment',
                    realignment = realignment + '$realignment'
                 WHERE acc_name = '$acc_name' AND status = 'ACTIVE'";
    }

    mysqli_begin_transaction($conn);
    try {
        mysqli_query($conn, $sql1);
        mysqli_query($conn, $sql2);
        mysqli_commit($conn);
        echo "Record updated successfully";
        header("Location: office.php");
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>
