 <?php


include_once('../includes/analisis_texto.php');

// se crea el semaforo para la cola de ejecucion
$semaforo=basename(__file__,".php").".pid";
file_put_contents($semaforo, "true");

// ####################################################################
// # FUNCION PARA MOSTRAR MENSAJES CUANDO UN PROCESO ES LARGO         #
// ####################################################################

function consolelog ($message) {
    echo $message.PHP_EOL;
    ob_flush();
    flush();
}





// ####################################################################
// # CUERPO PRINCIPAL                                                 #
// ####################################################################


ob_start();
ignore_user_abort(true); // Ignore user aborts and allow the script to run forever
set_time_limit(0); // for scripts that run really long

$tiempo_inicio = microtime(true);

//ini_set('memory_limit', '-1');

require_once('../sentiment/valencias.php');
$sat = new SentimentAnalyzer();


// se cargan los trigers para filtrar las noticias que interesan
$fileTriggers='../definiciones/trigers.json';
$trigers_words=array();
$trigers_words = json_decode(file_get_contents($fileTriggers), true);



// se abre la base de datos de noticias
$db = new SQLite3('../data/noticias.db') or die('no se puede abrir la base de datos data/noticias.db');


// se traen solo las noticias de x dias atras desde la fceha mas
// antigua a la  actual (los primeros registros son las fechas mas alejadas);




$final=strtotime("2017-04-25");
$inicio=strtotime("2017-03-25");
//$inicio=strToTime ( "today 03:00" ); 






//if(isset($_GET["inicio"])) $inicio=$_GET["inicio"];
//if(isset($_GET["final"])) $final=$_GET["final"];


$sqlquery= "SELECT * FROM news WHERE epocnews > $inicio AND epocnews < $final AND origen='REC' and medio='canal 3' ORDER BY epocnews ASC";
$results = $db->query($sqlquery);


// para saber la cantidad de filas a procesar - procesadas
$columnas = $db->query("SELECT COUNT(*) as count FROM news WHERE epocnews > $inicio AND epocnews < $final AND origen='REC' ");
$cantidadCol = $columnas->fetchArray();
$numRows = $cantidadCol['count'];
consolelog("total de noticias a procesar en el periodo = ".$numRows);



$meses = array("ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SEP","OCT","NOV","DIC");
$fecha_inicio= date("d",$inicio)." ".$meses[date("n",$inicio)-1];
$fecha_final= date("d",$final)." ".$meses[date("n",$final)-1];




$neu=0;
$neg=0;
$pos=0;
$dud=0;
$cantidad=0;


$pertinentes_neu=0;
$pertinentes_neg=0;
$pertinentes_pos=0;
$pertinentes_dud=0;
$count_pertinentes=0;



$totalTextoPos="";
$totalTextoNeg="";

// el cuerpo total de texto de las noticias
$totaltexto="";

$noticiasGestion=array();

///// LOOP PRINCIPAL ///////

$salida="";

while($res = $results->fetchArray(SQLITE3_ASSOC)){ 

        
        // el html_entity_decode se agrego a partir de que
        // se adicionaron las noticias generadas por sitios web
        // donde algunos caracteres especiales (acentos y eñes)
        // viene codificados como  entidades html           
        $noticia= html_entity_decode($res['noticia']);         
       
 
        // se quitan los espacios antes y despues de la noticia
        $noticia=trim($noticia);

        // se remueven los espacios extras entre palabras
        $noticia = preg_replace('/\s+/', ' ', $noticia);
        
        // si la noticia tiene una sola palabra
        // no se procesa
        // si no se encuentra un espacio es una sola palabra
        if(strchr($noticia," ")===false) continue; 


        //si la noticia tiene menos de 32 caracteres no se analiza
        if(strlen($noticia)<=32)  continue; 
       

        // la noticia original conserva las mayusculas/minusculas 
        $noticiaOriginal= $noticia;

        // la noticia para ser analizada se normaliza a minusculas
        $noticia= strtolower($noticia);
        $cantidad++;  

      
       


        $fecha= date('Y-m-d', $res['epocnews']);
        $medio=$res['medio'];
       
        // se cuenta la cantidad de noticias por dia
        if(!isset($NoticiasDiarias[$fecha])) $NoticiasDiarias[$fecha]=0;       
        $NoticiasDiarias[$fecha]++; 

        // se verifica si la noticia es pertinente
        // esto es, si contiene alguno de los triggers de interes
        $pertinente=false;
        foreach($trigers_words as $key => $value){
                if(strpos($noticia,$key)!==false) {
                      $pertinente=true;
                      if(!isset($noticiasGestion[$key]))$noticiasGestion[$key]=0;
                      $noticiasGestion[$key]++;
                      break;
                }
                
        }   
        
       
         // si la noticia no es pertinente no se procesa y se sigue con otra
        if ($pertinente==false)continue;
        $count_pertinentes++;  

             
      
        
        // se verifica que la cantidad de fechas no sea
        // superior a la cantidad de dias que se quiere analizar
        // si es asi se sale
        //if (count($fechas)>$dias) break;
        $fechas[$fecha]=1;

        // se determina el humor de la noticia 
        $resultado = $sat->analyzeSentence($noticia);
               
        if($resultado['sentimiento']=="neu")  $neu++; 
        if($resultado['sentimiento']=="pos")  {$pos++; $totalTextoPos.=$noticia.PHP_EOL; }
        if($resultado['sentimiento']=="neg")  {$neg++; $totalTextoNeg.=$noticia.PHP_EOL; }
        if($resultado['sentimiento']=="dud")  $dud++; 
       
        
        $id=$res['id'];
        $programa=$res['programa'];
        $humor=$resultado['sentimiento'];
        $valor=$resultado['valor'];
        $normalizado=$resultado['normalizado'];
       

        $personas=utf8_decode(contarPersonajes($noticia, 5, true));

        //$entidades=utf8_decode(extraer_entidades($noticiaOriginal, 5, true));
        $hora=$res['hora'];
         
        $salida.=$fecha." | ".$medio." | ".$hora." | ".$humor." | ".$personas." | ".utf8_decode($noticiaOriginal).PHP_EOL;

        
      
} 

///// FIN LOOP PRINCIPAL ///////


file_put_contents("auditoria.csv", $salida);


$tiempo_fin = microtime(true);
$tiempo = $tiempo_fin - $tiempo_inicio;

echo "<h2>Noticias de gestion: " . $count_pertinentes."</h2>";


echo "<h2>Tiempo empleado: " . ($tiempo_fin - $tiempo_inicio)."</h2>";

unlink($semaforo);


?>
