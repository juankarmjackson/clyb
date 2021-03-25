<?php
// 12/3/21 Version inicial completa

if ($IDU>0 || $IDC>0) {
	header('Location: /', true, 302);
} else {
	$aviso='';
	if ($_POST['usr']!='' && $_POST['pass']!='')	{
		$res=$conn->query("SELECT id, nombre, clave, perfiles FROM usuarios WHERE email='{$conn->real_escape_string($_POST['usr'])}' AND clave='{$conn->real_escape_string($_POST['pass'])}' AND activo UNION ALL SELECT id, nombre, clave, 'CLIENTE' AS perfiles FROM clientes WHERE email='{$conn->real_escape_string($_POST['usr'])}' AND clave='{$conn->real_escape_string($_POST['pass'])}' AND activo ");
		if ($res->num_rows > 0) {
			$row = $res->fetch_assoc();
			$perfiles=$row['perfiles'];
			if ($perfiles=='CLIENTE')
				$IDC=$row['id'];
			else
				$IDU=$row['id'];
			$nombre=$row['nombre'];
			$clave=$row['clave'];
			if ($IDU>0) {
				$conn->query("UPDATE usuarios SET fechalogin=UTC_TIMESTAMP, iplogin='{$_SERVER['REMOTE_ADDR']}' WHERE id=$IDU");
				setcookie('TMPUSR',substr('000000'.$IDU,-6).sha1($IDU.$clave.'Cadena de texto de aleatorizacion de Clyb'),time()+30*24*3600);
			}	elseif ($IDC>0)
				setcookie('TMPCLI',substr('000000'.$IDC,-6).sha1($IDC.$clave.'Cadena de texto de aleatorizacion de cliente Clyb'),time()+30*24*3600);
			header('Location: /', true, 302);
			
		} else $aviso=popupaviso("Correo electrónico y/o contraseña incorrectos");
		$res->Close(); 
	}
	
$contenido_completo=<<<END
<!DOCTYPE html>
<html>
		<head>
				<title>Accceso Clyb App</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
				<link rel="shortcut icon" type="image/x-icon" href="/icon-normal.png">
				<link rel="stylesheet" href="/estilos.css?$cssversion">
		</head>
		<body class="fondo_degradado" style="padding-top: max(10px, calc(50vh - 275px)); padding-bottom: 10px;">
				<img src="img/icon2.png" width="76px" alt="Clyb">
				<p class="texto blanco t40"><br>Bienvenido<br>de nuevo<br><br></p>
				<div class="process"></div>
        <form method="post">
            <input type="text" name="usr" value="{$_POST['usr']}" placeholder="Correo Electrónico" required pattern="^[-\w\.%+]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,125}[a-zA-Z]{2,63}$" oninvalid="this.setCustomValidity('Debes introducir tu correo electrónico')" onchange="this.value=this.value.trim();this.setCustomValidity('');"><br>
            <br>
            <input type="password" name="pass" placeholder="Contraseña" required pattern="^(?=.*?[a-zA-Z])(?=.*?[^a-zA-Z]).{6,16}$" oninvalid="this.setCustomValidity('Debes introducir tu contraseña')" onchange="this.value=this.value.trim();this.setCustomValidity('');">
            <br>
            <br> 
            <input type="submit" class="boton azul" value="INICIAR SESION"><br>
            <a href="registro"><button type="button" class="boton blanco">REGISTRARME</button></a><br>
            <br>
            <a href="olvido" style="color: white;">He olvidado mi contraseña</a>
        </form>   
				$aviso
		</body>
</html>
END;

}
?>