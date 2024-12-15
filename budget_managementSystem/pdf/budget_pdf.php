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
ob_start();

require '../vendor/autoload.php';
require '../db_connect.php';

date_default_timezone_set('Asia/Manila'); 
$activity = "Generate Report in Budget Records";

$query = "INSERT INTO tbl_activity (user_id, user, role, activity, date_activity) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssss", $user_id, $fullname, $role, $activity);
$stmt->execute();

// Get filter inputs
$department = isset($_GET['department']) ? $_GET['department'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$sql = "SELECT date_start, obr_no, payee, office, acc_name, acc_code, details, amount FROM tbl_office";

if (!empty($department)) {
    $sql .= " WHERE office = '$department'";
}
if (!empty($dateFrom) && !empty($dateTo)) {
    $sql .= " AND date_start BETWEEN '$dateFrom' AND '$dateTo'";
}
$result = $conn->query($sql);
if (!$result) {
    die("Error executing query: " . $conn->error);
}


class MYPDF extends TCPDF {
    public function Header() {
        if ($this->getPage() == 1) {
            $this->Image('../assets/img/phil.png', 120, 5, 20, 18, '', '', '', false, 300, '', false, false, 0, false, false, false);
            $this->Image('../assets/img/laur.png', 215, 5, 20, 18, '', '', '', false, 300, '', false, false, 0, false, false, false);
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

$pdf = new MYPDF('L', PDF_UNIT, 'LEGAL', true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('bms');
$pdf->SetTitle('Budget Records Report');
$pdf->SetSubject('TCPDF Report');
$pdf->SetKeywords('TCPDF, PDF, report, Budget Records');

$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 15, 'Budget Records Report', 0, 1, 'C');

$pdf->Cell(35, 2, "Generate Report", 0, 0);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 5.5, ": " . date('M d, Y h:i A'), 0, 1); 
$pdf->Ln();

$pdf->SetFont('helvetica', 'B', 10); //72date_start, obr_no, payee, office, acc_name, acc_code, details, amount
$pdf->Cell(10, 8, 'No.', 1);
$pdf->Cell(29, 8, 'DATE', 1); 
$pdf->Cell(20, 8, 'OBR_NO', 1);
$pdf->Cell(50, 8, 'PAYEE NAME', 1);
$pdf->Cell(35, 8, 'OFFICE', 1);
$pdf->Cell(58, 8, 'ACCOUNT NAME', 1);
$pdf->Cell(32, 8, 'ACCOUNT CODE', 1);
$pdf->Cell(80, 8, 'DETAILS', 1);
$pdf->Cell(22, 8, 'AMOUNT', 1);
$pdf->Ln();

$pdf->SetFont('helvetica', '', 8);
$i = 1;
if ($result->num_rows > 0) {
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(10, 8, $i, 1);
        $pdf->Cell(29, 8, $row['date_start'], 1);
        $pdf->Cell(20, 8, $row['obr_no'], 1);
        $pdf->Cell(50, 8, $row['payee'], 1);
        $pdf->Cell(35, 8, $row['office'], 1);
        $pdf->Cell(58, 8, $row['acc_name'], 1);
        $pdf->Cell(32, 8, $row['acc_code'], 1);
        $pdf->Cell(80, 8, $row['details'], 1);
        $pdf->Cell(22, 8, $row['amount'], 1);
        $pdf->Ln();
        $i++;
    }
} else {
    $pdf->Cell(150, 8, 'No records found', 1, 1, 'C');
}

$pdf->Output('budget_logs_report.pdf', 'D');

ob_end_flush();
?>
