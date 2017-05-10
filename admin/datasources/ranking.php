<?php

$nombre=basename($_SERVER['PHP_SELF'],".php");
$nombre.=".html";
$cachepath="cache";
$htmlpath=$cachepath."/".$nombre;


$DBpath="../../data/noticias.db";
$DBpath=realpath($DBpath);


$DBmodified=filemtime ($DBpath);



// se abre la base de datos de noticias
$db = new SQLite3($DBpath) or die('no se puede abrir la base de datos '.$DBpath);


// para saber los medios indexados en la base de datos
// y cuantas noticias tiene cada uno (desde el principio)
$result = $db->query("SELECT DISTINCT medio FROM news");
$medios=array();
while($res = $result->fetchArray(SQLITE3_ASSOC)){ 
    $medio=$res['medio'];
    $columnas = $db->query("SELECT COUNT(*) as count FROM news WHERE medio='$medio'");
    $cantidadCol = $columnas->fetchArray();
    $medios[$medio]=  $cantidadCol['count'];
}

arsort($medios);

/*
// para saber los medios indexados en la base de datos
// y cuantas noticias tiene cada uno (desde que se comenzaron a indexar portales)
$medios2=array();
while($res = $result->fetchArray(SQLITE3_ASSOC)){ 
    $medio=$res['medio'];

    $quiebre=1485187200-(86400*0);

    $columnas = $db->query("SELECT COUNT(*) as count FROM news WHERE medio='$medio' and epocnews > '$quiebre'");
    $cantidadCol = $columnas->fetchArray();
    $medios2[$medio]=  $cantidadCol['count'];
}

arsort($medios2);
*/


histograma($medios,"ranking.png","Ranking de medios");


echo "<pre>";
print_r($medios);
print_r($medios2);
echo "</pre>";



///////////////////////////////////////////////////
// FUNCION HISTOGRAMA
///////////////////////////////////////////////////

function histograma($datos,$nombreArchivo, $titulo){

    global $cache;

    $cantidadDatos=count($datos);

    $r=0;
    $g=0;
    $b=200;

    $ancho=800;
    $alto=600;
    $centro=$ancho/2;
    $borde=40;
    $altoBarra=50;
    $anchoBarra=$ancho-(2*$borde);
    $font_size=20;
    $espacioBarra=4; 

    $alto=$font_size+ (1+$cantidadDatos) * ($altoBarra+$espacioBarra);

    

    // se crea la imagen 
    $image = imagecreatetruecolor($ancho, $alto);
   
    $background = imagecolorallocate($image, 0, 0, 0);


    // Make the background transparent
    imagecolortransparent($image, $background);
    
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $colorbarra = imagecolorallocate($image, $r, $g, $b);
    $red = imagecolorallocate($image, 140, 0, 0);
    $green = imagecolorallocate($image, 0, 140, 0);

    // alpha 0=opaco 127=transparente
    $gris = imagecolorallocatealpha($image, 100, 100, 100,40);
       

    // el texto del titulo
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

    $valorMax=reset( $datos);

   

    foreach ($datos as $key => $value) {
       
      $largoBarra= ($value/$valorMax) * $anchoBarra;
      
      
      // se dibuja la barra base
      imagefilledrectangle($image, $posx, $posy, $posx+$anchoBarra, $posy+$altoBarra, $gris);


      // se dibuja la barra cn el valor
      imagefilledrectangle($image, $posx, $posy, $posx+$largoBarra, $posy+$altoBarra, $colorbarra);

     
    
    
      // se escribe tema
      list($left, $bottom, $right, , , $top) = imageftbbox($font_size, 0, $font, $key);
      $posyTexto=$posy+$font_size+12;
      $posxTexto=$borde+15;
      // esta funcion no es propia de GD y esta definida mas abajo
      stroketext($image, $font_size, 0, $posxTexto, $posyTexto, $white,$black, $font, $key,2);


      // se escribe el valor 
      list($left, $bottom, $right, , , $top) = imageftbbox($font_size, 0, $font, $value);
      $textwidth = $right - $left;
      $posxTexto=$ancho-$borde-15-$textwidth;
      // esta funcion no es propia de GD y esta definida mas abajo
      stroketext($image, $font_size, 0, $posxTexto, $posyTexto, $white,$black, $font, $value,2);
      
      

      $posy+=$altoBarra+$espacioBarra;
    
     
    }

    
    $file="cache/graficos/".$nombreArchivo;
    

    // se graba la imagen en la cache de disco
    imagepng($image, $file);
    imagedestroy($image);



}

///////////////////////////////////////////////////
// FUNCION dibujar una string con outline
///////////////////////////////////////////////////
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


?>