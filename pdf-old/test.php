<?php
require_once('./fpdf.php');
require_once('./fpdi.php');

// initiate FPDI
$pdf = new FPDI();
// add a page
$pdf->AddPage();
$pdf->SetFont('Helvetica');
$pdf->SetTextColor(255, 0, 0);
$pdf->SetXY(30, 30);
$pdf->Write(0, 'This is just a simple text');
// set the source file
$pageCount = $pdf->setSourceFile("srinivasan2016.pdf");
// import page 1
for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
    // import a page
    $templateId = $pdf->importPage($pageNo);
    // get the size of the imported page
    $size = $pdf->getTemplateSize($templateId);

    // create a page (landscape or portrait depending on the imported page size)
    if ($size['w'] > $size['h']) {
        $pdf->AddPage('L', array($size['w'], $size['h']));

    } else {
        $pdf->AddPage('P', array($size['w'], $size['h']));

    }

    // use the imported page
	
    $pdf->useTemplate($templateId);

    $pdf->SetFont('Helvetica');
    $pdf->SetXY(5, 5);
    $pdf->Write(8, '');
}


$pdf->Output();