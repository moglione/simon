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
$DBsize= round($size/pow(1024,($i = floor(log($size, 1024)))),$decimales ).$clase[$i];



// se abre la base de datos de noticias
$db = new SQLite3($DBpath) or die('no se puede abrir la base de datos '.$DBpath);

// para saber la cantidad de filas que tiene la base de datos
$columnas = $db->query("SELECT COUNT(*) as count FROM news");
$cantidadCol = $columnas->fetchArray();
$numRows = $cantidadCol['count'];


// para saber la cantidad de medios diferentes que se monitorean
$columnas = $db->query("SELECT COUNT(DISTINCT medio) as count FROM news");
$cantidadCol = $columnas->fetchArray();
$cantMedios = $cantidadCol['count'];

// ultima modificacion de la base de datos
$modificada=gmdate ( "d/m/Y h:i" , $DBmodified )."       [".$DBmodified."]";


/*
// para saber los medios indexados en la base de datos
// y cuantas noticias tiene cada uno (desde el principio)
$result = $db->query("SELECT DISTINCT medio FROM news");
$medios=array();
while($res = $result->fetchArray(SQLITE3_ASSOC)){ 
    $medio=$res['medio'];
    $columnas = $db->query("SELECT COUNT(*) as count FROM news WHERE medio='$medio'");
    $cantidadCol = $columnas->fetchArray();
    $medios[$medio]=  $cantidadCol['count'];
}

arsort($medios);


// para saber los medios indexados en la base de datos
// y cuantas noticias tiene cada uno (desde que se comenzaron a indexar portales)
$medios2=array();
while($res = $result->fetchArray(SQLITE3_ASSOC)){ 
    $medio=$res['medio'];

    $quiebre=1485187200-(86400*0);

    $columnas = $db->query("SELECT COUNT(*) as count FROM news WHERE medio='$medio' and epocnews > '$quiebre'");
    $cantidadCol = $columnas->fetchArray();
    $medios2[$medio]=  $cantidadCol['count'];
}

arsort($medios2);

*/


include_once('includes/tbs_class.php');
$TBS = new clsTinyButStrong;
//$TBS->SetOption('noerr', false); 
$TBS->SetOption('charset', 'UTF-8'); 
$TBS->LoadTemplate( 'templates/bdd.html');
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