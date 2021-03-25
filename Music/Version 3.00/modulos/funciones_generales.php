<?php
// 12/3/21 Version inicial completa

function crearfactura ($IDC,$tmp_conceptos,$tmp_precios,$tmp_total,$idp=0) {
	$conn=$GLOBALS['conn'];
	$tmp_serie='FS'.date('y');
	$res=$conn->query("SELECT IFNULL(MAX(numero)+1,1) AS siguiente FROM facturas WHERE serie='$tmp_serie';");
	$row = $res->fetch_assoc();
	$tmp_numero=intval($row['siguiente']);
	$res->close();
	$conn->query("INSERT INTO facturas (idc, idp, serie, numero, fecha, importe) VALUES ($IDC, $idp, '$tmp_serie', $tmp_numero, CURRENT_DATE, $tmp_total);");
	$tmp_idfactura=$conn->insert_id;
	for ($i=0;$i<count($tmp_conceptos);$i++)
		$conn->query("INSERT INTO facturas_conceptos (idf, concepto, importe) VALUES ($tmp_idfactura, '{$conn->real_escape_string($tmp_conceptos[$i])}', {$tmp_precios[$i]});");
}

function textoahtml($texto) {
	$resultado=str_replace("<","&lt;",$resultado);
	$resultado=str_replace(">","&gt;",$resultado);
	$resultado=str_replace("\r\n","<br>",$texto);
	$resultado=str_replace("\n\r","<br>",$resultado);
	$resultado=str_replace("\n","<br>",$resultado);
	$resultado=str_replace("\r","<br>",$resultado);
	return $resultado;
}
	
function popupaviso($textoaviso, $icono='ico_alerta.png') {
	return 	<<<END
<div id="Aviso" class="popup on">
		<div class="variable">
				<img src="/img/$icono" width="50" height="50"><br>
				<br>
				<p class="texto t16">$textoaviso</p>
				<img class="cerrar" src="/img/ico_cerrar.png" alt="cerrar" onclick="document.getElementById('Aviso').className='popup off';">            
		</div>
</div>
END;	
}
?>