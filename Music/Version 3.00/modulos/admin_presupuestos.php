<?php
// 12/3/21 Version inicial completa


if ($IDU>0 && $admin) {
	$tituloseccion='Presupuestos';
	$idcliente=0;
	$vistapresupuesto=false;
	$sinedicion='';
	$idp=intval($_POST["id2"]);
	if ($_POST['accion']=='autoform' && is_numeric($_POST['id'])) $idcliente=intval($_POST['id']);
	if ($_POST['op']=="Borrar" && $idcliente>0) {
		$idp=intval($_POST["id2"]);
		$conn->query("DELETE FROM presupuestos WHERE idc=$idcliente AND id=$idp AND fechacontratacion<'2020-01-01' AND activo;");
		if ($conn->affected_rows>0) $conn->query("DELETE FROM presupuestos_conceptos WHERE idp=$idp;");
		$idp=0;
	}
	if ($_POST['op']=="Guardar" && $idcliente>0) {
		$idp=intval($_POST["id2"]);
		$tmp_servicio=intval($_POST["servicio"]);
		$tmp_alternativo=trim($_POST["alternativo"]);
		$tmp_texto_superior=trim($_POST["texto_superior"]);
		$tmp_texto_inferior=trim($_POST["texto_inferior"]);
		if ($idp>0) {
			$conn->query("UPDATE presupuestos SET ids=$tmp_servicio, alternativo='{$conn->real_escape_string($tmp_alternativo)}', texto_superior='{$conn->real_escape_string($tmp_texto_superior)}', texto_inferior='{$conn->real_escape_string($tmp_texto_inferior)}', fechacreacion=CURRENT_TIMESTAMP WHERE idc=$idcliente AND id=$idp AND fechacontratacion<'2020-01-01' AND activo;");
			if ($conn->affected_rows<=0) $idp=0;
		} else {
			$conn->query("INSERT INTO presupuestos (ids, alternativo, texto_superior, texto_inferior, idc, fechacreacion) VALUES($tmp_servicio, '{$conn->real_escape_string($tmp_alternativo)}', '{$conn->real_escape_string($tmp_texto_superior)}', '{$conn->real_escape_string($tmp_texto_inferior)}', $idcliente, CURRENT_TIMESTAMP);");
			$idp = $conn->insert_id;
		}
		if (is_numeric($_POST['conceptos'])) $totalservicios=intval($_POST['conceptos']); else $totalservicios=0;
		if ($idp>0 && $totalservicios>0) {
			$importe_fijo=0;
			$importe_mensual=0;
			for ($i=0;$i<$totalservicios;$i++) {
				$idconcepto=intval($_POST["idconcepto$i"]);
				$concepto=trim($_POST["concepto$i"]);
				$importe=doubleval($_POST["importe$i"]);
				$mensual=intval($_POST["mensual$i"]);
				if ($idconcepto>0) {
					if ($concepto!='') {
						$conn->query("UPDATE presupuestos_conceptos SET concepto='{$conn->real_escape_string($concepto)}', importe=$importe, mensual=$mensual WHERE idp=$idp AND id=$idconcepto;");
						if ($mensual==0) $importe_fijo+=$importe; else $importe_mensual+=$importe;
					} else $conn->query("DELETE FROM presupuestos_conceptos WHERE idp=$idp AND id=$idconcepto;");
				} elseif ($concepto!='') {
					$conn->query("INSERT INTO presupuestos_conceptos (concepto, importe, mensual, idp) VALUES ('{$conn->real_escape_string($concepto)}', $importe, $mensual, $idp);");
					if ($mensual==0) $importe_fijo+=$importe; else $importe_mensual+=$importe;
				}
			}
			$conn->query("UPDATE presupuestos SET importe_fijo=$importe_fijo, importe_mensual=$importe_mensual  WHERE idc=$idcliente AND id=$idp AND fechacontratacion<'2020-01-01' AND activo;");
			$vistapresupuesto=true;
		}
	}
	if ($_POST['op']=="presupuesto" || $vistapresupuesto) {
		$vistapresupuesto=true;
		if ($idp>0) {
			$res=$conn->query("SELECT *,IF(fechacontratacion>'2020-01-01',' disabled','') AS sinedicion FROM presupuestos WHERE id=$idp AND idc=$idcliente AND activo;");
			if ($row = $res->fetch_assoc()) {
				$tmp_servicio=intval($row["ids"]);
				$tmp_alternativo=trim($row["alternativo"]);
				$tmp_texto_superior=trim($row["texto_superior"]);
				$tmp_texto_inferior=trim($row["texto_inferior"]);	
				$sinedicion=$row["sinedicion"];
			} else $vistapresupuesto=false;			
		}		
	}

	if ($vistapresupuesto) {
		$informacion="<div class='bloquelinea'></div>".PHP_EOL;
		if ($idp>0)
			$informacion.="<div class='bloquelinea'><b>Edicion de presupuesto</b></div>".PHP_EOL;
		else
			$informacion.="<div class='bloquelinea'><b>Nuevo presupuesto</b></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		$informacion.="<form method='post'>".PHP_EOL;
		$informacion.="<div class='bloquelinea' style='height: 32px;'>Servicio ofertado: ".PHP_EOL;
		$informacion.="<select name='servicio' style='height: 24px;'$sinedicion>".PHP_EOL;
		$res=$conn->query("SELECT * FROM servicios ORDER BY orden, nombre");
		while ($row = $res->fetch_assoc()) {
			if ($tmp_servicio==$row['id'])
				$informacion.="<option value='{$row['id']}' selected>{$row['nombre']}</option>>".PHP_EOL;
			else
				$informacion.="<option value='{$row['id']}'>{$row['nombre']}</option>>".PHP_EOL;
		}
		$res->Close();
		$informacion.="</select></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'>Titulo alternativo:</div>".PHP_EOL;
		$informacion.="<div class='bloquealto'><input type='text' style='width: 99%; height: 24px;' name='alternativo' value='$tmp_alternativo' $sinedicion></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'>Texto superior:</div>".PHP_EOL;
		$informacion.="<div class='bloquealto'><textarea style='width: 99%; height: 60px;' name='texto_superior'$sinedicion>$tmp_texto_superior</textarea></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'>Conceptos:</div>".PHP_EOL;
		$contador=0;
		$res=$conn->query("SELECT * FROM presupuestos_conceptos WHERE idp=$idp ORDER BY id");
		while ($row = $res->fetch_assoc()) {
			$concepto=$row['concepto'];
			$importe=$row['importe'];
			if ($row['mensual']) $mensual=' selected'; else $mensual='';
			$informacion.="<div class='bloquelinea' style='height: 32px;'><input type='text' name='concepto$contador' value='$concepto' style='width: 70%; height: 24px;'$sinedicion> <input type='text' name='importe$contador' value='$importe' style='width: 12%; height: 24px;'$sinedicion> <select name='mensual$contador' style='width: 12%; height: 24px;'$sinedicion><option value='0'>€</option><option value='1'$mensual>€/mes</option></select><input type='hidden' name='idconcepto$contador' value='{$row['id']}'></div>".PHP_EOL;
			$contador++;
		}
		$res->Close();
		if ($sinedicion=='')
			for ($i=0;$i<3;$i++) {
				$concepto='';
				$importe='';
				$mensual='';
				$informacion.="<div class='bloquelinea' style='height: 32px;'><input type='text' name='concepto$contador' value='$concepto' style='width: 70%; height: 24px;'> <input type='text' name='importe$contador' value='$importe' style='width: 12%; height: 24px;'> <select name='mensual$contador' style='width: 12%; height: 24px;'><option value='0'>€</option><option value='1'$mensual>€/mes</option></select><input type='hidden' name='idconcepto$contador' value='0'></div>".PHP_EOL;
				$contador++;
			}
		$informacion.="<input type='hidden' name='conceptos' value='$contador'>".PHP_EOL;
		$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'>Texto inferior:</div>".PHP_EOL;
		$informacion.="<div class='bloquealto'><textarea style='width: 99%; height: 60px;' name='texto_inferior'$sinedicion>$tmp_texto_inferior</textarea></div>".PHP_EOL;
		$informacion.="<input type='hidden' name='id' value='$idcliente'>".PHP_EOL;
		$informacion.="<input type='hidden' name='id2' value='$idp'>".PHP_EOL;
		$informacion.="<input type='hidden' name='accion' value='autoform'>".PHP_EOL;
		$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		if ($sinedicion=='')
			$informacion.="<br><br><input type='submit' name='op' value='Guardar'> <input type='submit' onclick=\"return confirm('¿Seguro que desea borrar?');\" name='op' value='Borrar'> <input type='submit' name='op' value='Salir'><br><br>".PHP_EOL;
		else
			$informacion.="<br><br><input type='submit' name='op' value='Salir'><br><br>".PHP_EOL;
		$informacion.="</form>".PHP_EOL;
	}	elseif ($idcliente>0) {
		$informacion='';
		$res=$conn->query("SELECT * FROM clientes WHERE id=$idcliente");
		if ($row = $res->fetch_assoc()) {
			$informacion.="<div class='bloquelinea'><b>{$row['nombre']} {$row['apellidos']} ({$row['telefono']})</b></div>".PHP_EOL;
			$informacion.="<div class='bloquelinea'>Correo electrónico: {$row['email']}</div>".PHP_EOL;
			$informacion.="<div class='bloquelinea'>Empresa: {$row['negocio']}</div>".PHP_EOL;
			$informacion.="<div class='bloquelinea'>Dirección: {$row['direccion']}</div>".PHP_EOL;
			$informacion.="<div class='bloquelinea'>CP: {$row['cp']}</div>".PHP_EOL;
			$informacion.="<div class='bloquelinea'>Tipo de negocio: {$row['tiponegocio']}</div>".PHP_EOL;
			$informacion.="<div class='bloquelinea'>CIF: {$row['cif']}</div>".PHP_EOL;
			$informacion.="<div class='bloquelinea'>Web: {$row['web']}</div>".PHP_EOL;
			$informacion.="<div class='bloquelinea'>Instagram: {$row['instagram']}</div>".PHP_EOL;
			$informacion.="<div class='bloquelinea'>Facebook: {$row['facebook']}</div>".PHP_EOL;
			$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		}
		$res->Close();
		
		
		
		
		$informacion.="<div class='bloquelinea'><b>Presupuestos</b><img src='img/add.png' class='clickable' dato-id='$idcliente' dato-op='presupuesto'></div>".PHP_EOL;
		$res=$conn->query("SELECT presupuestos.*, servicios.nombre, IF(fechacancelacion>'2020-01-01','Cancelado',IF(fechacontratacion>'2020-01-01','Contratado',IF(fechacreacion>DATE_SUB(CURRENT_DATE, INTERVAL $caducidadppto DAY),'Pendiente','Caducado'))) AS estado FROM presupuestos LEFT JOIN servicios ON servicios.id=presupuestos.ids WHERE presupuestos.activo AND presupuestos.idc=$idcliente ORDER BY fechacontratacion DESC, fechacancelacion DESC, fechacreacion DESC");
		while ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			if ($row['alternativo']!='')
				$tmp_nombre="{$row['alternativo']} ({$row['estado']})";
			else
				$tmp_nombre="{$row['nombre']} ({$row['estado']})";
			if ($row['estado']=='Pendiente') $tmp_icono='pencil'; else $tmp_icono='eye';
			$informacion.="<div class='bloquelinea resaltar clickable' dato-id='$idcliente' dato-id2='$tmp_id' dato-op='presupuesto'>$tmp_nombre <img src='img/$tmp_icono.png'></div>".PHP_EOL;
		}
		$res->Close(); 
	} else {
		$informacion="<div class='bloquelinea'><b>Clientes</b></div>".PHP_EOL;
		$res=$conn->query("SELECT * FROM clientes ORDER BY nombre, apellidos, telefono");
		while ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre="{$row['nombre']} {$row['apellidos']} ({$row['telefono']})";
			$informacion.="<div class='bloquelinea resaltar clickable' dato-id='$tmp_id' dato-op='cliente'>$tmp_nombre <img src='img/cart.png'></div>".PHP_EOL;
		}
		$res->Close(); 
	}
	
	
	
	
}
?>