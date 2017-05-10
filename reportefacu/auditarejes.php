 <?php


include_once "graficos.php";
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



$final=strtotime("2017-04-30");
$inicio=strtotime("2017-04-01");

$cache="cache/cache7";



//if(isset($_GET["inicio"])) $inicio=$_GET["inicio"];
//if(isset($_GET["final"])) $final=$_GET["final"];


$sqlquery= "SELECT * FROM news WHERE epocnews > $inicio AND epocnews < $final  ORDER BY epocnews ASC";
$results = $db->query($sqlquery);


// para saber la cantidad de filas a procesar - procesadas
$columnas = $db->query("SELECT COUNT(*) as count FROM news WHERE epocnews > $inicio AND epocnews < $final ");
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

$parciales=array();
$NoticiasDiarias=array();
$PertinentesDiarias=array();

$PositivasDiarias=array();
$NegativasDiarias=array();
$NeutrasDiarias=array();

$medioPositivo=array();
$medioNegativo=array();
$medioNeutro=array();

$totalMedio=array();

$fechas=array();

$totalTextoPos="";
$totalTextoNeg="";

// el cuerpo total de texto de las noticias
$totaltexto="";

$noticiasGestion=array();

///// LOOP PRINCIPAL ///////

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

        //$trigers["lifschitz"]="miguel";
        //$trigers["Lifschitz"]="miguel";

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

         // se agrega la noticia original al corpus de texto
        $totaltexto.=$noticia.PHP_EOL; 
       
      
        
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
       
        
        // se cuenta la cantidad de noticias pertinentes por dia
        if(!isset($PertinentesDiarias[$fecha])) $PertinentesDiarias[$fecha]=0;       
        $PertinentesDiarias[$fecha]++; 

       
        // se cuenta la cantidad de noticias positivas y negativas por dia
        if(!isset($PositivasDiarias[$fecha])) $PositivasDiarias[$fecha]=0;  
        if(!isset($NegativasDiarias[$fecha])) $NegativasDiarias[$fecha]=0;  
        if(!isset($NeutrasDiarias[$fecha])) $NeutrasDiarias[$fecha]=0; 

        // para contar el medio mas positivo y el mas negativo  
        if(!isset($medioPositivo[$medio])) $medioPositivo[$medio]=0;    
        if(!isset($medioNegativo[$medio])) $medioNegativo[$medio]=0;  
        if(!isset($medioNeutro[$medio])) $medioNeutro[$medio]=0;    
        if(!isset($totalMedio[$medio])) $totalMedio[$medio]=0;    
 
     
        if($resultado['sentimiento']=="neu")  { $pertinentes_neu++;   $NeutrasDiarias[$fecha]++;  $medioNeutro[$medio]++; }
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



consolelog("<br>total de noticias pertinentes = ".$count_pertinentes);

echo "<pre>";
arsort($totalMedio);
print_r($totalMedio);

arsort($noticiasGestion);
print_r($noticiasGestion);

echo "<h3>Positivas: ".$pertinentes_pos."</h3>";
echo "<h3>Negativas: ".$pertinentes_neg."</h3>";
echo "<h3>Neutras: ".$pertinentes_neu."</h3>";



// para los datos de la polarizacion de los medios
// se transforma en porcentaje las cantidades de noticias positivas y negativas
foreach ( $totalMedio as $key => $value){
   $medioNegativo[$key]=number_format(($medioNegativo[$key]/$value),2);
   $medioPositivo[$key]=number_format(($medioPositivo[$key]/$value),2);
   $medioNeutro[$key]=number_format(($medioNeutro[$key]/$value),2);
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
   $polarizacion[$i]['neutra']=$medioNeutro[$key];


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

$porcentaje_pertinentes=number_format($count_pertinentes/$cantidad,2);


if ($porcentaje_pertinentes>0       && $porcentaje_pertinentes<=0.01 )  $simple="1 de cada 100 noticias";
if ($porcentaje_pertinentes>0.01    && $porcentaje_pertinentes<=0.02 )  $simple="2 de cada 100 noticias";
if ($porcentaje_pertinentes>0.02    && $porcentaje_pertinentes<=0.03 )  $simple="3 de cada 100 noticias";
if ($porcentaje_pertinentes>0.03    && $porcentaje_pertinentes<=0.04 )  $simple="4 de cada 100 noticias"; 
if ($porcentaje_pertinentes>0.04    && $porcentaje_pertinentes<0.1 )    $simple="1 de cada 20 noticias"; 

if ($porcentaje_pertinentes>=0.1    && $porcentaje_pertinentes<=0.18 )  $simple="1 de cada 10 noticias";
if ($porcentaje_pertinentes>=0.19   && $porcentaje_pertinentes<=0.23 )  $simple="1 de cada 5 noticias";
if ($porcentaje_pertinentes>=0.24   && $porcentaje_pertinentes<=0.28 )  $simple="1 de cada 4 noticias";
if ($porcentaje_pertinentes>=0.29   && $porcentaje_pertinentes<=0.33 )  $simple="1 de cada 3 noticias";
if ($porcentaje_pertinentes>=0.34   && $porcentaje_pertinentes<=0.48 )  $simple="2 de cada 5 noticias";
if ($porcentaje_pertinentes>=0.49   && $porcentaje_pertinentes<=0.57 )  $simple="1 de cada 2 noticias";
if ($porcentaje_pertinentes>=0.58   && $porcentaje_pertinentes<=0.67 )  $simple="3 de cada 5 noticias";
if ($porcentaje_pertinentes>=0.68   && $porcentaje_pertinentes<=0.77 )  $simple="7 de cada 10 noticias";
if ($porcentaje_pertinentes>=0.78   && $porcentaje_pertinentes<=0.87 )  $simple="4 de cada 5 noticias";
if ($porcentaje_pertinentes>=0.88   && $porcentaje_pertinentes<=0.95 )  $simple="9 de cada 10 noticias";
if ($porcentaje_pertinentes>=0.95   && $porcentaje_pertinentes<=1 )     $simple="TODAS LAS NOTICIAS";

$porcentaje_pertinentes=$porcentaje_pertinentes."%";


///////////////////////////////////////////////////////////
// el periodo analizado
//////////////////////////////////////////////////////////
periodo($fecha_inicio,$fecha_final, $periodo );


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
$labelinfo=$count_pertinentes. " de ".$cantidad;
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
polarizacion($polarizacion, TRUE);



////////////////////////////////////////////////////////////////////
// HISTOGRAMA DE PERSONAS MAS MENCIONADOS
///////////////////////////////////////////////////////////////////


// se limpia el texto  de algunas nombres "ruidosos"
// ver el archivo definiciones/nonames.json para saber  cuales son
$limpiarFile=file_get_contents("../definiciones/nonames.json");
$limpiar=json_decode($limpiarFile,TRUE);
$textonombres=str_replace ($limpiar,"####", $totaltexto);


$entidades=contarPersonajes($textonombres, 10);
histograma($entidades,"personas.png", "LAS 10 PERSONAS MAS MENCIONADOS",20,133,204);








////////////////////////////////////////////////////////////////////
// HISTOGRAMA DE TEMAS MAS MENCIONADOS
///////////////////////////////////////////////////////////////////


 // se extraen los acentos
// porque la base de datos de nombres no los tiene   
$contilde=array('á','é','í','ó','ú','Á','É','Í','Ó','Ú');
$sintilde=array('a','e','i','o','u','A','E','I','O','U');
$totaltexto=str_replace($contilde, $sintilde , $totaltexto);

//se quitan la entidades (personajes) del total de texto
//para que no sean contados como temas
$sacar= array_keys($entidades);
$totaltexto=str_replace ($sacar,"", $totaltexto);


// se limpia el texto  de algunas expresiones "ruidosas"
// ver el archivo definiciones/limpiar.json para saber  cuales son
$limpiarFile=file_get_contents("../definiciones/limpiar.json");
$limpiar=json_decode($limpiarFile,TRUE);
$totaltexto=str_replace ($limpiar,"####", $totaltexto);


$salida2=contarBigramas($totaltexto,10);

echo "<pre>";
print_r($salida2);

histograma($salida2,"temas.png", "TEMAS MAS MENCIONADOS",0,100,150);





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

unlink($semaforo);


?>
