<?php
include 'db_connect.php';

$exclude = $_GET['exclude'] ?? '';

// Validate input
if (empty($exclude)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid department exclusion.']);
    exit;
}

$sql = "SELECT department_name 
        FROM tbl_departments 
        WHERE department_name != ? 
        ORDER BY department_name ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to prepare SQL statement.']);
    exit;
}

$stmt->bind_param('s', $exclude);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to execute SQL query.']);
    exit;
}

$departments = [];
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}

// Close statement and connection
$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($departments);
?>
