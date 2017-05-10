<?php



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



$dias=0;
$horas=23;
$segundos=(3600*24)*$dias +($horas *3600) ;
$final=strtotime("today 23:00");
$inicio=$final-$segundos;


$sqlquery= "SELECT * FROM news WHERE epocnews > $inicio AND epocnews < $final  ORDER BY epocnews ASC";
$results = $db->query($sqlquery);


// para saber la cantidad de filas a procesar - procesadas
$columnas = $db->query("SELECT COUNT(*) as count FROM news WHERE epocnews > $inicio AND epocnews < $final ");
$cantidadCol = $columnas->fetchArray();
$numRows = $cantidadCol['count'];


$neu=0;
$neg=0;
$pos=0;
$dud=0;
$cantidad=0;

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
       

        // la noticia para ser analizada se normaliza a minusculas
        $noticia= strtolower($noticia);

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
         //if ($pertinente==false)continue;
         $gestion="";
         if ($pertinente==true)$gestion="<h2>NOTICIA DE GESTION</h2>";

        $id=$res['id'];
        $fecha= date('d-m-Y', $res['epocnews']);

 
        $resultado = $sat->analyzeSentence($noticia);

          

        

        $cantidad++;


        if($resultado['sentimiento']=="neu")  { $neu++; $color="gris";}
        if($resultado['sentimiento']=="pos") { $pos++; $color="verde";}
        if($resultado['sentimiento']=="neg") { $neg++; $color="rojo";}
        if($resultado['sentimiento']=="dud") { $dud++; $color="amarillo";}  // dudosas

        
          
       
        $colorbadge="azul";

         $interno="";

         if(trim($resultado['internal'])!=""){

            $partes=explode(" ",trim($resultado['internal']));
            $valoreInternos=array();
            foreach ($partes as $parte) {
                $numero = filter_var( $parte, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) ;
                $posicion=strpos($parte,"(");
                $palabraValor=substr ( $parte , 0 ,$posicion );
                $valoreInternos[$palabraValor]=$numero; 
           }
        
           //asort($valoreInternos);
           
           
           foreach ($valoreInternos as $key => $value) {

              if($value<0) $colorbadge="rojobadge";
              if($value>0) $colorbadge="verdebadge";
              $marca='<div class="btn noselect"><span class="palabra">'.$key.'</span><span class="badge '.$colorbadge.'">'.$value.'</span> </div>';
            
              $interno.=$marca;

            }
          
        }

 
        $palabra['medio']=$res['medio'];
        $palabra['programa']=$res['programa'];


        $palabra['id']=$id;
        $palabra['fecha']=$fecha;
        $palabra['color']=$color;
        $palabra['noticia']=$gestion." ". $noticia;
        $palabra['humor']=$resultado['sentimiento'];
        $palabra['valor']=$resultado['valor'];
        $palabra['interno']=$interno;
       
        
        $salida[]=$palabra;
            
        
        
       
} 




 $total=$neg + $pos + $neu + $dud;
 $pneg=($neg / $total) * 100;
 $pneu=($neu / $total) * 100;
 $ppos=($pos / $total) * 100;
 $pdud=($dud / $total) * 100;


 $pneg=number_format($pneg,2);
 $pneu=number_format($pneu,2);
 $ppos=number_format($ppos,2);
 $pdud=number_format($pdud,2);


 $negativos= "Negativos= $neg ($pneg %)";
 $positivos= "Positivos= $pos ($ppos %)";
 $neutros= "Neutros= $neu ($pneu %)";
 $dudosas= "Dudosas= $dud ($pdud %)";
    
 
include_once('includes/tbs_class.php');
$TBS = new clsTinyButStrong;
//$TBS->SetOption('noerr', false); 
$TBS->SetOption('charset', 'UTF-8'); 
$TBS->LoadTemplate( 'templates/valorar.html');
$TBS->MergeBlock('bloque1',$salida); 
$TBS->Show();




 


$tiempo_fin = microtime(true);
$tiempo = $tiempo_fin - $tiempo_inicio;

echo "<h2>Tiempo empleado: " . ($tiempo_fin - $tiempo_inicio)."</h2>";



?>
