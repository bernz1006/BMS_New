<?php
session_start();
require 'db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_start = $_POST['date_start'];
    $obr_no = $_POST['obr_no'];
    $payee = $_POST['payee'];
    $office = $_POST['office'];
    $acc_name = $_POST['acc_name'];
    $acc_code = $_POST['acc_code'];
    $details = $_POST['details'];
    $amount = $_POST['amount'];
    $ident = $_POST['ident'];
    $budget = $_POST['budget'];

    // Prepare and execute the insert statement for tbl_office
    $stmt = $conn->prepare("INSERT INTO tbl_office (identifier, date_start, obr_no, payee, office, acc_name, acc_code, details, amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $ident, $date_start, $obr_no, $payee, $office, $acc_name, $acc_code, $details, $amount);

    if ($stmt->execute()) {
        // Update tbl_budget to reflect the changes
        $update_stmt = $conn->prepare("UPDATE tbl_budget SET balance = balance - ?, expense = expense + ?, aro = aro - ?");
        $update_stmt->bind_param("ddd", $amount, $amount, $amount);

        if ($update_stmt->execute()) {
            $_SESSION['message'] = "Record added and budget updated successfully.";
        } else {
            $_SESSION['message'] = "Record added, but error updating budget: " . $conn->error;
        }

        $update_stmt->close();
    } else {
        $_SESSION['message'] = "Error adding record: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: budget.php"); 
    exit();
}
?>
