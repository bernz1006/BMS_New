<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $office = $_POST['office'];
    $acc_name = $_POST['acc_name'];
    $acc_code = $_POST['acc_code'];
    $budget = $_POST['budget'];
    $identifier = $_POST['identifier'];
    $zero = "0";
    $status = "ACTIVE";
    $aro = $budget / 4;

    $stmt = $conn->prepare("INSERT INTO tbl_budget 
    (identifier, date_created, office, acc_name, acc_code, budget, supplemental, realignment, reprogram, expense, balance, aro, `release`, `status`) 
    VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt === false) {
        die("MySQL Error (INSERT): " . $conn->error);
    }

    $stmt->bind_param("sssssssssssss", $identifier, $office, $acc_name, $acc_code, $budget, $zero, $zero, $zero, $zero, $budget, $aro, $zero, $status);

    if (!$stmt->execute()) {
        die("Error executing INSERT query: " . $conn->error);
    }

    $stmt->close();

    $stmt = $conn->prepare("UPDATE tbl_departments SET balance = balance - ? WHERE identifier = ?");

    if ($stmt === false) {
        die("MySQL Error (UPDATE): " . $conn->error);
    }

    $stmt->bind_param("ss", $budget, $identifier);

    if ($stmt->execute()) {
        $_SESSION['messages'] = "Record added and department balance updated successfully.";
        header("Location: office.php");
    } else {
        $_SESSION['messages'] = "Error updating department balance: " . $conn->error;
    }

    // Close the second statement and the connection
    $stmt->close();
    $conn->close();
} else {
    header("Location: office.php");
    exit();
}
?>
