<?php 

// Make sure this script will keep on runing after we close the connection with it.
//ignore_user_abort(TRUE);
ini_set('html_errors', false);
set_time_limit ( 0 );
if(isset($_GET["verbosetofile"])) include_once "../includes/verbosetofile.php";
else include_once "../includes/verboseconsole.php";    

// se crea el semaforo para la cola de ejecucion
$semaforo=basename(__file__,".php").".pid";
file_put_contents($semaforo, "true");


// ####################################################################
// # FUNCION procesar_mail                                            #
// ####################################################################

function procesar_mail($cuerpo, $epoc_date) {
    
  
   // se divide el cuerpo en renglones
   $renglones = preg_split("/(\r\n|\n|\r)/", $cuerpo);
   $medio="";
   $noticia="";
   $medioEncontrado="";
   $noticiaNueva=false;
   
   global $cantidad;
   $cantidad=0;
   
    echo "<pre>";
    print_r($renglones);
    echo "</pre>";
    return;


    // se analiza cada renglon
    foreach ($renglones as $renglon) {
            
            $noticiaOriginal=$renglon;
            $renglon=strtolower($renglon);
            

            // se detectan los medios (cuando un renglon empieza con algunos de los token indicados)
            $medios='(^canal 5|^canal 3|^lt2|^lt3|^lt8|^radio 2|^canal 4|^canal 6|^somos rosario)';
            if(preg_match($medios, $renglon, $match) === 1) { 
                //guardarNoticia($noticia,$medioEncontrado,$medio,$epoc_date);
                $medioEncontrado=$match[0];
                $medio=$renglon;
                $noticiaNueva=false;
               
            } 

            

            // si hay un medio activo  (detectado) se recogen las noticias
            // de ese medio en particular que empiezan  por la hora y un guion
            // aqui se detecta elcomienzo de una noticia (y el final de otra)
            if(preg_match("/([0-9]|0[0-9]):([0-5][0-9]) – /", $renglon)===1){
                //guardarNoticia($noticia,$medioEncontrado,$medio,$epoc_date);
                $noticiaNueva=true;
            }

            
            //if ($noticiaNueva) $noticia.=$renglon." ";
            if ($noticiaNueva) $noticia.=$noticiaOriginal." ";
          
    }

  
  //se guarda la ultima noticia del mail (ya que no se detecta otra noticia u otro medio)
  // y se quita la firma final : -- rec archivos informes informes@reccomar​
  $firmaFinal="--   rec archivos informes  informes@reccomar";
  //echo "---->".$firmaFinal."<----<br>";
  $noticia=str_replace($firmaFinal,"", $noticia); 
  //guardarNoticia($noticia,$medioEncontrado,$medio,$epoc_date);


}
  

// ####################################################################
// # FUNCION guardarNoticia                                          #
// ####################################################################


function guardarNoticia(&$noticia,$medio,$programa,$epocmail) { 
    
     // la variable noticia se pasa por referencia

    global $cantidad;
    global $dbnews;
    global $uid;
    global $total;
    global $porcentaje;

    $hora=0;
    $segundosDelDia=0;

    if($noticia=="") return;
    if($medio=="") return;
    if($programa=="") return;

    $cantidad++;
    $total++;
    
    //se quita el medio del nombre del programa con el guion largo 
    $programa=str_replace($medio." – ","", $programa); 
    
    //se quita el medio del nombre del programa con el guion corto
    $programa=str_replace($medio." - ","", $programa); 

   
    $fecha="";
    $epocnews=$epocmail;
        
    // se verifica si el nombre del programa viene con una fecha
    // es asi cuando la sinstesis es de un programa del dia anterior 
    if (preg_match("/([0-9]{2})-([0-9]{2})-([0-9]{4})/", $programa, $match)===1) $fecha=$match[0];

   

    //se la fecha (si existe) se  quita del nombre del programa
    // y se transforma a formato epoch
    if($fecha!==""){ 
        $programa=str_replace("(".$fecha.")","", $programa); 
        $fecha=strtotime ( $fecha );
        $epocnews=$fecha;

    }

   
     
     //consolelog ("medio= ". $medio."<br>");
    
     //consolelog ("programa= ".$programa."<br>");
     //consolelog ("epocnews= ". $epocnews."<br>");
     //consolelog ("epocmail= ".$epocmail."<br>");
     //consolelog ($noticia);
     //consolelog ("<br>-------------------------------------<br>");

      
     $noticia=trim($noticia);
     

     // si la noticia tiene una sola palabra
     // no se indexa
     if(str_word_count ($noticia)<2) {
        consolelog ("no se indexa= ". $noticia."<br>");
         
        return;
        } 

     // para evitar algunos problemitas
    // de caracteres que  ensucian el sql
    $noticia =SQLite3::escapeString($noticia);
    $programa =SQLite3::escapeString($programa);


    // buscar el primer espacio desde el comienzo
    $offset=0;
    $pos = strpos($noticia, " ",$offset);
    // buscar el segundo espacio desde el comienzo
    $offset=$pos+1;
    $pos2 = strpos($noticia, " ",$offset);
   

    //se extrae la hora
    $hora=substr ( $noticia , 0 , $pos );

    //se le saca la hora a la noticia
    $noticia=substr ($noticia , $pos2+1 );

    //para saber los segundosDelDia
    $partes=explode(":",$hora);
    $segundosDelDia=3600*strval($partes[0])+60*strval($partes[1]);



     // la valoracion de la noticia que se hara en otor momento
     $valoracion="ninguna";
     
     //se genera un indentificador unico de la noticia
     // con el uid del mail mas la posicion de la noticia en ese mail
     $uidnews= strval($uid).strval($cantidad);

     consolelog ($uidnews."($total)<br>", $porcentaje);
    
    // se inserta la noticia en la base de datos
    // primero se comprueba si la noticia ya esta en la base de datos
    //$sqlquery= "SELECT 1 FROM news WHERE uid='$uidnews'";
    //$results = $dbnews->query($sqlquery);
    //$row = $results->fetchArray(SQLITE3_ASSOC);


    // si la noticia no esta se agrega a la base de datos
    //if ($row==false){
    //    consolelog("No esta ");
     $sqlquery="INSERT INTO news (epocmail, epocnews, medio, programa, noticia, uid, valoracion, uidmail, hora, segundosDelDia, vocabulario, valor, origen, url) VALUES ('$epocmail', '$epocnews', '$medio', '$programa', '$noticia','$uidnews', '$valoracion', '$uid', '$hora', '$segundosDelDia', 'no','NULL', 'REC','NULL') " ;
     $results = $dbnews->query($sqlquery);
        
    // } else  consolelog("YAAAAAA esta<br> ");
       
     

     // se vacia el contenedor de noticia para la proxima
     $noticia="";
     

} 

// ####################################################################
// # CUERPO PRINCIPAL                                                 #
// ####################################################################


//ob_start();
//ignore_user_abort(true); // Ignore user aborts and allow the script to run forever
set_time_limit(0); // for scripts that run really long

$tiempo_inicio = microtime(true);
$cantidad=0;
$total=0;
$uid="";


// se abre la base de datos de mails
$db = new SQLite3('../data/rawmailssfe.db') or die('no se puede abrir la base de datos ../data/rawmailssfe.db');


// se abre la base de datos de noticias
$dbnews = new SQLite3('../data/noticias.db') or die('no se puede abrir la base de datos ../data/noticias.db');

$sqlquery= "SELECT * FROM emails WHERE procesado='no'";
$results = $db->query($sqlquery);



// para saber la cantidad de filas a procesar - procesadas
$columnas = $db->query("SELECT COUNT(*) as count FROM emails WHERE procesado='no'");
$cantidadCol = $columnas->fetchArray();
$numRows = $cantidadCol['count'];


consolelog("cantidad de filas a procesar = ".$numRows);

// cuidado con esto que sino lo epoc times dan con un dia de diff
date_default_timezone_set ( "America/Argentina/Buenos_Aires" );

$mailprocesados=0;
$porcentaje=0;
while($res = $results->fetchArray(SQLITE3_ASSOC)){ 

        $subject= $res['subject'];

         
        $tema=strtolower($subject);  

        $fecha=$res['date'];
        $fecha_epoc=$res['udate'];
        $uid=$res['uid'];
        $id=$res['id'];
 

        
        $sqlquery= "UPDATE emails SET  procesado = 'si' WHERE id='$id'";
        //$resultado = $db->query($sqlquery);
        

        // si no es un resumen
        // o una sintesis se saltea
        if(preg_match('(compacto|sintesis)', $tema) !== 1) continue;

      

        
        $body= $res['bodytext'];
        
       
       

        // se quitan los astericos que ensucian
        //$body = str_replace( '*', '', $body);
        //$body = str_replace( '.', '', $body);
    
        
        echo "<h2>$subject</h2>";
        echo "<h4>$fecha</h4>";
        echo "<h4>$fecha_epoc</h4>";

        procesar_mail($body, $fecha_epoc);


                
        $mailprocesados++;

        $porcentaje=($mailprocesados/$numRows)*100;
        
        consolelog("",$porcentaje);      
     
       //if ($mailprocesados > 20 ) break;
       
} 



$tiempo_fin = microtime(true);
$tiempo = $tiempo_fin - $tiempo_inicio;

consolelog("Tiempo empleado--> " . ($tiempo_fin - $tiempo_inicio));

consolelog("", 'TERMINATE'); 

unlink($semaforo);    
?>




