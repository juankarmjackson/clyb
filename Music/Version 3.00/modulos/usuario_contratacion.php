<?php
// 12/3/21 Version inicial completa

if ($IDC<=0) {
	header('Location: /', true, 302);
} else {

	$contratar=false;
	$cancelar=false;
	$fichas='';
	$ventanapresupuesto='';

	
	
	if (is_numeric($_POST['id'])) $idppto=intval($_POST['id']);
	if ($_POST['accion']=='Contratar') $contratar=true;
	
	if($_POST['datostarjeta']=='SI') {
		$tmp_tarjeta=str_replace(' ', '', trim($_POST['numerotarjeta']));
		$tmp_caducidad=str_replace(' ', '', trim($_POST['caducidadtarjeta']));
		$tmp_cvc=str_replace(' ', '', trim($_POST['cvctarjeta']));
		$tmp_mescaducidad=0;
		$tmp_anocaducidad=0;		
		if (strpos($tmp_caducidad,'/')>0) {
			$tmp_mescaducidad=intval(substr($tmp_caducidad, 0, strpos($tmp_caducidad,'/')));
			$tmp_anocaducidad=intval(substr($tmp_caducidad, strpos($tmp_caducidad,'/')+1,4));
		} elseif (strlen($tmp_caducidad)==3) {
			$tmp_mescaducidad=intval(substr($tmp_caducidad, 0, 1));
			$tmp_anocaducidad=intval(substr($tmp_caducidad, -2));
		} elseif (strlen($tmp_caducidad)==4) {
			$tmp_mescaducidad=intval(substr($tmp_caducidad, 0, 2));
			$tmp_anocaducidad=intval(substr($tmp_caducidad, -2));
		} elseif (strlen($tmp_caducidad)==5) {
			$tmp_mescaducidad=intval(substr($tmp_caducidad, 0, 1));
			$tmp_anocaducidad=intval(substr($tmp_caducidad, -4));
		} elseif (strlen($tmp_caducidad)==6) {
			$tmp_mescaducidad=intval(substr($tmp_caducidad, 0, 2));
			$tmp_anocaducidad=intval(substr($tmp_caducidad, -4));
		}
		if ($tmp_anocaducidad<100) $tmp_anocaducidad+=2000;
	 
		if (!preg_match('/^[0-9]{16}$/', $tmp_tarjeta)) {
			$aviso=popupaviso("El número de tarjeta es incorrecto");
		} elseif (!preg_match('/^[0-9]{3}$/', $tmp_cvc)) {
			$aviso=popupaviso("El CVC es incorrecto");
		} elseif ($tmp_anocaducidad<intval(date('Y')) || $tmp_mescaducidad<1 || $tmp_mescaducidad>12 || ($tmp_anocaducidad==intval(date('Y')) && $tmp_mescaducidad<intval(date('m'))) ) {
			$aviso=popupaviso("La fecha de caducidad no es válida");
		}
		if ($aviso=='') {
			$tmp_continuar=true;
			require_once('stripe-php-7.61.0/init.php');
			try {$stripe = new \Stripe\StripeClient($sk_stripe);} catch (Exception $e) {$tmp_continuar=false;}
			if ($tmp_continuar && $idstripe=='') {
				try {
					$customer = $stripe->customers->create([
						'description' => "Cliente de API $IDC $negocio ($nombre $apellidos) ".date('Ymd'),
					]);
				} catch (Exception $e) {$tmp_continuar=false;}
				if ($tmp_continuar && $customer['id']!='') {
					$idstripe=$customer['id'];
					$conn->query("UPDATE clientes SET idstripe='{$conn->real_escape_string($idstripe)}' WHERE idstripe='' AND id=$IDC");					
				}				
			}
			if ($tmp_continuar && $idstripe!='') {
				try {
					$tarjeta = $stripe->paymentMethods->create([
						'type' => 'card',
						'card' => [
							'number' => $tmp_tarjeta,
							'exp_month' => $tmp_mescaducidad,
							'exp_year' => $tmp_anocaducidad,
							'cvc' => $tmp_cvc,
						],
					]);
				} catch (Exception $e) {
					$aviso=popupaviso("La tarjeta no es válida");
				}
				$tmp_tarjeta='**** **** **** '.substr($tmp_tarjeta,-4);				
				if ($aviso=='' && $tarjeta['id']!='') {
					try {
						$asociartarjeta = $stripe->paymentMethods->attach(
							$tarjeta['id'],
							['customer' => $idstripe]
						);						
					} catch (Exception $e) {$tmp_continuar=false;}		
					if ($tmp_continuar) {
						$idtarjetastripe=$tarjeta['id'];
						$conn->query("UPDATE tarjetas SET predeterminada=false WHERE idc=$IDC");
						$conn->query("INSERT INTO tarjetas (idc, tarjeta, mes, ano, idstripe, predeterminada) VALUES ($IDC, '$tmp_tarjeta', $tmp_mescaducidad, $tmp_anocaducidad, '{$conn->real_escape_string($idtarjetastripe)}', true)");
						$conn->query("UPDATE clientes SET idtarjetastripe='{$conn->real_escape_string($idtarjetastripe)}' WHERE id=$IDC");
					} else {$aviso=popupaviso("Error al guardar la tarjeta");}
				} else {$aviso=popupaviso("Error al procesar la tarjeta");}
			} else {$aviso=popupaviso("Error al tramitar la tarjeta");}
		}
	}
	
	if ($_POST['accion']=='Pagar' && $idtarjetastripe!='' && $idstripe!='' && $idppto>0) {
		$tmp_contratable=false;
		$res=$conn->query("SELECT presupuestos.*, servicios.nombre, IF(fechacancelacion>'2020-01-01','Cancelado',IF(fechacontratacion>'2020-01-01','Contratado',IF(fechacreacion>DATE_SUB(CURRENT_DATE, INTERVAL $caducidadppto DAY),'Pendiente','Caducado'))) AS estado FROM presupuestos LEFT JOIN servicios ON servicios.id=presupuestos.ids WHERE presupuestos.idc=$IDC AND presupuestos.activo AND (importe_fijo!=0 OR importe_mensual!=0) AND presupuestos.id=$idppto");
		if ($row = $res->fetch_assoc()) {
			if ($row['alternativo']!='') $tmp_producto=$row['alternativo']; else  $tmp_producto=$row['nombre'];
			$tmp_importe=round($row['importe_fijo'],2)+round($row['importe_mensual'],2);
			if ($row['estado']=='Pendiente') $tmp_contratable=true;
		} else $idppto=0;
		$res->Close();
		$res=$conn->query("SELECT * FROM presupuestos_conceptos WHERE idp=$idppto ORDER BY id");
		$tmp_total=0;
		while ($row = $res->fetch_assoc()) {
			$tmp_conceptos[]=$row['concepto'];
			$tmp_precios[]=round($row['importe'],2);
			$tmp_total+=round($row['importe'],2);
		}
		$res->Close();
		if ($tmp_total==$tmp_importe && $tmp_total>0 && $tmp_contratable && $idppto>0) {
			$tmp_continuar=true;
			require_once('stripe-php-7.61.0/init.php');
			try {$stripe = new \Stripe\StripeClient($sk_stripe);} catch (Exception $e) {$tmp_continuar=false;}
			if ($tmp_continuar) {
				try {
					$cargo = $stripe->paymentIntents->create([
						'amount' => round($tmp_importe*100)+round($tmp_importe*21),
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
					$conn->query("INSERT INTO transacciones (idc, idp, idstripe, importe, cargado, estado) VALUES ($IDC, $idppto, '{$conn->real_escape_string($tmp_idcargostripe)}', $tmp_importe, $tmp_amount_received, '{$conn->real_escape_string($tmp_status)}')");
					if ($tmp_status=='succeeded' && $tmp_amount_received>=($tmp_importe-0.1)) {
						$conn->query("UPDATE presupuestos SET fechacontratacion=CURRENT_TIMESTAMP, fecharenovacion=IF(importe_mensual>0,DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 1 MONTH),0) WHERE id=$idppto AND idc=$IDC");
						$conn->query("INSERT INTO chat (para, texto) VALUES ($IDC, '".$conn->real_escape_string("¡Hola $nombre!\r\n\r\nHemos recibido tu contratación de $tmp_producto.\r\nMuchas gracias por confiar en Clyb Company.\r\nDesde este momento nos ponemos en marcha para prestarte un buen servicio. Te mantendremos puntualmente informado del estado de tu encargo.\r\n\r\nMuchas gracias por tu confianza.")."');");
						mail("$nombre $apellidos<$email>", '=?UTF-8?Q?'.imap_8bit("Contratación de $tmp_producto").'?=', "¡Hola $nombre!\r\n\r\nHemos recibido tu contratación de $tmp_producto.\r\nMuchas gracias por confiar en Clyb Company.\r\nDesde este momento nos ponemos en marcha para prestarte un buen servicio. Te mantendremos puntualmente informado del estado de tu encargo.\r\n\r\nMuchas gracias por tu confianza.", "From: Clyb Company <noreply@clyb.es>".PHP_EOL."Bcc: soporte@anescu.es,info@clybconnect.com".PHP_EOL);
						crearfactura($IDC,$tmp_conceptos,$tmp_precios,$tmp_total);
						$aviso=popupaviso('Muchas gracias ¡Nos ponemos a trabajar!','icon.png');
						$idppto=0;
					}
				} elseif (is_object($cargo)) {
					$tmp_idcargostripe='KO';
					$tmp_amount_received=0;
					$tmp_status='Error: '.$cargo.error.code;
					$conn->query("INSERT INTO transacciones (idc, idp, idstripe, importe, cargado, estado) VALUES ($IDC, $idppto, '{$conn->real_escape_string($tmp_idcargostripe)}', $tmp_importe, $tmp_amount_received, '{$conn->real_escape_string($tmp_status)}')");
					$tmp_error='El pago fue rechazado';
				} else $tmp_error='El pago no pudo ser procesado';
			} else $tmp_error='La tarjeta de crédito no es válida';				
		} elseif ($tmp_total==$tmp_importe && $tmp_total==0 && $tmp_contratable && $idppto>0) {
			$conn->query("UPDATE presupuestos SET fechacontratacion=CURRENT_TIMESTAMP, fecharenovacion=IF(importe_mensual>0,DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 1 MONTH),0) WHERE id=$idppto AND idc=$IDC");
			$conn->query("INSERT INTO chat (para, texto) VALUES ($IDC, '".$conn->real_escape_string("¡Hola $nombre!\r\n\r\nHemos recibido tu contratación de $tmp_producto.\r\nMuchas gracias por confiar en Clyb Company.\r\nDesde este momento nos ponemos en marcha para prestarte un buen servicio. Te mantendremos puntualmente informado del estado de tu encargo.\r\n\r\nMuchas gracias por tu confianza.")."');");
			mail("$nombre $apellidos <$email>", '=?UTF-8?Q?'.imap_8bit("Contratación de $tmp_producto").'?=', "¡Hola $nombre!\r\n\r\nHemos recibido tu contratación de $tmp_producto.\r\nMuchas gracias por confiar en Clyb Company.\r\nDesde este momento nos ponemos en marcha para prestarte un buen servicio. Te mantendremos puntualmente informado del estado de tu encargo.\r\n\r\nMuchas gracias por tu confianza.", "From: Clyb Company <noreply@clyb.es>".PHP_EOL."Bcc: soporte@anescu.es,info@clybconnect.com".PHP_EOL);
			crearfactura($IDC,$tmp_conceptos,$tmp_precios,$tmp_total,$idppto);
			$aviso=popupaviso('Muchas gracias ¡Nos ponemos a trabajar!','icon.png');
			$idppto=0;
		} else {
			$aviso=popupaviso("Ocurrio un error durante la contratación");
		}
	}
	
	
	
	if ($_POST['accion']=='Cancelar' && $idppto>0) {
		$conn->query("UPDATE presupuestos SET fechacancelacion=CURRENT_TIMESTAMP WHERE idc=$IDC AND id=$idppto AND fechacancelacion<'2020-01-01' AND fechacontratacion>'2020-01-01' AND importe_mensual!=0;");
		if ($conn->affected_rows>0) {
			$aviso=popupaviso("<b>Servicio cancelado</b><br>¡Gracias por confiar en CLYB! Estaremos encantados de volver a ofrecerte cualquiera de nuestros servicios",'icon.png');
			$idppto=0;
			mail("$nombre $apellidos<$email>", '=?UTF-8?Q?'.imap_8bit("Servicio cancelado").'?=', "¡Hola $nombre!\r\n\r\nHemos cancelado el servicio, tal y como nos has pedido.\r\n\r\n¡Gracias por confiar en CLYB! Estaremos encantados de volver a ofrecerte cualquiera de nuestros servicios", "From: Clyb Company <noreply@clyb.es>".PHP_EOL."Bcc: soporte@anescu.es,info@clybconnect.com".PHP_EOL);			
			$conn->query("INSERT INTO chat (para, texto) VALUES ($IDC, '".$conn->real_escape_string("¡Hola $nombre!\r\n\r\nHemos cancelado el servicio, tal y como nos has pedido.\r\n\r\n¡Gracias por confiar en CLYB! Estaremos encantados de volver a ofrecerte cualquiera de nuestros servicios")."');");
		} else {
			$aviso=popupaviso("<b>Error cancelación</b><br>No se ha podido cancelar el servicio automáticamente. Por favor, ponte en contacto con nosotros para cancelar el servicio.");
		}
	}

	
	
	if ($idppto>0) {
		$res=$conn->query("SELECT presupuestos.*, servicios.nombre, IF(fechacancelacion>'2020-01-01','Cancelado',IF(fechacontratacion>'2020-01-01','Contratado',IF(fechacreacion>DATE_SUB(CURRENT_DATE, INTERVAL $caducidadppto DAY),'Pendiente','Caducado'))) AS estado FROM presupuestos LEFT JOIN servicios ON servicios.id=presupuestos.ids WHERE presupuestos.idc=$IDC AND presupuestos.activo AND (importe_fijo!=0 OR importe_mensual!=0) AND presupuestos.id=$idppto");
		if ($row = $res->fetch_assoc()) {
			$idservicio=$row['ids'];
			$versionservicio=$row['version'];
			$nombreservicio=textoahtml($row['nombre']);
			$texto_superior=textoahtml($row['texto_superior']);
			$texto_inferior=textoahtml($row['texto_inferior']);
			$texto_superior="<p class=\"texto gris t16 a90 s10 js\">$texto_superior</p>";
			$texto_inferior="<p class=\"texto gris t16 a90 s10 js\">$texto_inferior</p>";
			if ($row['estado']=='Pendiente')
				$botoncontratar="<form method=\"post\" onclick=\"this.submit();return false;\"><input type=\"hidden\" name=\"id\" value=\"$idppto\"><input type=\"hidden\" name=\"accion\" value=\"Contratar\"><a href=\"#\" class=\"no boton azul presupuesto texto t20 l1\" onclick=\"this.submit();return false;\">CONTRATAR</a></form>";
			elseif ($row['estado']=='Contratado' && $row['importe_mensual']!=0)
				$botoncontratar="<p class=\"texto gris t14 a90 s10 ce\"><a href=\"#\" onclick=\"document.getElementById('Cancelar').className='popup on';return false;\">Cancelar servicio</a><br></p><br>".PHP_EOL;
			else
				$botoncontratar='';

		} else $idppto=0;
		$res->Close();


		if ($contratar) {
			if ($idtarjetastripe=='') {
				$infotarjeta="<div class=\"ventana tarjeta gris\"><a href=\"#\" class=\"no\" onclick=\"document.getElementById('Tarjeta').className='popup solido on';return false;\"><img src=\"/img/ico_mas.png\" width=\"35\" height=\"35\"><br><p class=\"texto grisclaro4 t14 s05\">Añadir tarjeta</p></a></div>";
				$botoncontratar="<div onclick=\"document.getElementById('Tarjeta').className='popup solido on';return false;\"><a href=\"#\" class=\"no boton azul presupuesto texto t20 l1\" onclick=\"document.getElementById('Tarjeta').className='popup solido on';return false;\">PAGAR</a></div>";
			} else {
				$res=$conn->query("SELECT * FROM tarjetas WHERE idstripe='{$conn->real_escape_string($idtarjetastripe)}' AND idc=$IDC;");
				if ($row = $res->fetch_assoc()) {
					$numerotarjeta=$row['tarjeta'];
					$caducidadtarjeta="{$row['mes']}/{$row['ano']}";
					$infotarjeta="<div class=\"ventana tarjeta visa\"><a href=\"#\" class=\"icoper editar pt\" onclick=\"document.getElementById('Tarjeta').className='popup solido on';return false;\"></a><p class=\"texto blanco t14 s05\">$numerotarjeta</p><p class=\"texto blanco t10 s05\"><span class=\"texto t8\">EXPIRA:</span> &nbsp; &nbsp; $caducidadtarjeta</p></div>";
					$botoncontratar="<form id=\"FormPago\"method=\"post\"><input type=\"hidden\" name=\"id\" value=\"$idppto\"><input type=\"hidden\" name=\"accion\" value=\"Pagar\"><a href=\"#\" class=\"no boton azul presupuesto texto t20 l1\" onclick=\"document.getElementById('FormPago').submit();this.setAttribute('onClick', 'return false;');this.innerHTML='PAGO EN CURSO...';return false;\">PAGAR</a></form>";
				} else {
					$infotarjeta="<div class=\"ventana tarjeta gris\"><a href=\"#\" class=\"no\" onclick=\"document.getElementById('Tarjeta').className='popup solido on';return false;\"><img src=\"/img/ico_mas.png\" width=\"35\" height=\"35\"><br><p class=\"texto grisclaro4 t14 s05\">Añadir tarjeta</p></a></div>";
					$botoncontratar="<div onclick=\"document.getElementById('Tarjeta').className='popup solido on';return false;\"><a href=\"#\" class=\"no boton azul presupuesto texto t20 l1\" onclick=\"document.getElementById('Tarjeta').className='popup solido on';return false;\">PAGAR</a></div>";
				}
				$res->Close();
			}
			$texto_superior='';
			$texto_inferior="<p class=\"texto gris t24 a90 s10 ce\">FORMA DE PAGO</p>$infotarjeta";
		}
		
		$ventanapresupuesto="<div class=\"ventana titulo\"><div class=\"encabezado texto blanco t14 l1\">PRESUPUESTO</div><table class=\"tabla presupuesto texto gris t14\">";
		$baseimponible=0;
		$res=$conn->query("SELECT * FROM presupuestos_conceptos WHERE idp=$idppto ORDER BY id");
		while ($row = $res->fetch_assoc()) {
			$concepto=$row['concepto'];
			$importe=number_format ($row['importe'], 2, '\'','.');
			$baseimponible+=$row['importe'];
			if ($row['mensual']) $mensual='€/mes'; else $mensual='€';
			$ventanapresupuesto.="<tr><td class=\"concepto\">$concepto</td><td class=\"importe\">$importe</td><td class=\"unidades\">$mensual</td></tr>";
		}
		$res->Close();
		$iva=$baseimponible*0.21;
		$total=$baseimponible+$iva;
		$baseimponible=number_format ($baseimponible, 2, '\'','.');
		$iva=number_format ($iva, 2, '\'','.');
		$total=number_format ($total, 2, '\'','.');
		$ventanapresupuesto.="<tr><td colspan=\"3\">&nbsp;</td></tr><tr><td class=\"derecha\">SUBTOTAL</td><td class=\"texto de\">$baseimponible</td><td class=\"texto iz\">€</td></tr><tr><td class=\"derecha\">I.V.A.</td><td class=\"texto de\">$iva</td><td class=\"texto iz\">€</td></tr><tr><td class=\"derecha\"><b>TOTAL</b></td><td class=\"texto azul de\"><b>$total</b></td><td class=\"texto azul iz\"><b>€</b></td></tr></table></div>";
	} else {
		$res=$conn->query("SELECT * FROM clientes WHERE id=$IDC;");
		if ($row = $res->fetch_assoc()) {
			$valor_fotover=$row['foto'];
			if ($valor_fotover>0) $valor_foto=$IDC; else $valor_foto=0;
		} else header('Location: /', true, 302);
		$res->Close(); 
		
		$res=$conn->query("SELECT presupuestos.*, servicios.nombre, servicios.id AS ids, servicios.version, IF(fechacancelacion>'2020-01-01','Cancelado',IF(fechacontratacion>'2020-01-01','Contratado',IF(fechacreacion>DATE_SUB(CURRENT_DATE, INTERVAL $caducidadppto DAY),'Pendiente','Caducado'))) AS estado FROM presupuestos LEFT JOIN servicios ON servicios.id=presupuestos.ids WHERE presupuestos.idc=$IDC AND activo AND (importe_fijo!=0 OR importe_mensual!=0) ORDER BY fechacontratacion>'2020-01-01', fechacreacion DESC, fechacontratacion DESC");
		while ($row = $res->fetch_assoc()) {
			if ($row['estado']=='Pendiente' || $row['estado']=='Contratado') {
				if ($row['estado']=='Pendiente') $tmp_pendiente='<p class="pendiente">PENDIENTE</p>'; else $tmp_pendiente='';
				if ($row['alternativo']!='') $tmp_nombre=$row['alternativo']; else $tmp_nombre=$row['nombre'];
				$fichas.="<form class=\"ventana presupuesto\" method=\"post\" onclick=\"this.submit();return false;\"><a href=\"#\" class=\"no\" onclick=\"this.submit();return false;\"><input type=\"hidden\" name=\"id\" value=\"{$row['id']}\"><img src=\"/img/pixel_transparente.png\" style=\"background-image: url('/img/servicio_{$row['ids']}.jpg?{$row['version']}')\"><p class=\"texto gris a90 t11 l1 iz s05\"><b>$tmp_nombre</b></p>$tmp_pendiente</a></form>".PHP_EOL;
			}
		}
		$res->Close(); 
	}

if ($idppto>0) {
$contenido_completo=<<<END
<!DOCTYPE html>
<html>
    <head>
        <title>Clyb App</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <link rel="shortcut icon" type="image/x-icon" href="/icon-normal.png">
        <link rel="stylesheet" href="estilos.css?$cssversion">
    </head>
    <body class="relleno servicio fondo_gris">
				<div class="fondo_gris" style="position:fixed; top: 0; left: 0; width: 100vw; height: 150px;"></div>
        <div class="filtro_sombra"><div class="topbar elipse" style="background-image: url(/img/servicio_$idservicio.jpg?$versionservicio);">
            <img>
            <a href="/contratacion" class="ico_atrasw"></a>
            <a class="logo_centralw"></a>
            <a href="/chat" class="ico_chatw"></a>
            <p class="texto t20 blanco l1 pex1">$nombreservicio</p>
        </div></div>
        <div class="navbar cuadrado"><a href="/" class="iconav inicio">Inicio</a><a href="/servicios" class="iconav servicios">Servicios</a><a href="/push" class="iconav push">Push</a></div>
        $texto_superior
				$ventanapresupuesto
        $texto_inferior
				$botoncontratar
        <div id="Cancelar" class="popup off">
            <div class="variable" style="top: 230px">
                <form method="post">                    
                    <p class="texto t20 s10">¿Estas seguro de querer<br>cancelar el servicio?</p>
                    <br>
                    <input type="hidden" name="id" value="$idppto">
                    <input type="hidden" name="accion" value="Cancelar">
                    <button type="submit" class="boton blanco">SI, CANCELAR</button><br>
                    <a href="#"><button type="button" class="boton azul" onclick="document.getElementById('Cancelar').className='popup off';return false;">NO, VOLVER</button></a><br>
                    <br>
                </form>
            </div>
        </div>
        <div id="Tarjeta" class="popup off">
						<img class="cerrar" src="/img/ico_cerrar.png" alt="cerrar" onclick="document.getElementById('Tarjeta').className='popup off';">
            <form method="post" class="ventana tarjeta popup">
                <input type="hidden" name="datostarjeta" value="SI">
								<input type="hidden" name="id" value="$idppto">
								<input type="hidden" name="accion" value="Contratar">
                <input type="text" class="texto blanco t20 ce" style="height: 27px;" name="numerotarjeta" placeholder="____    ____    ____    ____" required pattern="^([0-9]{4}[ ]*){4}$" oninvalid="this.setCustomValidity('Debes introducir un número de tarjeta válido')" onchange="this.setCustomValidity('')" maxlength="19" onkeyup="this.value=this.value.replaceAll(/[^0-9]*/g,'').replaceAll(/(\d{4})/g, '$1 ').substring(0, 19);">
                <p class="texto blanco t11 s05">EXPIRA &nbsp; <input type="text" class="texto blanco t14 ce" style="width: 72px; height: 27px;" name="caducidadtarjeta" placeholder="__/__" required pattern="^[01]?[0-9]\/?(20)?[2-9][0-9]$" oninvalid="this.setCustomValidity('Debes introducir la fecha de caducidad de la tarjeta')" onchange="this.setCustomValidity('')" maxlength="5" onkeyup="this.value=this.value.replaceAll(/[^0-9]*/g,'').replaceAll(/(\d{2})/g, '$1/').substring(0, 5);"></p>
                <p class="texto blanco t11 pex6"><input type="text" class="texto blanco t14 iz" style="width: 157px; height: 52px; border-radius: 30px; padding-left: 14px; margin-right: -50px" name="cvctarjeta" maxlength="3" placeholder="CVC" required pattern="^([0-9]{3}$" oninvalid="this.setCustomValidity('Debes introducir el CVC de la tarjeta')" onchange="this.setCustomValidity('')" onkeyup="this.value=this.value.replaceAll(/[^0-9]*/g,'');"><button type="submit" class="icoper guardar" style="margin-top: 3px;"></button></p>
            </form>
        </div>
				$aviso
    </body>
</html>
END;
} else {
$contenido_completo=<<<END
<!DOCTYPE html>
<html>
    <head>
        <title>Clyb App</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <link rel="shortcut icon" type="image/x-icon" href="/icon-normal.png">
        <link rel="stylesheet" href="/estilos.css?$cssversion">
    </head>
    <body class="relleno perfil fondo_gris">
				<div class="fondo_gris tapa"></div>
        <div class="fondo_gris" style="position:fixed; top: 0; left: 0; width: 100vw; height: 260px;"></div>
        <div class="topbar suplemento fondo_degradado">
            <a href="/perfil" class="ico_perfilw"></a>
            <a class="logo_centralw"></a>
            <a href="/chat" class="ico_chatw"></a>
        </div>
        
        <div class="filtro_sombra flotante">
            <div class="topbar contenedor flotante">
                <img src="/img/pixel_transparente.png" style="background-image: url('/img/perfil_$valor_foto.jpg?$valor_fotover')">
            </div>
            <div class="topbar elipse flotante fondo_degradado"></div>
            <div class="topbar menu_perfil flotante">
                <a href="/perfil" class="iz">MI PERFIL</a><a href="/contratacion" class="on">MIS SERVICIOS</a><a href="/pagos" class="dcha">PAGOS</a>
            </div>
        </div>
        
        <div class="navbar cuadrado"><a href="/" class="iconav inicio">Inicio</a><a href="/servicios" class="iconav servicios">Servicios</a><a href="/push" class="iconav push">Push</a></div>
				$fichas
        <div id="Salir" class="popup off">
            <div class="variable" style="top: 230px">
                <p class="texto t20 s10">¿Estas seguro de querer<br>cerrar tu sesion?</p>
                <br>
                <a href="/logout"><button type="button" class="boton blanco">CERRAR SESION</button></a><br>
                <a href="#"><button type="button" class="boton azul" onclick="document.getElementById('Salir').className='popup off';return false;">CANCELAR</button></a><br>
                <br>
            </div>
        </div>
				$aviso
    </body>
</html>
END;
}
}
?>