<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require('fpdf/fpdf.php');
$host = "localhost";
$user = "root";
$password = "";
$dbname = "afrikala_arts";
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT name, age, email, phone, event FROM registrations ORDER BY name ASC");

$pdf = new FPDF('L', 'mm', 'A4');
$pdf->SetMargins(15, 15, 15); // Extra margin for neatness
$pdf->AddPage();
$pdf->SetFont('Arial','B',13);

// Set custom column widths [Name, Age, Email, Phone, Event]
$widths = [55, 18, 65, 40, 80]; // Adjust these as needed for your data
$rowHeight = 12;

// Table header
$header = ['Name', 'Age', 'Email', 'Phone', 'Event'];
for ($i = 0; $i < count($header); $i++) {
    $pdf->Cell($widths[$i], $rowHeight, $header[$i], 1, 0, 'C');
}
$pdf->Ln();

// Table data
$pdf->SetFont('Arial','',12);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell($widths[0], $rowHeight, $row['name'], 1);
    $pdf->Cell($widths[1], $rowHeight, $row['age'], 1, 0, 'C');
    $pdf->Cell($widths[2], $rowHeight, $row['email'], 1);
    $pdf->Cell($widths[3], $rowHeight, $row['phone'], 1);
    $pdf->Cell($widths[4], $rowHeight, $row['event'], 1);
    $pdf->Ln();
}

$pdf->Output('D', 'registrations.pdf');
$conn->close();
exit;
?>