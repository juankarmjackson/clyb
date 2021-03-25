<?php
// 12/3/21 Version inicial completa

if ($IDC<=0) {
	header('Location: /', true, 302);
} else {
	$editar=false;
	$aviso='';
	
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
	
	if ($idtarjetastripe=='') {
		$infotarjeta="<div class=\"ventana tarjeta gris\"><a href=\"#\" class=\"no\" onclick=\"document.getElementById('Tarjeta').className='popup solido on';return false;\"><img src=\"/img/ico_mas.png\" width=\"35\" height=\"35\"><br><p class=\"texto grisclaro4 t14 s05\">Añadir tarjeta</p></a></div>";
	} else {
		$res=$conn->query("SELECT * FROM tarjetas WHERE idstripe='{$conn->real_escape_string($idtarjetastripe)}' AND idc=$IDC;");
		if ($row = $res->fetch_assoc()) {
			$numerotarjeta=$row['tarjeta'];
			$caducidadtarjeta="{$row['mes']}/{$row['ano']}";
			$infotarjeta="<div class=\"ventana tarjeta visa\"><a href=\"#\" class=\"icoper editar pt\" onclick=\"document.getElementById('Tarjeta').className='popup solido on';return false;\"></a><p class=\"texto blanco t14 s05\">$numerotarjeta</p><p class=\"texto blanco t10 s05\"><span class=\"texto t8\">EXPIRA:</span> &nbsp; &nbsp; $caducidadtarjeta</p></div>";
		} else {
			$infotarjeta="<div class=\"ventana tarjeta gris\"><a href=\"#\" class=\"no\" onclick=\"document.getElementById('Tarjeta').className='popup solido on';return false;\"><img src=\"/img/ico_mas.png\" width=\"35\" height=\"35\"><br><p class=\"texto grisclaro4 t14 s05\">Añadir tarjeta</p></a></div>";			
		}
		$res->Close();
	}

	$infofacturas='';
	$res=$conn->query("SELECT * FROM facturas WHERE idc=$IDC ORDER BY fecha;");
	while ($row = $res->fetch_assoc()) {
		if ($infofacturas=='') $infofacturas="<p class=\"texto grisclaro2 t14\"><b>FACTURAS</b><br><br></p><form method=\"post\" action=\"Factura.pdf\"><table class=\"tabla facturas texto gris t18\">";
		$tmp_numerofactura=$row['serie'].substr("0000{$row['numero']}",-4);
		$tmp_fechafactura=date('d/m/Y',strtotime($row['fecha']));
		$infofacturas.="<tr><td class=\"factura\">$tmp_numerofactura</td><td class=\"fecha\">$tmp_fechafactura</td><td class=\"descarga\"><button type=\"submit\" name=\"id\" value=\"{$row['id']}\"></button></td></tr>";
	}
	$res->Close();
	if ($infofacturas=='') $infofacturas="<p class=\"texto grisclaro2 t14\"><b>FACTURAS</b><br></p><p class=\"texto grisclaro3 t18\"><br><br><br>Aún no tiene ninguna<br>factura registrada.</p>";
	else $infofacturas.="</table></form>";
	
	
	
	

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
                <a href="/perfil" class="iz">MI PERFIL</a><a href="/contratacion">MIS SERVICIOS</a><a href="/pagos" class="dcha on">PAGOS</a>
            </div>
        </div>
        
        <div class="navbar cuadrado"><a href="/" class="iconav inicio">Inicio</a><a href="/servicios" class="iconav servicios">Servicios</a><a href="/push" class="iconav push">Push</a></div>
				
				$infotarjeta
				$infofacturas
				
				
        <div id="Tarjeta" class="popup off">
            <img class="cerrar" src="/img/ico_cerrar.png" alt="cerrar" onclick="document.getElementById('Tarjeta').className='popup off';">
            <form method="post" class="ventana tarjeta popup">
                <input type="hidden" name="datostarjeta" value="SI">
                <input type="text" class="texto blanco t20 ce" style="height: 27px;" name="numerotarjeta" placeholder="____    ____    ____    ____" required pattern="^([0-9]{4}[ ]*){4}$" oninvalid="this.setCustomValidity('Debes introducir un número de tarjeta válido')" onchange="this.setCustomValidity('')" maxlength="19" onkeyup="this.value=this.value.replaceAll(/[^0-9]*/g,'').replaceAll(/(\d{4})/g, '$1 ').substring(0, 19);">
                <p class="texto blanco t11 s05">EXPIRA &nbsp; <input type="text" class="texto blanco t14 ce" style="width: 72px; height: 27px;" name="caducidadtarjeta" placeholder="__/__" required pattern="^[01]?[0-9]\/?(20)?[2-9][0-9]$" oninvalid="this.setCustomValidity('Debes introducir la fecha de caducidad de la tarjeta')" onchange="this.setCustomValidity('')" maxlength="5" onkeyup="this.value=this.value.replaceAll(/[^0-9]*/g,'').replaceAll(/(\d{2})/g, '$1/').substring(0, 5);"></p>
                <p class="texto blanco t11 pex6"><input type="text" class="texto blanco t14 iz" style="width: 157px; height: 52px; border-radius: 30px; padding-left: 14px; margin-right: -50px" name="cvctarjeta" maxlength="3" placeholder="CVC" required pattern="^([0-9]{3}$" oninvalid="this.setCustomValidity('Debes introducir el CVC de la tarjeta')" onchange="this.setCustomValidity('')" onkeyup="this.value=this.value.replaceAll(/[^0-9]*/g,'');"><button type="submit" class="icoper guardar" style="margin-top: 3px;"></button></p>
            </form>
        </div>
        
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
?>