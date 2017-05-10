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
set_time_limit ( 0 );


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
    'referer: http://www.rosario3.com/',
    'authority: rosario3.com',
    'x-asset-version: 117aa3',
    "User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.130 Safari/537.36");


    $ch = curl_init();
    
    if($proxy!=="") curl_setopt($ch, CURLOPT_PROXY, $proxy);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
    // rosario3 manda la respuestas content-encoding:gzip
    curl_setopt($ch,CURLOPT_ENCODING , "gzip");
    $html =  curl_exec($ch);
    if($html === false)  echo 'Curl error: ' . curl_error($ch);
    return $html;
}



echo '<meta charset="UTF-8">';


$url = "https://www.rosario3.com/";


$html=getPage($url,$proxy);


// se quitan los doble espacios o "trenes" de espacios
$html=preg_replace('/\s+/', ' ', $html);  


// para el debug
$pagina=1;
//file_put_contents("debug/pagina".$pagina.".html", $html);




//$patron='#http://www.rosario3.com/noticias/(.*?).html#'; 
$patron='#href="/noticias/(.*?)"#'; 
preg_match_all($patron, $html, $m);

// se sacan los valores duplicados
$links= array_keys(array_flip($m[1])); 



$salida=array();
foreach ($links as $key => $value) {

    if(strpos($value, "#")>0)continue;
    
    $salida[]= "https://www.rosario3.com/noticias/".$value;
    
}

$links=$salida;




// se abre la base de datos de noticias
$dbnews = new SQLite3('../data/noticias.db') or die('no se puede abrir la base de datos ../data/noticias.db');

//$dbnews->exec('BEGIN;');

$tope=count($links);

//$tope=4;

for($n=0;$n<$tope;$n++){

    $titulo="";
    $fecha="";
    $bajada="";
    $pieimagen= "";
    $urlimagen= "";
    $urlarticulo="";
    $seccion="";
    $colorseccion="";
    $urlinterno="";
    $texto="";
    $html=NULL;
    $almargen="";
    $nombreArchivo="";
   
    
    $url=$links[$n];

    
    // primero se comprueba si la noticia ya esta en la base de datos
    $sqlquery= "SELECT 1 FROM news WHERE url='$url'";
    $results = $dbnews->query($sqlquery);
    $row = $results->fetchArray(SQLITE3_ASSOC);

    // si la noticia esta se continua con el siguente url
    if($row>0){
      consolelog( $url);
      consolelog( "Ya esta en la BDD, la noticia no se inserta"); 
      continue; 
    }



    $html=getPage($url,$proxy);
    

      
    // se quitan los doble espacios o "trenes" de espacios
    $html=preg_replace('/\s+/', ' ', $html);  

    
   
    
    // se busca la fecha

    $patron= '#<time datetime="(.*?)" class="entry-time clearfix" itemprop="datePublished">#';
    preg_match($patron, $html, $m);
    $fecha=($m[1]);
    $hora="17:00";
    $fecha= $hora. " ". $fecha;
    $epocnews=strtotime($fecha);
    $epocmail=strtotime($fecha);

   

   
        
    // se busca el titulo

    $patron= '#<h1 class="entry-single-title" itemprop="name headline">(.*?)</h1>#';
    preg_match($patron, $html, $m);
    $titulo=($m[1]);

    

    // se busca la bajada


    $patron= '#<div class="entry-single-excerpt" itemprop="description alternativeHeadline">(.*?)</div>#'; 
    preg_match($patron, $html, $m);
    $bajada=($m[1]);

    

    // se busca el texto
    $patron='#<div itemprop="articleBody">(.*?)</div>#';
    preg_match($patron, $html, $m);
    $texto=(($m[1]));
    $texto=strip_tags($texto);

    
  
     
     
    //se genera un indentificador unico de la noticia
    $uidnews= uniqid ("rosario3_", TRUE);
    $valoracion="ninguna";
    $medio="rosario3.com";
    $programa="rosario3.com";
    $noticia=$titulo.PHP_EOL.$bajada.PHP_EOL.$texto;
    $noticia=SQLite3::escapeString ( $noticia );
    $titulo=SQLite3::escapeString ( $titulo );
    $uid=NULL;
    $segundosDelDia=NULL;
    $origen="rosario3.com";
    
    consolelog("<br>------------------------------------");
    consolelog("Noticia ".$n." de ".$tope);
    consolelog("intentando insertar en la BDD..");
    consolelog( $url);
    consolelog( "epoch= ".$epocnews);
    consolelog( "Fecha= ".$fecha);


    // si se llego hasta aqui la noticia no esta, entonces
    // se inserta
      consolelog( "no esta, insertando noticia");
      
      $sqlquery="INSERT INTO news (epocmail, epocnews, medio, programa, noticia, uid, valoracion, uidmail, hora, segundosDelDia, vocabulario, valor, origen, url, titulo, ciudad) VALUES ('$epocmail', '$epocnews', '$medio', '$programa', '$noticia','$uidnews', '$valoracion', '$uid', '$hora', '$segundosDelDia', 'no','NULL', '$origen','$url','$titulo', 'ROSARIO') " ;
      $results = $dbnews->query($sqlquery);
      
      if ($results) consolelog("Se inserto con exito");
      else consolelog( "Problemas de inserccion: ".SQLite3::lastErrorMsg ());
      

    
      
    


   consolelog("<br>------------------------------------");
  


}


//$dbnews->exec('COMMIT;');


unlink($semaforo);

?>