<?php    
 /* CAT:Spline chart */ 

 /* pChart library inclusions */ 
 include("pchart/pData.class.php"); 
 include("pchart/pDraw.class.php"); 
 include("pchart/pImage.class.php"); 

 /* Create and populate the pData object */ 
 $MyData = new pData();   
 
 $MyData->addPoints(array(3,12,15,8,5,-5),"Negativas"); 
 $MyData->addPoints(array(2,7,5,18,19,22),"Positivas"); 
 

 $MyData->setPalette("Negativas",array("R"=>255,"G"=>0,"B"=>0)); 
 $MyData->setPalette("Positivas",array("R"=>0,"G"=>255,"B"=>0)); 


 $MyData->setSerieWeight("Negativas",2);
 $MyData->setSerieWeight("Positivas",2); 

 
 $MyData->addPoints(array("Jan","Feb","Mar","Apr","May","Jun"),"Labels"); 
 $MyData->setSerieDescription("Labels","Months"); 
 $MyData->setAbscissa("Labels"); 

 /* Create the pChart object, TRUE is for transparent background*/ 
 $myPicture = new pImage(700,250,$MyData, TRUE); 


 /* Turn on Antialiasing */ 
 $myPicture->Antialias = TRUE; 

 
  
 /* Write the chart title */  
$myPicture->setFontProperties(array("FontName"=>"fonts/OpenSans-Regular.ttf","FontSize"=>12, "R"=>255,"G"=>255,"B"=>255)); 
$myPicture->drawText(350,35,"Evolución Positivas Vs. Negativas",array("FontSize"=>14,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE)); 

 /* Set the default font */ 
 $myPicture->setFontProperties(array("FontName"=>"fonts/OpenSans-Light.ttf","FontSize"=>9,"R"=>255,"G"=>255,"B"=>255)); 

 /* Define the chart area */ 
 $myPicture->setGraphArea(40,15,690,200); 



 $Settings = array("Pos"=>SCALE_POS_LEFTRIGHT, "Mode"=>SCALE_MODE_FLOATING, "LabelingMethod"=>LABELING_ALL
, "GridR"=>217, "GridG"=>255, "GridB"=>66, "GridAlpha"=>50, "TickR"=>255, "TickG"=>255, "TickB"=>255, "TickAlpha"=>255, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>0, "DrawSubTicks"=>FALSE, "SubTickR"=>255, "SubTickG"=>255, "SubTickB"=>255, "SubTickAlpha"=>255, "XMargin"=>20, "YMargin"=>0, "DrawYLines"=>NONE,"AxisR"=>255,"AxisG"=>255,"AxisB"=>255, "AxisAlpha"=>0);
$myPicture->drawScale($Settings);


 /* Draw the line chart */ 
  $myPicture->drawSplineChart();
 $myPicture->drawPlotChart(array("PlotBorder"=>TRUE,"BorderSize"=>1,"Surrounding"=>-60,"BorderAlpha"=>80)); 

 /* Write the chart legend */ 
 //$myPicture->drawLegend(540,20,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL)); 

 /* Render the picture (choose the best way) */ 
 $myPicture->render("cache/mypic.png");


echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
echo '<body style="background-color:#333333;margin:0px; padding:0px;">';
echo '<img src="cache/mypic.png" width="100%" height="auto">';

?>