<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_line.php');
require_once ('jpgraph/jpgraph_scatter.php');
require_once ('jpgraph/jpgraph_regstat.php');

// Original data points
$xdata = array(1,3,5,7,9,12,15,17.1);
$ydata = array(5,1,9,6,4,3,19,12);



// Original data points
$ydata2 = array(3,4,5,9,9,2,13,8);

// Get the interpolated values by creating
// a new Spline object.
$spline = new  Spline($xdata,$ydata);


$spline2 = new Spline($xdata,$ydata2);

// For the new data set we want 40 points to
// get a smooth curve.
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


//$g->img->SetAntiAliasing();

// We need a linlin scale since we provide both
// x and y coordinates for the data points.
$g->SetScale('linlin');

// We want 1 decimal for the X-label
$g->xaxis->SetLabelFormat('%1.1f');

// We use a scatterplot to illustrate the original
// contro points.
$splot = new ScatterPlot($ydata,$xdata);
$splot->mark->SetFillColor('blue');
$splot->mark->SetColor('white');
$splot->mark->SetSize(5);

$splot->mark->SetType(MARK_FILLEDCIRCLE);

// And a line plot to stroke the smooth curve we got
// from the original control points
$lplot = new LinePlot($newy,$newx);
$lplot->SetWeight(4); 
$lplot->SetColor("blue");
$lplot->SetStyle("solid");



$lplot2 = new LinePlot($newy2,$newx2);
$lplot2->SetWeight(4); 
$lplot2->SetColor("red");
$lplot2->SetStyle("solid");





// Add the plots to the graph and stroke
$g->Add($lplot2);
$g->Add($lplot);
$g->Add($splot);
$g->Stroke();

?>

