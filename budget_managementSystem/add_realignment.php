<?php
session_start();

if(isset($_SESSION['fullname']) && isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $fullname = $_SESSION['fullname'];
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
} else {
    $fullname = "Guest";
    $user_id = "Unknown";
    $role = "Unknown";
}
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $officeFrom = $_POST['acc_name_from'];
    $officeTo = $_POST['acc_name_to'];
    $amount = $_POST['amount'];
    $date_transfer = $_POST['redate_transfer'];
    $realignment = 'Realignment';
    $office = $_POST['office'];

    // Insert Query
    $stmt = $conn->prepare("INSERT INTO tbl_transfer 
    (office_from, office_to, amount, date_transfer, type_of_transfer) 
    VALUES (?, ?, ?, ?, ?)");

    if ($stmt === false) {
        die("MySQL Error (INSERT): " . $conn->error);
    }

    $stmt->bind_param("sssss", $officeFrom, $officeTo, $amount, $date_transfer, $realignment);

    if (!$stmt->execute()) {
        die("Error executing INSERT query: " . $conn->error);
    }

    $stmt->close();

    // Update officeFrom balance
    $stmt = $conn->prepare("UPDATE tbl_budget SET balance = balance - ? WHERE acc_name = ? and identifier = ?");
    if ($stmt === false) {
        die("MySQL Error (UPDATE officeFrom): " . $conn->error);
    }

    $stmt->bind_param("sss", $amount, $officeFrom, $office);

    if (!$stmt->execute()) {
        die("Error executing UPDATE query for officeFrom: " . $conn->error);
    }

    $stmt->close();

    // Update officeTo budget and balance
    $stmt = $conn->prepare("UPDATE tbl_budget SET budget = budget + ?, balance = balance + ? WHERE acc_name = ? and identifier = ?");
    if ($stmt === false) {
        die("MySQL Error (UPDATE officeTo): " . $conn->error);
    }

    $stmt->bind_param("ssss", $amount, $amount, $officeTo, $office);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Record added successfully.";
    } else {
        $_SESSION['message'] = "Error updating data: " . $conn->error;
    }

    $stmt->close();

    date_default_timezone_set('Asia/Manila'); 
    $activity = "Add New Realignment Records";

    $query = "INSERT INTO tbl_activity (user_id, user, role, activity, date_activity) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $user_id, $fullname, $role, $activity);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Redirect to the main page
    header("Location: supplimental_realignment.php");
    exit();
} else {
    header("Location: supplimental_realignment.php");
    exit();
}
?>
