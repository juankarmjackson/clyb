<?php
// 11/11/20 Version inicial
// 27/11/20 Se para Stripe a entorno real
// 5/3/21 Version con presupuestos


$sk_stripe="XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

$conn = new mysqli('localhost', 'XXXXXXXX', 'XXXXXXXX', 'XXXXXXXX');
if ($conn->connect_error) exit('Error de conexión');
$conn->set_charset("utf8");
$conn->query("SET group_concat_max_len = 10240;");

// Aviso tarjetas caducadas
if (date('j')=='1') {
	$res=$conn->query("SELECT clientes.id AS cid, nombre, apellidos, telefono, email, tarjeta, mes, ano FROM tarjetas INNER JOIN clientes ON tarjetas.idc=clientes.id AND tarjetas.idstripe=clientes.idtarjetastripe WHERE clientes.idtarjetastripe<>'' AND tarjetas.mes=MONTH(CURRENT_DATE - INTERVAL 15 DAY) AND tarjetas.ano=YEAR(CURRENT_DATE - INTERVAL 15 DAY)");
	while ($row = $res->fetch_assoc()) {
		$cid=$row['cid'];
		$nombre=$row['nombre'];
		$apellidos=$row['apellidos'];
		$email=$row['email'];
		$tarjeta=$row['tarjeta'];
		$mes=$row['mes'];
		$ano=$row['ano'];
		$conn->query("UPDATE clientes SET idtarjetastripe='' WHERE id=$cid;");
		mail("$nombre $apellidos<$email>", '=?UTF-8?Q?'.imap_8bit("Su tarjeta de crédito ha caducado").'?=', "¡Hola $nombre!\r\n\r\nNos ponemos en contacto contigo para informarte que tu tarjeta $tarjeta con fecha de caducidad $mes/$ano ya ha caducado y no podremos tramitar la renovación de sus servicios de forma automática.\r\n\r\nPor favor, acceda a su área de cliente para introducir los datos de una tarjeta de crédito válida.", "From: Clyb Company <noreply@clyb.es>".PHP_EOL."Bcc: soporte@anescu.es,info@clybconnect.com".PHP_EOL);
		file_get_contents("https://apisms.anescu.net/?usr=giga1929&pass=XXXXXX&from=CLYB&to={$row['telefono']}&msg=".urlencode("Tu tarjeta de credito ha caducado y no podremos renovar tus servicios. Accede para acutizarla. Para obtener ayuda o mas informacion llama al 911239057"));
		$conn->query("INSERT INTO chat (para, texto) VALUES ($cid, '".$conn->real_escape_string("¡Hola $nombre!\r\n\r\nNos ponemos en contacto contigo para informarte que tu tarjeta $tarjeta con fecha de caducidad $mes/$ano ya ha caducado y no podremos tramitar la renovación de sus servicios de forma automática.\r\n\r\nPor favor, introduce una nueva tarjeta en el apartado PAGOS de tu perfil.")."');");
	}
	$res->Close();
}

// Aviso tarjetas que caducarán este mes
if (date('j')=='1' || date('j')=='15') {
	$res=$conn->query("SELECT nombre, apellidos, email, tarjeta, mes, ano FROM tarjetas INNER JOIN clientes ON tarjetas.idc=clientes.id AND tarjetas.idstripe=clientes.idtarjetastripe WHERE tarjetas.mes=MONTH(CURRENT_DATE) AND tarjetas.ano=YEAR(CURRENT_DATE)");
	while ($row = $res->fetch_assoc()) {
		$nombre=$row['nombre'];
		$apellidos=$row['apellidos'];
		$email=$row['email'];
		$tarjeta=$row['tarjeta'];
		$mes=$row['mes'];
		$ano=$row['ano'];
		mail("$nombre $apellidos<$email>", '=?UTF-8?Q?'.imap_8bit("Su tarjeta de crédito caduca este mes").'?=', "¡Hola $nombre!\r\n\r\nNos ponemos en contacto contigo para informarte que tu tarjeta $tarjeta con fecha de caducidad $mes/$ano caducará este mes y no podremos tramitar la renovación de sus servicios de forma automática.\r\n\r\nPor favor, acceda a su área de cliente para introducir los datos de una tarjeta de crédito válida.", "From: Clyb Company <noreply@clyb.es>".PHP_EOL."Bcc: soporte@anescu.es,info@clybconnect.com".PHP_EOL);
		file_get_contents("https://apisms.anescu.net/?usr=giga1929&pass=XXXXXX&from=CLYB&to={$row['telefono']}&msg=".urlencode("Tu tarjeta de credito va a caducar y no podremos renovar tus servicios. Accede para acutizarla. Para obtener ayuda o mas informacion llama al 911239057"));
		$conn->query("INSERT INTO chat (para, texto) VALUES ($cid, '".$conn->real_escape_string("¡Hola $nombre!\r\n\r\nNos ponemos en contacto contigo para informarte que tu tarjeta $tarjeta con fecha de caducidad $mes/$ano va a caducar y no podremos tramitar la renovación de sus servicios de forma automática.\r\n\r\nPor favor, introduce una nueva tarjeta en el apartado PAGOS de tu perfil.")."');");
	}
	$res->Close();
}

// Renovación de presupuestos desde 5 días antes
$tmp_continuar=true;
require_once('stripe-php-7.61.0/init.php');
require_once "modulos/funciones_generales.php";
try {$stripe = new \Stripe\StripeClient($sk_stripe);} catch (Exception $e) {$tmp_continuar=false;}
if ($tmp_continuar) {
	$res=$conn->query("SELECT clientes.id AS cid, clientes.nombre, apellidos, email, telefono, idstripe, idtarjetastripe, presupuestos.id AS pid, IF(alternativo<>'',alternativo,servicios.nombre) AS producto, importe_mensual as importe, fecharenovacion, IF(DATE(presupuestos.fecharenovacion)<=CURRENT_DATE,true,false) AS cancelar FROM presupuestos INNER JOIN clientes ON presupuestos.idc=clientes.id INNER JOIN servicios ON presupuestos.ids=servicios.id WHERE presupuestos.importe_mensual>0 AND presupuestos.fechacancelacion=0 AND DATE(presupuestos.fecharenovacion)-INTERVAL 5 DAY<CURRENT_DATE");
	while ($row = $res->fetch_assoc()) {
		$tmp_continuar=true;
		$renovado=false;
		$cid=$row['cid'];
		$nombre=$row['nombre'];
		$apellidos=$row['apellidos'];
		$email=$row['email'];
		$idstripe=$row['idstripe'];
		$idtarjetastripe=$row['idtarjetastripe'];
		$pid=$row['pid'];
		$producto=$row['producto'];
		$importe=round($row['importe'],2)+round($row['importe']*0.21,2);		
		$cancelar=$row['cancelar'];
		if ($idtarjetastripe!='' && $idstripe!='') {
			$res2=$conn->query("SELECT * FROM presupuestos_conceptos WHERE idp=$pid AND mensual ORDER BY id");
			$tmp_total=0;
			while ($row2 = $res2->fetch_assoc()) {
				$tmp_conceptos[]=$row2['concepto'];
				$tmp_precios[]=round($row2['importe'],2);
				$tmp_total+=round($row2['importe'],2);
			}
			$res2->Close();
			if (round($tmp_total*1.21,2)==$importe) {
				try {
					$cargo = $stripe->paymentIntents->create([
						'amount' => round($importe*100),
						'currency' => 'eur',
						'confirm' => true,
						'customer' => $idstripe,
						'off_session' => true,
						'payment_method' => $idtarjetastripe,
					]);
				} catch (Exception $e) {$cargo=$e;$tmp_continuar=false;}
				if ($tmp_continuar && $cargo['id']!='') {
					$tmp_idcargostripe=$cargo['id'];
					$tmp_amount_received=intval($cargo['amount_received'])/100;
					$tmp_status=$cargo['status'];
					$conn->query("INSERT INTO transacciones (idc, idp, idstripe, importe, cargado, estado) VALUES ($cid, $pid, '{$conn->real_escape_string($tmp_idcargostripe)}', $importe, $tmp_amount_received, '{$conn->real_escape_string($tmp_status)}')");
					if ($tmp_status=='succeeded' && $tmp_amount_received>=($importe-0.1)) {
						$renovado=true;
						$conn->query("UPDATE presupuestos SET fecharenovacion=DATE_ADD(fecharenovacion, INTERVAL 1 MONTH) WHERE id=$pid AND idc=$cid");
						crearfactura($cid,$tmp_conceptos,$tmp_precios,$tmp_total,$pid);
						// mail("$nombre $apellidos<$email>", '=?UTF-8?Q?'.imap_8bit("Renovación de $producto").'?=', "¡Hola $nombre!\r\n\r\nHemos renovado por un mes $producto.\r\nMuchas gracias por confiar en Clyb Company.\r\nSeguimos trabajando para prestarte un buen servicio. Te mantendremos puntualmente informado del estado de tu encargo.\r\n\r\nMuchas gracias por tu confianza.", "From: Clyb Company <noreply@clyb.es>".PHP_EOL."Bcc: soporte@anescu.es,info@clybconnect.com".PHP_EOL);						
						mail("info@clybconnect.com", '=?UTF-8?Q?'.imap_8bit("Renovación de $producto").'?=', "Se ha renovado por un mes $producto de $nombre $apellidos<$email>.", "From: Clyb Company <noreply@clyb.es>".PHP_EOL."Bcc: soporte@anescu.es".PHP_EOL);						
						$conn->query("INSERT INTO chat (para, texto) VALUES ($cid, '".$conn->real_escape_string("¡Hola $nombre!\r\n\r\nHemos renovado por un mes $producto.\r\nMuchas gracias por confiar en Clyb Company.\r\nSeguimos trabajando para prestarte un buen servicio. Te mantendremos puntualmente informado del estado de tu encargo.\r\n\r\nMuchas gracias por tu confianza.")."');");
					}
				} elseif (is_object($cargo)) {
					$tmp_idcargostripe='KO';
					$tmp_amount_received=0;
					$tmp_status='Error: '.$cargo.error.code;
					$conn->query("INSERT INTO transacciones (idc, idp, idstripe, importe, cargado, estado) VALUES ($cid, $pid, '{$conn->real_escape_string($tmp_idcargostripe)}', $importe, $tmp_amount_received, '{$conn->real_escape_string($tmp_status)}')");
				}
			} else mail("Soporte CLYB<soporte@anescu.es>", '=?UTF-8?Q?'.imap_8bit("ERROR: Renovación de $producto").'?=', "No se ha podido renovar $producto ($pid) de $nombre $apellidos ($cid) porque {$tmp_total}+IVA no es igual que $importe\r\n\r\nMuchas gracias por tu confianza.", "From: Clyb Company <noreply@clyb.es>".PHP_EOL);						
		}
		if (!$renovado) {
			if ($cancelar) {
				$conn->query("UPDATE presupuestos SET fechacancelacion=CURRENT_TIMESTAMP WHERE id=$pid AND idc=$cid AND fechacancelacion=0");
				mail("$nombre $apellidos<$email>", '=?UTF-8?Q?'.imap_8bit("Anulación de $producto").'?=', "¡Hola $nombre!\r\n\r\nNo hemos podido renovar $producto.\r\nEs posible que los datos de tu tarjeta de crédito hayan cambiado y no hayamos podído realizar el cargo de forma automática.\r\n\r\nSi no deseabas cancelarlo, dinoslo para volverlo a activar.", "From: Clyb Company <noreply@clyb.es>".PHP_EOL."Bcc: soporte@anescu.es,info@clybconnect.com".PHP_EOL);			
				file_get_contents("https://apisms.anescu.net/?usr=giga1929&pass=XXXXXX&from=CLYB&to={$row['telefono']}&msg=".urlencode("Hemos cancelado alguno de tus servicios por no poder renovarlos. Para obtener ayuda o mas informacion llama al 911239057"));
				$conn->query("INSERT INTO chat (para, texto) VALUES ($cid, '".$conn->real_escape_string("¡Hola $nombre!\r\n\r\nNo hemos podido renovar $producto.\r\nEs posible que los datos de tu tarjeta de crédito hayan cambiado y no hayamos podído realizar el cargo de forma automática.\r\n\r\nSi no deseabas cancelarlo, dinoslo para volverlo a activar.")."');");
			} else {
				mail("$nombre $apellidos<$email>", '=?UTF-8?Q?'.imap_8bit("Error en la renovación de $producto").'?=', "¡Hola $nombre!\r\n\r\nNo hemos podido renovar $producto.\r\nEs posible que los datos de tu tarjeta de crédito hayan cambiado y no hayamos podído realizar el cargo de forma automática. Mañana intentaremos realizar la renovación de nuevo.\r\n\r\nPor favor, accede a tu área de cliente para revisar que los datos de tu tarjeta de crédito son correctos y actualizarlos si fuera necesario.\r\nSeguimos trabajando para prestarte un buen servicio. Te mantendremos puntualmente informado del estado de tu encargo.\r\n\r\nMuchas gracias por tu confianza.", "From: Clyb Company <noreply@clyb.es>".PHP_EOL."Bcc: soporte@anescu.es,info@clybconnect.com".PHP_EOL);
				file_get_contents("https://apisms.anescu.net/?usr=giga1929&pass=XXXXXX&from=CLYB&to={$row['telefono']}&msg=".urlencode("No hemos podido renovar alguno de tus servicios. Comprueba que los datos de tu tarjeta son correctos. Para obtener ayuda o mas informacion llama al 911239057"));
				$conn->query("INSERT INTO chat (para, texto) VALUES ($cid, '".$conn->real_escape_string("¡Hola $nombre!\r\n\r\nNo hemos podido renovar $producto.\r\nEs posible que los datos de tu tarjeta de crédito hayan cambiado y no hayamos podído realizar el cargo de forma automática. Mañana intentaremos realizar la renovación de nuevo.\r\n\r\nPor favor, revisa en el apartado de PAGOS de tu perfil que los datos de tu tarjeta de crédito son correctos y actualizalos si fuera necesario.\r\nSeguimos trabajando para prestarte un buen servicio. Te mantendremos puntualmente informado del estado de tu encargo.\r\n\r\nMuchas gracias por tu confianza.")."');");
			}
		}
	}
	$res->Close();
}





$conn->close();
?>
OK