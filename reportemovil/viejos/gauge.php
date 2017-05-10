<?php



gauge(0.65,"#ff0000","#0f0f0f",150,600,"40 de 143");



function gauge ($porcentaje,  $colorlinea, $colorpista, $grosor, $ancho, $label){
   	

$borde=8;

// se crea la imagen 
$image = imagecreatetruecolor($ancho, $ancho);
$background = imagecolorallocate($image, 0, 0, 0);


// Make the background transparent
imagecolortransparent($image, $background);

// se transforma el codigo de color html en tres $r $g $b
list($r, $g, $b) = sscanf($colorpista, "#%02x%02x%02x");
$colorpista = imagecolorallocate($image, $r, $g, $b);
  

// se transforma el codigo de color html en tres $r $g $b
list($r, $g, $b) = sscanf($colorlinea, "#%02x%02x%02x");
$foreground = imagecolorallocate($image, $r, $g, $b);

$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);


// se enciende el antialias
imageantialias($image, true);

$ancho=$ancho-(2*$borde);

$centro=$borde+($ancho/2);
$grueso=$ancho-$grosor;

$angulo=($porcentaje * 360)-90;


imagefilledellipse ( $image , $centro, $centro, $ancho , $ancho , $colorpista) ;
imagefilledarc($image, $centro, $centro, $ancho, $ancho, -90, $angulo, $foreground, IMG_ARC_PIE);
imagefilledellipse ( $image , $centro, $centro,  $grueso , $grueso , $background) ;


// el texto del porcentaje
$font="fonts/OpenSans-Bold.ttf";
$texto=$porcentaje*100;
$texto=number_format($texto,0);
$texto=$texto."%";
$angle=0;
$font_size=$ancho/8;
$color=$foreground;
$posy=$centro+($font_size/2);
list($left, $bottom, $right, , , $top) = imageftbbox($font_size, $angle, $font, $texto);
$textwidth = $right - $left;
$posx =  $centro - ($textwidth / 2);
imagettftext($image, $font_size, $angle, $posx, $posy, $color, $font, $texto);


// el texto del label inferior
$font="fonts/OpenSans-Regular.ttf";
$color=$foreground;
$posy=$posy+($font_size/2);
$posy+=$ancho/25;
$font_size=$ancho/20;
list($left, $bottom, $right, , , $top) = imageftbbox($font_size, $angle, $font, $label);
$textwidth = $right - $left;
$posx =  $centro - ($textwidth / 2);
imagettftext($image, $font_size, $angle, $posx, $posy, $white, $font, $label);



// flush image
//header('Content-type: image/png');
imagepng($image, "cache/gauge.png");
imagedestroy($image);
  
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
echo '<body style="background-color:#000000;">';
echo '<img src="cache/gauge.png" width="100%" height="auto">';


}

?>