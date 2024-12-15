<?php
if (isset($_POST['acc_name']) && isset($_POST['department'])) {
    include 'db_connect.php';  // Ensure you're including your DB connection file here

    $acc_name = $_POST['acc_name'];
    $department = $_POST['department'];

    // Prepare SQL query to fetch acc_code and budget based on acc_name and department
    $sql = "SELECT acc_code, balance, identifier FROM tbl_budget WHERE acc_name = ? AND office = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $acc_name, $department);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'acc_code' => $row['acc_code'],
            'budget' => $row['balance'],
            'ident' => $row['identifier']
        ]);
    } else {
        echo json_encode(['acc_code' => '', 'budget' => '','ident' => '']);
    }
}
?>
