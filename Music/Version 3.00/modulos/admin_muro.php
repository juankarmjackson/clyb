<?php
// 12/3/21 Version inicial completa

if ($IDU>0 && $admin) {
	$tituloseccion='Muro';
	$tmp_edit=false;
	
	$tmp_id=intval($_POST['id']);	
	if ($_POST['accion']=='autoform' && $_POST['op']=='subir' && $tmp_id>0) {
		$conn->query("UPDATE muro SET orden=orden-3 WHERE id=$tmp_id;");
	} elseif ($_POST['accion']=='autoform' && $_POST['op']=='bajar' && $tmp_id>0) {
		$conn->query("UPDATE muro SET orden=orden+3 WHERE id=$tmp_id;");
	} elseif ($_POST['accion']=='autoform' && $_POST['op']=='borrar' && $tmp_id>0) {
		$conn->query("UPDATE muro SET activo=0 WHERE id=$tmp_id;");
	}
	
	if ($_POST['accion']=='edit') {
		$tmp_autor_nombre=trim($_POST['autor_nombre']);
		$tmp_titulo=trim($_POST['titulo']);
		$tmp_subtitulo=trim($_POST['subtitulo']);
		$tmp_descripcion=trim($_POST['descripcion']);
		$tmp_video=trim($_POST['video']);
		$tmp_servicioid=intval($_POST['servicioid']);
		$tmp_nfoto=intval($_POST['nfoto']);
		$tmp_nautor_ico=intval($_POST['nautor_ico']);
		
		if (is_uploaded_file($_FILES['foto']['tmp_name']))
			if (strtolower(substr($_FILES['foto']['name'],-4))=='.jpg' || strtolower(substr($_FILES['foto']['name'],-4))=='.png' || strtolower(substr($_FILES['foto']['name'],-5))=='.jpeg' || strtolower(substr($_FILES['foto']['name'],-4))=='.bmp' || strtolower(substr($_FILES['foto']['name'],-4))=='.gif')
					$tmp_foto=true;
			else {
				$tmp_foto=false;
				$tmp_error='Las fotografías deben tener formato JPG, JPEG, PNG, BMP o GIF';				
			}
		else
			$tmp_foto=false;
		if (is_uploaded_file($_FILES['autor_ico']['tmp_name']))
			if (strtolower(substr($_FILES['autor_ico']['name'],-4))=='.jpg' || strtolower(substr($_FILES['autor_ico']['name'],-4))=='.png' || strtolower(substr($_FILES['autor_ico']['name'],-5))=='.jpeg' || strtolower(substr($_FILES['autor_ico']['name'],-4))=='.bmp' || strtolower(substr($_FILES['autor_ico']['name'],-4))=='.gif')
					$tmp_autor_ico=true;
			else {
				$tmp_autor_ico=false;
				$tmp_error='Las fotografías deben tener formato JPG, JPEG, PNG, BMP o GIF';				
			}
		else
			$tmp_autor_ico=false;
		if ($tmp_error!='') $tmp_guardar=false; else $tmp_guardar=true;
		if ($tmp_guardar && $tmp_autor_nombre=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar un autor';
		} elseif ($tmp_guardar && $tmp_titulo=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar un titulo';
		} elseif ($tmp_guardar && $tmp_subtitulo=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar un subtitulo';
		} elseif ($tmp_guardar && $tmp_descripcion=='') {
			$tmp_guardar=false;
			$tmp_edit=true;
			$tmp_error='Debe insertar una descripción';
		} elseif ($tmp_guardar && $tmp_id>0) {
			$conn->query("UPDATE muro SET autor_nombre='{$conn->real_escape_string($tmp_autor_nombre)}', titulo='{$conn->real_escape_string($tmp_titulo)}', subtitulo='{$conn->real_escape_string($tmp_subtitulo)}', descripcion='{$conn->real_escape_string($tmp_descripcion)}', video='{$conn->real_escape_string($tmp_video)}', servicioid=$tmp_servicioid WHERE id=$tmp_id");
			if ($tmp_autor_ico) $conn->query("UPDATE muro SET autor_ico=autor_ico+1 WHERE id=$tmp_id");
			if ($tmp_foto) $conn->query("UPDATE muro SET foto=foto+1 WHERE id=$tmp_id");
		}	elseif ($tmp_guardar) {
			if ($tmp_autor_ico) $tmp_autor_ico=1; else $tmp_autor_ico=0;
			if ($tmp_foto) $tmp_foto=1; else $tmp_foto=0;
			$conn->query("INSERT INTO muro (autor_ico, autor_nombre, titulo, subtitulo, descripcion, foto, video, servicioid) VALUES ($tmp_autor_ico,'{$conn->real_escape_string($tmp_autor_nombre)}', '{$conn->real_escape_string($tmp_titulo)}', '{$conn->real_escape_string($tmp_subtitulo)}', '{$conn->real_escape_string($tmp_descripcion)}', $tmp_foto, '{$conn->real_escape_string($tmp_video)}', $tmp_servicioid)");
			$tmp_id = $conn->insert_id;
		} else {
			$tmp_edit=true;
		}
		if ($tmp_guardar && !$tmp_edit && $tmp_id>0) {
			if (is_uploaded_file($_FILES['autor_ico']['tmp_name'])) {
				if (strtolower(substr($_FILES['autor_ico']['name'],-4))=='.jpg') {
					file_put_contents("img/autor_$tmp_id.jpg", file_get_contents($_FILES['autor_ico']['tmp_name']));
				} else {
					$image=false;
					if (strtolower(substr($_FILES['autor_ico']['name'],-4))=='.png')
						$image=@imagecreatefrompng($_FILES['autor_ico']['tmp_name']);
					elseif (strtolower(substr($_FILES['autor_ico']['name'],-5))=='.jpeg')
						$image=@imagecreatefromjpeg($_FILES['autor_ico']['tmp_name']);
					elseif (strtolower(substr($_FILES['autor_ico']['name'],-4))=='.bmp')
						$image=@imagecreatefrombmp($_FILES['autor_ico']['tmp_name']);
					elseif (strtolower(substr($_FILES['autor_ico']['name'],-4))=='.gif')
						$image=@imagecreatefromgif($_FILES['autor_ico']['tmp_name']);
					else file_put_contents("img/autor_{$tmp_id}_{$_FILES['autor_ico']['name']}", file_get_contents($_FILES['autor_ico']['tmp_name']));
					if ($image!=false) {
						imagejpeg($image, "img/autor_{$tmp_id}.jpg");
						imagedestroy($image);
					}
				}
			}
			if (is_uploaded_file($_FILES['foto']['tmp_name'])) {
				if (strtolower(substr($_FILES['foto']['name'],-4))=='.jpg') {
					file_put_contents("img/muro_$tmp_id.jpg", file_get_contents($_FILES['foto']['tmp_name']));
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
					else file_put_contents("img/muro_{$tmp_id}_{$_FILES['foto']['name']}", file_get_contents($_FILES['foto']['tmp_name']));
					if ($image!=false) {
						imagejpeg($image, "img/muro_{$tmp_id}.jpg");
						imagedestroy($image);
					}
				}
			}
			header("Location: $seccion", true, 302);			
		}
		
	}	elseif ($_POST['accion']=='autoform' && $_POST['op']=="edit") {
		$tmp_id=intval($_POST['id']);
		$res=$conn->query("SELECT * FROM muro WHERE id=$tmp_id");
		if ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_nautor_ico=$row['autor_ico'];
			$tmp_autor_nombre=$row['autor_nombre'];
			$tmp_titulo=$row['titulo'];
			$tmp_subtitulo=$row['subtitulo'];
			$tmp_descripcion=$row['descripcion'];
			$tmp_nfoto=$row['foto'];
			$tmp_video=$row['video'];
			$tmp_servicioid=$row['servicioid'];
			$tmp_edit=true;			
		}
		$res->Close();
	}	elseif ($_POST['accion']=='autoform' && $_POST['op']=="add") {
		$tmp_id=0;
		$tmp_titulo='';
		$tmp_edit=true;			
	}
	if ($tmp_edit) {
		$tmp_descripcion=str_replace('<','&lt;',$tmp_descripcion);
		$tmp_descripcion=str_replace('>','&gt;',$tmp_descripcion);
		$tmp_titulop=($tmp_id>0) ? 'Editar publicación' : 'Nueva publicación';
		$informacion="<div class='bloquelinea'><b>$tmp_titulop</b></div>".PHP_EOL;
		$informacion.="<div class='bloquelinea'></div>".PHP_EOL;
		$informacion.="<form method='post' id='formdatos' enctype='multipart/form-data'>".PHP_EOL;
		$informacion.="<input type='hidden' name='id' value='$tmp_id'>".PHP_EOL;
		$informacion.="<input type='hidden' name='nfoto' value='$tmp_nfoto'>".PHP_EOL;
		$informacion.="<input type='hidden' name='nautor_ico' value='$tmp_nautor_ico'>".PHP_EOL;
		$informacion.="<input type='hidden' name='accion' value='edit'>".PHP_EOL;
		if ($tmp_error!='') $informacion.="<span style='color:red'>$tmp_error</span><br><br>".PHP_EOL;
		$informacion.="Autor:<br>".PHP_EOL;
		$informacion.="<input type='text' name='autor_nombre' value='$tmp_autor_nombre'><br>".PHP_EOL;
		$informacion.="Foto del autor:<br>".PHP_EOL;
		if ($tmp_nautor_ico>0) $informacion.="<img src='/img/autor_{$tmp_id}.jpg?$tmp_nautor_ico' style='max-width: 50vw; max-height: 20vh;'><br>Cambiar foto:<br>".PHP_EOL;
		$informacion.="<input type='file' name='autor_ico'><br><br><br>".PHP_EOL;

		$informacion.="Titulo:<br>".PHP_EOL;
		$informacion.="<input type='text' name='titulo' value='$tmp_titulo'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Subtitulo:<br>".PHP_EOL;
		$informacion.="<input type='text' name='subtitulo' value='$tmp_subtitulo'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Descripción:<br>".PHP_EOL;
		$informacion.="<textarea name='descripcion'>$tmp_descripcion</textarea><br>".PHP_EOL;
		$informacion.="<br><br>".PHP_EOL;
		
		$informacion.="Foto:<br>".PHP_EOL;
		if ($tmp_nfoto>0) $informacion.="<img src='/img/muro_$tmp_id.jpg?$tmp_nfoto' style='max-width: 50vw; max-height: 20vh;'><br><br>Cambiar foto:<br>".PHP_EOL;
		$informacion.="<input type='file' name='foto'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		$informacion.="Video (Referencia YouTube):<br>".PHP_EOL;
		$informacion.="<input type='text' name='video' value='$tmp_video'><br>".PHP_EOL;
		$informacion.="<br>".PHP_EOL;
		
		
		$informacion.="Servicio asociado:<br>".PHP_EOL;
		$informacion.="<select name='servicioid'>".PHP_EOL;
		$informacion.="<option value='0'>Ninguno</option>>".PHP_EOL;
		$res=$conn->query("SELECT * FROM servicios ORDER BY orden, nombre");
		while ($row = $res->fetch_assoc()) {
			if ($tmp_servicioid==$row['id'])
				$informacion.="<option value='{$row['id']}' selected>{$row['nombre']}</option>>".PHP_EOL;
			else
				$informacion.="<option value='{$row['id']}'>{$row['nombre']}</option>>".PHP_EOL;
		}
		$res->Close();
		$informacion.="</select>".PHP_EOL;
		$informacion.="<br><br>".PHP_EOL;

		
		$informacion.="<button>Guardar</button> <button onclick='window.location.href=\"$seccion\";return false;'>Cancelar</button><br><br>".PHP_EOL;
		$informacion.="</form>".PHP_EOL;
	}	else {
		$informacion="<div class='bloquelinea'><b>Publicaciones del muro</b><img src='img/add.png' class='clickable' dato-id='0' dato-op='add'></div>".PHP_EOL;
		$res=$conn->query("SELECT * FROM muro WHERE activo ORDER BY orden, id");
		$orden=2;
		while ($row = $res->fetch_assoc()) {
			$tmp_id=$row['id'];
			$tmp_titulo=$row['titulo'];
			if ($row['orden']!=$orden) $conn->query("UPDATE muro SET orden=$orden WHERE id=$tmp_id;");
			$informacion.="<div class='bloquelinea resaltar'>$tmp_titulo<img src='img/pencil.png' class='clickable' dato-id='$tmp_id' dato-op='edit'><img onclick=\"if (window.confirm('¿Seguro que desea borrar?')) this.setAttribute('dato-op','borrar');\" src='img/bin.png' class='clickable' dato-id='$tmp_id' dato-op='borrar0'>";
			if ($orden>2) $informacion.="<img src='img/arrow_up.png' class='clickable' dato-id='$tmp_id' dato-op='subir'>";
			if ($orden<2*$res->num_rows) $informacion.="<img src='img/arrow_down.png' class='clickable' dato-id='$tmp_id' dato-op='bajar'>";
			$informacion.="</div>".PHP_EOL;
			$orden+=2;
		}
		$res->Close(); 
	}
}
?>