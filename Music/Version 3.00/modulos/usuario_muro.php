<?php
// 12/3/21 Version inicial completa

if ($IDC<=0) {
	header('Location: /', true, 302);
} else {
	$fichas='';

	$res=$conn->query("SELECT * FROM muro WHERE activo ORDER BY orden,id");
	$fichas=PHP_EOL;
	while ($row = $res->fetch_assoc()) {
		if ($row['autor_ico']>0) $autor_ico="autor_{$row['id']}.jpg?{$row['autor_ico']}"; else $autor_ico="icon.png";
		if ($row['foto']>0) $foto="<img src=\"/img/pixel_transparente.png\" class=\"foto\" style=\"background-image: url('/img/muro_{$row['id']}.jpg?{$row['foto']}')\">"; else $foto="";
		if ($row['video']!='') $video="<iframe width=\"100%\" height=\"250px\" src=\"https://www.youtube-nocookie.com/embed/{$row['video']}?controls=1\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>"; else $video="";
		if ($row['servicioid']>0) $boton="<form method=\"post\" action=\"/servicios\"><input type=\"hidden\" name=\"id\" value=\"{$row['servicioid']}\"><br><input type=\"submit\" class=\"boton azul\" value=\"VER SERVICIO\"><br></form>"; else $boton="";
		$tmp_autor_nombre=textoahtml($row['autor_nombre']);
		$tmp_titulo=textoahtml($row['titulo']);
		$tmp_subtitulo=textoahtml($row['subtitulo']);
		$tmp_descripcion=textoahtml($row['descripcion']);
		$fichas.="<div class=\"ventana muro\" method=\"post\" action=\"/servicios\" onclick=\"this.submit();return false;\"><p class=\"texto gris t16 iz l1 pex4\"><img src=\"/img/pixel_transparente.png\" class=\"logo_iz2\" style=\"background-image: url('/img/$autor_ico');\"> $tmp_autor_nombre</p>{$foto}{$video}<p class=\"texto negro t16 l1 iz s10 pex5\"><b>$tmp_titulo</b></p><p class=\"texto grisclaro t10 l1 iz pex5\">$tmp_subtitulo</p><p class=\"texto gris t14 iz pex5 s05\">$tmp_descripcion</p>$boton<br></div>".PHP_EOL;
	}
	$res->close();
	
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
        <div class="navbar cuadrado"><a href="/" class="iconav inicio on">Inicio</a><a href="/servicios" class="iconav servicios">Servicios</a><a href="/push" class="iconav push">Push</a></div>
				$fichas
    </body>
</html>
END;
}
?>