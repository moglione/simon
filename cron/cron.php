<?php

register_shutdown_function( "cierre_del_cron" );
set_error_handler( "error_cron" );
error_reporting( E_ALL );


$archivoLock="cron.lock";

// para que el cron no se re-entrante se verifica
// si ya esta en ejecucion "mirando" si existe
// el archivo cron.lock que indica que el cron
// esta activo, si es asi se sale
if(file_exists($archivoLock)) exit;


// para que el script se ejecute "eternamente"
ignore_user_abort(TRUE);
set_time_limit (0);

// se genera el archivo de "lock" (cron.lock)
// mientras este archivo exista se ejecutara el
// bloque principal de este script
file_put_contents($archivoLock, "true");



$fecha="[".date("d/m/Y H:i:s")."] ";
error_log("el cron arranco  ".$fecha.PHP_EOL ,3,"cron.log");
$tiempo_inicio = microtime(true);

/////////////////////////
//el bucle principal
////////////////////////

$intervalo=7200;  // segundos -->(3600 una hora) (7200 -->dos horas)
$beacon=10;       //segundos --> espacio de tiempo para verificar si se termona el cron
$tope=$intervalo / $beacon;

$contador=$tope;
while (file_exists($archivoLock)){

 	if ($contador==$tope)cronRun();
    sleep($beacon);
    $contador--;
    if ($contador<=0)$contador=$tope;
    if(!file_exists($archivoLock))cierre_del_cron();
    if(file_exists($archivoLock))touch($archivoLock);
    

}
/////////////////////////
//Fin del bucle principal
////////////////////////

cierre_del_cron();

exit;



//-----------------------------------------------------
function cierre_del_cron()
{
    
    global $tiempo_inicio;
    // medicion de tiempo de uptime con dias, horas , minutos y segundos
    $uptime = '';
    $tiempo_fin = microtime(true);
    $secs = $tiempo_fin - $tiempo_inicio;
    $uptime=convertir_segundos($secs);
    $fecha="[".date("d/m/Y H:i:s")."] ";
    
    // rastreo de errores
    $error = error_get_last();
    $error_salida="Ningun error (ejecucion normal)";
    if ($error !== NULL) $error_salida= print_r($error, true);
    error_log("el cron finalizo ".$fecha.PHP_EOL ,3,"cron.log");
    error_log("errores: ".$error_salida.PHP_EOL ,3,"cron.log");
    error_log("Uptime: ".$uptime.PHP_EOL ,3,"cron.log");
    error_log("------------------------------".PHP_EOL ,3,"cron.log");

    if(file_exists("cron.lock"))unlink("cron.lock");
    exit();
}


function error_cron( $errno, $errstr, $errfile, $errline ){
    echo "<h1>Error interno Marcelo<h1>";
    echo "<h3>$errno<h3>";
    echo "<h3>$errstr<h3>";
    echo "<h3>$errfile<h3>";
    echo "<h3>$errline<h3>";

    cierre_del_cron();

}

//-----------------------------------------------------


function cronRun(){

    
    $cola=array();

    $cola[]="../rec/descargar-mails.php";
    $cola[]="../rec/procesar.php";
    //------------------------------------------------------
    $cola[]="../medios-digitales/lt3.php";
    $cola[]="../medios-digitales/canal5.php";
    $cola[]="../medios-digitales/elciudadano.php";
    $cola[]="../medios-digitales/elciudadano_impresa.php";
    $cola[]="../medios-digitales/lacapital_web.php";
    $cola[]="../medios-digitales/lacapital_impresa.php";
    $cola[]="../medios-digitales/rosario3.php";
    $cola[]="../medios-digitales/notiexpress.php";
    $cola[]="../medios-digitales/rosarioplus.php";
    $cola[]="../medios-digitales/rosario12.php";
    //------------------------------------------------------
    $cola[]="../reportemovil/estadisticas.php";
    $cola[]="../reportemovil/estadisticas1dias.php";
    $cola[]="../reportemovil/estadisticas3dias.php";
    $cola[]="../reportemovil/subir.php";



    queueRun($cola);
    
    
} 

//-----------------------------------------------------
// ejecuta uno a uno los script, esperando que terminen
// no ejecuta mas de un script por vez
// cada script debe generar su archivo de lock(.pid)
// al iniciar y borrarlo al terminar

function queueRun($cola){

     global $archivoLock;
    $principio = microtime(true);

    // primero se borran todos los archivos pid
    foreach ($cola as $key => $value) {
            $dir=dirname($value);
		    array_map('unlink', glob($dir."/*.pid"));
    }	

       
    foreach ($cola as $key => $value) {
    	$dir=dirname($value);
        if (strpos($value, "../")!==false) $value=substr($value, 3);
        // mientras halla un archivo pid se espera
        $pid = $dir."/*.pid";
        while (count(glob($pid)) > 0)sleep(10);
        if(file_exists($archivoLock))touch($archivoLock);
        if(!file_exists($archivoLock))cierre_del_cron();
        scriptRun($value);


     } 

    $fin = microtime(true);
    $secs = $fin - $principio;
    $tiempodeCola=convertir_segundos($secs);
    error_log("La cola se proceso en: ".$tiempodeCola.PHP_EOL ,3,"cron.log");

} 




//-----------------------------------------------------

// Funcion para correr un script via http
// y entonces imitar un cronjob

function scriptRun($path){
	

	$actualdir=dirname($_SERVER['REQUEST_URI']);
	$parentdir=dirname($actualdir);
    $fullurl="http://".$_SERVER['HTTP_HOST'].$parentdir;
    $url=$fullurl."/".$path;
    
    echo $url."<br>";  ob_flush(); flush();
    
    $hora="[".date("H:i:s")."] ";
    error_log($url."  ".$hora.PHP_EOL ,3,"cron.log");

    get_headers($url);



}


function convertir_segundos($secs){
    $uptime="";
	$seconds = intval($secs) % 60;
    $minutes = (intval($secs) / 60) % 60;
    $hours = (intval($secs) / 3600) % 24;
    $days = intval(intval($secs) / (3600*24));
    if ($days > 0 and $days==1 ) $uptime = str_pad($days, 2, '0', STR_PAD_LEFT) . ' dia ';
    if ($days > 0 and $days>1 ) $uptime = str_pad($days, 2, '0', STR_PAD_LEFT) . ' dias ';
    if(($hours > 0) || ($uptime!=""))  $uptime .= str_pad($hours, 2, '0', STR_PAD_LEFT) . ' horas';
    if (($minutes > 0) || ($uptime!="")) $uptime .= str_pad($minutes, 2, '0', STR_PAD_LEFT) . ' minutos ';
    $uptime .= str_pad($seconds, 2, '0', STR_PAD_LEFT). ' segundos ';
    return $uptime;
}


?>