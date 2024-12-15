<?php
session_start();
require 'db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match';
        header('Location: account.php');
        exit();
    }

    $query = "SELECT user_id FROM users WHERE user_id LIKE 'BMS-L%' ORDER BY user_id DESC LIMIT 1";
    $result = $conn->query($query);
    $new_user_id = "BMS-L01"; 

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_user_id = $row['user_id'];
  
        $last_number = (int)substr($last_user_id, 6);

        $new_number = $last_number + 1;
        $new_user_id = 'BMS-L' . str_pad($new_number, 2, '0', STR_PAD_LEFT);
    }

    $sql = "INSERT INTO users (user_id, fullname, username, password, role, email, status)
            VALUES (?, ?, ?, ?, ?,?, 'active')"; 
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssss", $new_user_id, $fullname, $username, $hashed_password, $role,$email);

        if ($stmt->execute()) {
            $_SESSION['success'] = 'User added successfully';

            // Log the activity after successful insertion
            $activity = "New Account Added: $fullname";
            $activity_query = "INSERT INTO tbl_activity (user_id, user, role, activity, date_activity) VALUES (?, ?, ?, ?, NOW())";
            $activity_stmt = $conn->prepare($activity_query);
            $activity_stmt->bind_param("ssss", $new_user_id, $fullname, $role, $activity);
            $activity_stmt->execute();
            $activity_stmt->close();
        } else {
            $_SESSION['error'] = 'Error adding user';
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = 'Database error: ' . $conn->error;
    }

    header('Location: account.php');
    exit();
} else {
    $_SESSION['error'] = 'Invalid request';
    header('Location: account.php');
    exit();
}
?>
