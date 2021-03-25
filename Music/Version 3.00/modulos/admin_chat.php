<?php
// 26/2/21 Version inicial


if ($IDU>0 && $admin) {
	$tituloseccion='Chat';
	$idcliente=0;
	if (is_numeric($_POST['id'])) $idcliente=intval($_POST['id']);
	
	if ($idcliente==0) {
		$informacion="<div class='bloquelinea'><b>Clientes</b></div>".PHP_EOL;
		$res=$conn->query("SELECT clientes.id, clientes.nombre, clientes.apellidos, clientes.negocio, clientes.telefono, MAX(fecha) AS ULTI FROM clientes LEFT JOIN chat ON clientes.id=de+para GROUP BY clientes.id ORDER BY ULTI DESC, clientes.id DESC");
		while ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre="{$row['nombre']} {$row['apellidos']} ({$row['telefono']})";
			$informacion.="<div class='bloquelinea resaltar clickable' dato-id='$tmp_id' dato-op='edit'>$tmp_nombre <img src='img/chat.png'></div>".PHP_EOL;
		}
		$res->Close(); 
	}	else {



		if (trim($_POST['enviar'])!='')
			$conn->query("INSERT INTO chat (para, texto) VALUES ($idcliente, '{$conn->real_escape_string(trim($_POST['enviar']))}');");	

		$conversacion.="<div class=\"bocadillo blanco\"><img src=\"/img/pixel_transparente.png\">Cuéntanos lo que quieras sobre tu negocio, el producto en el que estás interesado o pregúntanos por la información que necesites. Nuestro equipo contestará a la mayor brevedad posible.</div>".PHP_EOL;
		
		$res=$conn->query("SELECT * FROM chat WHERE de=$idcliente OR para=$idcliente ORDER BY fecha, id");
		while ($row = $res->fetch_assoc()) {
			$texto=textoahtml($row['texto']);
			if ($row['para']==$idcliente)
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
            <a href="/chat" class="ico_atras"></a>
            <a href="tel:911239057" class="ico_tel"></a>
            <a class="logo_iz"></a>
            <p class="texto t20 iz l1 pex2">CLYB Company</p>
            <p class="texto t10 iz l1 pex3"><img src="/img/ico_estado_activo.png" width="10" height="10"> Activo</p>
        </div>   
				$conversacion
        <form class="navbar chat" method="post">
						<input type="hidden" name="id" value="$idcliente">
            <div class="marco"><textarea name="enviar" onkeyup="if (this.value.trim()=='') document.getElementById('enviar').disabled=true; else document.getElementById('enviar').disabled=false;" required>$preparar</textarea></div>            
            <input id="enviar" type="submit" value=""$estadoavion>
        </form>
    </body>
</html>				
END;
	}
}
?>