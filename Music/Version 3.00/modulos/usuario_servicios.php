<?php
// 26/2/21 Version inicial de registro
// 1/3/21 Se ordenan los servicios de forma manual


if ($IDC<=0) {
	header('Location: /', true, 302);
} else {
	$idservicio=0;
	$fichas='';

	if (is_numeric($_POST['id'])) $idservicio=intval($_POST['id']);
	
	if ($idservicio>0) {
		$res=$conn->query("SELECT * FROM servicios WHERE id=$idservicio");
		if ($row = $res->fetch_assoc()) {
			$nombreservicio=textoahtml($row['nombre']);
			$resumenservicio=textoahtml($row['resumen']);
			$descipcionservicio=textoahtml($row['descripcion']);
			$version=$row['version'];
		} else $idservicio=0;
		$res->Close();
	}
	
	
	
	if ($idservicio==0) {
		$res=$conn->query("SELECT * FROM servicios ORDER BY orden,nombre");
		$fichas=PHP_EOL;
		while ($row = $res->fetch_assoc())
      $fichas.="<form class=\"ventana servicio\" method=\"post\" onclick=\"this.submit();return false;\"><a href=\"#\" class=\"no\" onclick=\"this.submit();return false;\"><input type=\"hidden\" name=\"id\" value=\"{$row['id']}\"><img src=\"/img/pixel_transparente.png\" style=\"background-image: url('/img/servicio_{$row['id']}.jpg?{$row['version']}')\"><p class=\"texto gris a90 t12 l1 iz s10\"><b>{$row['nombre']}</b></p><p class=\"texto gris a90 t12 l2 iz s05\">{$row['resumen']}</p></a></form>".PHP_EOL;
	}
	
	

	
if ($fichas!='')
$contenido_completo=<<<END
<!DOCTYPE html>
<html>
    <head>
        <title>Clyb App</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <link rel="shortcut icon" type="image/x-icon" href="/icon-normal.png">
        <link rel="stylesheet" href="/estilos.css?$cssversion">
    </head>
    <body class="relleno cuadrado fondo_gris">
        <div class="topbar cuadrado"><a href="/perfil" class="ico_perfil"></a><a class="logo_central"></a><a href="/chat" class="ico_chat"></a></div>   
        <div class="navbar cuadrado"><a href="/" class="iconav inicio">Inicio</a><a href="/servicios" class="iconav servicios on">Servicios</a><a href="/push" class="iconav push">Push</a></div>
				$fichas
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
        <link rel="stylesheet" href="estilos.css?$cssversion">
    </head>
    <body class="relleno servicio fondo_gris">
				<div class="fondo_gris" style="position:fixed; top: 0; left: 0; width: 100vw; height: 150px;"></div>
        <div class="filtro_sombra"><div class="topbar elipse" style="background-image: url(/img/servicio_$idservicio.jpg?$version);">
            <img>
            <a href="/servicios" class="ico_atrasw"></a>
            <a class="logo_centralw"></a>
            <a href="/chat" class="ico_chatw"></a>
            <p class="texto t20 blanco l1 pex1">$nombreservicio</p>
        </div></div>
        <div class="navbar cuadrado"><a href="/" class="iconav inicio">Inicio</a><a href="/servicios" class="iconav servicios on">Servicios</a><a href="/push" class="iconav push">Push</a></div>
        <p class="texto azul t16 a90 s10">$resumenservicio</p>
        <p class="texto t16 a90 s10 js">$descipcionservicio</p>
        <form method="post" action="/chat" onclick="this.submit();return false;">
            <input type="hidden" name="enviar" value="Deseo que me preparéis un presupuesto de $nombreservicio">
            <input type="hidden" name="preparar" value="Lo que necesito es: ">
            <a href="#" class="no boton azul presupuesto texto t20 l1" onclick="this.submit();return false;">PEDIR PRESUPUESTO</a>
        </form>
    </body>
</html>
END;
}
?>