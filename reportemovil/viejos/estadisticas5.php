 <?php


include_once "graficos.php";
include_once('includes/analisis_texto.php');



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
//ignore_user_abort(true); // Ignore user aborts and allow the script to run forever
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


//$final=strtotime("today 8:00");

$final=strtotime("today 8:00");
$inicio=strToTime ( "-8 days 22:00" ); 


if(isset($_GET["inicio"])) $inicio=$_GET["inicio"];
if(isset($_GET["final"])) $final=$_GET["final"];






$sqlquery= "SELECT * FROM news WHERE epocnews > $inicio AND epocnews < $final  ORDER BY epocnews ASC";


$results = $db->query($sqlquery);



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

$parciales=array();
$NoticiasDiarias=array();
$PertinentesDiarias=array();
$PositivasDiarias=array();
$NegativasDiarias=array();
$medioPositivo=array();
$medioNegativo=array();
$totalMedio=array();



// el cuerpo total de texto de las noticias
$totaltexto="";

///// LOOP PRINCIPAL ///////

while($res = $results->fetchArray(SQLITE3_ASSOC)){ 

        
                  
        $noticia= $res['noticia'];         
       
 
        // se quitan los espacios antes y despues de la noticia
        $noticia=trim($noticia);

        // se remueven los espacios extras entre palabras
        $noticia = preg_replace('/\s+/', ' ', $noticia);
        
        // si la noticia tiene una sola palabra
        // no se procesa
        // si no se encuentra un espacio es una sola palabra
        if(strchr($noticia," ")===false) continue; 

        

        // la noticia original conserva las mayusculas/minusculas 
        $noticiaOriginal= $noticia;

        // la noticia para ser analizada se normaliza a minusculas
        $noticia= strtolower($noticia);
        $cantidad++;  

      
        // se agrega la noticia original al corpus de texto
        $totaltexto.=$noticiaOriginal.PHP_EOL; 


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
                      break;
                }
                
        }   
        
       
         // si la noticia no es pertinente no se procesa y se sigue con otra
        if ($pertinente==false)continue;
        $count_pertinentes++;   

        // se determina el humor de la noticia 
        $resultado = $sat->analyzeSentence($noticia);
               
        if($resultado['sentimiento']=="neu")  $neu++; 
        if($resultado['sentimiento']=="pos")  $pos++; 
        if($resultado['sentimiento']=="neg")  $neg++; 
        if($resultado['sentimiento']=="dud")  $dud++; 
       
        
        // se cuenta la cantidad de noticias pertinentes por dia
        if(!isset($PertinentesDiarias[$fecha])) $PertinentesDiarias[$fecha]=0;       
        $PertinentesDiarias[$fecha]++; 

       
        // se cuenta la cantidad de noticias positivas y negativas por dia
        if(!isset($PositivasDiarias[$fecha])) $PositivasDiarias[$fecha]=0;  
        if(!isset($NegativasDiarias[$fecha])) $NegativasDiarias[$fecha]=0;  

        // para contar el medio mas positivo y el mas negativo  
        if(!isset($medioPositivo[$medio])) $medioPositivo[$medio]=0;    
        if(!isset($medioNegativo[$medio])) $medioNegativo[$medio]=0;    
        if(!isset($totalMedio[$medio])) $totalMedio[$medio]=0;    
 
     

        if($resultado['sentimiento']=="neu")  $pertinentes_neu++; 
        if($resultado['sentimiento']=="pos")  { $pertinentes_pos++;  $PositivasDiarias[$fecha]++; $medioPositivo[$medio]++; }
        if($resultado['sentimiento']=="neg")  { $pertinentes_neg++;  $NegativasDiarias[$fecha]++; $medioNegativo[$medio]++; }
        if($resultado['sentimiento']=="dud")  $pertinentes_dud++; 

        $totalMedio[$medio]++;  

        $id=$res['id'];
        $programa=$res['programa'];
        $humor=$resultado['sentimiento'];
        $valor=$resultado['valor'];
        $normalizado=$resultado['normalizado'];
       
        if(!isset($parciales[$medio][$humor]))$parciales[$medio][$humor]=0;

        $parciales[$medio][$humor]++;
      
} 

///// FIN LOOP PRINCIPAL ///////









// para los datos de la polarizacion de los medios
// se transforma en porcentaje las cantidades de noticias positivas y negativas
foreach ( $totalMedio as $key => $value){
   $medioNegativo[$key]=number_format(($medioNegativo[$key]/$value),2);
   $medioPositivo[$key]=number_format(($medioPositivo[$key]/$value),2);
}   

// se clasifica de los medios mas positivos a los mas negativos
arsort($medioPositivo);

// se mete todo en una sola array (medios y porcentajes positivos y negativos)
$i=0;
$polarizacion=array();
foreach ($medioPositivo as $key => $value) {
    
   $polarizacion[$i]['medio']=$key;
   $polarizacion[$i]['positiva']=$value;
   $polarizacion[$i]['negativa']=$medioNegativo[$key];

   if($value >=50) $polarizacion[$i]['color']="#00ff00";
   if($value <50) $polarizacion[$i]['color']="#ff0000";
   
    
   $i++;
}





 $total=$neg + $pos + $neu + $dud;
 $pneg=($neg / $total);
 $pneu=($neu / $total);
 $ppos=($pos / $total);
 $pdud=($dud / $total);
 




IF ( $pertinentes_pos > $pertinentes_neg )  { $humor="POSITIVO"; $colorhumor="#00ff00";}
IF ( $pertinentes_pos < $pertinentes_neg )  { $humor="NEGATIVO"; $colorhumor="#ff0000";}
IF ( $pertinentes_pos == $pertinentes_neg ) { $humor="NEUTRO";   $colorhumor="#f0f0f0";}


$PercentgestionPositivas=($pertinentes_pos/$count_pertinentes);
$PercentgestionNegativas=($pertinentes_neg/$count_pertinentes);






// el primer panel///////////////////

$porcentaje_pertinentes= $count_pertinentes/$cantidad;
if ($porcentaje_pertinentes<=0.01) $simple="1 de cada 100 noticias";
if ($porcentaje_pertinentes==0.02) $simple="2 de cada 100 noticias";
if ($porcentaje_pertinentes==0.03) $simple="3 de cada 100 noticias";
if ($porcentaje_pertinentes==0.04) $simple="4 de cada 100 noticias";
if ($porcentaje_pertinentes==0.05) $simple="1 de cada 20 noticias";
if ($porcentaje_pertinentes>=0.05 && $porcentaje_pertinentes<=8 ) $simple="1 de cada 20 noticias";
if ($porcentaje_pertinentes>=0.9 && $porcentaje_pertinentes<=18 ) $simple="1 de cada 10 noticias";
if ($porcentaje_pertinentes>=0.19 && $porcentaje_pertinentes<=23 ) $simple="1 de cada 5 noticias";
if ($porcentaje_pertinentes>=0.24 && $porcentaje_pertinentes<=28 ) $simple="1 de cada 4 noticias";
if ($porcentaje_pertinentes>=0.29 && $porcentaje_pertinentes<=33 ) $simple="1 de cada 3 noticias";
if ($porcentaje_pertinentes>=0.34 && $porcentaje_pertinentes<=48 ) $simple="2 de cada 5 noticias";
if ($porcentaje_pertinentes>=0.49 && $porcentaje_pertinentes<=57 ) $simple="1 de cada 2 noticias";
if ($porcentaje_pertinentes>=0.58 && $porcentaje_pertinentes<=67 ) $simple="3 de cada 5 noticias";
if ($porcentaje_pertinentes>=0.68 && $porcentaje_pertinentes<=77 ) $simple="7 de cada 10 noticias";
if ($porcentaje_pertinentes>=0.78 && $porcentaje_pertinentes<=87 ) $simple="4 de cada 5 noticias";
if ($porcentaje_pertinentes>=0.88 && $porcentaje_pertinentes<=95 ) $simple="9 de cada 10 noticias";
if ($porcentaje_pertinentes>=0.95 && $porcentaje_pertinentes<=100 ) $simple="TODAS LAS NOTICIAS";

$porcentaje_pertinentes=$porcentaje_pertinentes."%";


///////////////////////////////////////////////////////////
// el periodo analizado
//////////////////////////////////////////////////////////
periodo($fecha_inicio,$fecha_final );


///////////////////////////////////////////////////////////
// los dos graficos circulares de noticias malas y buenas
//////////////////////////////////////////////////////////
$labelneg=$pertinentes_neg. " de ".$count_pertinentes;
gauge($PercentgestionNegativas,"#ff0000","#0f0f0f",150,600,$labelneg,"NOTICIAS NEGATIVAS", "gaugeneg.png");

$labelpos=$pertinentes_pos. " de ".$count_pertinentes;
gauge($PercentgestionPositivas,"#00ff00","#0f0f0f",150,600,$labelpos,"NOTICIAS POSITIVAS", "gaugepos.png");

////////////////////////////////////////////////////////////////////
// el texto de informacion de cuantas noticias son sobre gestion
///////////////////////////////////////////////////////////////////
$labelinfo=$count_pertinentes. " de ".$total;
infotext("INTERES DE LOS MEDIOS EN LA GESTIÓN", $porcentaje_pertinentes, $simple, $labelinfo,600);

////////////////////////////////////////////////////////////////////
// el humor sobre la gestion
///////////////////////////////////////////////////////////////////
humor($humor,600);

////////////////////////////////////////////////////////////////////
// evolucion noticias buenas vs malas
///////////////////////////////////////////////////////////////////
foreach ( $PertinentesDiarias as $key => $value){
   $valoresX[]=date('d/m',strtotime($key));;
}

evolucionPN($NegativasDiarias,$PositivasDiarias,$valoresX);


////////////////////////////////////////////////////////////////////
// evolucion noticias totales buenas vs gestion
///////////////////////////////////////////////////////////////////
$diarias=array_intersect_key ( $NoticiasDiarias ,$PertinentesDiarias);
evolucionTotales($PertinentesDiarias,$diarias,$valoresX);


////////////////////////////////////////////////////////////////////
// Polarizacion de los medios sin gap
///////////////////////////////////////////////////////////////////
polarizacion($polarizacion, FALSE);



////////////////////////////////////////////////////////////////////
// HISTOGRAMA DE TEMAS MAS MENCIONADOS
///////////////////////////////////////////////////////////////////

// se limpia el texto de algunas expresiones ruidosas
$limpiar=array('recinfoarch@gmailcom','Llamado de oyentes','Corresponsal Santa Fe');
$totaltexto=str_replace($limpiar, "", $totaltexto);
$salida2=contarBigramas($totaltexto,15);

histograma($salida2,"temas.png");

/*
$entidades=extraer_entidades($totaltexto,100);
//se quitan la entidades del total de texto
//$sacar= array_keys($entidades);
//$totaltexto=str_replace ($sacar,"--#--", $totaltexto);
$salida4=contarTetragramas($totaltexto,25);
$salida3=contarTrigramas($totaltexto,25);

$salida1=contarMonogramas($totaltexto,15);


$temas= array_merge ( $salida3 , $salida2);



arsort($temas);  
$temas=array_slice($temas, 0, 25, true);



echo "<pre>";
print_r($temas);
print_r($entidades);
print_r($salida1);
print_r($salida2);
print_r($salida3);
print_r($salida4);
echo "------------------<br>";
echo($totaltexto); 
*/



///////////////////////////////////////////////////////////////////////////////////////////////////////////////
///// AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI ////
///// ZONA DE PRUEBAS .....................
///////////////////////////////////////////////////////////////////////////////////////////////////////////////









///////////////////////////////////////////////////////////////////////////////////////////////////////////////
///// AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI ////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////
// se actualiza el servidor "vivo"
// http://robotcountry.net/simon/auditoria/reportemovil
///////////////////////////////////////////////////////////////////
$path="subir.php";
$actualdir=dirname($_SERVER['REQUEST_URI']);
$fullurl="http://".$_SERVER['HTTP_HOST'].$actualdir;
$url=$fullurl."/".$path;
//get_headers($url);



$tiempo_fin = microtime(true);
$tiempo = $tiempo_fin - $tiempo_inicio;

echo "<h2>Tiempo empleado: " . ($tiempo_fin - $tiempo_inicio)."</h2>";



?>
