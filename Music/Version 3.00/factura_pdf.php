<?php
// 12/3/21 Version inicial completa

$factura=intval($_POST['id']);
$IDC=intval(substr($_COOKIE['TMPCLI'],0,6));

if ($factura>0 && $IDC>0) {
	$conn = new mysqli('localhost', 'XXXXXXXX', 'XXXXXXXX', 'XXXXXXXX');
	if ($conn->connect_error) exit('Error de conexión');

	if ($IDC>0) {
		$res=$conn->query("SELECT * FROM clientes WHERE id=$IDC AND activo");
		if ($res->num_rows > 0) {
			$row = $res->fetch_assoc();
			$IDC=$row['id'];
			$clave=$row['clave'];
			$IDC=($_COOKIE['TMPCLI']==substr('000000'.$IDC,-6).sha1($IDC.$clave.'Cadena de texto de aleatorizacion de cliente Clyb')) ? $IDC : 0;
		} else $IDC=0;
		$res->Close();
	}
	
	if ($IDC==0) {
		setcookie('TMPCLI', '000000', time()+30);
		header("Location: /", true, 302);
	}

	$res=$conn->query("SELECT * FROM facturas WHERE id=$factura AND idc=$IDC");
	if ($row = $res->fetch_assoc()) {
		$numerofactura=$row['serie'].substr("0000{$row['numero']}",-4);
		$fechafactura=date('d/m/Y',strtotime($row['fecha']));
		$total=round($row['importe'],2);
	} else $factura=0;
	$res->close();
}

if ($factura>0) {
	require_once ('fpdf/fpdf.php');
	$pdf = new FPDF('P','mm','A4');
	$pdf->AddPage();
	$pdf->Image('img/icon.png',97,15,16,16);
	$pdf->SetDrawColor(0,0,255);
	$pdf->Line(15, 35, 195, 35);
	$pdf->Line(15, 47, 195, 47);
	$pdf->SetDrawColor(0,0,0);
	$pdf->Line(15, 105, 195, 105);
	$pdf->SetFont('Arial','B',16);
	$pdf->SetTextColor(0,0,255);
	$pdf->SetXY(15, 35);
	$pdf->Cell(180,12,"FACTURA SIMPLIFICADA $numerofactura",0,0,'C');
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('Arial','B',9);
	$pdf->SetXY(20, 55);
	$pdf->Cell(50,4,'FECHA',0,0,'I');
	$pdf->SetXY(140, 55);
	$pdf->Cell(50,4,'CLYB CONNECT SL',0,0,'R');
	$pdf->SetXY(20, 100);
	$pdf->Cell(50,4,'CONCEPTO',0,0,'I');
	$pdf->SetXY(140, 100);
	$pdf->Cell(50,4,'PRECIO',0,0,'R');
	$pdf->SetFont('Arial','',9);
	$pdf->SetXY(20, 59);
	$pdf->Cell(50,4,$fechafactura,0,0,'I');
	$pdf->SetXY(140, 59);
	$pdf->MultiCell(50, 4, "B42778977\nCalle Felipe III, N1\n28012, Madrid, España\n911239057", 0, 'R');
	$pdf->SetY(108);

	$baseimponible=0;
	$res=$conn->query("SELECT * FROM facturas_conceptos WHERE idf=$factura ORDER BY id");
	while ($row = $res->fetch_assoc()) {
		$concepto=$row['concepto'];
		$importe=number_format($row['importe'], 2, ',','.');
		$baseimponible+=round($row['importe'],2);
		$pdf->SetX(20);
		$pdf->MultiCell(150, 4, $concepto, 0, 'L');
		$pdf->SetXY(160,$pdf->GetY()-4);
		$pdf->MultiCell(30,4,$importe.'€',0,'R');
	}
	$res->close();


	$posicion_y=$pdf->GetY()+15;
	$pdf->SetXY(20,$posicion_y);
	$pdf->Cell(150, 6, "BASE IMPONIBLE", 0, 0, 'R');
	$pdf->SetXY(160,$posicion_y);
	$pdf->Cell(30, 6, number_format($baseimponible, 2, ',','.').'€', 0, 0, 'R');
	$posicion_y+=6;
	$pdf->SetXY(20,$posicion_y);
	$pdf->Cell(150, 6, "IVA 21%", 0, 0, 'R');
	$pdf->SetXY(160,$posicion_y);
	$pdf->Cell(30, 6, number_format($baseimponible*0.21, 2, ',','.').'€', 0, 0, 'R');
	$pdf->SetFont('Arial','B',9);
	$posicion_y+=6;
	$pdf->SetXY(20,$posicion_y);
	$pdf->Cell(150, 6, "TOTAL", 0, 0, 'R');
	$pdf->SetXY(160,$posicion_y);
	$pdf->Cell(30, 6, number_format($baseimponible*1.21, 2, ',','.').'€', 0, 0, 'R');

	if ($total==$baseimponible) {
		$pdf->Output('D',$numerofactura.'.pdf');
		// $ficheropdf=$pdf->Output('S');
		
		 // header('Content-Description: File Transfer');
		 // header('Content-Type: application/octet-stream');
		 // header('Content-Disposition: attachment;filename="Factura.pdf"');
		 // header('Content-Transfer-Encoding: binary');
		 // header('Expires: 0');
		 // header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		 // header('Pragma: public');
		 // header('Content-Length: ' . count($ficheropdf));

		// header('Content-Type: application/octet-stream');	
		// header('Content-Disposition: attachment;filename="Factura.pdf"');	
		// header('Content-Length: '.count($ficheropdf));
		// echo $ficheropdf;
		
		
		
		
	} else {
		echo "ERROR";
	}
} else {
	header("Location: /", true, 302);
}
?>