<?php
// 12/3/21 Version inicial completa

if ($IDU>0 || $IDC>0) {
	header('Location: /', true, 302);
} else {
	$aviso='';
	$campos='';
	$mensaje='';
	$idcliente=0;
	$passid=0;
	$actualizado=false;
	if ($_POST['fin']=='OK') header('Location: /acceso', true, 302);
	if ($_POST['usr']!='' && $_POST['movil']!='')	{
		$res=$conn->query("SELECT * FROM clientes WHERE LCASE(email)=LCASE('{$conn->real_escape_string($_POST['usr'])}') AND telefono='{$conn->real_escape_string($_POST['movil'])}' AND activo;");
		if ($res->num_rows > 0) {
			$row = $res->fetch_assoc();
			$idcliente=$row['id'];
			$nombrecompleto=$row['nombre'].' '.$row['apellidos'].' ('.$row['telefono'].')';
		} else $aviso=popupaviso("El correo electrónico y teléfono introducidos no corresponden a ningún cliente");
		$res->Close(); 
	}
	if (is_numeric($_POST['passid'])) {
		$passid=intval($_POST['passid']);
	} elseif ($idcliente>0 && preg_match('/^(?=.*?[a-zA-Z])(?=.*?[^a-zA-Z]).{6,16}$/', $_POST['pass']) && $_POST['pass']==$_POST['pass2']) {
		$codigo=substr('000000'.strval(random_int(1 ,999999)),-6);
		$conn->query("INSERT INTO codigos (idcliente, pass, movil, codigo, ip) VALUES ($idcliente, '{$conn->real_escape_string($_POST['pass'])}', '{$conn->real_escape_string($_POST['movil'])}', '$codigo', '{$conn->real_escape_string($_SERVER['REMOTE_ADDR'])}');");
		$passid = $conn->insert_id;
		if ($passid>0) {
		if ($resultado=file_get_contents("https://apisms.anescu.net/?usr=giga1929&pass=XXXXXX&from=CLYB&to={$_POST['movil']}&msg=Tu%20codigo%20de%20verificacion%20para%20cambiar%20la%20contraseña%20es%20el%20$codigo.%20Si%20no%20lo%20has%20solicitado%20o%20deseas%20ayuda%20puedes%20llamarnos%20al%20911239057")) {
				if (substr($resultado,0,7)!='Enviado')
					$aviso=popupaviso("Hubo un problema al procesar el cambio de contraseña y deberás intentarlo pasados unos minutos");
			} else {
					$aviso=popupaviso("Hubo un problema al procesar el cambio de contraseña y deberás intentarlo pasados unos minutos");
			}
		} else {
			$aviso=popupaviso("Ha ocurrido un error inesperado y deberás intentarlo más tarde");
		}
	} elseif ($idcliente>0 && $_POST['pass']!='') {
		$aviso=popupaviso("Debes insertar la misma contraseña, con letras y números o símbolos");
	}	
	if ($_POST['codigo']!='' && $passid>0 && $idcliente>0) {
		$res=$conn->query("SELECT *, IF(CURRENT_TIMESTAMP<DATE_ADD(fecha, INTERVAL 1 HOUR),false,true) AS caducado FROM codigos WHERE id=$passid AND idcliente=$idcliente AND movil='{$conn->real_escape_string($_POST['movil'])}';");
		if ($row = $res->fetch_assoc()) {
			$conn->query("UPDATE codigos SET intentos=intentos+1 WHERE id=$passid AND idcliente=$idcliente AND movil='{$conn->real_escape_string($_POST['movil'])}';");
			if ($row['intentos']>=5 || $row['caducado']) {
				$aviso=popupaviso("El código de verificación ha caducado y no puede ser validado");
			} elseif ($_POST['codigo']==$row['codigo']) {
				$conn->query("UPDATE clientes SET clave='{$conn->real_escape_string($row['pass'])}' WHERE id=$idcliente;");
				$conn->query("INSERT INTO logs (valor1, valor2, texto1, texto2, ip) VALUES (2, $idcliente, 'Cambio de contraseña', '{$conn->real_escape_string($nombrecompleto)}', '{$conn->real_escape_string($_SERVER['REMOTE_ADDR'])}');");
				$actualizado=true;
			} else $aviso=popupaviso("El código de verificación es incorrecto. Dispones de ".(4-$row['intentos'])." intentos más");
		} else $aviso=popupaviso("El código de verificación no es correcto");
	}

	if ($actualizado) {
		$mensaje='Tu contraseña ya ha sido cambiada. Ya puedes acceder a tu cuenta con la nueva contraseña';
		$campos.="<input type=\"hidden\" name=\"fin\" value=\"OK\">".PHP_EOL;		
	} elseif ($passid>0) {
		$mensaje='Introduce ahora el código de verificación de 6 dígitos que has recibido por SMS';
		$campos.="<input type=\"hidden\" name=\"usr\" value=\"{$_POST['usr']}\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"movil\" value=\"{$_POST['movil']}\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"passid\" value=\"$passid\">".PHP_EOL;
		$campos.="<input type=\"text\" name=\"codigo\" value=\"{$_POST['codigo']}\" placeholder=\"Código de verificación\" maxlength=\"6\" required pattern=\"^[0-9]{6}$\" oninvalid=\"this.setCustomValidity('Debes introducir el código de verificación que te hemos enviado por SMS')\" onchange=\"this.value=this.value.trim();this.setCustomValidity('');\"><br><br>".PHP_EOL;
	} elseif ($idcliente>0) {
		$mensaje='Introduce ahora la nueva contraseña que deseas para tu cuenta';
		$campos.="<input type=\"hidden\" name=\"usr\" value=\"{$_POST['usr']}\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"movil\" value=\"{$_POST['movil']}\">".PHP_EOL;
		$campos.="<input type=\"password\" id=\"pass\" name=\"pass\" placeholder=\"Establece una contraseña\" required pattern=\"^(?=.*?[a-zA-Z])(?=.*?[^a-zA-Z]).{6,16}$\" oninvalid=\"this.setCustomValidity('Debes introducir una contraseña de entre 6 y 16 dígitos con letras y números o símbolos')\" onchange=\"this.value=this.value.trim();this.setCustomValidity('');\"><br><br> ".PHP_EOL;
		$campos.="<input type=\"password\" id=\"pass2\" name=\"pass2\" placeholder=\"Repite tu contraseña\" required pattern=\"^(?=.*?[a-zA-Z])(?=.*?[^a-zA-Z]).{6,16}$\" oninvalid=\"this.setCustomValidity('Debes repetir la misma contraseña')\" onsubmit=\"document.getElementById('pass2').setAttribute('pattern','^'+document.getElementById('pass').value+'$');\" onchange=\"this.value=this.value.trim();this.setCustomValidity('');\"><br><br>".PHP_EOL;
	} else {
		$mensaje='Introduce el correo electrónico y teléfono con el que te registraste para crear una nueva contraseña';
		$campos.="<input type=\"text\" name=\"usr\" value=\"{$_POST['usr']}\" placeholder=\"Correo electrónico\" required pattern=\"^[-\w\.%+]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,125}[a-zA-Z]{2,63}$\" oninvalid=\"this.setCustomValidity('Debes introducir tu correo electrónico')\" onchange=\"this.value=this.value.trim();this.setCustomValidity('');\"><br><br>".PHP_EOL;
		$campos.="<input type=\"text\" name=\"movil\" value=\"{$_POST['movil']}\" placeholder=\"Teléfono movil\" maxlength=\"9\" required pattern=\"^[67][0-9]{8}$\" oninvalid=\"this.setCustomValidity('Debes introducir tu número de teléfono móvil de España')\" onchange=\"this.value=this.value.trim();this.setCustomValidity('');\"><br><br>".PHP_EOL;
	}

	
$contenido_completo=<<<END
<!DOCTYPE html>
<html>
    <head>
        <title>Clyb App</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <link rel="shortcut icon" type="image/x-icon" href="/icon-normal.png">
        <link rel="stylesheet" href="estilos.css?$cssversion">
    </head>
    <body class="fondo_degradado" style="padding-top: max(10px, calc(50vh - 275px)); padding-bottom: 10px;">
        <img src="/img/icon2.png" width="76px" alt="Clyb">
        <p class="texto blanco t40"><br>Recuperar contraseña<br><br></p>
        <p class="texto blanco t16" style="width: 320px; margin: auto;">$mensaje</p>
        <br>
        <form method="post">
						$campos
            <input type="submit" class="boton blanco" value="CONTINUAR"><br>
            <a href="/acceso"><button type="button" class="boton transparente">INICIAR SESIÓN</button></a><br>
        </form>
				$aviso
    </body>
</html>
END;
}
?>