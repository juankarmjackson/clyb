<?php
// 12/3/21 Version inicial completa
	
if ($IDU>0 && $admin) {
	$tituloseccion='Servicios';
	$tmp_edit=false;
	$tmp_id=intval($_POST['id']);
	if ($_POST['accion']=='autoform' && $_POST['op']=='subir' && $tmp_id>0) {
		$conn->query("UPDATE servicios SET orden=orden-3 WHERE id=$tmp_id;");
	} elseif ($_POST['accion']=='autoform' && $_POST['op']=='bajar' && $tmp_id>0) {
		$conn->query("UPDATE servicios SET orden=orden+3 WHERE id=$tmp_id;");
	}
	
	if ($_POST['accion']=='edit') {
		$tmp_id=intval($_POST['id']);
		$tmp_nombre=trim($_POST['nombre']);
		$tmp_resumen=trim($_POST['resumen']);
		$tmp_descripcion=trim($_POST['descripcion']);
		if (is_uploaded_file($_FILES['fichero']['tmp_name']))
			if (strtolower(substr($_FILES['fichero']['name'],-4))=='.jpg' || strtolower(substr($_FILES['fichero']['name'],-4))=='.png' || strtolower(substr($_FILES['fichero']['name'],-5))=='.jpeg' || strtolower(substr($_FILES['fichero']['name'],-4))=='.bmp' || strtolower(substr($_FILES['fichero']['name'],-4))=='.gif') $tmp_foto=true;
			else {
				$tmp_foto=false;
				$tmp_error='Las fotografías deben tener formato JPG, JPEG, PNG, BMP o GIF';				
			}
		else
			$tmp_foto=false;
		$res=$conn->query("SELECT * FROM servicios WHERE nombre='{$conn->real_escape_string($tmp_nombre)}' AND id<>$tmp_id");
		$tmp_guardar=($res->num_rows>0) ? false : true;
		$res->Close();
		if ($tmp_guardar && $tmp_nombre=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar un nombre';
		} elseif ($tmp_guardar && $tmp_resumen=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar un resumen';
		} elseif ($tmp_guardar && $tmp_descripcion=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar una descripción';
		} elseif ($tmp_guardar && !$tmp_foto && $tmp_id==0) {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar una foto en formato JPG';
		} elseif ($tmp_guardar && $tmp_error!='') {
			$tmp_guardar=false;
			$tmp_edit=true;
		} elseif ($tmp_guardar && $tmp_id>0) {
			$conn->query("UPDATE servicios SET version=version+1, nombre='{$conn->real_escape_string($tmp_nombre)}', resumen='{$conn->real_escape_string($tmp_resumen)}', descripcion='{$conn->real_escape_string($tmp_descripcion)}' WHERE id=$tmp_id");
			if ($tmp_foto) {
				if (strtolower(substr($_FILES['fichero']['name'],-4))=='.jpg') {
					file_put_contents("img/servicio_$tmp_id.jpg", file_get_contents($_FILES['fichero']['tmp_name']));
				} else {
					$image=false;
					if (strtolower(substr($_FILES['fichero']['name'],-4))=='.png')
						$image=@imagecreatefrompng($_FILES['fichero']['tmp_name']);
					elseif (strtolower(substr($_FILES['fichero']['name'],-5))=='.jpeg')
						$image=@imagecreatefromjpeg($_FILES['fichero']['tmp_name']);
					elseif (strtolower(substr($_FILES['fichero']['name'],-4))=='.bmp')
						$image=@imagecreatefrombmp($_FILES['fichero']['tmp_name']);
					elseif (strtolower(substr($_FILES['fichero']['name'],-4))=='.gif')
						$image=@imagecreatefromgif($_FILES['fichero']['tmp_name']);
					else file_put_contents("img/servicio_{$tmp_id}_{$_FILES['fichero']['name']}", file_get_contents($_FILES['fichero']['tmp_name']));
					if ($image!=false) {
						imagejpeg($image, "img/servicio_{$tmp_id}.jpg");
						imagedestroy($image);
					}
				}
			}
			header("Location: $seccion", true, 301);			
		}	elseif ($tmp_guardar) {			
			$conn->query("INSERT INTO servicios (id, nombre, resumen, descripcion) VALUES (NULL, '{$conn->real_escape_string($tmp_nombre)}', '{$conn->real_escape_string($tmp_resumen)}', '{$conn->real_escape_string($tmp_descripcion)}')");
			$tmp_id = $conn->insert_id;
			if ($tmp_foto) file_put_contents("img/servicio_$tmp_id.jpg", file_get_contents($_FILES['fichero']['tmp_name']));				
			header("Location: $seccion", true, 301);
		} else {
			$tmp_edit=true;
			$tmp_error='Ya existe un servicio con este nombre';
		}
	}	elseif ($_POST['accion']=='autoform' && $_POST['op']=="edit") {
		$tmp_id=intval($_POST['id']);
		$res=$conn->query("SELECT * FROM servicios WHERE id=$tmp_id");
		if ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			$tmp_resumen=$row['resumen'];
			$tmp_descripcion=$row['descripcion'];
			$tmp_version=$row['version'];
			$tmp_edit=true;			
		}
		$res->Close();
	}	elseif ($_POST['accion']=='autoform' && $_POST['op']=="add") {
		$tmp_id=0;
		$tmp_nombre='';
		$tmp_edit=true;			
	}
	if ($tmp_edit) {
		$tmp_descripcion=str_replace('<','&lt;',$tmp_descripcion);
		$tmp_descripcion=str_replace('>','&gt;',$tmp_descripcion);
		$tmp_titulo=($tmp_id>0) ? 'Editar servicio' : 'Nuevo servicio';
		$informacion="<div class='bloquelinea'><b>$tmp_titulo</b></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		$informacion.="<form method='post' id='formdatos' enctype='multipart/form-data'>".PHP_EOL;
		$informacion.="<input type='hidden' name='id' value='$tmp_id'>".PHP_EOL;
		$informacion.="<input type='hidden' name='accion' value='edit'>".PHP_EOL;
		if ($tmp_error!='') $informacion.="<span style='color:red'>$tmp_error</span><br><br>".PHP_EOL;
		$informacion.="Nombre del servicio:<br>".PHP_EOL;
		$informacion.="<input type='text' name='nombre' maxlength='20' value='$tmp_nombre'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Resumen del servicio:<br>".PHP_EOL;
		$informacion.="<input type='text' name='resumen' maxlength='250' value='$tmp_resumen'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Descripción del servicio:<br>".PHP_EOL;
		$informacion.="<textarea name='descripcion'>$tmp_descripcion</textarea><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		if ($tmp_id>0) $informacion.="<img src='/img/servicio_$tmp_id.jpg?$tmp_version' style='max-width: 50vw; max-height: 20vh;'><br><br>Cambiar foto:<br>".PHP_EOL;
		else $informacion.="Cargar foto:<br>".PHP_EOL;
		$informacion.="<input type='file' name='fichero'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="<button>Guardar</button> <button onclick='window.location.href=\"$seccion\";return false;'>Cancelar</button><br><br>".PHP_EOL;
		$informacion.="</form>".PHP_EOL;
	}	else {
		$informacion="<div class='bloquelinea'><b>Servicios ofrecidos</b><img src='img/add.png' class='clickable' dato-id='0' dato-op='add'></div>".PHP_EOL;
		$res=$conn->query("SELECT * FROM servicios ORDER BY orden, nombre");
		$orden=2;
		while ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nombre=$row['nombre'];
			if ($row['orden']!=$orden) $conn->query("UPDATE servicios SET orden=$orden WHERE id=$tmp_id;");
			$informacion.="<div class='bloquelinea resaltar'>$tmp_nombre<img src='img/pencil.png' class='clickable' dato-id='$tmp_id' dato-op='edit'>";
			if ($orden>2) $informacion.="<img src='img/arrow_up.png' class='clickable' dato-id='$tmp_id' dato-op='subir'>";
			if ($orden<2*$res->num_rows) $informacion.="<img src='img/arrow_down.png' class='clickable' dato-id='$tmp_id' dato-op='bajar'>";
			$informacion.="</div>".PHP_EOL;
			$orden+=2;
		}
		$res->Close(); 
	}
}
?>