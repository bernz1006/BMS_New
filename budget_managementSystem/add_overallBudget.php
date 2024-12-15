<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $office = $_POST['officeName'];
    $budget = $_POST['setbudget'];
    $identifier = $_POST['officeidentifier'];

    $stmt = $conn->prepare("UPDATE tbl_departments SET budget = ?, balance = ? WHERE identifier = ?");

    if ($stmt === false) {
        die("MySQL Error: " . $conn->error);
    }

    $bind = $stmt->bind_param("sss", $budget, $budget, $identifier);

    if ($stmt->execute()) {
        $_SESSION['messages'] = "Add ".$office." Budget Successfully.";
    } else {
        $_SESSION['messages'] = "Error updating record: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
    header("Location: office.php"); 
    exit();
} else {
    header("Location: office.php");
    exit();
}
?>
