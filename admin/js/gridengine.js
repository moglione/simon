// seteos iniciales de la geometria
 		
 		var anchoVentana=$("body").width();

 		var columnas=12;
 		var gutter=anchoVentana/150;  //10px
 		var lado;

	 	
        

        ///////////////////////////////////////////////////////////////

        function grillar(){
            
            var anchoVentana=$("body").width();
            gutter=anchoVentana/150;  //10px

            //console.log("gutter= "+gutter);

	 		// seteos derivados;
	 		var cantGutters=columnas-1;
	 		var anchoGutters=cantGutters*gutter;
	 		var anchoContenedor=contenedor.width();
			var altoContenedor=contenedor.height();
			lado=Math.floor((anchoContenedor-anchoGutters)/columnas);

	        console.log("Ancho="+anchoContenedor);
			console.log("lado="+lado);

			tile.each(function (index, value) { 
				posicionar($(this));

			});
        }

        ////////////////////////////////////////////////////

   	    function posicionar(panel){
           	var clases= panel.attr('class'); 
           	var partes=clases.split(" ");

           	var pos=partes[1].substring(3);
			var size=partes[2].substring(4);

			var posTemp=pos.split("-");
			var sizeTemp=size.split("-");

            var x=posTemp[0];
			var y=posTemp[1];

			var ancho=sizeTemp[0];
			var alto=sizeTemp[1];

            // se calcula la posicion x e y
            // teniendo en cuenta el gutter
            x= (x*lado) + (x*gutter); 
            y= (y*lado) + (y*gutter); 

			// se calcula el ancho y el alto
            // teniendo en cuenta el gutter
			ancho= (ancho*lado)+ ((ancho-1)*gutter);
			alto= (alto*lado)+ ((alto-1)*gutter);

			// para posiciones que tengan cero o
			// tamaños que  tengan cero la inclusion
			// del gutter de valores negativos por eso
			// esta correccion
			if(x<0) x=0;
			if(y<0) y=0;
			if(ancho<0) ancho=0;
			if(alto<0) alto=0;


			panel.css({top: y, left: x, width: ancho , height: alto});

   	    }