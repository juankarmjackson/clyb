<?php
// 12/3/21 Version inicial completa

if ($IDC<=0) {
	header('Location: /', true, 302);
} else {
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
        <div class="filtro_sombra"><div class="topbar elipse fondo_degradado">
            <a href="/perfil" class="ico_perfil2"></a>
            <a class="logo_centralw"></a>
            <a href="/chat" class="ico_chatw"></a>
            <p class="texto t20 blanco l1 pex1">PUSH</p>
        </div></div>
        <div class="navbar cuadrado"><a href="/" class="iconav inicio">Inicio</a><a href="/servicios" class="iconav servicios">Servicios</a><a href="/push" class="iconav push on">Push</a></div>
				
        <div class="ventana muro">
            <img src="/img/ico_QR.png" class="logo_iz2" style="margin: 5px;">
            
            <img src="/img/pixel_transparente.png" class="foto" style="background-image: url('/img/push_0.jpg')">
            <p class="texto gris t16 l1 ce s10"><b>NOTIFICACIONES PUSH</b></p>
            <p class="texto gris t14 a90 ce s10">Tu mejor publicidad es un cliente satisfecho y con ganas de repetir, ¿Te atreves a preguntárselo?<br><br>Posiciónate como uno de los mejores.<br><br></p>
        </div>

        
				
        <form method="post" action="/chat" onclick="this.submit();return false;">
            <input type="hidden" name="enviar" value="Deseo que me preparéis un presupuesto de Push">
            <input type="hidden" name="preparar" value="Lo que necesito es: ">
            <a href="#" class="no boton azul presupuesto texto t20 l1" onclick="this.submit();return false;">PEDIR PRESUPUESTO</a>
        </form>
    </body>
</html>
END;
}
?>