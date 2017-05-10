<?php

include_once('includes/tbs_class.php');

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
         
          $vocabularioCorto= array( 'nota','comunicación','telefónica', 'móvil', 'espectáculos', 'grabada','años','dos','deportes','recinfoarch', 'rec', 'gmailcom', 'archivo' );
         
         
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
// # FUNCION PARA cCONTAR LOS TRIGRAMAS                                #
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
// # FUNCION PARA GENERAR LOS HTML A PRTIR DE LOS TEMPLATES          #
// ####################################################################

function generaDatasource ($nombre, $bloque=null) {
    $TBS = new clsTinyButStrong;
    $TBS->SetOption('charset', 'UTF-8'); 
    $TBS->LoadTemplate( 'templates/'.$nombre);
    if($bloque!=null) $TBS->MergeBlock('bloque1',$bloque); 
    $TBS->Show(TBS_NOTHING); 
    $result = $TBS->Source;
    file_put_contents("datasources/".$nombre,$result);

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





$sqlquery= "SELECT * FROM news WHERE epocnews > $inicio AND epocnews < $final   ORDER BY epocnews ASC";


$results = $db->query($sqlquery);



$meses = array("enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre");
$fecha_inicio= date("d",$inicio)." de ".$meses[date("n",$inicio)-1];
$fecha_final= date("d",$final)." de ".$meses[date("n",$final)-1];




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




// para el grafico de la evolucion dia a dia de las noticias
$DatosEvolucion="";
$DatosEvolucionPN="";
foreach ( $NoticiasDiarias as $key => $value){

   $t = strtotime($key);
   $fechaTemp = date('d/m',$t);
   
   if(isset($PertinentesDiarias[$key]))  $DatosEvolucion.='{fechas:"'.$fechaTemp.'", totales:'.$value.', gestion:'.$PertinentesDiarias[$key].'},';
   if(isset($PositivasDiarias[$key]) && isset($NegativasDiarias[$key])   ) $DatosEvolucionPN.='{fechas:"'.$fechaTemp.'", positivas:'.$PositivasDiarias[$key]. ', negativas:'.$NegativasDiarias[$key].'},'.PHP_EOL;

}



// para los datos de la polarixacion de los medios
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


// para debug
//$output .= print_r($totalMedio, true);
//file_put_contents('file.txt', $output);






 $total=$neg + $pos + $neu + $dud;
 $pneg=($neg / $total) * 100;
 $pneu=($neu / $total) * 100;
 $ppos=($pos / $total) * 100;
 $pdud=($dud / $total) * 100;
 $pneg=number_format($pneg,0);
 $pneu=number_format($pneu,0);
 $ppos=number_format($ppos,0);
 $pdud=number_format($pdud,0);


 

IF ( $pertinentes_pos > $pertinentes_neg )  { $humor="POSITIVO"; $colorhumor="#00ff00";}
IF ( $pertinentes_pos < $pertinentes_neg )  { $humor="NEGATIVO"; $colorhumor="#ff0000";}
IF ( $pertinentes_pos == $pertinentes_neg ) { $humor="NEUTRO";   $colorhumor="#f0f0f0";}


$PercentgestionPositivas=($pertinentes_pos/$count_pertinentes)*100;
$PercentgestionNegativas=($pertinentes_neg/$count_pertinentes)*100;

$PercentgestionPositivas=number_format($PercentgestionPositivas,0);
$PercentgestionNegativas=number_format($PercentgestionNegativas,0);


// el primer panel///////////////////

$porcentaje_pertinentes=number_format( 100*($count_pertinentes/$cantidad),0);
if ($porcentaje_pertinentes<=1) $simple="1 de cada 100 noticias";
if ($porcentaje_pertinentes==2) $simple="2 de cada 100 noticias";
if ($porcentaje_pertinentes==3) $simple="3 de cada 100 noticias";
if ($porcentaje_pertinentes==4) $simple="4 de cada 100 noticias";
if ($porcentaje_pertinentes==5) $simple="1 de cada 20 noticias";
if ($porcentaje_pertinentes>=5 && $porcentaje_pertinentes<=8 ) $simple="1 de cada 20 noticias";
if ($porcentaje_pertinentes>=9 && $porcentaje_pertinentes<=18 ) $simple="1 de cada 10 noticias";
if ($porcentaje_pertinentes>=19 && $porcentaje_pertinentes<=23 ) $simple="1 de cada 5 noticias";
if ($porcentaje_pertinentes>=24 && $porcentaje_pertinentes<=28 ) $simple="1 de cada 4 noticias";
if ($porcentaje_pertinentes>=29 && $porcentaje_pertinentes<=33 ) $simple="1 de cada 3 noticias";
if ($porcentaje_pertinentes>=34 && $porcentaje_pertinentes<=48 ) $simple="2 de cada 5 noticias";
if ($porcentaje_pertinentes>=49 && $porcentaje_pertinentes<=57 ) $simple="1 de cada 2 noticias";
if ($porcentaje_pertinentes>=58 && $porcentaje_pertinentes<=67 ) $simple="3 de cada 5 noticias";
if ($porcentaje_pertinentes>=68 && $porcentaje_pertinentes<=77 ) $simple="7 de cada 10 noticias";
if ($porcentaje_pertinentes>=78 && $porcentaje_pertinentes<=87 ) $simple="4 de cada 5 noticias";
if ($porcentaje_pertinentes>=88 && $porcentaje_pertinentes<=95 ) $simple="9 de cada 10 noticias";
if ($porcentaje_pertinentes>=95 && $porcentaje_pertinentes<=100 ) $simple="TODAS LAS NOTICIAS";

$porcentaje_pertinentes=$porcentaje_pertinentes."%";


/// el periodo analizado

$result='<div style="color:white;font-family: monserrat-light, sans-serif;position:absolute; top:50%; transform: translateY(-50%); left: 5px; font-size:24px;">'.$fecha_inicio.' al '.$fecha_final.'<div>';
file_put_contents("datasources/periodo.html",$result);


// total de noticias y porcentaje de interes
generaDatasource ("total_noticias.html");

/// el panel de buenas noticias
generaDatasource ("noticias_buenas.html");

/// el panel de malas noticias
generaDatasource ("noticias_malas.html");

// evolucion de las noticias
generaDatasource ("evolucion.html");

// evolucion de las noticias positivas y negativas
generaDatasource ("evolucionpn.html");

// humor general sobre la gestion
generaDatasource ("humor.html");

// humor general sobre la gestion
generaDatasource ("polarizacion.html",$polarizacion);

// temas (bigramas)
generaDatasource ("bigramas.html",$bigramas);


// temas (bigramas)
generaDatasource ("trigramas.html",$trigramas);



$tiempo_fin = microtime(true);
$tiempo = $tiempo_fin - $tiempo_inicio;

//echo "<h2>Tiempo empleado: " . ($tiempo_fin - $tiempo_inicio)."</h2>";



?>
