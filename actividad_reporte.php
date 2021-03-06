<?php 
/**
* actividad.php
*
* aplicaci�n para cargar nuevos puntos de relevamiento
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
* @copyright	esta aplicaci�n se desarrollo sobre una publicaci�n GNU (agpl) 2014 TReCC SA
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

//if($_SERVER[SERVER_ADDR]=='192.168.0.252')ini_set('display_errors', '1');ini_set('display_startup_errors', '1');ini_set('suhosin.disable.display_errors','0'); error_reporting(-1);

// verificaci�n de seguridad 
include('./includes/conexion.php');
include('./includes/conexionusuario.php');

// funciones frecuentes
include("./includes/fechas.php");
include("./includes/cadenas.php");

$UsuarioI = $_SESSION['USUARIOID'];
if($UsuarioI==""){
	$e=explode('/',__FILE__);
	$f=$e[(count($e)-1)];
	$dest="DEST=$f";
	foreach($_GET as $kg => $Vg){
		$dest.='&'.$kg.'='.$Vg;		
	}
	header('Location: ./login.php?'.$dest);
}

// funci�n de consulta de actividades a la base de datos 
include("./actividades_consulta.php");

$ID = isset($_GET['actividad'])?$_GET['actividad'] : '';
$RID = isset($_GET['registro'])?$_GET['registro'] : '';

$Hoy_a = date("Y");
$Hoy_m = date("m");	
$Hoy_d = date("d");
$HOY = $Hoy_a."-".$Hoy_m."-".$Hoy_d;	

// define si se esta defininedo una argumentaci�n, de no ser as� esta aplicaci�n solo devuelve un mensaje escrito
if(isset($_POST['actividad'])){
	$Actividad=$_POST['actividad'];
	$ACCION = $_POST['accion'];
}elseif(isset($_GET['actividad'])){
	$Actividad=$_GET['actividad'];
}else{
	$Actividad='';
}
if($Actividad==''){
	header('location: ./actividades.php');	//si no hay una actividad definida esta p�gina no deber�a consultarse
	echo "ERROR de Acceso 1";
	break;
}


$UsuariosList = usuariosconsulta($ID);

// el reingreso a esta direcci�n desde su propio formulario php crea o modifica un registro de actividad 
if(isset($_POST['accion'])){
	$accion =$_POST['accion'];
	
	if($accion=='crear'){
		$query="
		INSERT INTO 
			`MAPAUBA`.`actividades`
			SET
			`zz_AUTOUSUARIOCREAC`='".$UsuarioI."'
		";
		mysql_query($query,$Conec1);
		$NID=mysql_insert_id($Conec1);
		if($NID!=''){
			$ID=$NID;
		}else{
			$mensaje="<div class='error'>no se ha podido crear el nuevo registro, por favor vuelva a intentar}";
		}
	}	
}


// medicion de rendimiento lamp 
$starttime = microtime(true);

// filtro de representaci�n restringe documentos visulazados, no altera datos estadistitico y de agregaci�n 
$FILTRO=isset($_GET['filtro'])?$_GET['filtro']:'';	

// filtro temporal de representaci�n restringe documentos visulazados, no altera datos estadistitico y de agregaci�n 	
$fechadesde_a=isset($_GET['fechadesde_a'])?str_pad($_GET['fechadesde_a'], 4, "0", STR_PAD_LEFT):'0000';
$fechadesde_m=isset($_GET['fechadesde_m'])?str_pad($_GET['fechadesde_m'], 2, "0", STR_PAD_LEFT):'00';
$fechadesde_d=isset($_GET['fechadesde_d'])?str_pad($_GET['fechadesde_d'], 2, "0", STR_PAD_LEFT):'00';
if($fechadesde_a!='0000'&&$fechadesde_m!='00'&&$fechadesde_d!='00'){
	$FILTROFECHAD=$fechadesde_a."-".$fechadesde_m."-".$fechadesde_d;
}else{
	$FILTROFECHAD='';
}
$fechahasta_a=isset($_GET['fechahasta_a'])?str_pad($_GET['fechahasta_a'], 4, "0", STR_PAD_LEFT):'0000';
$fechahasta_m=isset($_GET['fechahasta_m'])?str_pad($_GET['fechahasta_m'], 2, "0", STR_PAD_LEFT):'00';
$fechahasta_d=isset($_GET['fechahasta_d'])?str_pad($_GET['fechahasta_d'], 2, "0", STR_PAD_LEFT):'00';
if($fechahasta_a!='0000'&&$fechahasta_m!='00'&&$fechahasta_d!='00'){
	$FILTROFECHAH=$fechahasta_a."-".$fechahasta_m."-".$fechahasta_d;
}else{	
	$FILTROFECHAH='';
}

$seleccion['zoom'] = 0;

// funci�n para obtener listado formateado html de actividades 
$Contenido =  reset(actividadesconsulta($ID,$seleccion));
//echo "<pre>";print_r($Contenido);echo "</pre>";


	foreach($Contenido['Acc'][2] as $acc => $accdata){
		if($accdata['id_usuarios']==$UsuarioI){
			$Coordinacion='activa';
		}
	}
	foreach($Contenido['Acc'][3] as $acc => $accdata){
		if($accdata['id_usuarios']==$UsuarioI){
			$Administracion='activa';
			$Coordinacion='activa';
		}
	}

$Actividad=reset(actividadesconsulta($ID,$seleccion));
//echo "<pre>";print_r($Actividad);echo "</pre>";
if($Actividad['zz_PUBLICO']!='1'&&$Actividad['zz_AUTOUSUARIOCREAC']!=$UsuarioI){
	echo "<h2>Error en el acceso, esta actividad no se encuentra a�n publicada y usted no se encuentra registrado como autor de la misma.</h2>";
	break;
}


if($RID>0){
	$Registro=$Actividad['GEO'][$RID];
	//print_r($Registro);
	if($Registro['id_usuarios']==$UsuarioI){
		$Accion='cambia';
		$Valores=$Registro;
		$ValoresRef=$Registro;
		$AccionTx='Guardar cambios';
	}else{
		$Accion='ver';
		$ValoresRef=$Registro;
		$AccionTx='';			
	}
}else{
	$Accion='crear';
	$AccionTx='Guardar punto';
}
?>

	<title>MAPAUBA - �rea de Trabajo</title>
	<?php include("./includes/meta.php");?>
	<link href="css/treccppu.css" rel="stylesheet" type="text/css">
	<link href="css/mapauba.css" rel="stylesheet" type="text/css">	
	
	
  <link rel="stylesheet" href="./js/jquery-ui-1.11.4.custom/jquery-ui.css">
  <script src="./js/jquery-ui-1.11.4.custom/external/jquery/jquery.js"></script>
  <script src="./js/jquery-ui-1.11.4.custom/jquery-ui.js"></script>
  <link rel="stylesheet" href="./js/jquery-ui-1.11.4.custom/jquery-ui.css">
  <style>
.ui-slider .ui-slider-handle {
    width: 0.9em;
}

span.ui-slider-handle:hover {
    background-color:#007fff;
    border-color:#003eff;
}


.ui-slider-vertical .ui-slider-handle {
    left: -0.4em;
}
.ui-slider-vertical {
    width: 0.4em;
}
.ui-widget {
    font-size: 0.6em;
}
  </style>
  <script>
 
  $(function() {
    $( "#slider-vertical" ).slider({
      orientation: "vertical",
      min: 0,
      max: 100,
      value: 100,
      slide: function( event, ui ) {
      	     _listado= document.getElementById('puntosdeactividad');	
      /*_porc = 100 * _listado.scrollHeight / (_listado.scrollHeight-_listado.clientHeight); 
      
      px= ;*/
     	_ch=_listado.clientHeight;
     	_sh=_listado.scrollHeight-_ch;
        $( "#puntosdeactividad" ).scrollTop( (_sh/100)*(100-ui.value) );
        console.log((_sh/100)*(100-ui.value));
      }
    });
    //$( "#puntosdeactividad" ).scrollTop( ($( "#puntosdeactividad" ).scrollHeight/100)*(100-slider( "value" )) );
  });

	

  </script>
  	
	<style type='text/css'>
	a{
		cursor:pointer;
	}
		.dato.fecha{
		    width: 60px;
		}
		.dato.autor{
		    width: 90px;
		}		
		.dato.descripcion{
		    width: 180px;
		}		
		
		.dato.localizaciones, .dato.imagenes{
			font-size: 11px;
		}
		
		.elemento {
		    background-color: #ADD8E6;
		    border: 2px solid #08AFD9;
		    cursor: pointer;
		    display: inline-block;
		    font-size: 10px;
		    height: 14px;
		    overflow: hidden;
		    padding: 2px 1px;
		    position: relative;
		    width: 16px;
		 }
		 
		
		li{
			border:2px solid transparent;
		}
		
		li:hover div.punto{
			border-color:blue;
		}
		
		
		label, .aclaracion{			
		    display: inline-block;
		    font-weight: normal;
		    margin-right: 2px;
		    text-align: right;
		    vertical-align: middle;
		    width: 200px;
		}
		
		label.lon{
			width:50px;
		}
		
		label{
			 height: 26px;
		}
		.aclaracion{	
		 	text-align: left;
		 	margin:2px;
		 	border:none;		
		}
		
		.formulario{
			border:2px solid #f55;
			background-color:#ffb;
			margin: 10px;
			display:inline-block;
			position:relative;
			width:750px;
		}
		.formulario form{
			margin: 0px;
		}
				
		.formulario input,.formulario select{
			color: #f55;
			background-color:#ffb;
			vertical-align: middle;
			margin-top:2px;
			margin-bottom:2px;
		}	
		.formulario label,.formulario .aclaracion{
			color: #f55;
		}	
		
		.formulario.vista{
			border-color:#55f;
		}
		.formulario.vista, .formulario.vista input,.formulario.vista select,.formulario.vista label{
			color: #000;
			background-color:#abf;
		}			

		.vista #adjunto{
			border-color:#55f;
		}
				
		.formulario > span{
			min-height:26px;
			vertical-align: middle;
			display:inline-block;
			color:#55f;
		}
		
		form#adjuntador{
		    left: 442px;
		    position: absolute;
		    top: 27px;
		}		
				
			input#link{
				width: 236px;
			}		
				
		#inputsubmit{
			position:absolute;
			top:0px;
			right:0px;
			width:100px;
		}	
		#elimina,#elim,#elimNo{
			position:absolute;
			top:22px;
			right:0px;
			width:100px;
		}		
		#elimina{
			position:absolute;
			display:none;
			top:42px;
			background-color:red;
			color:#fff;
		}			
		
		#texto_parent{
			display: inline-block;
   			vertical-align: middle;
		}
		div#texto_parent{
		    border: 1px solid #f55;
		    font-size: 12px;
		    height: 100px;
		    overflow-y: auto;
		    padding: 5px;
		    width: 530px;
		}
		
		.formulario.vista div#texto_parent {
			border-color:#55f;
			color:#55f;
		}
		#texto_parent>p{
			font-size: 12px;
			text-align:justify;
		}
		
		#tinymce{
			background-color:#ffb;
		}
		.resumen{
			font-size:15px;
			font-weight:normal;
		}
		
		#uploadinput{
			width:90px;
		}
		
		#inputcatgorianueva{
			width:236px;
		}
		
		a.botonamarcar{
			position:absolute;
			top:-22px;
			right:-2px;
			border-width:2px;
			border-style:solid;
		}	
		#slider { margin: 10px; }	

	#formCoord{
		
	}
	
	#formCoord input{
		right:4px;
		border: 2px solid red;
		position:absolute;
		background-color: #ffb;
    	color: #f55;
    	font-size:12px;
    	height: 15px;
    	line-height: 9px;
    	width:103px;
    	padding: 0;
	} 
	
	
	#bloqPu{
		top:2px;
	}
	#bloqPuNo{
		top:2px;
		display:none;
	}
	input#bloqTx{
		top:21px;
		display:none;
		border-color: #008 #88f #88f #008;
	    border-width: 1px;	    
	}
	
	#bloqTxL{
		top:25px;
		right:108px;
	    display: none;
	    position:absolute;
	    font-size:12px;
	    color: #f55;
	}	
	#bloqTxVer{
		top:25px;
		right:4px;
		width:200px;
	    display: none;
	    position:absolute;
	    font-size:12px;
	    color: #f55;
	    text-align:right;
	}		
	#bloq{
		top:40px;
		display:none;
	}
	#bloqudescricion{
		z-index:10;
	    background-color: #fcc;
	    border: 1px solid red;
	    font-size: 12px;
	    padding: 2px;
	    position: absolute;
	    right: 0;
	    top: 67px;
	    width: 200px;
	}
	#bloqudescricion>p{
		font-size:inherit;
	}
	#adjunto{
		left: 540px;
	    position: absolute;
	    top: 1px;
	}
	div.punto{
		display:inline-block;
		width:10px;
		height:10px;
		border-radius:5px;
		border-width:2px;
		border-style:solid;
		margin:10px;
	}
	div.registro{
		border-top:3px solid #000;
		margin:8px;
		margin-top:28px;
	}
	div.registro h2{
		margin:8px;
		font-size:15px;
	}
	div.registro img{
		height:100px;
	}
	@media print {	
			
	   div.registro{page-break-inside: avoid;}
	   body{
	   	background-color:#fff;
	   	background-image:none;
	   }
	   div#recuadro1{
	   	display:none;
	   }
	}
			
	</style>

</head>

<body>
	
	<?php
	include('./includes/encabezado.php');
	?>	
	<div id="pageborde"><div id="page">
		<h1>Actividad: <span class='resumen'><?php echo $Actividad['resumen'];?></span></h1>
		<?php

		echo " / <span class='menor'>web de acceso directo: <span class='resaltado'>http://190.111.246.33/MAPAUBA/actividad.php?actividad=$ID</span></span>";	
		if($RID>0){$cons='verpuntos';}else{$cons='marcarpuntos';}	
		
		//echo"<pre>";print_r($Actividad);echo"</pre>";
		// formulario para agregar una nueva actividad		
		if($ID==''){
				echo "la actividad no fue llamada correctamnte";
		}else{
			
			echo "<p>".$Actividad['consigna']."</p>";
							
			echo "<p>".$Actividad['marco']."</p>";
			
			echo "<p>".$Actividad['objeto']."</p>";
			
			if($Actividad['hasta']<=$HOY&&$Actividad['hasta']>'0000-00-00'){
				$Stat='cerrada';
			}else{
				$Stat='abierta';
			}
					
			echo "<p>Estado de la actividad: ".$Actividad['consigna']."</p>";
			//echo "<pre>";print_r($Actividad);echo "</pre>";	
			//echo "<pre>";print_r($Actividad['GEO']);echo "</pre>";		
			foreach($Actividad['GEO'] as $idp => $valp){
				echo "<div class='registro'>";
				echo "<h1>id: $idp </h1>";
				echo "<h2>por: ".$valp['Usuario']['nombre']." ".$valp['Usuario']['apellido']."  | ".$valp['fecha']."</h2>";
				
				if($Actividad['adjuntosAct']==1){
					echo "<h2>".$Actividad['adjuntosDat']."</h2><img src='".$valp['link']."'>";
				}
				
				if($Actividad['valorAct']==1){
					echo "<h2>".$Actividad['valorDat']."</h2>";
					echo "<p>".$valp['valor']." ".$Actividad['valorUNI']."</p>";
				}

				if($Actividad['categAct']==1){
					echo "<h2>".$Actividad['categDat']."</h2>";
					echo "<p><div class='punto' style='background-color:".$valp['categoriaCo'].";border-color:".$valp['categoriaCo'].";'></div>".$valp['categoriaTx']."<br>".$valp['categoriaDes']."</p>";
				}
				
				if($Actividad['textobreveAct']==1){
					echo "<h2>".$Actividad['textobreveDat']."</h2>";
					echo "<p>".$valp['textobreve']."</p>";
				}

 				if($Actividad['textoAct']==1){
					echo "<h2>".$Actividad['textoDat']."</h2>";
					echo $valp['texto'];
				}
				echo "</div>";

			}
		}
		?>
	
	</div></div>

<script type="text/javascript">

	
</script>

<?php
include('./includes/pie.php');
	/*medicion de rendimiento lamp*/
	$endtime = microtime(true);
	$duration = $endtime - $starttime;
	$duration = substr($duration,0,6);
	echo "<br>tiempo de respuesta : " .$duration. " segundos";
?>
</body>
