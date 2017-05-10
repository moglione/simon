 <?php


include_once "graficos.php";
include_once('../includes/analisis_texto.php');



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


$periodo="ultima semana";


if ($periodo=="ultima semana")
{
    // si se quier anlaizar un x cantidad de dias
    // siempre se debe pedir una cantidad superior a la
    // base de datos para suplir los problemas
    // de sab y dom (dentro del bucle se realiza el conteo con 
    // la variable $dias para analizar ese periodo)
    $dias=7;
    $final=strtotime("today 23:00");
    $inicio=strToTime ( "-8 days 23:00" ); 
    $cache="cache/cache7";
}






$sqlquery= "SELECT * FROM news WHERE epocnews > $inicio AND epocnews < $final  ORDER BY epocnews ASC";
$results = $db->query($sqlquery);



$n=0;

///// LOOP PRINCIPAL ///////

while($res = $results->fetchArray(SQLITE3_ASSOC)){ 

                       
        $noticia= $res['noticia'];         
       
             

        

        if ($res['origen'] != "REC"){
            $n++;
            $titulo = explode(PHP_EOL, $noticia);
            
            $id=$res['id'];
            consolelog("<strong>".$n."</strong><br>");
            consolelog("<strong>".$id."</strong><br>");
            //consolelog("<strong>".$pertinenteEncontrada."</strong><br>");
            
            $tituloNota=$titulo[0];
            consolelog("<strong>".$res['origen']."</strong>");
            consolelog("<h3>".$tituloNota."</h3>");
            //consolelog($titulo[1]."<br><br>");
            //consolelog($titulo[2]."<br><br>");
            consolelog("<br>--------------------------------<br>");

             
            $tituloNota=SQLite3::escapeString ( $tituloNota ); 

            $sqlquery= "UPDATE news SET titulo = '$tituloNota' WHERE id='$id'";

            consolelog("<br>");
            consolelog($sqlquery);
            consolelog("<br>");
            $resultados = $db->query($sqlquery);

            if ($results) consolelog("Se updateo con exito");
            else consolelog( "Problemas de inserccion: ".SQLite3::lastErrorMsg ());
      
           


        }

        


       
       
        
       
      
} 

///// FIN LOOP PRINCIPAL ///////







$tiempo_fin = microtime(true);
$tiempo = $tiempo_fin - $tiempo_inicio;

echo "<h2>Tiempo empleado: " . ($tiempo_fin - $tiempo_inicio)."</h2>";



?>
