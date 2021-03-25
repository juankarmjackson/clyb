<?php
// 5/8/20 Version inicial incompleta
// 7/8/20 Versión inicial acabada
// 10/8/20 Usuario instagram - ultimos envios - no bloqueo de envio a su email - nombre agencia en perfil y remitente
// 28/10/20 Se cotejan clientes y usuarios para que no exista alguno con el mismo email o teléfono. Se sustituye 00 por + al inicio de cualquier teléfono y se elimina el +34. Los usuarios no ven clientes con clave y los administradores ven sus clientes y los que tienen clave
// 29/10/20 Los administradores pueden capturar un cliente. En ese momento se le asigna una contraseña y se envía por email
// 21/10/20 Permite insertar productos para un cliente
// 3/11/20 A los clientes les permite insertar su tarjeta de crédito
// 4/11/20 Se cambia la denominacion producto por servicio. Los clientes pueden contratar y cancelar servicios
// 27/11/20 Se pasa Stripe a entorno real
// 20/2/21 - Se separa clientes de clientes_potenciales en dos bases de datos distintas y se inhabilita el paso de clientes_potenciales a clientes
// 23/2/21 - Se crea la variable contenido_completo para HTML externo saltando el renderizado de motor
// 24/2/21 - Se añade la sección de recuperar contraseña y registro de usuario
// 26/2/21 - Se añade la sección de servicios y chat
// 27/2/21 - Se añade la sección de mi perfil
// 28/2/21 - Se añade la sección de mi perfil editable
// 1/3/21 - Se añade el muro
// 2/3/21 - Se añade la creación de presupuestos del administrador


// Variables globales
$caducidadppto=180; // Dias para la caducidad de un presupuesto
$cssversion='v1.00';date('dmyHi'); // Versión de CSS para actualización de contenidos
$seccion=substr($_SERVER['REQUEST_URI'],1);
$contenido='';
$contenido_completo='';

$sk_stripe="XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

require_once "modulos/funciones_generales.php";

$conn = new mysqli('localhost', 'XXXXXXXX', 'XXXXXXXX', 'XXXXXXXX');
if ($conn->connect_error) exit('Error de conexión');
$conn->set_charset("utf8");
$conn->query("SET group_concat_max_len = 10240;");

$admin=false;
$usuario=false;
$cliente=false;


$IDU=intval(substr($_COOKIE['TMPUSR'],0,6));
$IDC=intval(substr($_COOKIE['TMPCLI'],0,6));
if ($IDU>0) {
	$res=$conn->query("SELECT * FROM usuarios WHERE id=$IDU AND activo");
	if ($res->num_rows > 0) {
		$row = $res->fetch_assoc();
		$IDU=$row['id'];
		$nombre=$row['nombre'];
		$apellidos=$row['apellidos'];
		$agencia=$row['agencia'];
		$instagram=$row['instagram'];
		$email=$row['email'];
		$telefono=$row['telefono'];
		$clave=$row['clave'];
		$perfiles=$row['perfiles'];
		$admin=$row['admin'];
		$usuario=true;
		$IDU=($_COOKIE['TMPUSR']==substr('000000'.$IDU,-6).sha1($IDU.$clave.'Cadena de texto de aleatorizacion de Clyb')) ? $IDU : 0;
	} else $IDU=0;
	$res->Close();
	if ($IDU==0) {
		setcookie('TMPUSR', '000000', time()+30);
		if ($seccion!='') header("Location: /", true, 301);
	}
} elseif ($IDC>0) {
	$res=$conn->query("SELECT * FROM clientes WHERE id=$IDC AND activo");
	if ($res->num_rows > 0) {
		$row = $res->fetch_assoc();
		$IDC=$row['id'];
		$nombre=$row['nombre'];
		$apellidos=$row['apellidos'];
		$negocio=$row['negocio'];
		$email=$row['email'];
		$telefono=$row['telefono'];
		$clave=$row['clave'];
		$idstripe=$row['idstripe'];
		$idtarjetastripe=$row['idtarjetastripe'];
		$valor_fotover=$row['foto'];
		if ($valor_fotover>0) $valor_foto=$IDC; else $valor_foto=0;
		$cliente=true;
		$IDC=($_COOKIE['TMPCLI']==substr('000000'.$IDC,-6).sha1($IDC.$clave.'Cadena de texto de aleatorizacion de cliente Clyb')) ? $IDC : 0;
	} else $IDC=0;
	$res->Close();
	if ($IDC==0) {
		setcookie('TMPCLI', '000000', time()+30);
		if ($seccion!='') header("Location: /", true, 301);
	}
}
$tituloseccion='Inicio';
$menulateral="";
$informacion="";
if ($admin) {
	$menulateral="<a href='/'><img src='img/home.png'> Inicio</a><br><a href='clientes'><img src='img/chat_enviar2.png'> Leads</a><br><a href='logout'><img src='img/logout.png'> Salir</a><br>";
	$informacion=<<<END
<div id='logohome'></div><div id='bienvenida'><b>Bienvenido $nombre</b><br><br>
</div>
END;
} elseif ($usuario) {
	$menulateral="<a href='/'><img src='img/home.png'> Inicio</a><br><a href='clientes'><img src='img/chat_enviar2.png'> Leads</a><br><a href='logout'><img src='img/logout.png'> Salir</a><br>";
	$informacion="<div id='logohome'></div><div id='bienvenida'><b>Bienvenido $nombre</b><br><br><iframe width='80%' height='80%' src='https://www.youtube.com/embed/SajnhnZrimY' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe></div>";
}
if ($admin) $menulateral="<a href='/'><img src='img/home.png'> Inicio</a><br><a href='/muro'><img src='img/icon2.png'> Muro</a><br><a href='servicios'><img src='img/servicios.png'> Servicios</a><br><a href='presupuestos'><img src='img/clientes.png'> Clientes</a><br><a href='chat'><img src='img/ico_chatw.png'> Chat</a><br><a href='usuarios'><img src='img/usuarios.png'> Freelance</a><br><a href='perfiles'><img src='img/perfiles.png'> Perfiles</a><br><a href='plantillas'><img src='img/plantillas.png'> Plantillas</a><br><a href='dossieres'><img src='img/dossieres.png'> Dossieres</a><br><a href='clientes'><img src='img/chat_enviar2.png'> Leads</a><br><a href='logout'><img src='img/logout.png'> Salir</a><br>";



if ($seccion=="acceso" && $IDC==0 && $IDU==0) {
	require_once "modulos/usuario_acceso.php";
} elseif ($seccion=="logout")  {
	setcookie('TMPUSR', '000000', time()+30);
	setcookie('TMPCLI', '000000', time()+30);
	$IDU=0;
	$IDC=0;
	$admin=false;
	$usuario=false;
	$cliente=false;
	$contenido="<!DOCTYPE html><body><script type='text/javascript'>window.location.href='/';</script>Hasta otra</body></html>";
} elseif ($seccion=="olvido" && $IDC==0 && $IDU==0) {
	require_once "modulos/usuario_recuperarclave.php";
} elseif ($seccion=="registro" && $IDC==0 && $IDU==0) {
	require_once "modulos/usuario_registro.php";
} elseif ($seccion=="servicios" && $IDC>0) {
	require_once "modulos/usuario_servicios.php";
} elseif ($seccion=="perfil" && $IDC>0) {
	require_once "modulos/usuario_perfil.php";
} elseif ($seccion=="chat" && $IDC>0) {
	require_once "modulos/usuario_chat.php";
} elseif ($seccion=="chat" && $admin) {
	require_once "modulos/admin_chat.php";
} elseif ($seccion=="" && $IDC>0) {
	require_once "modulos/usuario_muro.php";
} elseif ($seccion=="muro" && $admin) {
	require_once "modulos/admin_muro.php";
} elseif ($seccion=="presupuestos" && $admin) {
	require_once "modulos/admin_presupuestos.php";
} elseif ($seccion=="contratacion" && $IDC>0) {
	require_once "modulos/usuario_contratacion.php";
} elseif ($seccion=="pagos" && $IDC>0) {
	require_once "modulos/usuario_pagos.php";
} elseif ($seccion=="push" && $IDC>0) {
	require_once "modulos/usuario_push.php";
} elseif ($seccion=="servicios" && $admin) {
	require_once "modulos/admin_servicios.php";
} elseif ($seccion=="clientes" && $usuario)  {
	$tituloseccion='Clientes';
	$tmp_edit=false;
	$tmp_send=false;
	$tmp_cart=false;
	$tmp_product=false;
	if ($_POST['accion']=='edit') {
		$tmp_id=intval($_POST['id']);
		$tmp_nombre=trim($_POST['nombre']);
		$tmp_apellidos=trim($_POST['apellidos']);
		$tmp_negocio=trim($_POST['negocio']);
		$tmp_email=trim($_POST['email']);
		$tmp_telefono=str_replace(' ', '', $_POST['telefono']);
		$tmp_telefono=preg_replace('/^00/', '+', $tmp_telefono);		
		$tmp_telefono=preg_replace('/^\+34/', '', $tmp_telefono);		
		$res=$conn->query("SELECT * FROM (SELECT email,telefono FROM clientes_potenciales WHERE id<>$tmp_id UNION ALL SELECT email,telefono FROM usuarios) AS CT1 WHERE email='{$conn->real_escape_string($tmp_email)}' OR (telefono='{$conn->real_escape_string($tmp_telefono)}' AND telefono<>'')");
		$tmp_guardar=($res->num_rows>0) ? false : true;
		$res->Close();
		if ($tmp_guardar && $tmp_nombre=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar el nombre';
		} elseif ($tmp_guardar && $tmp_apellidos=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar los apellidos';
		} elseif ($tmp_guardar && $tmp_negocio=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar el nombre del negocio';
		} elseif ($tmp_guardar && !preg_match('/^[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i', $tmp_email)) {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar un email correcto';
		} elseif ($tmp_guardar && $tmp_telefono!='' && !preg_match('/^(\+\d{9,}|[6-9]\d{8})$/i', $tmp_telefono)) {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar un teléfono correcto';
		} elseif ($tmp_guardar && $tmp_id>0) {
			$conn->query("UPDATE clientes_potenciales SET nombre='{$conn->real_escape_string($tmp_nombre)}', apellidos='{$conn->real_escape_string($tmp_apellidos)}', negocio='{$conn->real_escape_string($tmp_negocio)}', email='{$conn->real_escape_string($tmp_email)}', telefono='{$conn->real_escape_string($tmp_telefono)}' WHERE id=$tmp_id");
			header("Location: $seccion", true, 301);
		}	elseif ($tmp_guardar) {
			$conn->query("INSERT INTO clientes_potenciales (id, nombre, apellidos, negocio, email, telefono, idu) VALUES (NULL, '{$conn->real_escape_string($tmp_nombre)}', '{$conn->real_escape_string($tmp_apellidos)}', '{$conn->real_escape_string($tmp_negocio)}', '{$conn->real_escape_string($tmp_email)}', '{$conn->real_escape_string($tmp_telefono)}', $IDU)");
			header("Location: $seccion", true, 301);
		} else {
			$tmp_edit=true;
			$tmp_error='Ya existe algún cliente con el mismo email o teléfono';
		}

	}	elseif ($_POST['accion']=='send') {
		$tmp_send=true;
		$tmp_bloqueo=false;
		$tmp_id=intval($_POST['id']);
		$tmp_plantilla=intval($_POST['plantilla']);
		$tmp_fichero=intval($_POST['fichero']);
		$res=$conn->query("SELECT * FROM clientes_potenciales WHERE id=$tmp_id AND idu=$IDU");
		if ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			$tmp_apellidos=$row['apellidos'];
			$tmp_negocio=$row['negocio'];
			$tmp_email=$row['email'];
			$tmp_telefono=$row['telefono'];
		} else header("Location: $seccion", true, 301);
		$res->Close();
		if (!$admin) {
			$res=$conn->query("SELECT * FROM envios WHERE email='$tmp_email' AND email<>'$email' AND fechaenvio>CURRENT_DATE() AND idu=$IDU");
			if ($res->num_rows>0) {
				$tmp_error='Ya se ha realizado un envío hoy a este cliente';
				$tmp_bloqueo=true;
			}
			$res->Close();
		}
		$res=$conn->query("SELECT * FROM plantillas WHERE id=$tmp_plantilla");
		if ($row = $res->fetch_assoc()) {
			$tmp_asunto=$row['asunto'];
			$tmp_contenido=$row['contenido'];
		} else {
			$tmp_error='Debe seleccionar una plantilla';
			$tmp_bloqueo=true;
		}
		$res->Close();
		$res=$conn->query("SELECT * FROM ficheros WHERE id=$tmp_fichero");
		if ($row = $res->fetch_assoc()) {
			$tmp_nombrefichero=$row['fichero'];
		} else {
			$tmp_error='Debe seleccionar un dosier';
			$tmp_bloqueo=true;
		}
		$res->Close();
		if (!$tmp_bloqueo) {
			date_default_timezone_set("Europe/Madrid");
			if (intval(date('G'))<6) $saludo='Buenas noches';
			elseif (intval(date('G'))<12) $saludo='Buenos días';
			elseif (intval(date('G'))<21) $saludo='Buenas tardes';
			else $saludo='Buenas noches';
			$tmp_contenido=str_replace('{saludo}',$saludo,$tmp_contenido);
			$tmp_contenido=str_replace('{nombrecliente}',$tmp_nombre,$tmp_contenido);
			$tmp_contenido=str_replace('{apellidoscliente}',$tmp_apellidos,$tmp_contenido);
			$tmp_contenido=str_replace('{nombrenegocio}',$tmp_negocio,$tmp_contenido);
			$tmp_contenido=str_replace('{nombreusuario}',$nombre,$tmp_contenido);
			$tmp_contenido=str_replace('{apellidosusuario}',$apellidos,$tmp_contenido);
			$tmp_contenido=str_replace('{agenciausuario}',$agencia,$tmp_contenido);
			$tmp_contenido=str_replace('{instagramusuario}',$instagram,$tmp_contenido);
			$tmp_contenido=str_replace('{emailusuario}',$email,$tmp_contenido);
			$tmp_contenido=str_replace('{telefonousuario}',$telefono,$tmp_contenido);
			
			$content = file_get_contents("ficheros/$tmp_fichero");
			$content = chunk_split(base64_encode($content));

		 // a random hash will be necessary to send mixed content
			$separator = md5(time());

			// carriage return type (RFC)
			$eol = "\r\n";

			// main header (multipart mandatory)
			$headers = "From: $agencia - $nombre $apellidos <$email>" . $eol;
			$headers .= "MIME-Version: 1.0" . $eol;
			$headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
			$headers .= "Content-Transfer-Encoding: 7bit" . $eol;
			$headers .= "This is a MIME encoded message." . $eol;

			// message
			$body = "--" . $separator . $eol;
			$body .= "Content-Type: text/plain; charset=\"utf-8\"" . $eol;
			$body .= "Content-Transfer-Encoding: 8bit" . $eol;
			$body .= $eol . $tmp_contenido . $eol . $eol;

			// attachment
			$body .= "--" . $separator . $eol;
			$body .= "Content-Type: application/octet-stream; name=\"" . $tmp_nombrefichero . "\"" . $eol;
			$body .= "Content-Transfer-Encoding: base64" . $eol;
			$body .= "Content-Disposition: attachment" . $eol;
			$body .= $eol . $content . $eol . $eol;
			$body .= "--" . $separator . "--";

			//SEND Mail
			if (mail($tmp_email, '=?UTF-8?Q?'.imap_8bit($tmp_asunto).'?=', $body, $headers)) {
				$tmp_error='Envio realizado';
				$conn->query("INSERT INTO envios (id, idu, email, plantilla, fichero, fechaenvio) VALUES (NULL, $IDU, '{$conn->real_escape_string($tmp_email)}', $tmp_plantilla, $tmp_fichero, CURRENT_TIMESTAMP())");
			} else {
				$tmp_error='Error en el envío';
			}
		}

	}	elseif ($_POST['accion']=='autoform' && $_POST['op']=="send") {
		$tmp_id=intval($_POST['id']);
		$res=$conn->query("SELECT * FROM clientes_potenciales WHERE id=$tmp_id AND idu=$IDU");
		if ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			$tmp_apellidos=$row['apellidos'];
			$tmp_negocio=$row['negocio'];
			$tmp_email=$row['email'];
			$tmp_telefono=$row['telefono'];
			$tmp_send=true;	
		} else header("Location: $seccion", true, 301);
		$res->Close();
	}	elseif ($_POST['accion']=='autoform' && $_POST['op']=="edit") {
		$tmp_id=intval($_POST['id']);
		$res=$conn->query("SELECT * FROM clientes_potenciales WHERE id=$tmp_id AND idu=$IDU");
		if ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			$tmp_apellidos=$row['apellidos'];
			$tmp_negocio=$row['negocio'];
			$tmp_email=$row['email'];
			$tmp_telefono=$row['telefono'];
			$tmp_edit=true;			
		}
		$res->Close();
	}
	if ($tmp_edit) {
		$tmp_titulo=($tmp_id>0) ? 'Editar cliente' : 'Nuevo cliente';
		$informacion="<div class='bloquelinea'><b>$tmp_titulo</b></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		$informacion.="<form method='post' id='formdatos'>".PHP_EOL;
		$informacion.="<input type='hidden' name='id' value='$tmp_id'>".PHP_EOL;
		$informacion.="<input type='hidden' name='accion' value='edit'>".PHP_EOL;
		if ($tmp_error!='') $informacion.="<span style='color:red'>$tmp_error</span><br><br>".PHP_EOL;
		$informacion.="Nombre:<br>".PHP_EOL;
		$informacion.="<input type='text' name='nombre' maxlength='50' value='$tmp_nombre'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Apellidos:<br>".PHP_EOL;
		$informacion.="<input type='text' name='apellidos' maxlength='100' value='$tmp_apellidos'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Nombre negocio:<br>".PHP_EOL;
		$informacion.="<input type='text' name='negocio' maxlength='100' value='$tmp_negocio'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Email:<br>".PHP_EOL;
		$informacion.="<input type='text' name='email' maxlength='200' value='$tmp_email'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Teléfono:<br>".PHP_EOL;
		$informacion.="<input type='text' name='telefono' maxlength='20' value='$tmp_telefono'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="<button>Guardar</button> <button onclick='window.location.href=\"$seccion\";return false;'>Cancelar</button><br><br>".PHP_EOL;
		$informacion.="</form>".PHP_EOL;
	}	elseif ($tmp_send) {
		$informacion="<div class='bloquelinea'><img src='img/pencil.png' class='clickable' dato-id='$tmp_id' dato-op='edit'>$tmp_negocio ($tmp_nombre $tmp_apellidos)</div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		$informacion.="<form method='post' id='formdatos'>".PHP_EOL;
		$informacion.="<input type='hidden' name='id' value='$tmp_id'>".PHP_EOL;
		$informacion.="<input type='hidden' name='accion' value='send'>".PHP_EOL;
		if ($tmp_error!='') $informacion.="<span style='color:red'>$tmp_error</span><br><br>".PHP_EOL;
		if (!$admin) {
			$res=$conn->query("SELECT * FROM envios WHERE email='$tmp_email' AND email<>'$email' AND fechaenvio>CURRENT_DATE() AND idu=$IDU");		
			if ($res->num_rows>0) $tmp_bloqueo=true; else $tmp_bloqueo=false;
			$res->Close();
		} else $tmp_bloqueo=false;
		if ($tmp_bloqueo) {
			$informacion.="Hoy ya se ha realizado un envío a este cliente y no se puede realizar otro<br>".PHP_EOL;
			$informacion.="<br>".PHP_EOL;
			$informacion.="<button onclick='window.location.href=\"$seccion\";return false;'>Volver</button><br><br>".PHP_EOL;
			$informacion.="</form>".PHP_EOL;
		} else {
			$tmp_regex=str_replace(',','|',$perfiles);
			$informacion.="Seleccione plantilla:<br>".PHP_EOL;
			$informacion.="<select name='plantilla'><br>".PHP_EOL;
			$res=$conn->query("SELECT * FROM plantillas WHERE CONCAT(',',perfiles,',') REGEXP ',($tmp_regex),' ORDER BY nombre");
			while ($row = $res->fetch_assoc())
				$informacion.="<option value='{$row['id']}'>{$row['nombre']}</option>".PHP_EOL;
			$res->Close();
			$informacion.="</select><br>".PHP_EOL;
			$informacion.="<br>".PHP_EOL;
			$informacion.="Seleccione dosier:<br>".PHP_EOL;
			$informacion.="<select name='fichero'><br>".PHP_EOL;
			$res=$conn->query("SELECT * FROM ficheros WHERE CONCAT(',',perfiles,',') REGEXP ',($tmp_regex),' AND fichero<>'' ORDER BY nombre");
			while ($row = $res->fetch_assoc())
				$informacion.="<option value='{$row['id']}'>{$row['nombre']}</option>".PHP_EOL;
			$res->Close();
			$informacion.="</select><br>".PHP_EOL;
			$informacion.="<br>".PHP_EOL;
			$informacion.="<button>Enviar</button> <button onclick='window.location.href=\"$seccion\";return false;'>Cancelar</button><br><br>".PHP_EOL;
			$informacion.="</form>".PHP_EOL;
		}
	}	elseif ($tmp_cart && $admin) {
		$informacion="<div class='bloquelinea'><b>$tmp_negocio ($tmp_nombre $tmp_apellidos)</b><img src='img/add.png' class='clickable' dato-id='$tmp_id' dato-op='addproduct'></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		$res=$conn->query("SELECT * FROM productos WHERE idc=$tmp_id AND fechacontratacion>0 AND fechacancelacion=0 ORDER BY producto");
		if ($res->num_rows>0) {
			$informacion.="<div class='bloquelinea'><b>Servicios contratados</b></div>".PHP_EOL;
			while ($row = $res->fetch_assoc()) {
				$informacion.="<div class='bloquelinea resaltar clickable' dato-id='{$row['id']}' dato-op='editproduct'><img src='img/eye.png'>{$row['producto']}</div>".PHP_EOL;
			}
			$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		}
		$res->Close();

		$res=$conn->query("SELECT * FROM productos WHERE idc=$tmp_id AND fechacontratacion=0 AND fechacancelacion=0 ORDER BY producto");
		if ($res->num_rows>0) {
			$informacion.="<div class='bloquelinea'><b>Servicios disponibles</b></div>".PHP_EOL;
			while ($row = $res->fetch_assoc()) {
				$informacion.="<div class='bloquelinea resaltar clickable' dato-id='{$row['id']}' dato-op='editproduct'><img src='img/pencil.png'>{$row['producto']}</div>".PHP_EOL;
			}
			$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		}
		$res->Close();

		$res=$conn->query("SELECT * FROM productos WHERE idc=$tmp_id AND fechacancelacion>0 ORDER BY producto");
		if ($res->num_rows>0) {
			$informacion.="<div class='bloquelinea'><b>Servicios anulados</b></div>".PHP_EOL;
			while ($row = $res->fetch_assoc()) {
				$informacion.="<div class='bloquelinea resaltar clickable' dato-id='{$row['id']}' dato-op='editproduct'><img src='img/eye.png'>{$row['producto']}</div>".PHP_EOL;
			}
			$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		}
		$res->Close();

		// $informacion.="<div class='bloquelinea'><b>Otras opciones</b></div>".PHP_EOL;
		// $informacion.="<div class='bloquelinea resaltar clickable' dato-id='$tmp_id' dato-op='sendpass'><img src='img/email_go.png'>Enviar nueva contraseña de acceso</div>".PHP_EOL;
		// $informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		
	}	else {
		$informacion="<div class='bloquelinea'><b>Leads</b><img src='img/add.png' class='clickable' dato-id='0' dato-op='add'></div>".PHP_EOL;
		$res=$conn->query("SELECT id,nombre,apellidos,negocio FROM clientes_potenciales WHERE idu=$IDU ORDER BY negocio, nombre, apellidos");
		while ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			$tmp_apellidos=$row['apellidos'];
			$tmp_negocio=$row['negocio'];
			$informacion.="<div class='bloquelinea resaltar clickable' dato-id='$tmp_id' dato-op='send'><img src='img/email_go.png'>$tmp_negocio ($tmp_nombre $tmp_apellidos)</div>".PHP_EOL;
		}
		$res->Close(); 
	}
} elseif ($seccion=="usuarios" && $admin)  {
	$tituloseccion='Usuarios';
	$tmp_edit=false;
	if ($_POST['accion']=='edit') {
		$tmp_id=intval($_POST['id']);
		$tmp_nombre=trim($_POST['nombre']);
		$tmp_apellidos=trim($_POST['apellidos']);
		$tmp_agencia=trim($_POST['agencia']);
		$tmp_instagram=trim($_POST['instagram']);
		$tmp_email=trim($_POST['email']);
		$tmp_telefono=str_replace(' ', '', $_POST['telefono']);
		$tmp_telefono=preg_replace('/^00/', '+', $tmp_telefono);		
		$tmp_telefono=preg_replace('/^\+34/', '', $tmp_telefono);		
		$tmp_clave=trim($_POST['clave']);
		$tmp_activo=intval($_POST['activo']);
		$tmp_perfiles='';
		for ($i=1;$i<=intval($_POST['numeroperfiles']);$i++)
			if (intval($_POST["perfil$i"])>0)
				$tmp_perfiles.=($tmp_perfiles=='') ? $_POST["perfil$i"] : ','.$_POST["perfil$i"];
		$res=$conn->query("SELECT * FROM (SELECT email,telefono FROM clientes UNION ALL SELECT email,telefono FROM usuarios WHERE id<>$tmp_id) AS CT1 WHERE (email='{$conn->real_escape_string($tmp_email)}' OR telefono='{$conn->real_escape_string($tmp_telefono)}')");
		$tmp_guardar=($res->num_rows>0) ? false : true;
		$res->Close();
		if ($tmp_guardar && $tmp_nombre=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar el nombre';
		} elseif ($tmp_guardar && $tmp_apellidos=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar los apellidos';
		} elseif ($tmp_guardar && $tmp_agencia=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar la agencia';
		} elseif ($tmp_guardar && !preg_match('/^(admin|[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63})$/i', $tmp_email)) {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar un email correcto';
		} elseif ($tmp_guardar && !preg_match('/^(\+\d{9,}|[6-9]\d{8})$/i', $tmp_telefono)) {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar un teléfono correcto';
		} elseif ($tmp_guardar && strlen($tmp_clave)<6) {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='La clave debe tener 6 o más caracteres';
		} elseif ($tmp_guardar && $tmp_perfiles=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe seleccionar algún perfil';
		} elseif ($tmp_guardar && $tmp_id>0) {
			$conn->query("UPDATE usuarios SET nombre='{$conn->real_escape_string($tmp_nombre)}', apellidos='{$conn->real_escape_string($tmp_apellidos)}', agencia='{$conn->real_escape_string($tmp_agencia)}', instagram='{$conn->real_escape_string($tmp_instagram)}', email='{$conn->real_escape_string($tmp_email)}', telefono='{$conn->real_escape_string($tmp_telefono)}', clave='{$conn->real_escape_string($tmp_clave)}', perfiles='{$conn->real_escape_string($tmp_perfiles)}', activo=$tmp_activo WHERE id=$tmp_id");
			header("Location: $seccion", true, 301);
		}	elseif ($tmp_guardar) {
			$conn->query("INSERT INTO usuarios (id, nombre, apellidos, agencia, instagram, email, telefono, clave, perfiles, activo) VALUES (NULL, '{$conn->real_escape_string($tmp_nombre)}', '{$conn->real_escape_string($tmp_apellidos)}', '{$conn->real_escape_string($tmp_agencia)}', '{$conn->real_escape_string($tmp_instagram)}', '{$conn->real_escape_string($tmp_email)}', '{$conn->real_escape_string($tmp_telefono)}', '{$conn->real_escape_string($tmp_clave)}', '{$conn->real_escape_string($tmp_perfiles)}', $tmp_activo)");
			header("Location: $seccion", true, 301);
		} else {
			$tmp_edit=true;
			$tmp_error='Ya existe algún usuario con el mismo email o teléfono';
		}
	}	elseif ($_POST['accion']=='autoform' && $_POST['op']=="edit") {
		$tmp_id=intval($_POST['id']);
		$res=$conn->query("SELECT * FROM usuarios WHERE id=$tmp_id");
		if ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			$tmp_apellidos=$row['apellidos'];
			$tmp_agencia=$row['agencia'];
			$tmp_instagram=$row['instagram'];
			$tmp_email=$row['email'];
			$tmp_telefono=$row['telefono'];
			$tmp_clave=$row['clave'];
			$tmp_activo=$row['activo'];
			$tmp_perfiles=$row['perfiles'];
			$tmp_edit=true;			
		}
		$res->Close();
	}	elseif ($_POST['accion']=='autoform' && $_POST['op']=="add") {
		$tmp_id=0;
		$tmp_nombre='';
		$tmp_activo=1;
		$tmp_edit=true;			
	}
	if ($tmp_edit) {
		$tmp_titulo=($tmp_id>0) ? 'Editar usuario' : 'Nuevo usuario';
		$informacion="<div class='bloquelinea'><b>$tmp_titulo</b></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		$informacion.="<form method='post' id='formdatos'>".PHP_EOL;
		$informacion.="<input type='hidden' name='id' value='$tmp_id'>".PHP_EOL;
		$informacion.="<input type='hidden' name='accion' value='edit'>".PHP_EOL;
		if ($tmp_error!='') $informacion.="<span style='color:red'>$tmp_error</span><br><br>".PHP_EOL;
		$tmp_checked=($tmp_activo) ? '' : ' checked';
		if ($IDU!=$tmp_id) $informacion.="<label><input type='radio' name='activo' value='0'$tmp_checked>Inactivo</label> ".PHP_EOL;
		$tmp_checked=($tmp_activo) ? ' checked' : '';
		$informacion.="<label><input type='radio' name='activo' value='1'$tmp_checked>Activo</label><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Nombre:<br>".PHP_EOL;
		$informacion.="<input type='text' name='nombre' maxlength='50' value='$tmp_nombre'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Apellidos:<br>".PHP_EOL;
		$informacion.="<input type='text' name='apellidos' maxlength='100' value='$tmp_apellidos'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Agencia:<br>".PHP_EOL;
		$informacion.="<input type='text' name='agencia' maxlength='100' value='$tmp_agencia'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Instagram:<br>".PHP_EOL;
		$informacion.="<input type='text' name='instagram' maxlength='100' value='$tmp_instagram'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Email:<br>".PHP_EOL;
		$informacion.="<input type='text' name='email' maxlength='200' value='$tmp_email'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Teléfono:<br>".PHP_EOL;
		$informacion.="<input type='text' name='telefono' maxlength='20' value='$tmp_telefono'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Clave de acceso:<br>".PHP_EOL;
		$informacion.="<input type='text' name='clave' maxlength='20' value='$tmp_clave'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Perfiles:<br>".PHP_EOL;
		$tmp_numeroperfiles=0;
		$res=$conn->query("SELECT * FROM perfiles ORDER BY nombre");
		while ($row = $res->fetch_assoc()) {
			$tmp_numeroperfiles++;
			$tmp_pid=$row['id'];
			$tmp_pnombre=$row['nombre'];
			$tmp_checked=(strpos(",$tmp_perfiles,",",$tmp_pid,")===false) ? '' : ' checked';
			$informacion.="<div class='bloqueaislado'><label><input type='checkbox' name='perfil$tmp_numeroperfiles' value='$tmp_pid'$tmp_checked>$tmp_pnombre</label></div>".PHP_EOL;
		}
		$res->Close(); 
		$informacion.="<br><input type='hidden' name='numeroperfiles' value='$tmp_numeroperfiles'><br>".PHP_EOL;
		$informacion.="<button>Guardar</button> <button onclick='window.location.href=\"$seccion\";return false;'>Cancelar</button><br><br>".PHP_EOL;
		$informacion.="</form>".PHP_EOL;
		if ($tmp_id>0) {
			$res=$conn->query("SELECT * FROM envios WHERE idu=$tmp_id AND email<>'{$conn->real_escape_string($tmp_email)}' AND fechaenvio>DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL 2 DAY) ORDER BY fechaenvio");
			if ($res->num_rows>0) $informacion.="<div class='bloquelinea'><b>Últimos envíos</b></div>".PHP_EOL;
			while ($row = $res->fetch_assoc())
				$informacion.="<div class='bloquelinea'>{$row['email']} ".date('j-n-Y G:i:s',strtotime($row['fechaenvio']))."</div>".PHP_EOL;
			$res->Close(); 
			$informacion.="<br>".PHP_EOL;
		}
	}	else {
		$informacion="<div class='bloquelinea'><b>Usuarios</b><img src='img/add.png' class='clickable' dato-id='0' dato-op='add'></div>".PHP_EOL;
		$res=$conn->query("SELECT * FROM usuarios ORDER BY nombre, apellidos");
		while ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			$tmp_apellidos=$row['apellidos'];
			$tmp_email=$row['email'];
			if ($tmp_email!='soporte@anescu.es')
				$informacion.="<div class='bloquelinea resaltar clickable' dato-id='$tmp_id' dato-op='edit'><img src='img/pencil.png'>$tmp_nombre $tmp_apellidos</div>".PHP_EOL;
		}
		$res->Close(); 
	}
} elseif ($seccion=="perfiles" && $admin)  {
	$tituloseccion='Perfiles';
	$tmp_edit=false;
	if ($_POST['accion']=='edit') {
		$tmp_id=intval($_POST['id']);
		$tmp_nombre=trim($_POST['nombre']);
		$res=$conn->query("SELECT * FROM perfiles WHERE nombre='{$conn->real_escape_string($tmp_nombre)}' AND id<>$tmp_id");
		$tmp_guardar=($res->num_rows>0) ? false : true;
		$res->Close();
		if ($tmp_guardar && $tmp_nombre=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar un nombre';
		} elseif ($tmp_guardar && $tmp_id>0) {
			$conn->query("UPDATE perfiles SET nombre='{$conn->real_escape_string($tmp_nombre)}' WHERE id=$tmp_id");
			header("Location: $seccion", true, 301);
		}	elseif ($tmp_guardar) {
			$conn->query("INSERT INTO perfiles (id, nombre) VALUES (NULL, '{$conn->real_escape_string($tmp_nombre)}')");
			header("Location: $seccion", true, 301);
		} else {
			$tmp_edit=true;
			$tmp_error='El perfil ya existe';
		}
	}	elseif ($_POST['accion']=='autoform' && $_POST['op']=="edit") {
		$tmp_id=intval($_POST['id']);
		$res=$conn->query("SELECT * FROM perfiles WHERE id=$tmp_id");
		if ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			$tmp_edit=true;			
		}
		$res->Close();
	}	elseif ($_POST['accion']=='autoform' && $_POST['op']=="add") {
		$tmp_id=0;
		$tmp_nombre='';
		$tmp_edit=true;			
	}
	if ($tmp_edit) {
		$tmp_titulo=($tmp_id>0) ? 'Editar perfil' : 'Nuevo perfil';
		$informacion="<div class='bloquelinea'><b>$tmp_titulo</b></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		$informacion.="<form method='post' id='formdatos'>".PHP_EOL;
		$informacion.="<input type='hidden' name='accion' value='edit'>".PHP_EOL;
		$informacion.="<input type='hidden' name='id' value='$tmp_id'>".PHP_EOL;
		if ($tmp_error!='') $informacion.="<span style='color:red'>$tmp_error</span><br><br>".PHP_EOL;
		$informacion.="Nombre del perfil:<br>".PHP_EOL;
		$informacion.="<input type='text' name='nombre' maxlength='20' value='$tmp_nombre'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="<button>Guardar</button> <button onclick='window.location.href=\"$seccion\";return false;'>Cancelar</button>".PHP_EOL;
		$informacion.="</form>".PHP_EOL;
	}	else {
		$informacion="<div class='bloquelinea'><b>Perfiles</b><img src='img/add.png' class='clickable' dato-id='0' dato-op='add'></div>".PHP_EOL;
		$res=$conn->query("SELECT * FROM perfiles ORDER BY nombre");
		while ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			$informacion.="<div class='bloquelinea resaltar clickable' dato-id='$tmp_id' dato-op='edit'>$tmp_nombre<img src='img/pencil.png'></div>".PHP_EOL;
		}
		$res->Close(); 
	}
} elseif ($seccion=="plantillas" && $admin)  {
	$tituloseccion='Plantillas';
	$tmp_edit=false;
	if ($_POST['accion']=='edit') {
		$tmp_id=intval($_POST['id']);
		$tmp_nombre=trim($_POST['nombre']);
		$tmp_asunto=trim($_POST['asunto']);
		$tmp_contenido=trim($_POST['contenido']);
		$tmp_perfiles='';
		for ($i=1;$i<=intval($_POST['numeroperfiles']);$i++)
			if (intval($_POST["perfil$i"])>0)
				$tmp_perfiles.=($tmp_perfiles=='') ? $_POST["perfil$i"] : ','.$_POST["perfil$i"];
		$res=$conn->query("SELECT * FROM plantillas WHERE nombre='{$conn->real_escape_string($tmp_nombre)}' AND id<>$tmp_id");
		$tmp_guardar=($res->num_rows>0) ? false : true;
		$res->Close();
		if ($tmp_guardar && $tmp_nombre=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar un nombre';
		} elseif ($tmp_guardar && $tmp_asunto=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar un asunto';
		} elseif ($tmp_guardar && $tmp_contenido=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar un contenido';
		} elseif ($tmp_guardar && $tmp_id>0) {
			$conn->query("UPDATE plantillas SET nombre='{$conn->real_escape_string($tmp_nombre)}', asunto='{$conn->real_escape_string($tmp_asunto)}', contenido='{$conn->real_escape_string($tmp_contenido)}', perfiles='{$conn->real_escape_string($tmp_perfiles)}' WHERE id=$tmp_id");
			header("Location: $seccion", true, 301);
		}	elseif ($tmp_guardar) {
			$conn->query("INSERT INTO plantillas (id, nombre, asunto, contenido, perfiles) VALUES (NULL, '{$conn->real_escape_string($tmp_nombre)}', '{$conn->real_escape_string($tmp_asunto)}', '{$conn->real_escape_string($tmp_contenido)}', '{$conn->real_escape_string($tmp_perfiles)}')");
			header("Location: $seccion", true, 301);
		} else {
			$tmp_edit=true;
			$tmp_error='La plantilla ya existe';
		}
	}	elseif ($_POST['accion']=='autoform' && $_POST['op']=="edit") {
		$tmp_id=intval($_POST['id']);
		$res=$conn->query("SELECT * FROM plantillas WHERE id=$tmp_id");
		if ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			$tmp_asunto=$row['asunto'];
			$tmp_contenido=$row['contenido'];
			$tmp_perfiles=$row['perfiles'];
			$tmp_edit=true;			
		}
		$res->Close();
	}	elseif ($_POST['accion']=='autoform' && $_POST['op']=="add") {
		$tmp_id=0;
		$tmp_nombre='';
		$tmp_edit=true;			
	}
	if ($tmp_edit) {
		$tmp_titulo=($tmp_id>0) ? 'Editar plantilla' : 'Nueva plantilla';
		$informacion="<div class='bloquelinea'><b>$tmp_titulo</b></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		$informacion.="<form method='post' id='formdatos'>".PHP_EOL;
		$informacion.="<input type='hidden' name='id' value='$tmp_id'>".PHP_EOL;
		$informacion.="<input type='hidden' name='accion' value='edit'>".PHP_EOL;
		if ($tmp_error!='') $informacion.="<span style='color:red'>$tmp_error</span><br><br>".PHP_EOL;
		$informacion.="Nombre de la plantilla:<br>".PHP_EOL;
		$informacion.="<input type='text' name='nombre' maxlength='20' value='$tmp_nombre'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Asunto del email:<br>".PHP_EOL;
		$informacion.="<input type='text' name='asunto' maxlength='100' value='$tmp_asunto'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Contenido del email:<br>".PHP_EOL;
		$informacion.="<textarea name='contenido'>$tmp_contenido</textarea><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Perfiles:<br>".PHP_EOL;
		$tmp_numeroperfiles=0;
		$res=$conn->query("SELECT * FROM perfiles ORDER BY nombre");
		while ($row = $res->fetch_assoc()) {
			$tmp_numeroperfiles++;
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			$tmp_checked=(strpos(",$tmp_perfiles,",",$tmp_id,")===false) ? '' : ' checked';
			$informacion.="<div class='bloqueaislado'><label><input type='checkbox' name='perfil$tmp_numeroperfiles' value='$tmp_id'$tmp_checked>$tmp_nombre</label></div>".PHP_EOL;
		}
		$res->Close(); 
		$informacion.="<br><input type='hidden' name='numeroperfiles' value='$tmp_numeroperfiles'><br>".PHP_EOL;
		$informacion.="<button>Guardar</button> <button onclick='window.location.href=\"$seccion\";return false;'>Cancelar</button><br><br>".PHP_EOL;
		$informacion.="</form>".PHP_EOL;
	}	else {
		$informacion="<div class='bloquelinea'><b>Plantillas de correo</b><img src='img/add.png' class='clickable' dato-id='0' dato-op='add'></div>".PHP_EOL;
		$res=$conn->query("SELECT * FROM plantillas ORDER BY nombre");
		while ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			$informacion.="<div class='bloquelinea resaltar clickable' dato-id='$tmp_id' dato-op='edit'>$tmp_nombre<img src='img/pencil.png'></div>".PHP_EOL;
		}
		$res->Close(); 
	}
} elseif ($seccion=="dossieres" && $admin)  {
	$tituloseccion='Dosieres';
	$tmp_edit=false;
	if ($_POST['accion']=='edit') {
		$tmp_id=intval($_POST['id']);
		$tmp_nombre=trim($_POST['nombre']);
		if (is_uploaded_file($_FILES['fichero']['tmp_name']))
			$tmp_fichero=$_FILES['fichero']['name'];
		else
			$tmp_fichero='';
		$tmp_perfiles='';
		for ($i=1;$i<=intval($_POST['numeroperfiles']);$i++)
			if (intval($_POST["perfil$i"])>0)
				$tmp_perfiles.=($tmp_perfiles=='') ? $_POST["perfil$i"] : ','.$_POST["perfil$i"];
		$res=$conn->query("SELECT * FROM ficheros WHERE nombre='{$conn->real_escape_string($tmp_nombre)}' AND id<>$tmp_id");
		$tmp_guardar=($res->num_rows>0) ? false : true;
		$res->Close();
		if ($tmp_guardar && $tmp_nombre=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar un nombre';
		} elseif ($tmp_guardar && $tmp_id>0) {
			$conn->query("UPDATE ficheros SET nombre='{$conn->real_escape_string($tmp_nombre)}', perfiles='{$conn->real_escape_string($tmp_perfiles)}' WHERE id=$tmp_id");
			if ($tmp_fichero!='') {
				file_put_contents("ficheros/$tmp_id", file_get_contents($_FILES['fichero']['tmp_name']));				
				// move_uploaded_file($_FILES['fichero']['tmp_name'], "ficheros/$tmp_id");
				// chmod("ficheros/$tmp_id",0777);
				$conn->query("UPDATE ficheros SET fichero='{$conn->real_escape_string($tmp_fichero)}' WHERE id=$tmp_id");
			}
			header("Location: $seccion", true, 301);
		}	elseif ($tmp_guardar) {
			$conn->query("INSERT INTO ficheros (id, nombre, perfiles) VALUES (NULL, '{$conn->real_escape_string($tmp_nombre)}', '{$conn->real_escape_string($tmp_perfiles)}')");
			if ($tmp_fichero!='') {
				$tmp_id = $conn->insert_id;
				file_put_contents("ficheros/$tmp_id", file_get_contents($_FILES['fichero']['tmp_name']));				
				// move_uploaded_file($_FILES['fichero']['tmp_name'], "ficheros/$tmp_id");
				// chmod("ficheros/$tmp_id",0777);
				$conn->query("UPDATE ficheros SET fichero='{$conn->real_escape_string($tmp_fichero)}' WHERE id=$tmp_id");
			}
			header("Location: $seccion", true, 301);
		} else {
			$tmp_edit=true;
			$tmp_error='El dosier ya existe';
		}
	}	elseif ($_POST['accion']=='autoform' && $_POST['op']=="edit") {
		$tmp_id=intval($_POST['id']);
		$res=$conn->query("SELECT * FROM ficheros WHERE id=$tmp_id");
		if ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			$tmp_fichero=$row['fichero'];
			$tmp_perfiles=$row['perfiles'];
			$tmp_edit=true;			
		}
		$res->Close();
	}	elseif ($_POST['accion']=='autoform' && $_POST['op']=="add") {
		$tmp_id=0;
		$tmp_nombre='';
		$tmp_fichero='';
		$tmp_edit=true;			
	}
	if ($tmp_edit) {
		$tmp_titulo=($tmp_id>0) ? 'Editar dosier' : 'Nuevo dosier';
		$informacion="<div class='bloquelinea'><b>$tmp_titulo</b></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		$informacion.="<form method='post' id='formdatos' enctype='multipart/form-data'>".PHP_EOL;
		$informacion.="<input type='hidden' name='id' value='$tmp_id'>".PHP_EOL;
		$informacion.="<input type='hidden' name='accion' value='edit'>".PHP_EOL;
		if ($tmp_error!='') $informacion.="<span style='color:red'>$tmp_error</span><br><br>".PHP_EOL;
		$informacion.="Nombre del dosier:<br>".PHP_EOL;
		$informacion.="<input type='text' name='nombre' maxlength='100' value='$tmp_nombre'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		if ($tmp_fichero!='') $informacion.="<div class='bloqueaislado'>Fichero actual: $tmp_fichero</div><br>".PHP_EOL;
		$informacion.="Cargar fichero:<br>".PHP_EOL;
		$informacion.="<input type='file' name='fichero'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Perfiles:<br>".PHP_EOL;
		$tmp_numeroperfiles=0;
		$res=$conn->query("SELECT * FROM perfiles ORDER BY nombre");
		while ($row = $res->fetch_assoc()) {
			$tmp_numeroperfiles++;
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			$tmp_checked=(strpos(",$tmp_perfiles,",",$tmp_id,")===false) ? '' : ' checked';
			$informacion.="<div class='bloqueaislado'><label><input type='checkbox' name='perfil$tmp_numeroperfiles' value='$tmp_id'$tmp_checked>$tmp_nombre</label></div>".PHP_EOL;
		}
		$res->Close(); 
		$informacion.="<br><input type='hidden' name='numeroperfiles' value='$tmp_numeroperfiles'><br>".PHP_EOL;
		$informacion.="<button>Guardar</button> <button onclick='window.location.href=\"$seccion\";return false;'>Cancelar</button><br><br>".PHP_EOL;
		$informacion.="</form>".PHP_EOL;
	}	else {
		$informacion="<div class='bloquelinea'><b>Dosieres</b><img src='img/add.png' class='clickable' dato-id='0' dato-op='add'></div>".PHP_EOL;
		$res=$conn->query("SELECT * FROM ficheros ORDER BY nombre");
		while ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			$informacion.="<div class='bloquelinea resaltar clickable' dato-id='$tmp_id' dato-op='edit'>$tmp_nombre<img src='img/pencil.png'></div>".PHP_EOL;
		}
		$res->Close(); 
	}
} elseif ($seccion!="")  {
	header("Location: /", true, 302);
}

	
if ($contenido=='') {
	if ($usuario || $cliente) {
$contenido=<<<END
<!DOCTYPE html>
<html lang="es">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>CLYB App - $tituloseccion</title>
<meta name="title" content="CLYB App - $tituloseccion">
<meta name="description" content="La App de CLYB Company">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta property="og:title" content="CLYB App" />
<meta property="og:description" content="La App de CLYB Company" />
<meta property="og:locale" content="es_ES" />
<meta property="og:site_name" content="CLYB App" />
<meta property="og:type" content="website" />
<meta property="og:url" content="https://app.clyb.es/" />
<meta property="og:image" content="https://app.clyb.es/icon-normal.png" />
<link rel="shortcut icon" type="image/x-icon" href="/icon-normal.png">
<link rel="alternate" hreflang="es" href="https://app.clyb.es/" />
<link rel="alternate" hreflang="es-ES" href="https://app.clyb.es/" />
<link rel="alternate" hreflang="x-default" href="https://app.clyb.es/" />
<link href="https://app.clyb.es/" rel="canonical" />
<style>
<!--
* {
box-sizing: border-box;
-ms-overflow-style: none;
scrollbar-width: none;
}
*::-webkit-scrollbar {display: none;}

body
	{
	min-width: 320px;
	width: 100%;
	font-family: 'Arial';
	font-size: 18px;
	font-weight: normal;
	font-style: normal;
	text-align: center;
	text-decoration: none;
	letter-spacing: 0;
	color: #000000;
	overflow-x: hidden;
	margin: 0 auto;
	border: 0;
	padding: 0;
	line-height: normal;
	background: #ffffff;
	-webkit-text-size-adjust: 100%;
	}

#barrasuperior {
	position: fixed;
	top: 0;
	left: 0;
	min-width: 320px;
	width: 100vw;
	height: 50px;
	text-align: center;
	line-height: 50px;
	overflow: hidden;
	border: 0;
	padding: 0;
	margin: 0;
	background: #2e271f;
	color: #ffffff;
	z-index: 1500;
	}

#botonmenu
	{
	position: fixed;
	top: 5px;
	left: 5px;
	width: 30px;
	height: 40px;
	background: #2e271f url('img/menu.png') center no-repeat;
  background-size: 20px;
	z-index: 1600;
	}

#botonlogout
	{
	position: fixed;
	top: 5px;
	right: 5px;
	width: 30px;
	height: 40px;
	background: #2e271f url('img/logout.png') center no-repeat;
  background-size: 20px;
	z-index: 1600;
	}

.relleno {
width: 100vw;
height: 50px;
border: 0;
margin: 0;
padding: 0;
overflow: hidden;
}

#menulateral
  {
	position: fixed;
	top: 50px;
	left: 0px;
	font-size: 14px;
	font-weight: normal;
	font-style: normal;
	text-decoration: none;
	text-align: left;
	letter-spacing: -.5px;
	line-height: 25px;
	color: #ffffff;
	border: 0;
	padding: 0px 10px;
	margin: 0;
	background: #2e271f;
	z-index: 1500;
	visibility: hidden;
	width: 100px;
	max-width: 240px;
	max-height: calc(90vh - 50px);
	overflow: auto;
	}
#menulateral img {width: 18px; border: 0; margin: 0; padding: 0; vertical-align: text-bottom;}
#menulateral a {opacity: .8; color: #ffffff; text-decoration: none;}
#menulateral a:hover {opacity: 1;}

#logohome {
	width: 90vw;
	height: 30vh;
	background: url('img/logo-clyb.jpg') center center no-repeat;
	background-size: contain;
	border: 0;
	padding: 0;
	margin: 5vh 5vw 5vh 5vw;	
}

#bienvenida {
	width: 90vw;
	height: calc(55vh - 50px);
	text-align: center;
	vertical-align: center;
	border: 0;
	padding: 0;
	margin: 2vh 5vw;
}

.bloquealto {
	display: inline-block;
	width: 100vw;
	min-width: 320px;
	text-align: left;
	margin: 0;
	border: 0;
	padding: 5px;
}
.bloquelinea {
	display: inline-block;
	width: 100vw;
	min-width: 320px;
	height: 28px;
	text-align: left;
	overflow: hidden;
	margin: 0;
	border: 0;
	padding: 5px;
}
.bloquelinea img {
	float: right;
	margin: 0 5px;
}

.bloqueaislado {
	display: inline-block;
	width: 80vw;
	max-width: 320px;
	height: 25px;
	overflow: hidden;
	text-align: left;
	margin: 0;
	border: 0;
	padding: 1px 5px;
}

.resaltar:hover {
	background: #cadfff;
}

.clickable {
	cursor: pointer;
}

#formdatos {
	width: 90vw;
	text-align: center;
	vertical-align: center;
	border: 0;
	padding: 0;
	margin: 0;
}
#formdatos input[type="text"] {
	height: 20px;
	width: 80vw;
	max-width: 400px;
	text-align: center;
	margin: 0;
	border: 1px solid black;
	padding: 0;
}
#formdatos input[type="file"] {
	height: 20px;
	width: 80vw;
	max-width: 400px;
	text-align: center;
	margin: 0;
	border: 0;
	padding: 0;
}
#formdatos textarea {
	height: 100px;
	width: 80vw;
	max-width: 600px;
	text-align: left;
	margin: 0;
	border: 1px solid black;
	padding: 0;
}
#formdatos select {
	height: 20px;
	width: 80vw;
	max-width: 400px;
	text-align: left;
	margin: 0;
	border: default;
	padding: 0;
}
#formdatos button {
	height: 20px;
	margin: 0;
	border: default;
	padding: default;
}

a {text-decoration: none;}

.w50 {width: 50px !important;}
.w100 {width: 100px !important;}

-->
</style>
</head>

<body>
<div id="barrasuperior">
	<div id="botonmenu"></div>
	<div id="botonlogout" onclick="window.location.href='logout';"></div>
	$nombre
</div>
<div id="menulateral">$menulateral</div>
<div class="relleno"><form method="post" id="autoform"><input type="hidden" name="accion" value="autoform"><input type="hidden" id="datoid" name="id"><input type="hidden" id="datoid2" name="id2"><input type="hidden" id="datoop" name="op"></form></div>

$informacion

<script type="text/javascript">
	function menu() {
		if (document.getElementById("menulateral").style.visibility=='hidden')
			{
			document.getElementById("menulateral").style.visibility='visible';
			document.getElementById("botonmenu").style.backgroundImage='url(img/cerrar.png)';
			}
		else
			{
			document.getElementById("menulateral").style.visibility='hidden';
			document.getElementById("botonmenu").style.backgroundImage='url(img/menu.png)';
			}
	}
	function sendpost() {
		document.getElementById("datoid").value = this.getAttribute("dato-id");
		document.getElementById("datoid2").value = this.getAttribute("dato-id2");
		document.getElementById("datoop").value = this.getAttribute("dato-op");
		document.getElementById("autoform").submit();
	}
	document.getElementById("menulateral").style.visibility='hidden';
	document.getElementById("botonmenu").style.backgroundImage='url(img/menu.png)';
	document.getElementById("botonmenu").addEventListener("click", menu);
	for (var i = 0; i < document.getElementsByClassName("clickable").length; i++)
    document.getElementsByClassName("clickable")[i].addEventListener('click', sendpost);
</script>
</body>
</html>
END;
	} else {
$contenido=<<<END
<!DOCTYPE html>
<html>
    <head>
        <title>Clyb App</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
				<link rel="shortcut icon" type="image/x-icon" href="/icon-normal.png">
        <link rel="stylesheet" href="estilos.css?1">
    </head>
    <body>
        <div class="animacion">            
            <img class="texto" src="/img/clyb.png">
            <div id="barra" class="barra"></div>
            <img id="logo" class="logo" src="/img/icon.png">
        </div>
        <div id="botones">
            <a href="/registro"><button class="boton azul">REGISTRARME</button></a><br>
            <a href="/acceso"><button class="boton blanco">INICIAR SESIÓN</button></a><br>
        </div>   
		<script>
            setTimeout(function () {document.getElementById('logo').classList.add('reducir');},1000);
            setTimeout(function () {document.getElementById('logo').classList.add('derecha');},2000);
            setTimeout(function () {document.getElementById('logo').classList.add('izquierda');},3000);
            setTimeout(function () {document.getElementById('barra').classList.add('eliminar');},3100);
            setTimeout(function () {document.getElementById('logo').classList.add('centrar');},4000);
            setTimeout(function () {document.getElementById('botones').style.opacity='1';},5000);
		</script>
    </body>
</html>
END;
	}
}

header("Content-Language: es-ES");
header("Content-Type: text/html; charset=UTF-8");
if ($contenido_completo=='') echo $contenido; else echo $contenido_completo;
$conn->close();
?>