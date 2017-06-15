<?php
if(isset($USUARIO['id'])){
echo "<div class='recuadro' id='recuadro1'>";
echo "<p>hola: ".$USUARIO['nombre']." ".$USUARIO['apellido']."</p>";
echo "<a class='boton' href='./login.php'>cerrar sesión</a>";
echo "<a class='boton' href='./actividades.php'>salir al listado de actividades</a>";

echo "</div>";
}
?>
