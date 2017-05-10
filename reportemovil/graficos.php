<?php





////////////////////////////////////////////////////
// FUNCION GAUGE
///////////////////////////////////////////////////

function gauge ($porcentaje,  $colorlinea, $colorpista, $grosor, $ancho, $label,$titulo,$nombre){
   	
    global $cache;

    if ($porcentaje<=0.001)$porcentaje=0.0001;
	$borde=8;

	$title_size=$ancho/18;
	$title_space=$title_size*1.5;

	// se crea la imagen 
	$image = imagecreatetruecolor($ancho, $ancho+$title_space);
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
	$centroy=$centro +$title_space;


	imagefilledellipse ( $image , $centro, $centroy, $ancho , $ancho , $colorpista) ;
	imagefilledarc($image, $centro, $centroy, $ancho, $ancho, -90, $angulo, $foreground, IMG_ARC_PIE);
	imagefilledellipse ( $image , $centro, $centroy,  $grueso , $grueso , $background) ;


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

	// el texto del titulo
	$font_size=$title_size;
	$posy=$font_size;
	list($left, $bottom, $right, , , $top) = imageftbbox($font_size, $angle, $font, $titulo);
	$textwidth = $right - $left;
	$posx =  $centro - ($textwidth / 2);
	imagettftext($image, $font_size, $angle, $posx, $posy, $white, $font, $titulo);


     $file=$cache."/".$nombre;
	// flush image
	//header('Content-type: image/png');
	imagepng($image, $file);
	imagedestroy($image);

	return $file;
	  
	


}




////////////////////////////////////////////////////
// FUNCION INFOTEXT
///////////////////////////////////////////////////

function infotext($titulo1,  $data, $label1, $label2,$ancho){

    global $cache;

    $data_font_size=$ancho/5;
    $alto=2.5 * $data_font_size;

	// se crea la imagen 
	$image = imagecreatetruecolor($ancho, $alto);
	$background = imagecolorallocate($image, 0, 0, 0);

	// Make the background transparent
	imagecolortransparent($image, $background);

	$centro=$ancho/2;
	$white = imagecolorallocate($image, 255, 255, 255);
 
    // el texto del titulo1
	$font_size=$ancho/30;
	$posy= $font_size;
	$font="fonts/OpenSans-Regular.ttf";
	list($left, $bottom, $right, , , $top) = imageftbbox($font_size, 0, $font, $titulo1);
	$textwidth = $right - $left;
	$posx =  $centro - ($textwidth / 2);
	imagettftext($image, $font_size, 0, $posx, $posy, $white, $font, $titulo1);

	

    // el texto del valor
	$font2="fonts/OpenSans-Bold.ttf";
	$texto=$data*100;
	$texto=number_format($texto,0);
	$texto=$texto."%";
	$angle=0;
	
	$posy=$posy +  $font_size ;
	$font_size=$data_font_size;
	$posy=$posy + ( 1.1 * $font_size );
	list($left, $bottom, $right, , , $top) = imageftbbox($font_size, $angle, $font2, $texto);
	$textwidth = $right - $left;
	$posx =  $centro - ($textwidth / 2);
	imagettftext($image, $font_size, $angle, $posx, $posy, $white, $font2, $texto);


	// el texto del label1
	$posy=$posy + ( 0.6 * $font_size );
	$font_size=$ancho/24;
	list($left, $bottom, $right, , , $top) = imageftbbox($font_size, 0, $font2, $label1);
	$textwidth = $right - $left;
	$posx =  $centro - ($textwidth / 2);
	imagettftext($image, $font_size, 0, $posx, $posy, $white, $font2, $label1);


    // el texto del label2
	$posy=$posy + (1.8 * $font_size);
	list($left, $bottom, $right, , , $top) = imageftbbox($font_size, 0, $font, $label2);
	$textwidth = $right - $left;
	$posx =  $centro - ($textwidth / 2);
	imagettftext($image, $font_size, 0, $posx, $posy, $white, $font, $label2);


     $file=$cache."/infotext.png";
	// flush image
	//header('Content-type: image/png');
	imagepng($image, $file);
	imagedestroy($image);

	

}


////////////////////////////////////////////////////
// FUNCION HUMOR
///////////////////////////////////////////////////

function humor($humor,$ancho){

    global $cache;

    $data_font_size=$ancho/8;
    $alto=1.8 * $data_font_size;

	// se crea la imagen 
	$image = imagecreatetruecolor($ancho, $alto);
	$background = imagecolorallocate($image, 0, 0, 0);

	// Make the background transparent
	imagecolortransparent($image, $background);

	$centro=$ancho/2;
	$white = imagecolorallocate($image, 255, 255, 255);
	$red = imagecolorallocate($image, 255, 0, 0);
	$green = imagecolorallocate($image, 0, 255, 0);

	if ($humor=="POSITIVO") $color=$green;
	if ($humor=="NEGATIVO") $color=$red;
	if ($humor=="NEUTRO") $color=$white;

 
    // el texto del titulo
    $titulo="HUMOR SOBRE LA GESTIÓN";
	$font_size=$ancho/24;
	$posy= $font_size;
	$font="fonts/OpenSans-Regular.ttf";
	list($left, $bottom, $right, , , $top) = imageftbbox($font_size, 0, $font, $titulo);
	$textwidth = $right - $left;
	$posx =  $centro - ($textwidth / 2);
	imagettftext($image, $font_size, 0, $posx, $posy, $white, $font, $titulo);

    // el texto del humor
	$font2="fonts/OpenSans-Bold.ttf";
	$texto=$humor;
	$angle=0;
	$font_size=$data_font_size;
	$posy=$posy + ( 1.1 * $font_size );
	list($left, $bottom, $right, , , $top) = imageftbbox($font_size, $angle, $font2, $texto);
	$textwidth = $right - $left;
	$posx =  $centro - ($textwidth / 2);
	imagettftext($image, $font_size, $angle, $posx, $posy, $color, $font2, $texto);


	


     $file=$cache."/humor.png";
	// flush image
	//header('Content-type: image/png');
	imagepng($image, $file);
	imagedestroy($image);

}


////////////////////////////////////////////////////
// FUNCION periodo analizado
///////////////////////////////////////////////////

function periodo($inicio,$final, $texto){

	global $cache;

    $ancho=600;
    $font_size=$ancho/30;
    $alto=2.5 * $font_size;

	// se crea la imagen 
	$image = imagecreatetruecolor($ancho, $alto);
	
	//$gray=imagecolorallocate($image, 54, 54, 54);
	//imagefill($image, 0, 0, $gray);


	$background = imagecolorallocate($image, 0, 0, 0);

	// Make the background transparent
	imagecolortransparent($image, $background);

	$centro=$ancho/2;
	$white = imagecolorallocate($image, 255, 255, 255);
 
    // el texto de la fecha de inicio
    $texto="periodo analizado: ".$texto;
    $font_size=$ancho/30;
	$posy= $font_size;
	$font="fonts/OpenSans-Regular.ttf";
	list($left, $bottom, $right, , , $top) = imageftbbox($font_size, 0, $font, $texto);
	$textwidth = $right - $left;
	$posx =  $centro - ($textwidth / 2);
	imagettftext($image, $font_size, 0, $posx, $posy, $white, $font, $texto);

	   
    // el texto de la fecha 
    $font_size=$ancho/30;
    $texto=$inicio." - ".$final;
	$posy=$posy + ( 1.5 * $font_size );
	$font="fonts/OpenSans-Bold.ttf";
	list($left, $bottom, $right, , , $top) = imageftbbox($font_size, 0, $font, $texto);
	$textwidth = $right - $left;
	$posx =  $centro - ($textwidth / 2);
	imagettftext($image, $font_size, 0, $posx, $posy, $white, $font, $texto);


   

    $file=$cache."/periodo.png";
	imagepng($image, $file);
	imagedestroy($image);

}

////////////////////////////////////////////////////
// FUNCION periodo evolucionNP
///////////////////////////////////////////////////


 /* pChart library inclusions */ 
 include("pchart/pData.class.php"); 
 include("pchart/pDraw.class.php"); 
 include("pchart/pImage.class.php"); 


function evolucionPN ($negativas,$positivas,$fechas){

         global $cache;

		 /* Create and populate the pData object */ 
		 $MyData = new pData();   
		 
		 $MyData->addPoints($negativas,"Negativas"); 
		 $MyData->addPoints($positivas,"Positivas"); 
		 

		 $MyData->setPalette("Negativas",array("R"=>255,"G"=>0,"B"=>0)); 
		 $MyData->setPalette("Positivas",array("R"=>0,"G"=>255,"B"=>0)); 


		 $MyData->setSerieWeight("Negativas",2);
		 $MyData->setSerieWeight("Positivas",2); 

		 
		 $MyData->addPoints($fechas,"Labels"); 
		 $MyData->setSerieDescription("Labels","Months"); 
		 $MyData->setAbscissa("Labels"); 

		 /* Create the pChart object, TRUE is for transparent background*/ 
		 $myPicture = new pImage(1000,500,$MyData, TRUE); 


		 /* Turn on Antialiasing */ 
		 $myPicture->Antialias = TRUE; 

		 
		  
		 /* Write the chart title */  
		$myPicture->setFontProperties(array("FontName"=>"fonts/OpenSans-Regular.ttf","FontSize"=>12, "R"=>255,"G"=>255,"B"=>255)); 
		$myPicture->drawText(500,35,"Evolución Positivas Vs. Negativas",array("FontSize"=>24,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE)); 

		 /* Set the default font */ 
		 $myPicture->setFontProperties(array("FontName"=>"fonts/OpenSans-Light.ttf","FontSize"=>18,"R"=>255,"G"=>255,"B"=>255)); 

		 /* Define the chart area */ 
		 $myPicture->setGraphArea(20,70,980,470); 



		 $Settings = array("Pos"=>SCALE_POS_LEFTRIGHT, "Mode"=>SCALE_MODE_FLOATING, "LabelingMethod"=>LABELING_DIFFERENT
		, "GridR"=>217, "GridG"=>255, "GridB"=>66, "GridAlpha"=>50, "TickR"=>255, "TickG"=>255, "TickB"=>255, "TickAlpha"=>255, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>0, "DrawSubTicks"=>FALSE, "SubTickR"=>255, "SubTickG"=>255, "SubTickB"=>255, "SubTickAlpha"=>255, "XMargin"=>20, "YMargin"=>0, "DrawYLines"=>NONE,"AxisR"=>255,"AxisG"=>255,"AxisB"=>255, "AxisAlpha"=>0, "RemoveYAxis"=>TRUE);
		$myPicture->drawScale($Settings);


		 /* Draw the line chart */ 
		 $myPicture->drawSplineChart();
		 $myPicture->drawPlotChart(array("PlotBorder"=>TRUE,"BorderSize"=>1,"Surrounding"=>-60,"BorderAlpha"=>80,"DisplayValues"=>TRUE, "DisplayR"=>255,"DisplayG"=>255,"DisplayB"=>255)); 
 


    

		 /* Write the chart legend */ 
		 //$myPicture->drawLegend(540,20,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL)); 

		 /* Render the picture (choose the best way) */ 
		 $myPicture->render($cache."/evolucionpn.png");


		

}


///////////////////////////////////////////////////
// FUNCION periodo evolucion de totales
///////////////////////////////////////////////////

function evolucionTotales ($gestion,$totales,$fechas){

         global $cache;

		 /* Create and populate the pData object */ 
		 $MyData = new pData();   
		 
		 $MyData->addPoints($gestion,"gestion"); 
		 $MyData->addPoints($totales,"totales"); 
		 

		 $MyData->setPalette("gestion",array("R"=>200,"G"=>200,"B"=>200)); 
		 $MyData->setPalette("totales",array("R"=>0,"G"=>255,"B"=>255)); 


		 $MyData->setSerieWeight("gestion",2);
		 $MyData->setSerieWeight("totales",2); 

		 
		 $MyData->addPoints($fechas,"Labels"); 
		 $MyData->setSerieDescription("Labels","Months"); 
		 $MyData->setAbscissa("Labels"); 

		 /* Create the pChart object, TRUE is for transparent background*/ 
		 $myPicture = new pImage(1000,500,$MyData, TRUE); 


		 /* Turn on Antialiasing */ 
		 $myPicture->Antialias = TRUE; 

		 
		  
		 /* Write the chart title */  
		$myPicture->setFontProperties(array("FontName"=>"fonts/OpenSans-Regular.ttf","FontSize"=>12, "R"=>255,"G"=>255,"B"=>255)); 
		$myPicture->drawText(500,35,"Evolución Totales Vs. Gestión",array("FontSize"=>24,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE)); 

		 /* Set the default font */ 
		 $myPicture->setFontProperties(array("FontName"=>"fonts/OpenSans-Light.ttf","FontSize"=>18,"R"=>255,"G"=>255,"B"=>255)); 

		 /* Define the chart area */ 
		 $myPicture->setGraphArea(20,70,980,450); 



		 $Settings = array("Pos"=>SCALE_POS_LEFTRIGHT, "Mode"=>SCALE_MODE_FLOATING, "LabelingMethod"=>LABELING_DIFFERENT
		, "GridR"=>217, "GridG"=>255, "GridB"=>66, "GridAlpha"=>50, "TickR"=>255, "TickG"=>255, "TickB"=>255, "TickAlpha"=>255, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>0, "DrawSubTicks"=>FALSE, "SubTickR"=>255, "SubTickG"=>255, "SubTickB"=>255, "SubTickAlpha"=>255, "XMargin"=>20, "YMargin"=>0, "DrawYLines"=>NONE,"AxisR"=>255,"AxisG"=>255,"AxisB"=>255, "AxisAlpha"=>0, "RemoveYAxis"=>TRUE);
		$myPicture->drawScale($Settings);


		 /* Draw the line chart */ 
		 $myPicture->drawSplineChart();
		 $myPicture->drawPlotChart(array("PlotBorder"=>TRUE,"BorderSize"=>1,"Surrounding"=>-60,"BorderAlpha"=>80, "DisplayValues"=>TRUE, "DisplayR"=>255,"DisplayG"=>255,"DisplayB"=>255)); 

        



		 /* Write the chart legend */ 
		 //$myPicture->drawLegend(540,20,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL)); 

		 /* Render the picture (choose the best way) */ 
		 $myPicture->render($cache."/evoluciontotales.png");


		

}


///////////////////////////////////////////////////
// FUNCION polarizacion de los medios
///////////////////////////////////////////////////


function polarizacion($polarizacion, $MostrarGap=FALSE){

	global $cache;

    $cantidadMedios=count($polarizacion);

   

    $ancho=1080;
    $alto=600;
    $centro=$ancho/2;
    $borde=80;
    $altoBarra=80;
    $anchoBarra=$ancho-(2*$borde);
    $font_size=20;
    $espacioBarra=8; 

    $alto=$font_size+ (1+$cantidadMedios) * ($altoBarra+$espacioBarra);

    

    // se crea la imagen 
    $image = imagecreatetruecolor($ancho, $alto);
    $background = imagecolorallocate($image, 0, 0, 0);


    // Make the background transparent
    imagecolortransparent($image, $background);
    $white = imagecolorallocate($image, 255, 255, 255);
    $red = imagecolorallocate($image, 140, 0, 0);
    $green = imagecolorallocate($image, 0, 140, 0);
    $yellow = imagecolorallocate($image, 200, 200, 0);
    $gray = imagecolorallocate($image, 128, 128, 128);
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
    $font_size=28;
    $font="fonts/OpenSans-Bold.ttf";

    for($n=0; $n< $cantidadMedios; $n++){
      
      $medio=$polarizacion[$n]['medio'];
      $pos= $polarizacion[$n]['positiva'];
      $neg= $polarizacion[$n]['negativa'];
      $neu= $polarizacion[$n]['neutra'];
      
      $gap= 1-($pos+$neg+$neu);
      

      // se dibuja el rectangulo base (rojo)
      imagefilledrectangle($image, $posx, $posy, $posx+$anchoBarra, $posy+$altoBarra, $red);

      
      // se dibuja el rectangulo verde
      $anchoVerde=$anchoBarra*$pos;  
      imagefilledrectangle($image, $posx, $posy, $posx+$anchoVerde, $posy+$altoBarra, $green);


      // se dibuja el rectangulo gris (neutras)
      $anchoGris=$anchoBarra*$neu;  
      imagefilledrectangle($image, $posx, $posy, $posx+$anchoVerde, $posy+$altoBarra, $green);
      imagefilledrectangle($image, $posx+$anchoVerde, $posy, $posx+$anchoVerde+$anchoGris, $posy+$altoBarra, $gray);
      
      if ($MostrarGap)
        {
        // se dibuja el gap (lo que falta para llegar al 100% que el sistema no clasifica)
        $anchogap=$anchoBarra*$gap;  
        imagefilledrectangle($image, $posx+$anchoVerde+$anchoGris, $posy, $posx+$anchoVerde+$anchoGris+$anchogap, $posy+$altoBarra, $yellow);
        }


      // se escribe el nombre del medio
      list($left, $bottom, $right, , , $top) = imageftbbox($font_size, 0, $font, $medio);
      $posxTexto =  $centro - (($right - $left) / 2);
      $posyTexto=$posy+$font_size+28;
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

    
    $file=$cache."/polarizacion.png";
    if ($MostrarGap) $file=$cache."/polarizacion_gap.png";

    // se graba la imagen en la cache de disco
    imagepng($image, $file);
    imagedestroy($image);




}


///////////////////////////////////////////////////
// FUNCION HISTOGRAMA
///////////////////////////////////////////////////

function histograma($datos,$nombre, $titulo, $r, $g,$b,$datosPos=NULL,$datosNeg=NULL){

    global $cache;

    $cantidadDatos=count($datos);

    

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
      imagefilledrectangle($image, $posx, $posy, $posx+$largoBarra, $posy+$altoBarra, $colorbarra);

     if ($datosPos!=NULL)
     {
      // se dibuja la barra verde
      $valor=$datosPos[$key];
      $largo=($valor/$valorMax) * $anchoBarra;
      imagefilledrectangle($image, $posx, $posy, $posx+$largo, $posy+$altoBarra, $green); 
    }   

     if ($datosNeg!=NULL)
     {  
      // se dibuja la barra roja
      $valor=$datosNeg[$key];
      $largo2=($valor/$valorMax) * $anchoBarra;
      imagefilledrectangle($image, $posx+$largo, $posy, $posx+$largo+$largo2, $posy+$altoBarra, $red); 
     }   
    
    
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

    
    $file=$cache."/".$nombre;
    

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