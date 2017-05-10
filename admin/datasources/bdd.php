<?php

$nombre=basename($_SERVER['PHP_SELF'],".php");
$nombre.=".html";
$cachepath="cache";
$htmlpath=$cachepath."/".$nombre;


$DBpath="../../data/noticias.db";
$DBpath=realpath($DBpath);


$DBmodified=filemtime ($DBpath);

// se calcual el tamaño del archivo
$size=filesize($DBpath);
$decimales=2;
$clase = array(" Bytes", " KB", " MB", " GB", " TB"); 
$DBsize= round($size/pow(1024,($i = floor(log($size, 1024)))),$decimales );

$DBsize= number_format ( $DBsize , 2 ,  "," ,  "." ).$clase[$i];

// se abre la base de datos de noticias
$db = new SQLite3($DBpath) or die('no se puede abrir la base de datos '.$DBpath);

// para saber la cantidad de filas que tiene la base de datos
$columnas = $db->query("SELECT COUNT(*) as count FROM news");
$cantidadCol = $columnas->fetchArray();
$numRows = $cantidadCol['count'];
$numRows= number_format ( $numRows , 0 ,  "," ,  "." );


// para saber la cantidad de medios diferentes que se monitorean
$columnas = $db->query("SELECT COUNT(DISTINCT medio) as count FROM news");
$cantidadCol = $columnas->fetchArray();
$cantMedios = $cantidadCol['count'];


// ultima modificacion de la base de datos
$modificada=gmdate ( "d/m/Y h:i" , $DBmodified )."       [".$DBmodified."]";



include_once('includes/tbs_class.php');
$TBS = new clsTinyButStrong;
//$TBS->SetOption('noerr', false); 
$TBS->SetOption('charset', 'UTF-8'); 
$TBS->LoadTemplate( 'templates/'.$nombre);
//$TBS->MergeBlock('bloque1',$salida); 
$TBS->Show(TBS_NOTHING); 
$html = $TBS->Source;

// se graba el archivo en cache
if(!file_exists($cachepath)) mkdir($cachepath); 
file_put_contents($htmlpath, $html);   


exit;




echo "<pre>";
print_r($medios);
print_r($medios2);
echo "</pre>";



?>