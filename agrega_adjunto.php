<?php
/**
* agrega_adjunto.php
*
* aplicaci�n para guradar y registrar en la base correspondiente un archivo subido por el usuario
 * 
 *  
* @package    	Plataforma Colectiva de Informaci�n Territorial: UBATIC2014
* @subpackage 	actividad
* @author     	Universidad de Buenos Aires
* @author     	<mario@trecc.com.ar>
* @author    	http://www.uba.ar/
* @author    	http://www.trecc.com.ar/recursos/proyectoubatic2014.htm
* @author		based on TReCC SA Procesos Participativos Urbanos, development. www.trecc.com.ar/recursos
* @copyright	2015 Universidad de Buenos Aires
* @copyright	esta aplicaci�n se desarrollo sobre una publicaci�n GNU 2014 TReCC SA
* @license    	https://www.gnu.org/licenses/agpl-3.0-standalone.html GNU AFFERO GENERAL PUBLIC LICENSE, version 3 (agpl-3.0)
* Este archivo es parte de TReCC(tm) paneldecontrol y de sus proyectos hermanos: baseobra(tm), TReCC(tm) intraTReCC  y TReCC(tm) Procesos Participativos Urbanos.
* Este archivo es software libre: tu puedes redistriburlo 
* y/o modificarlo bajo los t�rminos de la "GNU AFero General Public License version 3" 
* publicada por la Free Software Foundation
* 
* Este archivo es distribuido por si mismo y dentro de sus proyectos 
* con el objetivo de ser �til, eficiente, predecible y transparente
* pero SIN NIGUNA GARANT�A; sin siquiera la garant�a impl�cita de
* CAPACIDAD DE MERCANTILIZACI�N o utilidad para un prop�sito particular.
* Consulte la "GNU General Public License" para m�s detalles.
* 
* Si usted no cuenta con una copia de dicha licencia puede encontrarla aqu�: <http://www.gnu.org/licenses/>.
*/





include('./includes/conexion.php');
include('./includes/conexionusuario.php');
include("./includes/fechas.php");
include("./includes/cadenas.php");

include("./includes/meta.php");
echo '
	<link href="css/mapauba.css" rel="stylesheet" type="text/css">	
	<style type="text/css">
		body{
		    overflow:hidden;
			margin:0px;
			font-size:9px;
		}
	</style>
';

$UsuarioI = $_SESSION['USUARIOID'];
if($UsuarioI<1){
	echo "error en la conexxion de ususario, abortando.";
	break;
}

$query="
	SELECT `ACTaccesos`.`id`,
	    `ACTaccesos`.`id_actividades`,
	    `ACTaccesos`.`id_usuarios`,
	    `ACTaccesos`.`nivel`
	FROM `MAPAUBA`.`ACTaccesos`
	WHERE id_actividades='".$_POST['actividad']."'
";
$Consulta = mysql_query($query,$Conec1);
echo mysql_error($Conec1);


while($row=mysql_fetch_assoc($Consulta)){
	$Accesos[$row['id_usuarios']]=$row['nivel'];
}

/*
if($Accesos[$UsuarioI]<1){
	//header('location: ./actividades.php');	//este usuario deber definir una actividad habilitada
	echo "ERROR de Acceso 2";
	break;
}*/

//echo "hola";

$nombre = $_FILES['upload']['name'];
$b = explode(".",$nombre);
$ext = strtolower($b[(count($b)-1)]);
$carpeta = str_pad($_POST['actividad'], 8, '0', STR_PAD_LEFT)."/";
echo $carpeta;
$path='./documentos/';
if(!file_exists($path)){
	echo "creando carpeta $path";mkdir($path, 0777, true);chmod($path, 0777);	
}
$path .= 'actividades/';
if(!file_exists($path)){
	echo "creando carpeta $path";mkdir($path, 0777, true);chmod($path, 0777);	
}
$path .= $carpeta;
if(!file_exists($path)){
	echo "creando carpeta $path";mkdir($path, 0777, true);chmod($path, 0777);	
}

$nuevonombre= $path."u".str_pad($UsuarioI, 6, '0', STR_PAD_LEFT)."f".date("Y-m-d-H-i-s").".".$ext;

		$extVal['jpg']='1';
		$extVal['png']='1';
		$extVal['tif']='1';
		$extVal['bmp']='1';
		$extVal['gif']='1';
		$extVal['pdf']='1';
		$extVal['zip']='1';
		
		
		if(!isset($extVal[strtolower($ext)])){
			echo"solo se aceptan los formatos:";
			foreach($extVal as $k => $v){echo" $k,";}
			break;
		}

		if (!copy($_FILES['upload']['tmp_name'], $nuevonombre)) {
		    echo "Error al copiar $nuevonombre";
		}else{
			echo "archivo guardado. ";			
			$query="
			INSERT INTO 
				`MAPAUBA`.`FILEadjuntos`
				SET
				`nombre`='$nombre',
				`ruta`='$nuevonombre',
				`fecha`='".date("Y-m-d")."',
				`hora`='".date("H-i-s")."',
				`usuario`='$UsuarioI',
				`actividad`=".$_POST['actividad']."				
			";
			
			$Consulta = mysql_query($query,$Conec1);
			$NID=mysql_insert_id($Conec1);
			if($NID>0){
			echo "registro guardado. ";
			
			echo "
				<script type='text/javascript'>
					//console.log('hola');
					parent.document.getElementById('adjunto').setAttribute('src','$nuevonombre');
					parent.document.getElementById('link').value='$nuevonombre';				
				</script>
			";	
			}else{
				echo "no pudo guardare el registro, puede que el documento permanezca inaccesible o sea eliminado. ";
				
			}
		}
		echo "
				<script type='text/javascript'>
					console.log('no pudo guardare el registro, puede que el documento permanezca inaccesible o sea eliminado. ');
					//parent.document.getElementById('cargaimagen').style.width='200';
				</script>";		


print_r($_FILES['upload']);
	
?>
