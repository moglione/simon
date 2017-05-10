<?php
///////////////////////////////////////
//　Marcelo Moglione 2016 
////////////////////////////////////////

// se crea el semaforo para la cola de ejecucion
$semaforo=basename(__file__,".php").".pid";
file_put_contents($semaforo, "true");


require_once ('../includes/proxy.php');

// Make sure this script will keep on runing after we close the connection with it.
ignore_user_abort(TRUE);
ini_set('html_errors', false);
set_time_limit (0);


// REPORTE DE ERRORES ACTIVADO
// PARA QUE NO TIRE SOLO ERROR 500
error_reporting(E_ALL);
ini_set('display_errors', 'On');


function consolelog( $message, $progress=0) 
{ 
    echo $message ."<br>" .PHP_EOL; 
    echo PHP_EOL; ob_flush();  
    flush();  
}



// FUNCION CURL PARA BAJAR LAS PAGINAS
function getPage($url, $proxy) {
    $headers = array(
    "HTTP/1.0",
    "Accept: application/json, text/javascript, */*; q=0.01",
    'x-push-state-request: true',
    'x-requested-with: XMLHttpRequest',
    "Accept-Language: es ",
    'referer: http://www.lacapital.com.ar/',
    'authority: lacapital.com.ar',
    'x-asset-version: 117aa3',
    "User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.130 Safari/537.36");


    $ch = curl_init();
    
    if($proxy!=="") curl_setopt($ch, CURLOPT_PROXY, $proxy);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
    $html =  curl_exec($ch);
    if($html === false)  echo 'Curl error: ' . curl_error($ch);
    return $html;
}



echo '<meta charset="UTF-8">';




$url = "http://www.lacapital.com.ar/";


$html=getPage($url,$proxy);


// se quitan los doble espacios o "trenes" de espacios
$html=preg_replace('/\s+/', ' ', $html);  


// para el debug
$pagina=1;
//file_put_contents("debug/pagina".$pagina.".html", $html);


$patron='#<h1 class="title-item"> <a href="(.*?)"(.*?)target="" >(.*?)</a> </h1>#';
preg_match_all($patron, $html, $m);

$links=$m[1];
$titulos=$m[3];

$paginador="";


// se abre la base de datos de noticias
$dbnews = new SQLite3('../data/noticias.db') or die('no se puede abrir la base de datos ../data/noticias.db');

$dbnews->exec('BEGIN;');

$tope=count($links);


for($n=0;$n<$tope;$n++){

    $titulo="";
    $fecha="";
    $bajada="";
    $seccion="";
    $texto="";
    $html=NULL;
 
    
   
    
    // se carga los links de la portada
    $url=$links[$n];


    
    // primero se comprueba si la noticia ya esta en la base de datos
    $sqlquery= "SELECT 1 FROM news WHERE url='$url'";
    $results = $dbnews->query($sqlquery);
    $row = $results->fetchArray(SQLITE3_ASSOC);

    // si la noticia esta se continua con el siguente url
    if($row>0){
      consolelog( $url);
      consolelog( "Ya esta en la BDD, la noticia no se inserta"); 
      consolelog( "--------------------------------------------"); 
      continue; 
    }



   // se comprueba si el link es de la version movil
   // si es asi no se explora
  

   if(strpos($url,'http://m.lacapital.com.ar')!== false){
   	  
      consolelog( "--------------------------------------------"); 
      consolelog( $url);
      consolelog( "El link es de la version movil"); 
      consolelog( "se cambia por la version desktop"); 
      $url= str_replace("http://m.lacapital.com.ar", "http://www.lacapital.com.ar", $url);
      
    }


    $html=getPage($url,$proxy);
    

    
    
    // se quitan los doble espacios o "trenes" de espacios
    $html=preg_replace('/\s+/', ' ', $html);  

    
    // para el debug
    $pagina++;
    //file_put_contents("debug/pagina".$pagina.".html", $html);

   
    
    // se busca la fecha
    $patron='#<div class="news-header-date">(.*?)</div>#';
    preg_match($patron, $html, $m);
    $fecha=($m[1]);
    $partes=explode(" ",$fecha);

 
    $meses = array("enero"=>"jan","febrero"=>"feb","marzo"=>"mar","abril"=>"apr","mayo"=>"may","junio"=>"jun","julio"=>"jul","agosto"=>"agu","septiembre"=>"sep","setiembre"=>"sep","octubre"=>"oct","noviembre"=>"nov","diciembre"=>"dec");
   
   

    if(count($partes)==8)
    {
    $hora=$partes[0];
    $dianumero=$partes[3];
    $mes=strtolower($partes[5]);
    $year=$partes[7];
    }

    if(count($partes)==6)
    {
    $hora="17:00";
    $dianumero=$partes[1];
    $mes=strtolower($partes[3]);
    $year=$partes[5];
    }

        
    if($hora=="00:00")$hora="17:00";
    
    
    
    $fecha= $hora. " ". $dianumero." ".$meses[$mes]." ".$year;
    $epocnews=strtotime($fecha);
    $epocmail=strtotime($fecha);
   
    // se busca el titulo
    $patron='#<h1 class="news-header-title">(.*?)</h1>#';
    preg_match($patron, $html, $m);
    $titulo=($m[1]);

    // se busca la bajada
    $patron='#<p class="news-header-sub-title">(.*?)</p>#';
    preg_match($patron, $html, $m);
    $bajada=($m[1]);


    // se busca el texto
    //$patron='#<!-- \[start\] news body-->(.*?)<!-- \[end\] news body-->#';
    $patron='#<div class="news-body">(.*?)<a name="fb-comment" href="">#'; 
    preg_match($patron, $html, $m);
    $texto=(($m[1]));
    $texto=strip_tags($texto);

   


  
    
     
     
    //se genera un indentificador unico de la noticia
    $uidnews= uniqid ("lacapital_", TRUE);
    $valoracion="ninguna";
    $medio="lacapital";
    $programa="lacapital.com.ar";
    $noticia=$titulo.PHP_EOL.$bajada.PHP_EOL.$texto;
    $noticia=SQLite3::escapeString ( $noticia );
    $titulo=SQLite3::escapeString ( $titulo );
    $uid=NULL;
    $segundosDelDia=NULL;
    $origen="lacapital_impresa";
    
    consolelog("Noticia ".$n." de ".$tope);
    consolelog("cantidad de texto ".strlen($texto)." caracteres ");
    consolelog("intentando insertar en la BDD..");
    consolelog( $url);
    consolelog( "epoch= ".$epocnews);
    consolelog( "Fecha= ".$fecha);


    

  
    consolelog( "no esta, insertando noticia");
      
    $sqlquery="INSERT INTO news (epocmail, epocnews, medio, programa, noticia, uid, valoracion, uidmail, hora, segundosDelDia, vocabulario, valor, origen, url, titulo, ciudad) VALUES ('$epocmail', '$epocnews', '$medio', '$programa', '$noticia','$uidnews', '$valoracion', '$uid', '$hora', '$segundosDelDia', 'no','NULL', '$origen','$url','$titulo', 'ROSARIO') " ;
    $results = $dbnews->query($sqlquery);

    if ($results) consolelog("Se inserto con exito");
    else consolelog( "Problemas de inserccion: ".SQLite3::lastErrorMsg ());
      

   

   consolelog("<br>------------------------------------");
  


}


$dbnews->exec('COMMIT;');


unlink($semaforo);


?>