<?php 
/**
* actividad.php
*
* aplicación para cargar nuevos puntos de relevamiento
*  
* 
* @package    	Plataforma Colectiva de Información Territorial: UBATIC2014
* @subpackage 	actividad
* @author     	Universidad de Buenos Aires
* @author     	<mario@trecc.com.ar>
* @author    	http://www.uba.ar/
* @author    	http://www.trecc.com.ar/recursos/proyectoubatic2014.htm
* @author		based on TReCC SA Procesos Participativos Urbanos, development. www.trecc.com.ar/recursos
* @copyright	2015 Universidad de Buenos Aires
* @copyright	esta aplicación se desarrollo sobre una publicación GNU (agpl) 2014 TReCC SA
* @license    	https://www.gnu.org/licenses/agpl-3.0-standalone.html GNU AFFERO GENERAL PUBLIC LICENSE, version 3 (agpl-3.0)
* Este archivo es parte de TReCC(tm) paneldecontrol y de sus proyectos hermanos: baseobra(tm), TReCC(tm) intraTReCC  y TReCC(tm) Procesos Participativos Urbanos.
* Este archivo es software libre: tu puedes redistriburlo 
* y/o modificarlo bajo los términos de la "GNU AFero General Public License version 3" 
* publicada por la Free Software Foundation
* 
* Este archivo es distribuido por si mismo y dentro de sus proyectos 
* con el objetivo de ser útil, eficiente, predecible y transparente
* pero SIN NIGUNA GARANTÍA; sin siquiera la garantía implícita de
* CAPACIDAD DE MERCANTILIZACIÓN o utilidad para un propósito particular.
* Consulte la "GNU General Public License" para más detalles.
* 
* Si usted no cuenta con una copia de dicha licencia puede encontrarla aquí: <http://www.gnu.org/licenses/>.
*/

//if($_SERVER[SERVER_ADDR]=='192.168.0.252')ini_set('display_errors', '1');ini_set('display_startup_errors', '1');ini_set('suhosin.disable.display_errors','0'); error_reporting(-1);

// verificación de seguridad 
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

// función de consulta de actividades a la base de datos 
include("./actividades_consulta.php");

$ID = isset($_GET['actividad'])?$_GET['actividad'] : '';
$RID = isset($_GET['registro'])?$_GET['registro'] : '';

$Hoy_a = date("Y");
$Hoy_m = date("m");	
$Hoy_d = date("d");
$HOY = $Hoy_a."-".$Hoy_m."-".$Hoy_d;	

// define si se esta defininedo una argumentación, de no ser así esta aplicación solo devuelve un mensaje escrito
if(isset($_POST['actividad'])){
	$Actividad=$_POST['actividad'];
	$ACCION = $_POST['accion'];
}elseif(isset($_GET['actividad'])){
	$Actividad=$_GET['actividad'];
}else{
	$Actividad='';
}
if($Actividad==''){
	header('location: ./actividades.php');	//si no hay una actividad definida esta página no debería consultarse
	echo "ERROR de Acceso 1";
	break;
}


$UsuariosList = usuariosconsulta($ID);

// el reingreso a esta dirección desde su propio formulario php crea o modifica un registro de actividad 
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

// filtro de representación restringe documentos visulazados, no altera datos estadistitico y de agregación 
$FILTRO=isset($_GET['filtro'])?$_GET['filtro']:'';	

// filtro temporal de representación restringe documentos visulazados, no altera datos estadistitico y de agregación 	
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

// función para obtener listado formateado html de actividades 
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
	echo "<h2>Error en el acceso, esta actividad no se encuentra aún publicada y usted no se encuentra registrado como autor de la misma.</h2>";
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

	<title>MAPAUBA - Área de Trabajo</title>
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
	</style>

</head>

<body>
	
	<?php
	include('./includes/encabezado.php');
	
	if($ID!=''){
	?>	
	<div class='recuadro' id="recuadro3" >		
		<div id="slider-vertical" style="height:200px;">
		</div>	
		<h4>Puntos relevados en esta actividad</h4>
		<a onclick="obtenerDescarga(this);">generar copia de descarga de la actividad</a>
		<a href="./actividad_reporte.php?actividad=<?php echo $ID;?>">ver resumen de contenidos de la actividad</a>
		<ul id='puntosdeactividad'>		
		</ul>
		
	
	</div>
	<div class='recuadro' id="recuadro2" >			
		<h4>Puntos visualizados del banco de datos</h4>
		<ul id='puntosdebase' class='scroll-content'>		
		</ul>	

	</div>
	<?php
	}
	?>
	
	<div id="pageborde"><div id="page">
		<h1>Actividad: <span class='resumen'><?php echo $Actividad['resumen'];?></span></h1>
		<?php
		if($Coordinacion=='activa'){
			echo "<a href='./actividad_config.php?actividad=$ID'>configurar esta actividad</a>";
		}
		echo " / <span class='menor'>web de acceso directo: <span class='resaltado'>http://190.111.246.33/MAPAUBA/actividad.php?actividad=$ID</span></span>";	
		if($RID>0){$cons='verpuntos';}else{$cons='marcarpuntos';}	
		echo "<iframe  name='mapa' id='mapa' src='./MAPAactividad.php?actividad=".$Actividad['id']."&consulta=".$cons."&rid=".$RID."'></iframe> ";
	
		//echo"<pre>";print_r($Actividad);echo"</pre>";
		// formulario para agregar una nueva actividad		
		if($ID==''){
				echo "la actividad no fue llamada correctamnte";
		}else{
			
				echo "<p>".$Actividad['consigna']."</p>";				
				
				if($Actividad['hasta']<=$HOY&&$Actividad['hasta']>'0000-00-00'){
					$Stat='cerrada';
				}
				
					if($Accion=='ver'||$Stat=='cerrada'){		
						echo "<div class='formulario vista'>";
								if($Coordinacion=='activa'&&$ValoresRef['zz_bloqueado']=='0'){		
									echo "<form autocomplete='off' id='formCoord' enctype='multipart/form-data' method='post' action='./MAPAactividad.php' target='mapa'>";
										echo "<input type='hidden' id='actividad' name='actividad' value='".$Actividad['id']."'>";
										echo "<input type='hidden' name='rid' value='$RID'>";
										echo "<input id='inputAccion' type='hidden' name='accion' value='bloquear'>";
										echo "<input id='bloqPu' type='button' value='Retirar Punto'  title='Al retirarse un punto solo puede ser visto por su autor y por el coordinador que lo ha retirado' onclick='this.style.display=\"none\";document.getElementById(\"bloq\").style.display=\"block\";document.getElementById(\"bloqPuNo\").style.display=\"block\";document.getElementById(\"bloqTx\").style.display=\"block\";document.getElementById(\"bloqTxL\").style.display=\"block\";'>";
										echo "<input id='bloqPuNo' style='display:none;' type='button' value='Cancelar'  title='cancelar eliminación' id='bloqPuNo'onclick='this.style.display=\"none\";document.getElementById(\"bloq\").style.display=\"none\";document.getElementById(\"bloqTx\").style.display=\"none\";document.getElementById(\"bloqTxL\").style.display=\"none\";document.getElementById(\"bloqPu\").style.display=\"block\";'>";
										echo "<span id='bloqTxL'>mensaje:</span>";
										echo "<input id='bloqTx' name='bloqTx' type='text' value='- escriba mensaje -' onclick='validarContenido(this);'>";
										echo "<input id='bloq' type='submit' value='Confirmo Retirar'>";									
									echo "</form>";
								}elseif($Coordinacion=='activa'&&$ValoresRef['zz_bloqueado']=='1'){		
									echo "<form autocomplete='off' id='formCoord' enctype='multipart/form-data' method='post' action='./MAPAactividad.php' target='mapa'>";
										echo "<input type='hidden' id='actividad' name='actividad' value='".$Actividad['id']."'>";
										echo "<input type='hidden' name='rid' value='$RID'>";
										echo "<input id='inputAccion' type='hidden' name='accion' value='desbloquear'>";
										echo "<input id='bloqPu' type='button' value='Reincorporar Punto'  title='Al retirarse un punto solo puede ser visto por su autor y por el coordinador que lo ha retirado' onclick='this.style.display=\"none\";document.getElementById(\"bloq\").style.display=\"block\";document.getElementById(\"bloqPuNo\").style.display=\"block\";'>";
										echo "<input id='bloqPuNo' style='display:none;' type='button' value='Cancelar'  title='cancelar eliminación' id='bloqPuNo'onclick='this.style.display=\"none\";document.getElementById(\"bloq\").style.display=\"none\";document.getElementById(\"bloqPu\").style.display=\"block\";'>";
										echo "<span id='bloqTxVer' style='display:block'>".$ValoresRef['zz_bloqueadoTx']."</span>";
										echo "<input id='bloq' type='submit' value='Confirmo Reinc.'>";									
									echo "</form>";
								}
								
								
									
								if($Stat!='cerrada'){
									echo "<a class='botonamarcar marcar' href='./actividad.php?actividad=".$Actividad['id']."'>cargar nuevos puntos</a>";
								}
								
								echo "<label for='y'>Lat:</label><span type='text' name='y' id='y'>".$ValoresRef['y']."</span>";
								echo "<label class='lon' for='x'>Lon:</label><span type='text' name='x' id='x'>".$ValoresRef['x']."</span>";
								echo "<input type='hidden' name='z' id='z' value='".$ValoresRef['z']."'>";
								echo "<input type='hidden' id='actividad' name='actividad' value='".$Actividad['id']."'>";
								
								if($Actividad['adjuntosAct']=='1'){
									
									echo "<br><label for='link'>".$Actividad['adjuntosDat'].":</label>";
									//echo "<span name='link' id='link'>".$ValoresRef['link']."</span>";	
										
									if(substr($ValoresRef['link'],0,4)=='www.'||substr($ValoresRef['link'],0,4)=='http'){
										echo "<span><a title='".$ValoresRef['link']."' target='_blank' href='".$ValoresRef['link']."'>ver link</a></span>";	
									}else{
										$extValImg['jpg']='1';
										$extValImg['png']='1';
										$extValImg['tif']='1';
										$extValImg['bmp']='1';
										$extValImg['gif']='1';
										$extValDown['pdf']='1';
										$extValDown['zip']='1';									
										if(substr($ValoresRef['link'],-4,1)=='.'){
											$ext=substr($ValoresRef['link'],-3);		
											
											if(isset($extValImg[strtolower($ext)])){
												echo "<img id='adjunto' src='".$ValoresRef['link']."'>";
											}elseif(isset($extValDown[strtolower($ext)])){
												echo "<a href='".$ValoresRef['link']."'>descargar $ext</a>";										
											}
										}									
									}	
								}				
								
								if($Actividad['valorAct']=='1'){
									$Vtx=$Actividad['valorDat'];if(str_replace(" ","",$Vtx)==''){$Vtx='valor';}
									echo "<br><label for='valor'>".$Vtx.":</label>";
									echo "<span name='valor' id='valor' value='".$ValoresRef['valor']."'><div class='aclaracion'>".$Actividad['valorUni']."</div>";	
								}
								
								echo "<br>";
								if($Actividad['textobreveAct']=='1'){									
									$TBtx=$Actividad['textobreveDat'];if(str_replace(" ","",$TBtx)==''){$TBtx='textobreve';}
									echo "<label for='textobreve'>".$TBtx.":</label>";
									echo "<span name='valor' id='textobreve'>".$ValoresRef['textobreve']."</span>";
								}
								
								echo "<br>";
								if($Actividad['categAct']=='1'){
									$Ctx=$Actividad['categDat'];if(str_replace(" ","",$Ctx)==''){$Ctx='categoria';}
									echo "<label for='valor'>".$Ctx.":</label>";
									
									foreach($Actividad['ACTcategorias'] as $cat){
										if($ValoresRef['categoria']==$cat['id']){
											echo "<span name='valor'>".$cat['nombre']."</span>";
										}
									}
									
								}
								echo "<br>";
								if($Actividad['textoAct']=='1'){
									echo "<label for='texto'>".$Actividad['textoDat']."</label>";
									echo "<div id='texto_parent' id='texto'>".$ValoresRef['texto']."</div>";
								}
								
					}else{
					
						echo "<input type='hidden' id='actividad' name='actividad' value='".$Actividad['id']."'>";
						
						if($Actividad['hasta']<=$HOY&&$Actividad['hasta']>'0000-00-00'){
							echo "<div class='formulario vista'>
							<h2>Esta actividad ha cerrado el día ".$Actividad['hasta'].", para la carga de datos</h2>
							";
							
							echo "<h3>Resultados Obtenidos</h3>";
							if($Actividad['resultados']==''){$Actividad['resultados']="- sin resultados cargados por el equipo coordinador";}
							echo "<p>".nl2br($Actividad['resultados'])."</p>";
							echo "<h3>Objeto de estudio</h3>";
							echo "<p>".nl2br($Actividad['objeto'])."</p>";
							echo "<h3>Marco de la actividad</h3>";
							echo "<p>".nl2br($Actividad['marco'])."</p>";
						}elseif($HOY<$Actividad['desde']){
							echo "
							<h2>Esta actividad se abrirá el día ".$Actividad['desde'].", para la carga de datos</h2>									
							";							
						}else{						
							echo "<div class='formulario activo'>";
								echo "<form id='formPuntos' enctype='multipart/form-data' method='post' action='./MAPAactividad.php' target='mapa'>";
									if($Valores['zz_bloqueado']=='1'&&($Accesos[$UsuarioI]>='2'||$UsuarioI==$Valores['id_usuarios'])){
										echo"
											<div id='bloqudescricion'>
											<p>Retirado por: ".$UsuariosList[$Valores['zz_bloqueadoUsu']]['nombre']." ".$UsuariosList[$Valores['zz_bloqueadoUsu']]['apellido']."</p>
											<p>Mensaje: ".$Valores['zz_bloqueadoTx']."</p>
											</div>
										";
									}	
									echo "<input type='hidden' id='actividad' name='actividad' value='".$Actividad['id']."'>";
									if($Accion=="cambia"&&$RID>0){
											echo "<input type='hidden' name='rid' value='$RID'>";
									}
									
									echo "<input id='inputsubmit' type='submit' value='$AccionTx'>";
									if($Accion=="cambia"&&$RID>0){
										
										echo "<input id='elim' type='button' value='Borrar Punto'  title='Al eliminarse un punto se perdera toda la información asociada al mismo'onclick='this.style.display=\"none\";document.getElementById(\"elimina\").style.display=\"block\";document.getElementById(\"elimNo\").style.display=\"block\";'>";
										echo "<input id='elimNo' style='display:none;' type='button' value='Cancelar'  title='cancelar eliminación' id='elimNo'onclick='this.style.display=\"none\";document.getElementById(\"elimina\").style.display=\"none\";document.getElementById(\"elim\").style.display=\"block\";'>";
										echo "<input id='elimina' type='button' value='Confirmo Borrar' onclick='document.getElementById(\"inputAccion\").value=\"borrar\";this.parentNode.submit();'>";
										
										echo "<a class='botonamarcar marcar' href='./actividad.php?actividad=".$Actividad['id']."'>cargar nuevos puntos</a>";
	
									}
									echo "<label for='y'>Lat:</label><input type='text' name='y' id='y' value='".$Valores['y']."'>";
									echo "<label class='lon' for='x'>Lon:</label><input type='text' name='x' id='x' value='".$Valores['x']."'>";
									echo "<input type='hidden' name='z' id='z' value='".$Valores['z']."'>";
									
									if($Actividad['adjuntosAct']=='1'){
										
										echo "<br><label for='link'>".$Actividad['adjuntosDat'].":</label>";
										echo "<input name='link' id='link' value='".$Valores['link']."'>";
										
										if(substr($Valores['link'],0,4)=='www.'||substr($Valores['link'],0,4)=='http'){									
											echo "<a target='_blank' href='".$Valores['link']."'>ver link</a>";	
										}else{
											
											$extValImg['jpg']='1';
											$extValImg['png']='1';
											$extValImg['tif']='1';
											$extValImg['bmp']='1';
											$extValImg['gif']='1';
											$extValDown['pdf']='1';
											$extValDown['zip']='1';
											
											echo substr($Valores['link'],-4,1);
											if(substr($Valores['link'],-4,1)=='.'){
												$ext=substr($Valores['link'],-3);			
												
												if(isset($extValImg[strtolower($ext)])){
													echo "<img id='adjunto' src='".$Valores['link']."'>";
												}elseif(isset($extValDown[strtolower($ext)])){
													echo "<img id='adjunto'>";
													echo "<a href='".$Valores['link']."'>descargar $ext</a>";										
												}
											}else{
												echo "<img id='adjunto'>";
											}
																				
										}
									}
									
									echo "<input id='inputAccion' type='hidden' name='accion' value='$Accion'>";
									
									if($Actividad['valorAct']=='1'){
										$Vtx=$Actividad['valorDat'];if(str_replace(" ","",$Vtx)==''){$Vtx='valor';}
										echo "<br><label for='valor'>$Vtx</label>";
										echo "<input name='valor' id='valor' value='".$Valores['valor']."'><div class='aclaracion'>".$Actividad['valorUni']."</div>";	
									}
									
									echo "<br>";
									if($Actividad['textobreveAct']=='1'){
										$Vtx=$Actividad['textobreveDat'];if(str_replace(" ","",$Vtx)==''){$Vtx='texto breve';}
										echo "<label for='textobreve'>".$Vtx.":</label>";
										echo "<input name='textobreve' id='textobreve' value='".$Valores['textobreve']."'>";	
									}						
									
									echo "<br>";
									if($Actividad['categAct']=='1'){
										$Ctx=$Actividad['categDat'];if(str_replace(" ","",$Ctx)==''){$Ctx='categoria';}
										echo "<label for='valor'>$Ctx</label>";
										echo "<select id='inputcatgoriaexistente' name='categoria'>";
											echo "<option style='border:1px solid #550;' value=''>-elegir categoria-</option>";
										foreach($Actividad['ACTcategorias'] as $cat){
											if($cat['zz_fusionadaa']>0){continue;}
											if($Valores['categoria']==$cat['id']){$sel=" selected='selected' ";}else{$sel='';}
											echo "<option $sel style='color:".$cat['CO_color'].";border:1px solid ".$cat['CO_color'].";' value='".$cat['id']."'>";
												echo $cat['nombre'];
											echo" </option>";
										}
										
										echo "</select>";
										
										if($Actividad['categLib']=='1'){
											echo "<input disabled='disabled' id='inputcatgorianueva' style='display:none' name='nuevacategoria' type='text' value='-escriba el nombre de la nueva categoría-' onclick='validarContenido(this);'>";
											echo "<input type='button' value='crear categoría' onclick='document.getElementById(\"inputcatgoriaexistente\").style.display=\"none\";document.getElementById(\"inputcatgorianueva\").style.display=\"inline-block\";document.getElementById(\"inputcatgorianueva\").removeAttribute(\"disabled\");this.style.display=\"none\";'>";
										}	
									}
									echo "<br>";
									if($Actividad['textoAct']=='1'){
										echo "<label for='texto'>".$Actividad['textoDat']."</label>";
										echo "<textarea name='texto' id='texto'>".$Valores['texto']."</textarea>";
									}
								echo "
									</form>
								";
								
								if($Actividad['adjuntosAct']=='1'){
									
									echo "<form id='adjuntador' enctype='multipart/form-data' method='post' action='./agrega_adjunto.php' target='cargaimagen'>";
									echo "<label style='position:relative;' class='upload'>";							
										echo "<span id='upload' style='position:absolute;top:0px;left:0px;'>arrastre o busque aquí un archivo</span>";
										echo "<input id='uploadinput' style='position:relative;opacity:0;' type='file' name='upload' value='' onchange='this.parentNode.parentNode.submit();'></label>";
										echo "<input type='hidden' id='actividad' name='actividad' value='".$Actividad['id']."'>";
									echo "</form>";
									echo "<iframe id='cargaimagen' name='cargaimagen'></iframe>";
								}
						}
					}
				echo "</div>";
				
		}
		?>
	
	</div></div>

<script type="text/javascript">
	function obtenerDescarga(_this){
		var parametros = {
			"idactividad" : '<?php echo $ID;?>'
		};		
			
		$.ajax({
			data:  parametros,
			url:   'actividades_descarga_ajax.php',
			type:  'post',
			success:  function (response){
				var _res = $.parseJSON(response);
				//console.log(_res);
				obtenerBotonDescarga(response, _this);
			}
		});
	}
	
function obtenerBotonDescarga(response, _this){
		var _res = $.parseJSON(response);
		console.log(_res);
		_this.innerHTML='Descargar AHORA';
		_this.setAttribute('href',_res.data.url);
		//_this.setAttribute('onclick','removerDescarga(this);');
		_this.setAttribute('download','');
	}	
	/*
function removerDescarga(_this){
	_this.innerHTML='generar nueva copia de descarga de la actividad';	
	_this.removeAttribute('href');
	_this.removeAttribute('download');
	_this.setAttribute('onclick','obtenerDescarga(this);');
}*/
	
</script>
	
<script type='text/javascript'>

		
		function validarContenido(_this){
			if(_this.value=='-agregar nuevo-'){
				_this.setAttribute('value','');
			}
			if(_this.value=='-escriba el nombre de la nueva categoría-'){
				_this.setAttribute('value','');
			}
			if(_this.value=='- escriba mensaje -'){
				_this.setAttribute('value','');
			}
		}
	/*
    var dropZone = document.getElementById('upload');
    var fileinput=    document.getElementById('uploadinput');

    // Optional.   Show the copy icon when dragging over.  Seems to only work for chrome.
    dropZone.addEventListener('dragover', function(e) {
        e.stopPropagation();
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    });
    

    // Get file data on drop
    dropZone.addEventListener('drop', function(e) {
        e.stopPropagation();
        e.preventDefault();
        var files = e.dataTransfer.files; // Array of all files
        
        
        
         for (var i=0, file; file=files[i]; i++) {
         	
         	
                fileinput.setAttribute('value',file);
                console.log(file);
   		 }
 
     });*/
</script>

<!-- este proyecto recurre al proyecto tiny_mce para las funciones de edición de texto -->
<script type="text/javascript" src="./js/editordetexto/tiny_mce.js"></script>

<script type="text/javascript">

		tinyMCE.init({
				
	        // General options
	        mode : "textareas",
	        theme : "advanced",
	        plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

			force_p_newlines : true,
			force_br_newlines : false,
			convert_newlines_to_brs : true,
			remove_linebreaks : false,
			
			width : "540px",
			height : "120px",
	
	        // Theme options
	        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontsizeselect|cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,link,unlink|visualchars,nonbreaking,blockquote|tablecontrols,|,removeformat,visualaid,",
	        theme_advanced_toolbar_location : "top",
	        theme_advanced_toolbar_align : "left",
	        theme_advanced_statusbar_location : false,
	
	        // Skin options
	        skin : "o2k7",
	        skin_variant : "silver",
	
	        // Example content CSS (should be your site CSS)
	        content_css : "./css/mapauba_texto.css",
	
	        // Drop lists for link/image/media/template dialogs
	        template_external_list_url : "js/template_list.js",
	        external_link_list_url : "js/link_list.js",
	        external_image_list_url : "js/image_list.js",
	        media_external_list_url : "js/media_list.js",
	
	        // Replace values for the template plugin
	       	 template_replace_values : {
	                username : "Some User",
	                staffid : "991234"
	        }
		});
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
