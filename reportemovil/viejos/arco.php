<?php



gauge(0.675,"#000000","#ff0000",30,200);



function gauge ($porcentaje, $colorfondo, $colorlinea, $grosor, $ancho){
   	

$borde=8;

// se crea la imagen 
$image = imagecreate($ancho, $ancho);


// se transforma el codigo de color html en tres $r $g $b
list($r, $g, $b) = sscanf($colorfondo, "#%02x%02x%02x");

// La primera llamada a imagecolorallocate  
// rellena  la imagen creada  con el color de fondo 
// las imágenes creadas usando imagecreate ().
$background = imagecolorallocate($image, $r, $g, $b);
   

// se transforma el codigo de color html en tres $r $g $b
list($r, $g, $b) = sscanf($colorlinea, "#%02x%02x%02x");
$foreground = imagecolorallocate($image, $r, $g, $b);



// se enciende el antialias
imageantialias($image, true);

$ancho=$ancho-(2*$borde);

$centro=$borde+($ancho/2);
$grueso=$ancho-$grosor;

$angulo=($porcentaje * 360)-90;

imagefilledarc($image, $centro, $centro, $ancho, $ancho, -90, $angulo, $foreground, IMG_ARC_PIE);
imagefilledellipse ( $image , $centro, $centro,  $grueso , $grueso , $background) ;



$font="fonts/OpenSans-Bold.ttf";
$texto=$porcentaje*100;
$texto=number_format($texto,2);
$texto=$texto."%";
$angle=0;
$font_size=$ancho/10;
$color=$foreground;
$posy=$centro+($font_size/2);

// el texto
list($left, $bottom, $right, , , $top) = imageftbbox($font_size, $angle, $font, $texto);
$textwidth = $right - $left;
$posx =  $centro - ($textwidth / 2);

imagettftext($image, $font_size, $angle, $posx, $posy, $color, $font, $texto);


// flush image
header('Content-type: image/png');
imagepng($image);
imagedestroy($image);
  



}

?>