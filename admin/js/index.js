$( ".panel" ).load( "modulos/dashboard.html" );

var pos= $(".active").offset();
var ancho=$(".active").width()+10;
$("#line").css("left", pos.left-5);
$("#line").css("width", ancho);

var selector = '.menuItem';
$(selector).on('click', function(){
	$(selector).removeClass('active');
    $(this).addClass('active');
    //var pos= $(this).position();
    var pos= $(this).offset();
    var ancho=$(this).width()+10;
    $("#line").css("left", pos.left-5);
    $("#line").css("width", ancho);


   	var cargar=$(this).attr("data-source");
	$( ".panel" ).load( "modulos/"+cargar );

});


$(window).on('resize', resizar);

function resizar(){
	var pos= $(".active").offset();
	var ancho=$(".active").width()+10;
	$("#line").css("left", pos.left-5);
	$("#line").css("width", ancho);
}
