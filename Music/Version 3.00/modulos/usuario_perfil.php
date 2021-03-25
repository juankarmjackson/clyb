<?php
// 12/3/21 Version inicial completa

if ($IDC<=0) {
	header('Location: /', true, 302);
} else {
	$editar=false;
	$aviso='';
	if ($_POST['datos']=="SI") {
		$valor_nombre=trim($_POST['nombre']);
		$valor_apellidos=trim($_POST['apellidos']);
		$valor_negocio=trim($_POST['negocio']);
		$valor_direccion=trim($_POST['direccion']);
		$valor_cp=trim($_POST['cp']);
		$valor_tiponegocio=trim($_POST['tiponegocio']);
		$valor_cif=trim($_POST['cif']);
		$valor_web=trim($_POST['web']);
		$valor_instagram=trim($_POST['instagram']);
		$valor_facebook=trim($_POST['facebook']);
		if ((preg_match('/^(?=.*?[a-zA-Z])(?=.*?[^a-zA-Z]).{6,16}$/', trim($_POST['pass'])) || $_POST['pass']=='') && trim($_POST['pass'])==trim($_POST['pass2']) && $valor_nombre!='' && $valor_apellidos!='') {
			$sql="UPDATE clientes SET facebook='{$conn->real_escape_string($valor_facebook)}', instagram='{$conn->real_escape_string($valor_instagram)}', web='{$conn->real_escape_string($valor_web)}', cif='{$conn->real_escape_string($valor_cif)}', tiponegocio='{$conn->real_escape_string($valor_tiponegocio)}', cp='{$conn->real_escape_string($valor_cp)}', direccion='{$conn->real_escape_string($valor_direccion)}', negocio='{$conn->real_escape_string($valor_negocio)}', negocio='{$conn->real_escape_string($valor_negocio)}', apellidos='{$conn->real_escape_string($valor_apellidos)}', nombre='{$conn->real_escape_string($valor_nombre)}'";
			if (trim($_POST['pass'])!='') {
				$sql.=", clave='{$conn->real_escape_string(trim($_POST['pass']))}'";
				setcookie('TMPCLI',substr('000000'.$IDC,-6).sha1($IDU.trim($_POST['pass']).'Cadena de texto de aleatorizacion de cliente Clyb'),time()+30*24*3600);
			}
			if (is_uploaded_file($_FILES['foto']['tmp_name'])) {
				if (strtolower(substr($_FILES['foto']['name'],-4))=='.jpg') {
					file_put_contents("img/perfil_$IDC.jpg", file_get_contents($_FILES['foto']['tmp_name']));
					$sql.=", foto=foto+1";					
				} else {
					$image=false;
					if (strtolower(substr($_FILES['foto']['name'],-4))=='.png')
						$image=@imagecreatefrompng($_FILES['foto']['tmp_name']);
					elseif (strtolower(substr($_FILES['foto']['name'],-5))=='.jpeg')
						$image=@imagecreatefromjpeg($_FILES['foto']['tmp_name']);
					elseif (strtolower(substr($_FILES['foto']['name'],-4))=='.bmp')
						$image=@imagecreatefrombmp($_FILES['foto']['tmp_name']);
					elseif (strtolower(substr($_FILES['foto']['name'],-4))=='.gif')
						$image=@imagecreatefromgif($_FILES['foto']['tmp_name']);
					else file_put_contents("img/perfil_{$IDC}_{$_FILES['foto']['name']}", file_get_contents($_FILES['foto']['tmp_name']));
					if ($image!=false) {
						imagejpeg($image, "img/perfil_{$IDC}.jpg");
						imagedestroy($image);
						$sql.=", foto=foto+1";
					}
				}
			}
			$sql.=" WHERE id=$IDC;";
			$conn->query($sql);
			$editar=false;
			$aviso=popupaviso("Los datos han sido guardados","ico_guardar.png");
		} else {
			$editar=true;
			$aviso=popupaviso("Los datos facilitados son incorrectos");
		}
	} elseif ($_POST['editar']=="SI") $editar=true;

	if (!$editar || $aviso=='') {
		$res=$conn->query("SELECT * FROM clientes WHERE id=$IDC;");
		if ($row = $res->fetch_assoc()) {
			$valor_nombre=textoahtml($row['nombre']);
			$valor_apellidos=textoahtml($row['apellidos']);
			$valor_negocio=textoahtml($row['negocio']);
			$valor_direccion=textoahtml($row['direccion']);
			$valor_cp=textoahtml($row['cp']);
			$valor_email=textoahtml($row['email']);
			$valor_telefono=textoahtml($row['telefono']);
			$valor_fotover=$row['foto'];
			$valor_tiponegocio=textoahtml($row['tiponegocio']);
			$valor_cif=textoahtml($row['cif']);
			$valor_web=textoahtml($row['web']);
			$valor_instagram=textoahtml($row['instagram']);
			$valor_facebook=textoahtml($row['facebook']);
			if ($valor_fotover>0) $valor_foto=$IDC; else $valor_foto=0;
			if ($valor_facebook=='') $valor_facebook='&nbsp;';
			if ($valor_instagram=='') $valor_instagram='&nbsp;';
			if ($valor_web=='') $valor_web='&nbsp;';
			if ($valor_cif=='') $valor_cif='&nbsp;';
			if ($valor_tiponegocio=='') $valor_tiponegocio='&nbsp;';
			if ($valor_cp=='') $valor_cp='&nbsp;';
			if ($valor_direccion=='') $valor_direccion='&nbsp;';
			if ($valor_negocio=='') $valor_negocio='&nbsp;';
		} else header('Location: /', true, 302);
		$res->Close(); 
	}

if ($editar)
$contenido_completo=<<<END
<!DOCTYPE html>
<html>
    <head>
        <title>Clyb App</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <link rel="shortcut icon" type="image/x-icon" href="/icon-normal.png">
        <link rel="stylesheet" href="/estilos.css?$cssversion">
    </head>
    <body class="relleno perfil fondo_gris" onbeforeunload="return 'Si sale, es posible que los cambios no se guarden';">
        <div class="fondo_gris" style="position:fixed; top: 0; left: 0; width: 100vw; height: 260px;"></div>
        <div class="topbar suplemento fondo_degradado">
            <a href="/perfil" class="ico_perfilw"></a>
            <a class="logo_centralw"></a>
            <a href="/chat" class="ico_chatw"></a>
        </div>
        
        <div class="filtro_sombra flotante">
            <div class="topbar contenedor flotante">
                <img src="/img/pixel_transparente.png" style="background-image: url('/img/perfil_$valor_foto.jpg?$valor_fotover')">
                <label for="foto"><img class="peq" src="/img/pixel_transparente.png" style="background-image: url('/img/ico_lapiz2.png')"></label>
                <a href="#" class="icoper guardar p1" onclick="if (document.getElementById('pass2').value!=document.getElementById('pass').value) document.getElementById('pass2').setCustomValidity('Debes repetir la misma contraseña'); else window.onbeforeunload = null; document.getElementById('boton').click(); return false;"></a>
            </div>
            <div class="topbar elipse flotante fondo_degradado"></div>
            <div class="topbar menu_perfil flotante">
                <a href="/perfil" class="iz on">MI PERFIL</a><a href="/contratacion">MIS SERVICIOS</a><a href="/pagos" class="dcha">PAGOS</a>
            </div>
        </div>
        
        <div class="navbar cuadrado"><a href="/" class="iconav inicio">Inicio</a><a href="/servicios" class="iconav servicios">Servicios</a><a href="/push" class="iconav push">Push</a></div>
				<form id="Formulario" method="post" enctype="multipart/form-data">
        
        <div class="ventana titulo"><div class="encabezado texto blanco t14 l1">DATOS DE ACCESO</div>
            <p class="texto grisclaro t12 s10 iz l1 a90">CORREO ELECTRONICO</p>
            <input type="text" name="email" value="$valor_email" required pattern="^[-\w\.%+]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,125}[a-zA-Z]{2,63}$" oninvalid="this.setCustomValidity('Debes introducir tu correo electrónico')" onchange="this.setCustomValidity('')" disabled>

            <p class="texto grisclaro t12 s10 iz l1 a90">CONTRASEÑA</p>
            <input type="password" id="pass" name="pass" value="" pattern="^(?=.*?[a-zA-Z])(?=.*?[^a-zA-Z]).{6,16}$" oninvalid="this.setCustomValidity('Debes introducir una contraseña de entre 6 y 16 dígitos con letras y números o símbolos')" onchange="this.setCustomValidity('');">

            <p class="texto grisclaro t12 s10 iz l1 a90">REPETIR CONTRASEÑA</p>
            <input type="password" id="pass2" name="pass2" value="" onchange="this.setCustomValidity('');">
        </div>
        
        <div class="ventana titulo"><div class="encabezado texto blanco t14 l1">DATOS PERSONALES</div>
            <p class="texto grisclaro t12 s10 iz l1 a90">Nombre</p>
            <input type="text" name="nombre" value="$valor_nombre" required oninvalid="this.setCustomValidity('Debes introducir tu nombre')" onchange=this.setCustomValidity('')">

            <p class="texto grisclaro t12 s10 iz l1 a90">APELLIDOS</p>
            <input type="text" name="apellidos" value="$valor_apellidos" required oninvalid="this.setCustomValidity('Debes introducir tus apellidos')" onchange="this.setCustomValidity('')">

            <p class="texto grisclaro t12 s10 iz l1 a90">TELÉFONO</p>
            <input type="text" name="telefono" value="$valor_telefono" maxlength="9" required pattern="^[67][0-9]{8}$" oninvalid="this.setCustomValidity('Debes introducir tu número de teléfono móvil de España')" onchange="this.setCustomValidity('')" disabled>
        </div>
        
        <div class="ventana titulo"><div class="encabezado texto blanco t14 l1">MI EMPRESA</div>
            <p class="texto grisclaro t12 s10 iz l1 a90">Nombre</p>
            <input type="text" name="negocio" value="$valor_negocio">

            <p class="texto grisclaro t12 s10 iz l1 a90">DIRECCIÓN DE LA EMPRESA</p>
            <input type="text" name="direccion" value="$valor_direccion">

            <p class="texto grisclaro t12 s10 iz l1 a90">CÓDIGO POSTAL</p>
            <input type="text" name="cp" value="$valor_cp">
        </div>
        
        <div class="ventana titulo"><div class="encabezado texto blanco t14 l1">OTROS DATOS</div>
            <p class="texto grisclaro t12 s10 iz l1 a90">TIPO DE NEGOCIO</p>
            <input type="text" name="tiponegocio" value="$valor_tiponegocio">

            <p class="texto grisclaro t12 s10 iz l1 a90">CIF</p>
            <input type="text" name="cif" value="$valor_cif">

            <p class="texto grisclaro t12 s10 iz l1 a90">SITIO WEB</p>
            <input type="text" name="web" value="$valor_web">

            <p class="texto grisclaro t12 s10 iz l1 a90">INSTAGRAM</p>
            <input type="text" name="instagram" value="$valor_instagram">

            <p class="texto grisclaro t12 s10 iz l1 a90">FACEBOOK</p>
            <input type="text" name="facebook" value="$valor_facebook">
        </div>
				<input id="foto" type="file" name="foto" style="visibility: hidden;" accept=".jpg,.png,.jpeg,.gif,.bmp">
				<input id="boton" type="submit" style="visibility: hidden;">
				<input type="hidden" name="editar" value="SI">				
				<input type="hidden" name="datos" value="SI">				
				</form>
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
else
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
                <a href="#" class="icoper editar p1" onclick="document.getElementById('Formulario').submit();return false;"></a>
                <a href="#" class="icoper salir p2" onclick="document.getElementById('Salir').className='popup on';return false;"></a>
            </div>
            <div class="topbar elipse flotante fondo_degradado"></div>
            <div class="topbar menu_perfil flotante">
                <a href="/perfil" class="iz on">MI PERFIL</a><a href="/contratacion">MIS SERVICIOS</a><a href="/pagos" class="dcha">PAGOS</a>
            </div>
        </div>
        
        <div class="navbar cuadrado"><a href="/" class="iconav inicio">Inicio</a><a href="/servicios" class="iconav servicios">Servicios</a><a href="/push" class="iconav push">Push</a></div>
        
        <div class="ventana titulo"><div class="encabezado texto blanco t14 l1">DATOS DE ACCESO</div>
            <p class="texto grisclaro t12 s10 iz l1 a90">CORREO ELECTRONICO</p>
            <p class="texto gris t16 s05 iz l1 a80">$valor_email</p>

            <p class="texto grisclaro t12 s10 iz l1 a90">CONTRASEÑA</p>
            <p class="texto gris t16 s05 iz l1 a80">············</p>
        </div>
        
        <div class="ventana titulo"><div class="encabezado texto blanco t14 l1">DATOS PERSONALES</div>
            <p class="texto grisclaro t12 s10 iz l1 a90">Nombre</p>
            <p class="texto gris t16 s05 iz l1 a80">$valor_nombre</p>

            <p class="texto grisclaro t12 s10 iz l1 a90">APELLIDOS</p>
            <p class="texto gris t16 s05 iz l1 a80">$valor_apellidos</p>

            <p class="texto grisclaro t12 s10 iz l1 a90">TELÉFONO</p>
            <p class="texto gris t16 s05 iz l1 a80">$valor_telefono</p>
        </div>
        
        <div class="ventana titulo"><div class="encabezado texto blanco t14 l1">MI EMPRESA</div>
            <p class="texto grisclaro t12 s10 iz l1 a90">Nombre</p>
            <p class="texto gris t16 s05 iz l1 a80">$valor_negocio</p>

            <p class="texto grisclaro t12 s10 iz l1 a90">DIRECCIÓN DE LA EMPRESA</p>
            <p class="texto gris t16 s05 iz l1 a80">$valor_direccion</p>

            <p class="texto grisclaro t12 s10 iz l1 a90">CÓDIGO POSTAL</p>
            <p class="texto gris t16 s05 iz l1 a80">$valor_cp</p>
        </div>
        
        <div class="ventana titulo"><div class="encabezado texto blanco t14 l1">OTROS DATOS</div>
            <p class="texto grisclaro t12 s10 iz l1 a90">TIPO DE NEGOCIO</p>
            <p class="texto gris t16 s05 iz l1 a80">$valor_tiponegocio</p>

            <p class="texto grisclaro t12 s10 iz l1 a90">CIF</p>
            <p class="texto gris t16 s05 iz l1 a80">$valor_cif</p>

            <p class="texto grisclaro t12 s10 iz l1 a90">SITIO WEB</p>
            <p class="texto gris t16 s05 iz l1 a80">$valor_web</p>

            <p class="texto grisclaro t12 s10 iz l1 a90">INSTAGRAM</p>
            <p class="texto gris t16 s05 iz l1 a80">$valor_instagram</p>

            <p class="texto grisclaro t12 s10 iz l1 a90">FACEBOOK</p>
            <p class="texto gris t16 s05 iz l1 a80">$valor_facebook</p>
        </div>
				<br>
				<a href="#"><button type="button" class="boton azul" style="width: 238px" onclick="document.getElementById('Condiciones').className='popup on';return false;">Condiciones del servicio</button></a><br>
				<a href="#"><button type="button" class="boton azul" style="width: 238px" onclick="document.getElementById('Politica').className='popup on';return false;">Política de privacidad</button></a><br>
				<br>
				<form id="Formulario" method="post">
				<input type="hidden" name="editar" value="SI">
				</form>
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
				<div id="Condiciones" class="popup off">
						<div class="full">
								<img src="/img/icon.png" width="44" height="44"><br>
								<br>
								<p class="texto t20">Condiciones del servicio</p>
								<br>
								<p class="texto t16 js">
										1.	Estos Términos y Condiciones de Uso regulan las reglas a que se sujeta la utilización de la APP CLYB (en adelante, la APP), que puede descargarse desde el dominio clybconnect.com. La descarga o utilización de la APP atribuye la condición de Usuario a quien lo haga e implica la aceptación de todas las condiciones incluidas en este documento, en la Política de Privacidad y el Aviso Legal de dicha página Web. El Usuario debería leer estas condiciones cada vez que utilice la APP, ya que podrían ser modificadas en lo sucesivo.<br>
										<br>
										2.	Únicamente los Usuarios expresamente autorizados por CLYB CONNECT SL podrán acceder a la descarga y uso de la APP. Los Usuarios que no dispongan de autorización, no podrán acceder a dicho contenido. <br>
										<br>
										3.	Cargos: eres responsable del pago de todos los costes o gastos en los que incurras como resultado de descargar y usar la Aplicación de CLYB CONNECT SL, incluido cualquier cargo de red de operador o itinerancia. Consulta con tu proveedor de servicios los detalles al respecto.<br>
										<br>
										4.	Estadísticas anónimas: CLYB CONNECT SL se reserva el derecho a realizar un seguimiento de tu actividad en la Aplicación y a informar de ello a nuestros proveedores de servicios estadísticos de terceros. Todo ello, de forma anónima.<br>
										<br>
										5.	Protección de tu información personal: queremos ayudarte a llevar a cabo todos los pasos necesarios para proteger tu privacidad e información. Consulta la Política de privacidad de CLYB CONNECT SL y los avisos sobre privacidad de la Aplicación para conocer qué tipo de información recopilamos y las medidas que tomamos para proteger tu información personal.<br>
										<br>
										6.	Queda prohibido alterar o modificar cualquier parte de la APP o de los contenidas de la misma, eludir, desactivar o manipular, de cualquier otra forma (o tratar de eludir, desactivar o manipular), las funciones de seguridad u otras funciones del programa y utilizar la APP o sus contenidos para un fin comercial o publicitario. Queda prohibido el uso de la APP con la finalidad de lesionar bienes, derechos o intereses de CLYB CONNECT SL o de terceros. Queda igualmente prohibido realizar cualquier otro uso que altere, dañe o inutilice las redes, servidores, equipos, productos y programas informáticos de CLYB CONNECT SL o de terceros. <br>
										<br>
										7.	La APP y sus contenidos (textos, fotografías, gráficos, imágenes, tecnología, software, links, contenidos, diseño gráfico, código fuente, etc.), así como las marcas y demás signos distintivos son propiedad de CLYB CONNECT SL o de terceros, no adquiriendo el Usuario ningún derecho sobre ellos por el mero uso de la APP. El Usuario, deberá abstenerse de: a) Reproducir, copiar, distribuir, poner a disposición de terceros, comunicar públicamente, transformar o modificar la APP o sus contenidos, salvo en los casos contemplados en la ley o expresamente autorizados por CLYB CONNECT SL o por el titular de dichos derechos. b) Reproducir o copiar para uso privado la APP o sus contenidos, así como comunicarlos públicamente o ponerlos a disposición de terceros, cuando ello conlleve su reproducción. c) Extraer o reutilizar todo o parte sustancial de los contenidos integrantes de la APP. <br>
										<br>
										8.	Con sujeción a las condiciones establecidas en el apartado anterior, CLYB CONNECT SL concede al Usuario una licencia de uso de la APP, no exclusiva, gratuita, para uso personal, circunscrita al territorio nacional y con carácter indefinido. Dicha licencia se concede también en los mismos términos con respecto a las actualizaciones y mejoras que se realizasen en la aplicación. Dichas licencias de uso podrán ser revocadas por CLYB CONNECT SL unilateralmente en cualquier momento, mediante la mera notificación al Usuario. <br>
										<br>
										9.	Corresponde al Usuario, en todo caso, disponer de herramientas adecuadas para la detección y desinfección de programas maliciosos o cualquier otro elemento informático dañino. CLYNB CONNECT SL no se responsabiliza de los daños producidos a equipos informáticos durante el uso de la APP. Igualmente, CLYB CONNECT SL no será responsable de los daños producidos a los Usuarios, cuando dichos daños tengan su origen en fallos o desconexiones en las redes de telecomunicaciones que interrumpan el servicio.<br>
										<br>
										10.	IMPORTANTE: Podemos, sin que esto suponga ninguna obligación con el usuario, modificar estas Condiciones de uso, sin avisar, en cualquier momento. Si continúas utilizando la aplicación, una vez realizada cualquier modificación en estas Condiciones de uso, esa utilización continuada constituirá la aceptación, por tu parte, de tales modificaciones. Si no aceptas estas condiciones de uso ni aceptas quedar sujeto a ellas, no debes utilizar la aplicación ni descargar o utilizar cualquier software relacionado. El uso que hagas de la aplicación queda bajo tu única responsabilidad. No tenemos responsabilidad alguna por la eliminación o la incapacidad de almacenar o trasmitir cualquier contenido u otra información mantenida o trasmitida por la aplicación. No somos responsables de la precisión o la fiabilidad de cualquier información o consejos trasmitidos a través de la aplicación. Podemos, en cualquier momento, limitar o interrumpir el uso a nuestra única discreción. Hasta el máximo que permite la ley, en ningún caso seremos responsables de cualquier pérdida o daño relacionados.<br>
										<br>
										11.	El Usuario se compromete a hacer un uso correcto de la APP, de conformidad con la Ley, con los presentes Términos y Condiciones de Uso y con los demás reglamentos e instrucciones que, en su caso, pudieran ser de aplicación. El Usuario responderá frente a CLYB CONNECT SL y frente a terceros de cualesquiera daños o perjuicios que pudieran causarse por incumplimiento de estas obligaciones. <br>
										<br>
										12.	Estos Términos y Condiciones de Uso se rigen íntegramente por la legislación española. Para la resolución de cualquier conflicto relativo a su interpretación o aplicación, el Usuario se somete expresamente a la jurisdicción de los tribunales de Madrid (España)<br>
										<br>
								</p>
								<img class="cerrar" style="z-index: 110;" src="/img/ico_cerrar.png" alt="cerrar" onclick="document.getElementById('Condiciones').className='popup off';">            
						</div>
				</div>
				<div id="Politica" class="popup off">
						<div class="full">
								<img src="/img/icon.png" width="44" height="44"><br>
								<br>
								<p class="texto t20">Política de Privacidad</p>
								<br>
								<p class="texto t16 js">
										<b>I. POLÍTICA DE PRIVACIDAD Y PROTECCIÓN DE DATOS</b><br>
										<br>
										Respetando lo establecido en la legislación vigente, clybconnect.com (en adelante, también Sitio Web) se compromete a adoptar las medidas técnicas y organizativas necesarias, según el nivel de seguridad adecuado al riesgo de los datos recogidos. <br>
										<br>
										<b>Leyes que incorpora esta política de privacidad</b><br>
										Esta política de privacidad está adaptada a la normativa española y europea vigente en materia de protección de datos personales en internet. En concreto, la misma respeta las siguientes normas:<br>
										<br>
										El Reglamento (UE) 2016/679 del Parlamento Europeo y del Consejo, de 27 de abril de 2016, relativo a la protección de las personas físicas en lo que respecta al tratamiento de datos personales y a la libre circulación de estos datos (RGPD).
										La Ley Orgánica 3/2018, de 5 de diciembre, de Protección de Datos Personales y garantía de los derechos digitales (LOPD-GDD). <br>
								</p>
								<ul>
										<li>El Real Decreto 1720/2007, de 21 de diciembre, por el que se aprueba el Reglamento de desarrollo de la Ley Orgánica 15/1999, de 13 de diciembre, de Protección de Datos de Carácter Personal (RDLOPD).</li>
										<li>La Ley 34/2002, de 11 de julio, de Servicios de la Sociedad de la Información y de Comercio Electrónico (LSSI-CE).</li>
								</ul>
								<p class="texto t16 js">
										<br>
										<b>Identidad del responsable del tratamiento de los datos personales</b><br>
										El responsable del tratamiento de los datos personales recogidos en clybconnect.com corresponde a CLYB CONNECT, S.L, provista de NIF: B42778977 e inscrita en Madrid con los siguientes datos registrales:<br>
										<br>
										Calle, Felipe III<br>
										Teléfono de contacto: 911239057<br>
										Fax: info@clybconnect.com<br>
										Email de contacto: info@clybconnet.com<br>
										<br>
										<b>Delegado de Protección de Datos (DPD) </b><br>
										El Delegado de Protección de Datos (DPD, o DPO por sus siglas en inglés) es el encargado de velar por el cumplimiento de la normativa de protección de datos a la cual se encuentra sujeta clybconnect.com. El Usuario puede contactar con el DPD designado por el Responsable del tratamiento utilizando los siguientes datos de contacto: 911 239 057. <br>
										<br>
										<b>Registro de Datos de Carácter Personal</b><br>
										En cumplimiento de lo establecido en el RGPD y la LOPD-GDD, le informamos que los datos personales recabados por clybconnect.com, mediante los formularios extendidos en sus páginas, quedarán incorporados y serán tratados en nuestros ficheros con el fin de poder facilitar, agilizar y cumplir los compromisos establecidos entre clybconnect.com y el Usuario o el mantenimiento de la relación que se establezca en los formularios que este rellene, o para atender una solicitud o consulta del mismo. Asimismo, de conformidad con lo previsto en el RGPD y la LOPD-GDD, salvo que sea de aplicación la excepción prevista en el artículo 30.5 del RGPD, se mantiene un registro de actividades de tratamiento que especifica, según sus finalidades, las actividades de tratamiento llevadas a cabo y las demás circunstancias establecidas en el RGPD. <br>
										<br>
										<b>Principios aplicables al tratamiento de los datos personales</b><br>
										El tratamiento de los datos personales del Usuario se someterá a los siguientes principios recogidos en el artículo 5 del RGPD y en el artículo 4 y siguientes de la Ley Orgánica 3/2018, de 5 de diciembre, de Protección de Datos Personales y garantía de los derechos digitales: <br>
										<br>
										Principio de licitud, lealtad y transparencia: se requerirá en todo momento el consentimiento del Usuario previa información, completamente transparente, de los fines para los cuales se recogen los datos personales. <br>
								</p>
								<ul>
										<li>Principio de limitación de la finalidad: los datos personales serán recogidos con fines determinados, explícitos y legítimos.</li>
										<li>Principio de minimización de datos: los datos personales recogidos serán únicamente los estrictamente necesarios en relación con los fines para los que son tratados.</li>
										<li>Principio de exactitud: los datos personales deben ser exactos y estar siempre actualizados.</li>
										<li>Principio de limitación del plazo de conservación: los datos personales solo serán mantenidos de forma que se permita la identificación del Usuario durante el tiempo necesario para los fines de su tratamiento.</li>
										<li>Principio de integridad y confidencialidad: los datos personales serán tratados de manera que se garantice su seguridad y confidencialidad.</li>
										<li>Principio de responsabilidad proactiva: el Responsable del tratamiento será responsable de asegurar que los principios anteriores se cumplen.</li>
								</ul>
								<p class="texto t16 js">
										<br>
										<b>Categorías de datos personales</b><br>
										Las categorías de datos que se tratan en clybconnect.com son únicamente datos identificativos. En ningún caso, se tratan categorías especiales de datos personales en el sentido del artículo 9 del RGPD. <br>
										<br>
										<b>Base legal para el tratamiento de los datos personales</b><br>
										La base legal para el tratamiento de los datos personales es el consentimiento. clybconnect.com se compromete a recabar el consentimiento expreso y verificable del Usuario para el tratamiento de sus datos personales, para uno o varios fines específicos. <br>
										<br>
										El Usuario tendrá derecho a retirar su consentimiento en cualquier momento. Será tan fácil retirar el consentimiento como darlo. Como regla general, la retirada del consentimiento no condicionará el uso del Sitio Web. <br>
										<br>
										En las ocasiones en las que el Usuario deba o pueda facilitar sus datos a través de formularios para realizar consultas, solicitar información o por motivos relacionados con el contenido del Sitio Web, se le informará en caso de que la cumplimentación de alguno de ellos sea obligatoria debido a que los mismos sean imprescindibles para el correcto desarrollo de la operación realizada. <br>
										<br>
										<b>Fines del tratamiento a que se destinan los datos personales</b><br>
										Los datos personales son recabados y gestionados por clybconnect.com con la finalidad de poder facilitar, agilizar y cumplir los compromisos establecidos entre el Sitio Web y el Usuario o el mantenimiento de la relación que se establezca en los formularios que este último rellene o para atender una solicitud o consulta. <br>
										<br>
										Igualmente, los datos podrán ser utilizados con una finalidad comercial de personalización, operativa y estadística, y actividades propias del objeto social de clybconnect.com, así como para la extracción, almacenamiento de datos y estudios de marketing para adecuar el Contenido ofertado al Usuario, así como mejorar la calidad, funcionamiento y navegación por el Sitio Web. <br>
										<br>
										En el momento en que se obtengan los datos personales, se informará al Usuario acerca del fin o fines específicos del tratamiento a que se destinarán los datos personales; es decir, del uso o usos que se dará a la información recopilada. <br>
										<br>
										<b>Períodos de retención de los datos personales</b><br>
										Los datos personales solo serán retenidos durante el tiempo mínimo necesario para los fines de su tratamiento y, en todo caso, únicamente durante el siguiente plazo: Indefinido, o hasta que el Usuario solicite su supresión. <br>
										<br>
										En el momento en que se obtengan los datos personales, se informará al Usuario acerca del plazo durante el cual se conservarán los datos personales o, cuando eso no sea posible, los criterios utilizados para determinar este plazo. <br>
										<br>
										<b>Destinatarios de los datos personales</b><br>
										Los datos personales del Usuario no serán compartidos con terceros. <br>
										<br>
										En cualquier caso, en el momento en que se obtengan los datos personales, se informará al Usuario acerca de los destinatarios o las categorías de destinatarios de los datos personales. <br>
										<br>
										<b>Datos personales de menores de edad</b><br>
										Respetando lo establecido en los artículos 8 del RGPD y 7 de la Ley Orgánica 3/2018, de 5 de diciembre, de Protección de Datos Personales y garantía de los derechos digitales, solo los mayores de 14 años podrán otorgar su consentimiento para el tratamiento de sus datos personales de forma lícita por clybconnect.com. Si se trata de un menor de 14 años, será necesario el consentimiento de los padres o tutores para el tratamiento, y este solo se considerará lícito en la medida en la que los mismos lo hayan autorizado. <br>
										<br>
										<b>Secreto y seguridad de los datos personales</b><br>
										clybconnect.com se compromete a adoptar las medidas técnicas y organizativas necesarias, según el nivel de seguridad adecuado al riesgo de los datos recogidos, de forma que se garantice la seguridad de los datos de carácter personal y se evite la destrucción, pérdida o alteración accidental o ilícita de datos personales transmitidos, conservados o tratados de otra forma, o la comunicación o acceso no autorizados a dichos datos. <br>
										<br>
										El Sitio Web cuenta con un certificado SSL (Secure Socket Layer), que asegura que los datos personales se transmiten de forma segura y confidencial, al ser la transmisión de los datos entre el servidor y el Usuario, y en retroalimentación, totalmente cifrada o encriptada. <br>
										<br>
										Sin embargo, debido a que clybconnect.com no puede garantizar la inexpugnabilidad de internet ni la ausencia total de hackers u otros que accedan, de modo fraudulento, a los datos personales, el Responsable del tratamiento se compromete a comunicar al Usuario, sin dilación indebida, cuando ocurra una violación de la seguridad de los datos personales, que sea probable que entrañe un alto riesgo para los derechos y libertades de las personas físicas. Siguiendo lo establecido en el artículo 4 del RGPD, se entiende por violación de la seguridad de los datos personales toda violación de la seguridad que ocasione la destrucción, pérdida o alteración accidental o ilícita de datos personales transmitidos, conservados o tratados de otra forma, o la comunicación o acceso no autorizados a dichos datos. <br>
										<br>
										Los datos personales serán tratados como confidenciales por el Responsable del tratamiento, quien se compromete a informar de y a garantizar por medio de una obligación legal o contractual que dicha confidencialidad sea respetada por sus empleados, asociados, y toda persona a la cual le haga accesible la información. <br>
										<br>
										<b>Derechos derivados del tratamiento de los datos personales</b><br>
										El Usuario tiene sobre clybconnect.com y podrá, por tanto, ejercer frente al Responsable del tratamiento los siguientes derechos reconocidos en el RGPD y en la Ley Orgánica 3/2018, de 5 de diciembre, de Protección de Datos Personales y garantía de los derechos digitales: <br>
										<br>
								</p>
								<ul>
										<li>Derecho de acceso: Es el derecho del Usuario a obtener confirmación de si clybconnect.com está tratando o no sus datos personales y, en caso afirmativo, obtener información sobre sus datos concretos de carácter personal y del tratamiento que clybconnect.com haya realizado o realice, así como, entre otra, de la información disponible sobre el origen de dichos datos y los destinatarios de las comunicaciones realizadas o previstas de los mismos.</li>
										<li>Derecho de rectificación: Es el derecho del Usuario a que se modifiquen sus datos personales que resulten ser inexactos o, teniendo en cuenta los fines del tratamiento, incompletos.</li>
										<li>Derecho de supresión ("el derecho al olvido"): Es el derecho del Usuario, siempre que la legislación vigente no establezca lo contrario, a obtener la supresión de sus datos personales cuando estos ya no sean necesarios para los fines para los cuales fueron recogidos o tratados; el Usuario haya retirado su consentimiento al tratamiento y este no cuente con otra base legal; el Usuario se oponga al tratamiento y no exista otro motivo legítimo para continuar con el mismo; los datos personales hayan sido tratados ilícitamente; los datos personales deban suprimirse en cumplimiento de una obligación legal; o los datos personales hayan sido obtenidos producto de una oferta directa de servicios de la sociedad de la información a un menor de 14 años. Además de suprimir los datos, el Responsable del tratamiento, teniendo en cuenta la tecnología disponible y el coste de su aplicación, deberá adoptar medidas razonables para informar a los responsables que estén tratando los datos personales de la solicitud del interesado de supresión de cualquier enlace a esos datos personales.</li>
										<li>Derecho a la limitación del tratamiento: Es el derecho del Usuario a limitar el tratamiento de sus datos personales. El Usuario tiene derecho a obtener la limitación del tratamiento cuando impugne la exactitud de sus datos personales; el tratamiento sea ilícito; el Responsable del tratamiento ya no necesite los datos personales, pero el Usuario lo necesite para hacer reclamaciones; y cuando el Usuario se haya opuesto al tratamiento.</li>
										<li>Derecho a la portabilidad de los datos: En caso de que el tratamiento se efectúe por medios automatizados, el Usuario tendrá derecho a recibir del Responsable del tratamiento sus datos personales en un formato estructurado, de uso común y lectura mecánica, y a transmitirlos a otro responsable del tratamiento. Siempre que sea técnicamente posible, el Responsable del tratamiento transmitirá directamente los datos a ese otro responsable.</li>
										<li>Derecho de oposición: Es el derecho del Usuario a que no se lleve a cabo el tratamiento de sus datos de carácter personal o se cese el tratamiento de los mismos por parte de clybconnect.com.</li>
										<li>Derecho a no ser objeto de una decisión basada únicamente en el tratamiento automatizado, incluida la elaboración de perfiles: Es el derecho del Usuario a no ser objeto de una decisión individualizada basada únicamente en el tratamiento automatizado de sus datos personales, incluida la elaboración de perfiles, existente salvo que la legislación vigente establezca lo contrario.</li>
								</ul>
								<p class="texto t16 js">
										<br>
										Así pues, el Usuario podrá ejercitar sus derechos mediante comunicación escrita dirigida al Responsable del tratamiento con la referencia "RGPD-clybconnect.com", especificando: <br>
										<br>
								</p>
								<ul>
										<li>Nombre, apellidos del Usuario y copia del DNI. En los casos en que se admita la representación, será también necesaria la identificación por el mismo medio de la persona que representa al Usuario, así como el documento acreditativo de la representación. La fotocopia del DNI podrá ser sustituida, por cualquier otro medio válido en derecho que acredite la identidad.</li>
										<li>Petición con los motivos específicos de la solicitud o información a la que se quiere acceder.</li>
										<li>Domicilio a efecto de notificaciones.</li>
										<li>Fecha y firma del solicitante.</li>
										<li>Todo documento que acredite la petición que formula.</li>
								</ul>
								<p class="texto t16 js">
										<br>
										Esta solicitud y todo otro documento adjunto podrá enviarse a la siguiente dirección y/o correo electrónico: <br>
										<br>
										Dirección postal: 	 <br>
										Calle Felipe III	 <br>
										Correo electrónico: info@clybconnect.com<br>
										<br>
										<b>Reclamaciones ante la autoridad de control</b><br>
										En caso de que el Usuario considere que existe un problema o infracción de la normativa vigente en la forma en la que se están tratando sus datos personales, tendrá derecho a la tutela judicial efectiva y a presentar una reclamación ante la Agencia Española de Protección de Datos (http://www.agpd.es). <br>
										<br>
										<br>
										<b>II. POLÍTICA DE COOKIES</b><br>
										<br>
										El acceso a este Sitio Web puede implicar la utilización de cookies. Las cookies son pequeñas cantidades de información que se almacenan en el navegador utilizado por cada Usuario —en los distintos dispositivos que pueda utilizar para navegar— para que el servidor recuerde cierta información que posteriormente y únicamente el servidor que la implementó leerá. Las cookies facilitan la navegación, la hacen más amigable, y no dañan el dispositivo de navegación. <br>
										<br>
										Las cookies son procedimientos automáticos de recogida de información relativa a las preferencias determinadas por el Usuario durante su visita al Sitio Web, con el fin de reconocerlo como Usuario y personalizar su experiencia y el uso del Sitio Web. Pueden también, por ejemplo, ayudar a identificar y resolver errores. <br>
										<br>
										La información recabada a través de las cookies puede incluir la fecha y hora de visitas al Sitio Web, las páginas visionadas, el tiempo que ha estado en el Sitio Web y los sitios visitados justo antes y después del mismo. Sin embargo, ninguna cookie permite que esta misma pueda contactarse con el número de teléfono del Usuario o con cualquier otro medio de contacto personal. Ninguna cookie puede extraer información del disco duro del Usuario o robar información personal. La única manera de que la información privada del Usuario forme parte del archivo Cookie es que el usuario dé personalmente esa información al servidor. <br>
										<br>
										Las cookies que permiten identificar a una persona se consideran datos personales. Por tanto, a las mismas les será de aplicación la Política de Privacidad anteriormente descrita. En este sentido, para la utilización de las mismas será necesario el consentimiento del Usuario. Este consentimiento será comunicado, en base a una elección auténtica, ofrecido mediante una decisión afirmativa y positiva, antes del tratamiento inicial, removible y documentado. <br>
										<br>
										<b>Cookies propias</b><br>
										Son aquellas cookies que son enviadas al ordenador o dispositivo del Usuario y gestionadas exclusivamente por clybconnect.com para el mejor funcionamiento del Sitio Web. La información que se recaba se emplea para mejorar la calidad del Sitio Web y su Contenido y su experiencia como Usuario. Estas cookies permiten reconocer al Usuario como visitante recurrente del Sitio Web y adaptar el contenido para ofrecerle contenidos que se ajusten a sus preferencias. <br>
										<br>
										La(s) entidad(es) encargada(s) del suministro de cookies podrá(n) ceder esta información a terceros, siempre y cuando lo exija la ley o sea un tercero el que procese esta información para dichas entidades. <br>
										<br>
										<b>Deshabilitar, rechazar y eliminar cookies</b><br>
										El Usuario puede deshabilitar, rechazar y eliminar las cookies —total o parcialmente— instaladas en su dispositivo mediante la configuración de su navegador (entre los que se encuentran, por ejemplo, Chrome, Firefox, Safari, Explorer). En este sentido, los procedimientos para rechazar y eliminar las cookies pueden diferir de un navegador de Internet a otro. En consecuencia, el Usuario debe acudir a las instrucciones facilitadas por el propio navegador de Internet que esté utilizando. En el supuesto de que rechace el uso de cookies —total o parcialmente— podrá seguir usando el Sitio Web, si bien podrá tener limitada la utilización de algunas de las prestaciones del mismo. <br>
										<br>
										<br>
										<b>III. ACEPTACIÓN Y CAMBIOS EN ESTA POLÍTICA DE PRIVACIDAD</b><br>
										<br>
										Es necesario que el Usuario haya leído y esté conforme con las condiciones sobre la protección de datos de carácter personal contenidas en esta Política de Privacidad y de Cookies, así como que acepte el tratamiento de sus datos personales para que el Responsable del tratamiento pueda proceder al mismo en la forma, durante los plazos y para las finalidades indicadas. El uso del Sitio Web implicará la aceptación de la Política de Privacidad y de Cookies del mismo. <br>
										<br>
										clybconnect.com se reserva el derecho a modificar su Política de Privacidad y de Cookies, de acuerdo a su propio criterio, o motivado por un cambio legislativo, jurisprudencial o doctrinal de la Agencia Española de Protección de Datos. Los cambios o actualizaciones de esta Política de Privacidad y de Cookies serán notificados de forma explícita al Usuario. <br>
										<br>
										Esta Política de Privacidad y de Cookies fue actualizada el día 24 de febrero 2021 para adaptarse al Reglamento (UE) 2016/679 del Parlamento Europeo y del Consejo, de 27 de abril de 2016, relativo a la protección de las personas físicas en lo que respecta al tratamiento de datos personales y a la libre circulación de estos datos (RGPD) y a la Ley Orgánica 3/2018, de 5 de diciembre, de Protección de Datos Personales y garantía de los derechos digitales. <br>
										<br>
								</p>
								<img class="cerrar" style="z-index: 110;" src="/img/ico_cerrar.png" alt="cerrar" onclick="document.getElementById('Politica').className='popup off';">            
						</div>
				</div>
    </body>
</html>
END;
}
?>