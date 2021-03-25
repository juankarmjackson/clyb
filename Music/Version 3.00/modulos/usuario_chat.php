﻿<?php
// 12/3/21 Version inicial completa

if ($IDC<=0) {
	header('Location: /', true, 302);
} else {
	$conversacion='';
	
	if (trim($_POST['enviar'])!='')
		$conn->query("INSERT INTO chat (de, texto) VALUES ($IDC, '{$conn->real_escape_string(trim($_POST['enviar']))}');");	

	$conversacion.="<div class=\"bocadillo azul\"><img src=\"/img/pixel_transparente.png\">Cuéntanos lo que quieras sobre tu negocio, el producto en el que estás interesado o pregúntanos por la información que necesites. Nuestro equipo contestará a la mayor brevedad posible.</div>".PHP_EOL;
	
	$res=$conn->query("SELECT * FROM chat WHERE de=$IDC OR para=$IDC ORDER BY fecha, id");
	while ($row = $res->fetch_assoc()) {
		$texto=textoahtml($row['texto']);
		if ($row['de']==$IDC)
			$conversacion.="<div class=\"bocadillo blanco\"><img src=\"/img/pixel_transparente.png\">$texto</div>".PHP_EOL;
		else
			$conversacion.="<div class=\"bocadillo azul\"><img src=\"/img/pixel_transparente.png\">$texto</div>".PHP_EOL;
	}
	$res->close();

	$preparar='';
	$estadoavion=' disabled';
	if (trim($_POST['preparar'])!='') {
		$preparar=trim($_POST['preparar']);
		$estadoavion='';
	}

$contenido_completo=<<<END
<!DOCTYPE html>
<html>
    <head>
        <title>Clyb App</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <link rel="shortcut icon" type="image/x-icon" href="/icon-normal.png">
        <link rel="stylesheet" href="/estilos.css?$cssversion">
    </head>
     <body class="relleno chat fondo_gris" onload="window.setTimeout(function() {window.scrollTo(0,document.body.scrollHeight);},500);">
        <div class="topbar cuadrado">
            <a href="/" class="ico_atras"></a>
            <a href="tel:911239057" class="ico_tel"></a>
            <a class="logo_iz"></a>
            <p class="texto t20 iz l1 pex2">CLYB Company</p>
            <p class="texto t10 iz l1 pex3"><img src="/img/ico_estado_activo.png" width="10" height="10"> Activo</p>
        </div>   
				$conversacion
        <form class="navbar chat" method="post">
            <div class="marco"><textarea name="enviar" onkeyup="if (this.value.trim()=='') document.getElementById('enviar').disabled=true; else document.getElementById('enviar').disabled=false;" required>$preparar</textarea></div>            
            <input id="enviar" type="submit" value=""$estadoavion>
        </form>
    </body>
</html>				
END;
}
?>