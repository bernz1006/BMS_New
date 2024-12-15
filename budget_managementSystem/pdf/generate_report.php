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
ob_start(); // Start output buffering

require '../vendor/autoload.php';
require '../db_connect.php';

date_default_timezone_set('Asia/Manila'); 
$activity = "Generate Report in Activity Logs";

// Insert activity log into the database
$query = "INSERT INTO tbl_activity (user_id, user, role, activity, date_activity) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssss", $user_id, $fullname, $role, $activity);
$stmt->execute();

// Get filter inputs
$userSelect = isset($_GET['userSelect']) ? $_GET['userSelect'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Create SQL query with filters
$sql = "SELECT user_id, user, role, activity, date_activity FROM tbl_activity WHERE 1=1";
if (!empty($userSelect)) {
    $sql .= " AND user_id = '$userSelect'";
}
if (!empty($dateFrom) && !empty($dateTo)) {
    $sql .= " AND date_activity BETWEEN '$dateFrom' AND DATE_ADD('$dateTo', INTERVAL 1 DAY)";
}
$sql .= " ORDER BY id asc";

// Execute the query and check for errors
$result = $conn->query($sql);
if (!$result) {
    die("Error executing query: " . $conn->error);
}

// Initialize variables for the first row (user details)
$userSelected = '';
$userRole = '';

if ($result->num_rows > 0) {
    $firstRow = $result->fetch_assoc();
    $userSelected = $firstRow['user'];
    $userRole = $firstRow['role'];
}

// Extend TCPDF to customize the header
class MYPDF extends TCPDF {
    // Page header
    public function Header() {
        if ($this->getPage() == 1) {
            $this->Image('../assets/img/phil.png', 50, 5, 20, 18, '', '', '', false, 300, '', false, false, 0, false, false, false);
            $this->Image('../assets/img/laur.png', 140, 5, 20, 18, '', '', '', false, 300, '', false, false, 0, false, false, false);
            $this->Ln(1);
            $this->SetFont('helvetica', '', 12);
            $this->Cell(0, 4, 'Republic of the Philippines', 0, 1, 'C');
            $this->SetFont('helvetica', 'B', 12);
            $this->Cell(0, 4, 'MUNICIPALITY OF LAUR', 0, 1, 'C');
            $this->SetFont('helvetica', '', 12);
            $this->Cell(0, 4, 'Province of Nueva Ecija', 0, 1, 'C');
            $this->Ln(1);
        }
    }
}

// Create new PDF document
$pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('bms');
$pdf->SetTitle('Activity Logs Report');
$pdf->SetSubject('TCPDF Report');
$pdf->SetKeywords('TCPDF, PDF, report, activity logs');

// Set margins and page breaks
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page to the PDF
$pdf->AddPage();

// Title
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 15, 'Activity Logs Report', 0, 1, 'C');

// User and date information
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(30, 2, "User Name", 0, 0); 
$pdf->SetFont('helvetica', 'B', 10); 
$pdf->Cell(0, 2, ": $userSelected", 0, 1);
$pdf->SetFont('helvetica', '', 10); 
$pdf->Cell(30, 2, "User Role", 0, 0); 
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 2, ": $userRole", 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(30, 2, "User ID", 0, 0); 
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 2, ": $userSelect", 0, 1); 
$pdf->SetFont('helvetica', '', 10); 
$pdf->Cell(30, 2, "Date From & To", 0, 0); 
$pdf->SetFont('helvetica', 'B', 10); 
$pdf->Cell(0, 2, ": $dateFrom - $dateTo", 0, 1);
$pdf->Ln();

// Table headers
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(20, 8, 'No.', 1);
$pdf->Cell(110, 8, 'Activity', 1);
$pdf->Cell(50, 8, 'Date & Time of Activity', 1);
$pdf->Ln();

// Table content
$pdf->SetFont('helvetica', '', 10);
$i = 1;
if ($result->num_rows > 0) {
    // Reset the result pointer after fetching the first row
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(20, 8, $i, 1);
        $pdf->Cell(110, 8, $row['activity'], 1);
        $pdf->Cell(50, 8, $row['date_activity'], 1);
        $pdf->Ln();
        $i++;
    }
} else {
    $pdf->Cell(190, 8, 'No records found', 1, 1, 'C');
}

// Output PDF
$pdf->Output('activity_logs_report.pdf', 'D');

// End output buffering
ob_end_flush();
?>
