<?php
// 12/3/21 Version inicial completa

if ($IDU>0 || $IDC>0) {
	header('Location: /', true, 302);
} else {
	$aviso='';
	$campos='';
	$mensaje='';
	$passid=0;
	$paso=0;
	$actualizado=false;

	if (is_numeric($_POST['passid'])) $passid=intval($_POST['passid']);
	if (is_numeric($_POST['paso'])) $paso=intval($_POST['paso']);
	if ($paso<1 || $paso>4) $paso=1;

	
	// Validación de datos y avance al paso siguiente
	if ($paso==1 && $_POST['usr']!='') {
		if (preg_match('/^[-\w\.%+]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,125}[a-zA-Z]{2,63}$/', $_POST['usr']) && preg_match('/^(?=.*?[a-zA-Z])(?=.*?[^a-zA-Z]).{6,16}$/', $_POST['pass']) && $_POST['pass']==$_POST['pass2']) {
				$res=$conn->query("SELECT * FROM (SELECT email FROM clientes UNION ALL SELECT email FROM usuarios) AS CT1 WHERE email='{$conn->real_escape_string($_POST['usr'])}'");
				if ($res->num_rows>0) $aviso=popupaviso("Ya existe un cliente registrado con ese correo electrónico");
				else {
					$conn->query("INSERT INTO codigos (pass, ip) VALUES ('{$conn->real_escape_string($_POST['pass'])}', '{$conn->real_escape_string($_SERVER['REMOTE_ADDR'])}');");
					$passid = $conn->insert_id;
					if ($passid>0) $paso=2; else $aviso=popupaviso("Ha ocurrido un error inesperado y deberás intentarlo más tarde");
				}
				$res->Close();		
		} else $aviso=popupaviso("Debes insertar un correo electrónico y una contraseña válidos");
		
	} elseif ($paso==2) {
		if (preg_match('/^[67][0-9]{8}$/', $_POST['movil']) && $_POST['nombre']!='' && $_POST['apellidos']!='') {
				$res=$conn->query("SELECT * FROM (SELECT telefono FROM clientes UNION ALL SELECT telefono FROM usuarios) AS CT1 WHERE telefono='{$conn->real_escape_string($_POST['movil'])}'");
				if ($res->num_rows>0) $aviso=popupaviso("Ya existe un cliente registrado con ese teléfono");
				else {
					$conn->query("UPDATE codigos SET movil='{$conn->real_escape_string($_POST['movil'])}' WHERE id=$passid AND movil='';");
					if ($conn->affected_rows>0) $paso=3; else $aviso=popupaviso("Ha ocurrido un error inesperado y deberás intentarlo más tarde");
				}
				$res->Close();		
		} else $aviso=popupaviso("Debes insertar tu nombre, apellidos y teléfono móvil");
	} elseif ($paso==3) {
		if ($_POST['aceptacion']=='SI') {
				$res=$conn->query("SELECT * FROM (SELECT email, telefono FROM clientes UNION ALL SELECT email, telefono FROM usuarios) AS CT1 WHERE email='{$conn->real_escape_string($_POST['usr'])}' OR telefono='{$conn->real_escape_string($_POST['movil'])}'");
				if ($res->num_rows>0) {
					$aviso=popupaviso("Se acaba de registrar un cliente con ese correo electrónico y/o teléfono");
					$paso=1;
				} else {
					$codigo=substr('000000'.strval(random_int(1 ,999999)),-6);
					$conn->query("UPDATE codigos SET codigo='$codigo' WHERE id=$passid AND movil='{$conn->real_escape_string($_POST['movil'])}' AND codigo='';");
					if ($conn->affected_rows>0) {
						if ($resultado=file_get_contents("https://apisms.anescu.net/?usr=giga1929&pass=XXXXXX&from=CLYB&to={$_POST['movil']}&msg=Tu%20codigo%20de%20verificacion%20para%20activar%20tu%20cuenta%20es%20el%20$codigo.%20Si%20no%20lo%20has%20solicitado%20o%20deseas%20ayuda%20puedes%20llamarnos%20al%20911239057")) {
								if (substr($resultado,0,7)!='Enviado') $aviso=popupaviso("Hubo un problema al crear la cuenta y deberás intentarlo pasados unos minutos");
								else {
									$paso=4;
									$mensaje='Introduce ahora el código de verificación de 6 dígitos que has recibido por SMS';
								}
						} else $aviso=popupaviso("Hubo un problema al crear la cuenta y deberás intentarlo pasados unos minutos");
					} else $aviso=popupaviso("Ha ocurrido un error inesperado y deberás intentarlo más tarde");
				}
				$res->Close();		
		} else $aviso=popupaviso("Debes aceptar los términos y condiciones");
	} elseif ($paso==4) {
		$res=$conn->query("SELECT *, IF(CURRENT_TIMESTAMP<DATE_ADD(fecha, INTERVAL 1 HOUR),false,true) AS caducado FROM codigos WHERE id=$passid AND movil='{$conn->real_escape_string($_POST['movil'])}';");
		if ($row = $res->fetch_assoc()) {
			$conn->query("UPDATE codigos SET intentos=intentos+1 WHERE id=$passid AND movil='{$conn->real_escape_string($_POST['movil'])}';");
			if ($row['intentos']>=5 || $row['caducado']) {
				$aviso=popupaviso("El código de verificación ha caducado y no puede ser validado");
			} elseif ($_POST['codigo']==$row['codigo']) {
				if (preg_match('/^[-\w\.%+]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,125}[a-zA-Z]{2,63}$/', $_POST['usr']) && preg_match('/^[67][0-9]{8}$/', $_POST['movil']) && $_POST['nombre']!='' && $_POST['apellidos']!='') {
					$res2=$conn->query("SELECT * FROM (SELECT email, telefono FROM clientes UNION ALL SELECT email, telefono FROM usuarios) AS CT1 WHERE email='{$conn->real_escape_string($_POST['usr'])}' OR telefono='{$conn->real_escape_string($_POST['movil'])}'");
					if ($res2->num_rows>0) {
						$aviso=popupaviso("Se acaba de registrar un cliente con ese correo electrónico y/o teléfono");
						$paso=1;
					} else {
						$conn->query("INSERT INTO clientes (nombre, apellidos, email, telefono, clave, negocio, direccion, cp) VALUES ('{$conn->real_escape_string($_POST['nombre'])}', '{$conn->real_escape_string($_POST['apellidos'])}', '{$conn->real_escape_string($_POST['usr'])}', '{$conn->real_escape_string($_POST['movil'])}', '{$conn->real_escape_string($row['pass'])}', '{$conn->real_escape_string($_POST['empresa'])}', '{$conn->real_escape_string($_POST['direccion'])}', '{$conn->real_escape_string($_POST['cp'])}');");
						$idcliente = $conn->insert_id;
						if ($idcliente>0) {
							$paso=5;
							$negocio=$_POST['nombre'].' '.$_POST['apellidos']+' ('+$_POST['movil']+')';				
							$conn->query("INSERT INTO logs (valor1, valor2, texto1, texto2, ip) VALUES (1, $idcliente, 'Alta cliente', '{$conn->real_escape_string($negocio)}', '{$conn->real_escape_string($_SERVER['REMOTE_ADDR'])}');");
						} else  {
							$paso=1;
							$aviso=popupaviso("Hubo un problema al crear la cuenta y deberás intentarlo pasados unos minutos");
						}
					}
					$res2->Close();		
				} else {
					$paso=1;
					$aviso=popupaviso("Hubo un problema al crear la cuenta y deberás intentarlo pasados unos minutos");
				}
			} else $aviso=popupaviso("El código de verificación es incorrecto. Dispones de ".(4-$row['intentos'])." intentos más");
		} else $aviso=popupaviso("El código de verificación no es correcto");
	}




	// Presentación del paso actual	
	if ($_POST['fin']=='OK') {
		header('Location: /acceso', true, 302);
	} elseif ($paso==5) {
		$mensaje='Tu cuenta ya ha sido creada. Ya puedes acceder con tu correo electrónico y contraseña';
		$campos.="<input type=\"hidden\" name=\"fin\" value=\"OK\">".PHP_EOL;		
	} elseif ($paso==4 && $passid>0) {
		$mensaje='Introduce ahora el código de verificación de 6 dígitos que has recibido por SMS';
		$campos.="<input type=\"hidden\" name=\"paso\" value=\"$paso\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"usr\" value=\"{$_POST['usr']}\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"passid\" value=\"$passid\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"nombre\" value=\"{$_POST['nombre']}\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"apellidos\" value=\"{$_POST['apellidos']}\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"movil\" value=\"{$_POST['movil']}\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"empresa\" value=\"{$_POST['empresa']}\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"direccion\" value=\"{$_POST['direccion']}\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"cp\" value=\"{$_POST['cp']}\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"aceptacion\" value=\"SI\">".PHP_EOL;
		$campos.="<input type=\"text\" name=\"codigo\" value=\"{$_POST['codigo']}\" placeholder=\"Código de verificación\" maxlength=\"6\" required pattern=\"^[0-9]{6}$\" oninvalid=\"this.setCustomValidity('Debes introducir el código de verificación que te hemos enviado por SMS')\" onchange=\"this.value=this.value.trim();this.setCustomValidity('');\"><br><br>".PHP_EOL;
	} elseif ($paso==3 && $passid>0) {
		$campos.="<input type=\"hidden\" name=\"paso\" value=\"$paso\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"usr\" value=\"{$_POST['usr']}\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"passid\" value=\"$passid\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"nombre\" value=\"{$_POST['nombre']}\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"apellidos\" value=\"{$_POST['apellidos']}\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"movil\" value=\"{$_POST['movil']}\">".PHP_EOL;
		$campos.="<input type=\"text\" name=\"empresa\" value=\"{$_POST['empresa']}\" placeholder=\"Nombre de la empresa (Opcional)\"><br><br>".PHP_EOL;
		$campos.="<input type=\"text\" name=\"direccion\" value=\"{$_POST['direccion']}\" placeholder=\"Dirección de la empresa (Opcional)\"><br><br>".PHP_EOL;
		$campos.="<input type=\"text\" name=\"cp\" value=\"{$_POST['cp']}\" placeholder=\"Código postal (Opcional)\" maxlength=\"5\" pattern=\"^[0-9]{5}$\" oninvalid=\"this.setCustomValidity('Debe introducir el código postal')\" onchange=\"this.setCustomValidity('')\"><br><br>".PHP_EOL;
		$campos.="<p class=\"texto blanco t10 iz\" style=\"width: 290px; margin: auto;\"><input type=\"checkbox\" style=\"float: left; margin: 4px 10px;\" name=\"aceptacion\" value=\"SI\" required oninvalid=\"this.setCustomValidity('Debes aceptar los términos y condiciones')\" onchange=\"this.setCustomValidity('')\"> He leído y acepto las <a href=\"#\" onclick=\"document.getElementById('Condiciones').className='popup on';return false;\">Condiciones del servicio</a> y la <a href=\"#\" onclick=\"document.getElementById('Politica').className='popup on';return false;\">Política de privacidad</a> de CLYB Company.<br></p><br>".PHP_EOL;
	} elseif ($paso==2 && $passid>0) {
		$campos.="<input type=\"hidden\" name=\"paso\" value=\"$paso\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"usr\" value=\"{$_POST['usr']}\">".PHP_EOL;
		$campos.="<input type=\"hidden\" name=\"passid\" value=\"$passid\">".PHP_EOL;
		$campos.="<input type=\"text\" name=\"nombre\" value=\"{$_POST['nombre']}\" placeholder=\"Nombre\" required oninvalid=\"this.setCustomValidity('Debes introducir tu nombre')\" onchange=\"this.value=this.value.trim();this.setCustomValidity('');\"><br><br>".PHP_EOL;
		$campos.="<input type=\"text\" name=\"apellidos\" value=\"{$_POST['apellidos']}\" placeholder=\"Apellidos\" required oninvalid=\"this.setCustomValidity('Debes introducir tus apellidos')\" onchange=\"this.value=this.value.trim();this.setCustomValidity('');\"><br><br>".PHP_EOL;
		$campos.="<input type=\"text\" name=\"movil\" value=\"{$_POST['movil']}\" placeholder=\"Teléfono movil\" maxlength=\"9\" required pattern=\"^[67][0-9]{8}$\" oninvalid=\"this.setCustomValidity('Debes introducir tu número de teléfono móvil de España')\" onchange=\"this.value=this.value.trim();this.setCustomValidity('');\"><br><br>".PHP_EOL;
	} else {
		$paso=1;
		$campos.="<input type=\"hidden\" name=\"paso\" value=\"$paso\">".PHP_EOL;
		$campos.="<input type=\"text\" name=\"usr\" value=\"{$_POST['usr']}\" placeholder=\"Correo electrónico\" required pattern=\"^[-\w\.%+]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,125}[a-zA-Z]{2,63}$\" oninvalid=\"this.setCustomValidity('Debes introducir tu correo electrónico')\" onchange=\"this.value=this.value.trim();this.setCustomValidity('');\"><br><br>".PHP_EOL;
		$campos.="<input type=\"password\" id=\"pass\" name=\"pass\" placeholder=\"Establece una contraseña\" required pattern=\"^(?=.*?[a-zA-Z])(?=.*?[^a-zA-Z]).{6,16}$\" oninvalid=\"this.setCustomValidity('Debes introducir una contraseña de entre 6 y 16 dígitos con letras y números o símbolos')\" onchange=\"this.value=this.value.trim();this.setCustomValidity('');\"><br><br> ".PHP_EOL;
		$campos.="<input type=\"password\" id=\"pass2\" name=\"pass2\" placeholder=\"Repite tu contraseña\" required pattern=\"^(?=.*?[a-zA-Z])(?=.*?[^a-zA-Z]).{6,16}$\" oninvalid=\"this.setCustomValidity('Debes repetir la misma contraseña')\" onsubmit=\"document.getElementById('pass2').setAttribute('pattern','^'+document.getElementById('pass').value+'$');\" onchange=\"this.value=this.value.trim();this.setCustomValidity('');\"><br><br>".PHP_EOL;
	}

if ($mensaje!='') $mensaje="<p class=\"texto blanco t16\" style=\"width: 320px; margin: auto;\">$mensaje</p>";
if ($paso>0 and $paso<4) $mensaje="<img src=\"/img/estado$paso.png\" width=\"245px\" alt=\"Paso $paso\"><br>";
if ($paso!=3) $literatura=''; else $literatura=<<<END
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
				<img class="cerrar" src="/img/ico_cerrar.png" alt="cerrar" onclick="document.getElementById('Condiciones').className='popup off';">            
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
				<img class="cerrar" src="/img/ico_cerrar.png" alt="cerrar" onclick="document.getElementById('Politica').className='popup off';">            
		</div>
</div>
END;

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
        <p class="texto blanco t40"><br>Bienvenido<br><br></p>
        $mensaje
        <br>
        <form method="post">
						$campos
            <input type="submit" class="boton blanco" value="CONTINUAR"><br>
            <a href="/acceso"><button type="button" class="boton transparente">INICIAR SESIÓN</button></a><br>
        </form>
				$aviso
				$literatura
    </body>
</html>
END;
}
?>