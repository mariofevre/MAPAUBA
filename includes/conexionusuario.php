<?php 
  
$query = "
 SELECT `usuarios`.`id`,
    `usuarios`.`nombre`,
    `usuarios`.`apellido`,
    `usuarios`.`organizacion`,
    `usuarios`.`pass`,
    `usuarios`.`nacimiento`,
    `usuarios`.`mail`,
    `usuarios`.`telefono`,
    `usuarios`.`log`,
    `usuarios`.`nivel`,
    `usuarios`.`zz_AUTOFECHACREACION`
FROM `MAPAUBA`.`usuarios`
WHERE id='".$_SESSION['USUARIOID']."'
";
	
$ConUsu = mysql_query($query,$Conec1);
echo mysql_error($Conec1);
while($row = mysql_fetch_assoc($ConUsu)){
$USUARIO=$row;
}

/* AUN NO SE ENCUENTRAN ACTIVAS LAS FNCIONES DE MÚLTIPLES PROCESOS NINIVELES DE ACCESO DIFERENCIADOS 
$query="SELECT * FROM paneles LEFT JOIN accesos ON accesos.id_paneles = paneles.id WHERE paneles.id = '$PanelI' AND accesos.id_usuario='$UsuarioI'";
$ConPan = mysql($Base, $query, $_SESSION['panelcontrol']->Conec1);/* buscar el panel activo */
/*if(mysql_num_rows($ConPan)<1){header('Location: ./listado.php');}/* seguridad: si no hay accesos habilitados para este usuario en el panel bloquea el acceso */	
/*
$PanelN = mysql_result($ConPan,0,'Nombre');	/* nombre del panel activo */
/*$PanelD = mysql_result($ConPan,0,'Descripcion'); /* descripción del panel activo */
/*$_SESSION['PanelI'] = $PanelI;
*/

?>
