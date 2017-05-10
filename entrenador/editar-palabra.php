<?php



//ignore_user_abort(true); // Ignore user aborts and allow the script to run forever
set_time_limit(0); // for scripts that run really long

ini_set('html_errors', false);


$palabra="";
$valor=0;
$editar="";

if (isset ($_POST["palabra"])) $palabra=$_POST["palabra"];
if (isset ($_POST["valor"])) $valor=$_POST["valor"];
if (isset ($_POST["editar"])) $editar=$_POST["editar"];


// se salta a guardar entidad si asi se lo requiere
if ($editar=="guardarentidad") goto guardarentidad;



$palabra=str_replace(" ", "_", $palabra);
$valencias = json_decode(file_get_contents("../sentiment/valencias.json"), true); 


if ($editar=="verificar"){
		if (array_key_exists($palabra, $valencias)){

			 echo '<div id="editarpalabra" >'.$palabra ."</div>";
			 echo '<div id="editarvalor">' .$valencias[$palabra]."</div>";

		} else {

			echo '<div id="editarpalabra" >'.$palabra ."</div>";
			 echo '<div id="editarvalor">No esta en la base de datos</div>';
		}
		exit;

}


$valor=floatval($valor);

if ($editar=="guardar") $valencias[$palabra]=$valor;

if ($editar=="borrar") unset( $valencias[$palabra]);
  
asort($valencias);
file_put_contents( "../sentiment/valencias.json", json_encode($valencias,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)  );

exit;

////////////////////////////////////////////////////

guardarentidad:



$archivo="../sentiment/entidades.json";
$entidades=array();
$valor=str_replace("_", " ", $palabra);

if(file_exists($archivo)) $entidades = json_decode(file_get_contents($archivo), true); 

$entidades[$palabra]=$valor;

asort($entidades);
file_put_contents( $archivo, json_encode($entidades,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)  );
?>
