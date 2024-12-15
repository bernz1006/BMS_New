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
$activity = "Generate Report in Accounts";

// Insert activity log into the database
$query = "INSERT INTO tbl_activity (user_id, user, role, activity, date_activity) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssss", $user_id, $fullname, $role, $activity);
$stmt->execute();

// Create SQL query with filters
$sql = "SELECT role, fullname, user_id, email, status FROM users WHERE 1=1";
$sql .= " ORDER BY id asc";

// Execute the query and check for errors
$result = $conn->query($sql);
if (!$result) {
    die("Error executing query: " . $conn->error);
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
$pdf->SetTitle('Accounts Report');
$pdf->SetSubject('TCPDF Report');
$pdf->SetKeywords('TCPDF, PDF, report, accounts');

// Set margins and page breaks
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page to the PDF
$pdf->AddPage();

// Title
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 15, 'Accounts Report', 0, 1, 'C');

$pdf->Cell(35, 2, "Generate Report", 0, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 5.5, ": " . date('M d, Y h:i A'), 0, 1); 
$pdf->Ln();

// Table headers
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(10, 8, 'No.', 1);
$pdf->Cell(30, 8, 'User ID.', 1);
$pdf->Cell(55, 8, 'Full Name', 1);
$pdf->Cell(30, 8, 'Role', 1);
$pdf->Cell(55, 8, 'Email', 1);
$pdf->Ln();

// Table content
$pdf->SetFont('helvetica', '', 10);
$i = 1;
if ($result->num_rows > 0) {
    // Reset the result pointer after fetching the first rowrole, fullname, user_id, email
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(10, 8, $i, 1);
        $pdf->Cell(30, 8, $row['user_id'], 1);
        $pdf->Cell(55, 8, $row['fullname'], 1);
        $pdf->Cell(30, 8, $row['role'], 1);
        $pdf->Cell(55, 8, $row['email'], 1);
        $pdf->Ln();
        $i++;
    }
} else {
    $pdf->Cell(190, 8, 'No records found', 1, 1, 'C');
}

// Output PDF
$pdf->Output('accounts_logs_report.pdf', 'D');

// End output buffering
ob_end_flush();
?>
