<?php
session_start();

include 'db_connect.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $newAccCode = isset($_POST['newAccCode']) ? trim($_POST['newAccCode']) : '';
    $newAccName = isset($_POST['newAccName']) ? trim($_POST['newAccName']) : '';


    if (empty($newAccCode) || empty($newAccName)) {
        echo "Account Code and Account Name are required.";
        exit;
    }

    $sql = "INSERT INTO tbl_list (acc_code, acc_title) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $stmt->bind_param("ss", $newAccCode, $newAccName);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Account successfully added!";
            header("Location: office.php?success=1");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}

// Close the database connection
$conn->close();
?>
