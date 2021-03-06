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
    
    $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';


    $ch = curl_init();
    
    if($proxy!=="") curl_setopt($ch, CURLOPT_PROXY, $proxy);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
    // por las dudas la respuestas sean  content-encoding:gzip
    curl_setopt($ch,CURLOPT_ENCODING , "gzip");
    $html =  curl_exec($ch);
    if($html === false)  echo 'Curl error: ' . curl_error($ch);
    return $html;
}



echo '<meta charset="UTF-8">';



$url = "http://www.elciudadanoweb.com/seccion/edicion-impresa/";




$html=getPage($url,$proxy);


// se quitan los doble espacios o "trenes" de espacios
$html=preg_replace('/\s+/', ' ', $html);  






          
$patron='#<a href="http://www.elciudadanoweb.com/(.*?)"#'; 
preg_match_all($patron, $html, $m);

// se sacan los valores duplicados
$links= array_keys(array_flip($m[1])); 



$salida=array();
foreach ($links as $key => $value) {
    
    if( substr_count($value, '-') > 4 ) $salida[]= "http://www.elciudadanoweb.com/".$value;
    
}

$links=$salida;



// se abre la base de datos de noticias
$dbnews = new SQLite3('../data/noticias.db') or die('no se puede abrir la base de datos ../data/noticias.db');

//$dbnews->exec('BEGIN;');

$tope=count($links);



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


    $html=getPage($url,$proxy);
    

      
    // se quitan los doble espacios o "trenes" de espacios
    $html=preg_replace('/\s+/', ' ', $html);  

   
       
    // se busca la fecha


    $fecha=date("Y-m-d"); ;

    
    $patron= '#<time class="post-date updated" itemprop="datePublished" datetime="(.*?)">(.*?)</time>#';
    preg_match($patron, $html, $m);
  
    if(isset($m[1]))$fecha=$m[1]; 
    $hora="17:00";
    $fecha=$hora." ". $fecha;
    $epocnews=strtotime($fecha);
    $epocmail=strtotime($fecha);
    
	
     
        
    // se busca el titulo

    $patron= '#<h1 class="post-title left" itemprop="name headline">(.*?)</h1>#';
    preg_match($patron, $html, $m);
    $titulo=($m[1]);


    
    
    
  
   


    

    // se busca el texto

    $patron='#<!--social-sharing-top-->(.*?)<!-- Facebook Comments Plugin for WordPress:#';
    preg_match($patron, $html, $m);
    $texto=(($m[1]));
    $texto=strip_tags($texto);

    
     
     
    //se genera un indentificador unico de la noticia
    $uidnews= uniqid ("elciudadano_", TRUE);
    $valoracion="ninguna";
    $medio="elciudadano";
    
    $noticia=$titulo.PHP_EOL.$texto;
    $noticia=SQLite3::escapeString ( $noticia );
    $titulo=SQLite3::escapeString ( $titulo );
    $uid=NULL;
    $segundosDelDia=NULL;
    $origen="elciudadanoweb.com";
    $programa="elciudadano";
    
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
      
 


  
  


}


//$dbnews->exec('COMMIT;');

unlink($semaforo);


?>