<?php
session_start();
// if(isset($_SESSION['fullname']) && isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
//     $fullname = $_SESSION['fullname'];
//     $user_id = $_SESSION['user_id'];
//     $role = $_SESSION['role'];
// } else {
//     $fullname = "Guest";
//     $user_id = "Unknown";
//     $role = "Unknown";
// }
// $query = "INSERT INTO tbl_activity (user_id, user, role, activity, date_activity) VALUES (?, ?, ?, ?, NOW())";
// $stmt = $conn->prepare($query);
// $stmt->bind_param("ssss", $user_id, $fullname, $role, $activity);
// $stmt->execute();

session_unset();
session_destroy();
unset($_SESSION['last_page']);
?>