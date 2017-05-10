<?php


include_once "graficos.php";


// ####################################################################
// # FUNCION PARA TOKENIZAR UNA STRING                                #
// ####################################################################

function tokenise($oracion) 
        {
            
            
            setlocale(LC_ALL, 'es_ES');
            $oracion = strtolower($oracion);
            
            // antes de tokenizar se reemplaza santa fe por santa_fe
            $oracion = str_replace("santa fe", "santa_fe", $oracion);

            // antes de tokenizar se reemplaza gobierno nacional por gobierno_nacional
            $oracion = str_replace("gobierno nacional", "gobierno_nacional", $oracion);

            // antes de tokenizar se reemplaza gobierno provincial por gobierno_provincial
            $oracion = str_replace("gobierno provincial", "gobierno_provincial", $oracion);


            preg_match_all('/[\w]+/iu', $oracion, $matches);

            // se extraen los tokens que son stopwords 
            $palabras=removeStopwords($matches[0]);
            return $palabras;
 
        }


// ####################################################################
// # FUNCION EXTRAER LAS STOPWORDS                                   #
// # no solo quita los stoprwords del español, tambien quita algunas #
// # palabras "extrañas" que se fueron encontrado en el vocabulario  #
// ####################################################################
function removeStopwords ($input)
       {
        
        $stopwords = array('de','la','que','el','en','y','a','los','del','se','las','por','un','para','con','no','una','su','al','lo','como','más','pero','sus','le','ya','o','este','sí','porque','esta','entre','cuando','muy','sin','sobre','también','me','hasta','hay','donde','quien','desde','todo','nos','durante','todos','uno','les','ni','contra','otros','ese','eso','ante','ellos','e','esto','mí','antes','algunos','qué','unos','yo','otro','otras','otra','él','tanto','esa','estos','mucho','quienes','nada','muchos','cual','poco','ella','estar','estas','algunas','algo','nosotros','mi','mis','tú','te','ti','tu','tus','ellas','nosotras','vosostros','vosostras','os','mío','mía','míos','mías','tuyo','tuya','tuyos','tuyas','suyo','suya','suyos','suyas','nuestro','nuestra','nuestros','nuestras','vuestro','vuestra','vuestros','vuestras','esos','esas','estoy','estás','está','estamos','estáis','están','esté','estés','estemos','estéis','estén','estaré','estarás','estará','estaremos','estaréis','estarán','estaría','estarías','estaríamos','estaríais','estarían','estaba','estabas','estábamos','estabais','estaban','estuve','estuviste','estuvo','estuvimos','estuvisteis','estuvieron','estuviera','estuvieras','estuviéramos','estuvierais','estuvieran','estuviese','estuvieses','estuviésemos','estuvieseis','estuviesen','estando','estado','estada','estados','estadas','estad','he','has','ha','hemos','habéis','han','haya','hayas','hayamos','hayáis','hayan','habré','habrás','habrá','habremos','habréis','habrán','habría','habrías','habríamos','habríais','habrían','había','habías','habíamos','habíais','habían','hube','hubiste','hubo','hubimos','hubisteis','hubieron','hubiera','hubieras','hubiéramos','hubierais','hubieran','hubiese','hubieses','hubiésemos','hubieseis','hubiesen','habiendo','habido','habida','habidos','habidas','soy','eres','es','somos','sois','son','sea','seas','seamos','seáis','sean','seré','serás','será','seremos','seréis','serán','sería','serías','seríamos','seríais','serían','era','eras','éramos','erais','eran','fui','fuiste','fue','fuimos','fuisteis','fueron','fuera','fueras','fuéramos','fuerais','fueran','fuese','fueses','fuésemos','fueseis','fuesen','sintiendo','sentido','sentida','sentidos','sentidas','siente','sentid','tengo','tienes','tiene','tenemos','tenéis','tienen','tenga','tengas','tengamos','tengáis','tengan','tendré','tendrás','tendrá','tendremos','tendréis','tendrán','tendría','tendrías','tendríamos','tendríais','tendrían','tenía','tenías','teníamos','teníais','tenían','tuve','tuviste','tuvo','tuvimos','tuvisteis','tuvieron','tuviera','tuvieras','tuviéramos','tuvierais','tuvieran','tuviese','tuvieses','tuviésemos','tuvieseis','tuviesen','teniendo','tenido','tenida','tenidos','tenidas','tened' );
      
      
          $especiales= array( 'ñ','Ó', 'á','í','ó', 'xi', 'xiv', 'xix', 'xl', 'xn', 'xvi', 'xvii', 'xxi', 'xxl', 'xxx', 'xxxi', 'xxxii' );
         
          $vocabularioCorto= array( 'nota','comunicación','telefónica', 'móvil', 'espectáculos', 'grabada','años','dos','deportes' );
         
          // se fusionan las stopwords con las palabras "extrañas" extraidas del vocabulario 
          $stopwords=array_merge ( $stopwords , $especiales, $vocabularioCorto );

          //se ret
          return array_diff($input, $stopwords);
           
      }

// ####################################################################
// # FUNCION PARA cCONTAR LOS BIGRAMAS                                #
// ####################################################################
function contarBigramas($noticia){

        global $vocabulario2;

        $words=tokenise($noticia);
        $wordAnterior=""; 
        foreach ($words as $word)
        {
                    
                  
                    // si la palabra comienza con un numero no se suma al vocabulario
                    if (preg_match('/^[0-9]{1,}/', $word)) continue;

                    // si empieza con el caracter º y despues un numero no se suma al vocabulario
                    if (preg_match('/^º[0-9]{1,}/', $word)) continue;

                    // si empieza con el caracter nº  no se suma al vocabulario
                    if (preg_match('/^nº/', $word)) continue;

                    // si empieza con www no se suma al vocabulario
                    if (preg_match('/^www/', $word)) continue;
                    

                    // si la palabra tiene menos de dos caracteres no se suma
                    if (strlen(utf8_decode($word))<2) continue;
                    
                    $bigrama=$wordAnterior." ".$word;
                     // si la palabra no esta en el vocabulario se agrega
                    if (!isset($vocabulario2[$bigrama]) && $wordAnterior!="") $vocabulario2[$bigrama]= 0;
                    if (isset($vocabulario2[$bigrama])) $vocabulario2[$bigrama]++;
                   
                  
                    $wordAnterior=$word;
        }

}


// ####################################################################
// # FUNCION PARA CONTAR LOS TRIGRAMAS                                #
// ####################################################################
function contarTrigramas($noticia){

        global $vocabulario3;

        $words=tokenise($noticia);
        $wordAnterior1="";
        $wordAnterior2="";
         
        foreach ($words as $word)
        {
                    
                  
                    // si la palabra comienza con un numero no se suma al vocabulario
                    if (preg_match('/^[0-9]{1,}/', $word)) continue;

                    // si empieza con el caracter º y despues un numero no se suma al vocabulario
                    if (preg_match('/^º[0-9]{1,}/', $word)) continue;

                    // si empieza con el caracter nº  no se suma al vocabulario
                    if (preg_match('/^nº/', $word)) continue;

                    // si empieza con www no se suma al vocabulario
                    if (preg_match('/^www/', $word)) continue;
                    

                    // si la palabra tiene menos de dos caracteres no se suma
                    if (strlen(utf8_decode($word))<2) continue;
                    
                    $trigrama=$wordAnterior1." ".$wordAnterior2." ".$word;
                     // si la palabra no esta en el vocabulario se agrega
                    if (!isset($vocabulario3[$trigrama]) && $wordAnterior1!="") $vocabulario3[$trigrama]= 0;
                    if (isset($vocabulario3[$trigrama])) $vocabulario3[$trigrama]++;
                   
                  
                    $wordAnterior1=$wordAnterior2;
                    $wordAnterior2=$word;
                                 
        }

}

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


// el vocabulario de los bigramas
$vocabulario2=array();

// el vocabulario de los trigramas
$vocabulario3=array();



while($res = $results->fetchArray(SQLITE3_ASSOC)){ 

        
      
        
        $noticia= $res['noticia'];

        

         //////////////////////////////////////////   
        // Conteo de bigramas y trigramas
       
       contarBigramas($noticia);
          
       contarTrigramas($noticia);
            


        $noticia=trim($noticia);
        // se remueven los espacios extras entre palabras
        $noticia = preg_replace('/\s+/', ' ', $noticia);


        // si la noticia tiene una sola palabra
        // no se procesa
        // si no se encuentra un espacio es una sola palabra
        if(strchr($noticia," ")===false) continue; 

        $cantidad++;  

        // se verifica si la noticia es pertinente
        // esto es, si contiene alguno de los triggers de interes
        $pertinente=false;

        
       
        // se determina el humor de la noticia 
        $resultado = $sat->analyzeSentence($noticia);
          

        
        if($resultado['sentimiento']=="neu")  $neu++; 
        if($resultado['sentimiento']=="pos")  $pos++; 
        if($resultado['sentimiento']=="neg")  $neg++; 
        if($resultado['sentimiento']=="dud")  $dud++; 
       
        
        
        foreach($trigers_words as $key => $value){
                if(strpos($noticia,$key)!==false) {
                      $pertinente=true;
                      break;
                }
                
        }   
        
        $fecha= date('Y-m-d', $res['epocnews']);
        $medio=$res['medio'];

        

        //echo "<pre>";
        //echo $medio."<br>";
        //echo $res['id']."<br>";
        //echo $noticia."<br>";
        //print_r ($resultado);

        
        
       
        // se cuenta la cantidad de noticias por dia
        if(!isset($NoticiasDiarias[$fecha])) $NoticiasDiarias[$fecha]=0;       
        $NoticiasDiarias[$fecha]++; 

        // si la noticia no es pertinente no se procesa y se sigue con otra
        if ($pertinente==false)continue;

        
        
    
        $count_pertinentes++;   

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



// se extraen bigramas "ruidosos"
unset($vocabulario2['municipalidad rosario']);
unset($vocabulario2['provincia santa_fe']);
unset($vocabulario2['informes reccomar']);
unset($vocabulario2['buenos aires']);
unset($vocabulario2['ciudadana municipalidad']);
unset($vocabulario2['llamados oyentes']);
unset($vocabulario2['secretario general']);
unset($vocabulario2['varios sectores']);
unset($vocabulario2['sectores provincia']);


// se ordenan los bigramas
// de mayor a menor por frecuencia
arsort($vocabulario2);   
$bigramas=array_slice($vocabulario2, 0, 13, true);



// se ordenan los trigramas
// de mayor a menor por frecuencia
arsort($vocabulario3);   
$trigramas=array_slice($vocabulario3, 0, 13, true);





// para los datos de la polarizacion de los medios
// se transforma en porcentaje las cantidades de noticias positivas y negativas
foreach ( $totalMedio as $key => $value){
   $medioNegativo[$key]=number_format(($medioNegativo[$key]/$value)*100,0);
   $medioPositivo[$key]=number_format(($medioPositivo[$key]/$value)*100,0);
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

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
///// AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI ////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////






require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_line.php');
require_once ('jpgraph/jpgraph_scatter.php');
require_once ('jpgraph/jpgraph_regstat.php');



$xdata = array();
$ydata = array();
$ydata2 = array();
$labels=array();

$n=0;
// para el grafico de la evolucion dia a dia de las noticias
foreach ( $NoticiasDiarias as $key => $value){

   $t = strtotime($key);
   $fechaTemp = date('d/m',$t);
   

   $labels[]=$fechaTemp;
   $xdata[]=$n;
   $ydata[]=$NegativasDiarias[$key];
   $ydata2[]=$PositivasDiarias[$key];

   //if(isset($PertinentesDiarias[$key]))  $DatosEvolucion.='{fechas:"'.$fechaTemp.'", totales:'.$value.', gestion:'.$PertinentesDiarias[$key].'},';
   
   
   $n++;

}






// Original data points
//$xdata = array(1,3,5,7,9,12,15,17.1);
//$ydata = array(5,1,9,6,4,3,19,12);
//$ydata2 = array(3,4,5,9,9,2,13,8);



linegraph($xdata,$ydata,$ydata2,$labels);

function linegraph($xdata,$ydata,$ydata2, $labels){

        // Get the interpolated values by creating
        // a new Spline object.
        $spline = new  Spline($xdata,$ydata);
        $spline2 = new Spline($xdata,$ydata2);

        // For the new data set we want 40 points to get a smooth curve.
        list($newx,$newy) = $spline->Get(50);
        list($newx2,$newy2) = $spline2->Get(50);

        // Create the graph
        $g = new Graph(600,400);
        $g->SetMargin(30,20,40,30);
        $g->title->Set("Natural cubic splines");
        $g->title->SetFont(FF_ARIAL,FS_NORMAL,12);
        $g->subtitle->Set('(Control points shown in red)');
        $g->subtitle->SetColor('darkred');
        $g->SetMarginColor('lightblue');
        //$g->xaxis->SetTickLabels($labels); 


        //$g->img->SetAntiAliasing();

        // We need a linlin scale since we provide both
        // x and y coordinates for the data points.
        $g->SetScale('linlin');

        //$g->xaxis->SetTickLabels(array('hola','marcelo','como')); 

        // We want 1 decimal for the X-label
        //$g->xaxis->SetLabelFormat('%1.1f');
        //$g->xaxis->SetTickLabels($labels); 



        // We use a scatterplot to illustrate the original
        // contro points.
        $splot = new ScatterPlot($ydata,$xdata);
        $splot->mark->SetFillColor('green');
        $splot->mark->SetColor('white');
        $splot->mark->SetSize(5);
        $splot->mark->SetType(MARK_FILLEDCIRCLE);

        // And a line plot to stroke the smooth curve we got
        // from the original control points
        $lplot = new LinePlot($newy,$newx);
        $lplot->SetWeight(4); 
        $lplot->SetColor("green");
        $lplot->SetStyle("solid");


        // We use a scatterplot to illustrate the original
        // contro points.
        $splot2 = new ScatterPlot($ydata2,$xdata);
        $splot2->mark->SetFillColor('red');
        $splot2->mark->SetColor('black');
        $splot2->mark->SetSize(5);
        $splot2->mark->SetType(MARK_FILLEDCIRCLE);

        $lplot2 = new LinePlot($newy2,$newx2);
        $lplot2->SetWeight(4); 
        $lplot2->SetColor("red");
        $lplot2->SetStyle("solid");


        // Add the plots to the graph and stroke
        $g->Add($lplot2);
        $g->Add($splot2);

        $g->Add($lplot);
        $g->Add($splot);

        $g->Stroke();

}



///////////////////////////////////////////////////////////////////////////////////////////////////////////////
///// FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN  ////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////

$tiempo_fin = microtime(true);
$tiempo = $tiempo_fin - $tiempo_inicio;

echo "<h2>Tiempo empleado: " . ($tiempo_fin - $tiempo_inicio)."</h2>";



?>
