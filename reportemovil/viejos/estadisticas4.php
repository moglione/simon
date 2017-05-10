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



///////////////////////////////////////////////////////////////////////
// POLARIZACION  POLARIZACION  POLARIZACION  POLARIZACION 
////////////////////////////////////////////////////////////////////////

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

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
///// AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI  AQUI ////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////



polarizacion($polarizacion, TRUE);


function polarizacion($polarizacion, $MostrarGap=FALSE){

    $cantidadMedios=count($polarizacion);

    

    $ancho=1080;
    $alto=600;
    $centro=$ancho/2;
    $borde=40;
    $altoBarra=50;
    $anchoBarra=$ancho-(2*$borde);
    $font_size=20;
    $espacioBarra=4; 

    $alto=$font_size+ (1+$cantidadMedios) * ($altoBarra+$espacioBarra);

    

    // se crea la imagen 
    $image = imagecreatetruecolor($ancho, $alto);
    $background = imagecolorallocate($image, 0, 0, 0);


    // Make the background transparent
    //imagecolortransparent($image, $background);
    $white = imagecolorallocate($image, 255, 255, 255);
    $red = imagecolorallocate($image, 255, 0, 0);
    $green = imagecolorallocate($image, 0, 255, 0);
    $black = imagecolorallocate($image, 0, 0, 0);
   
       

    // el texto del titulo
    $titulo="POLARIZACIÓN DE LOS MEDIOS";
    $posy=1.5*$font_size;
    $font="fonts/OpenSans-Regular.ttf";
    list($left, $bottom, $right, , , $top) = imageftbbox($font_size, 0, $font, $titulo);
    $textwidth = $right - $left;
    $posx =  $centro - ($textwidth / 2);
    imagettftext($image, $font_size, 0, $posx, $posy, $white, $font, $titulo);

    $posx=$borde;
    $posy+=20;
    $font_size=24;
    $font="fonts/OpenSans-Bold.ttf";

    for($n=0; $n< $cantidadMedios; $n++){
      
      $medio=$polarizacion[$n]['medio'];
      $pos= $polarizacion[$n]['positiva'];
      $neg= $polarizacion[$n]['negativa'];
      $gap= 1-$pos-$neg;
      

      // se dibuja el rectangulo base (rojo)
      imagefilledrectangle($image, $posx, $posy, $posx+$anchoBarra, $posy+$altoBarra, $red);

      
      // se dibuja el rectangulo verde
      $anchoVerde=$anchoBarra*$pos;  
      imagefilledrectangle($image, $posx, $posy, $posx+$anchoVerde, $posy+$altoBarra, $green);
      
      if ($MostrarGap)
        {
        // se dibuja el gap (lo que falta para llegar al 100% que el sistema no clasifica)
        $anchogap=$anchoBarra*$gap;  
        imagefilledrectangle($image, $posx+$anchoVerde, $posy, $posx+$anchoVerde+$anchogap, $posy+$altoBarra, $black);
        }


      // se escribe el nombre del medio
      list($left, $bottom, $right, , , $top) = imageftbbox($font_size, 0, $font, $medio);
      $posxTexto =  $centro - (($right - $left) / 2);
      $posyTexto=$posy+$font_size+15;
      // esta funcion no es propia de GD y esta definida mas abajo
      stroketext($image, $font_size, 0, $posxTexto, $posyTexto, $white,$black, $font, $medio,2);
      
      
      // se escribe el valor (porcentaje) positivo
      $pos=number_format(($pos*100),0)."%";
      list($left, $bottom, $right, , , $top) = imageftbbox($font_size, 0, $font, $pos);
      $posxTexto=$borde+15;
      // esta funcion no es propia de GD y esta definida mas abajo
      stroketext($image, $font_size, 0, $posxTexto, $posyTexto, $white,$black, $font, $pos,2);
      
      
      // se escribe el valor (porcentaje) negativo
      $neg=number_format(($neg*100),0)."%";
      list($left, $bottom, $right, , , $top) = imageftbbox($font_size, 0, $font, $neg);
      $textwidth = $right - $left;
      $posxTexto=$ancho-$borde-15-$textwidth;
      // esta funcion no es propia de GD y esta definida mas abajo
      stroketext($image, $font_size, 0, $posxTexto, $posyTexto, $white,$black, $font, $neg,2);
      


      $posy+=$altoBarra+$espacioBarra;
    
     
    }

    
    $file="cache/polarizacion.png";
    if ($MostrarGap) $file="cache/polarizacion_gap.png";

    // se graba la imagen en la cache de disco
    imagepng($image, $file);
    imagedestroy($image);


	echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
	echo '<body style="background-color:#333333;margin:0px; padding:0px; color:white;">';
	echo '<img src="'.$file.'" width="50%" height="auto">';

    echo  "<br>cantidad de medios --> " . $cantidadMedios."<br>";
    echo "<pre>";
    print_r($polarizacion);


}


/**
 * Writes the given text with a border into the image using TrueType fonts.
 * @param image An image resource
 * @param size The font size
 * @param angle The angle in degrees to rotate the text
 * @param x Upper left corner of the text
 * @param y Lower left corner of the text
 * @param textcolor This is the color of the main text
 * @param strokecolor This is the color of the text border
 * @param fontfile The path to the TrueType font you wish to use
 * @param text The text string in UTF-8 encoding
 * @param px Number of pixels the text border will be
 */

function stroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px) {

    for($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++)
        for($c2 = ($y-abs($px)); $c2 <= ($y+abs($px)); $c2++)
            $bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);

   return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
} 


///////////////////////////////////////////////////////////////////////////////////////////////////////////////
///// FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN FIN  ////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////

$tiempo_fin = microtime(true);
$tiempo = $tiempo_fin - $tiempo_inicio;

echo "<h2>Tiempo empleado: " . ($tiempo_fin - $tiempo_inicio)."</h2>";




?>
