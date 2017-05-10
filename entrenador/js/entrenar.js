
    $(function () {

    	
    	var startDate = moment("2016-05-11");
    	var endDate = moment();

        $("#range").ionRangeSlider({
            type: "double",
        	grid: true,
        	grid_num:10,
        	drag_interval: true,
            force_edges: true,
            keyboard: true,
            keyboard_step: 0.1,

        	min: +moment(startDate).format("X"),
        	max: +moment(endDate).format("X"),

        	from: +moment(endDate).subtract(8, "days").format("X"),
        	to: +moment(endDate).subtract(0, "days").format("X"),

        	prettify: function (num) { return moment(num, "X").format("LL");},

        	onChange: function (data) {  
                   var anticache=Math.floor((Math.random() * 10000) + 1);
                   var from = data.from;
                   var to =   data.to;
                   var ventana =  Math.floor(( to - from ) / 86400);
                   var inicio=moment(from,"X").format("LLLL");
                   var final=moment(to,"X").format("LLLL");
                   $("#fecha1").text(inicio);
                   $("#fecha2").text(final);
                   $("#ventana").text("Ventana de analisis: "+ ventana+" días");
                   
     
          },

          onFinish: function (data) {  
                   var anticache=Math.floor((Math.random() * 10000) + 1);
                   var from = data.from;
                   var to =   data.to;
                   var ventana =  Math.floor(( to - from ) / 86400);
                   var inicio=moment(from,"X").format("LLLL");
                   var final=moment(to,"X").format("LLLL");
                   $("#fecha1").text(inicio);
                   $("#fecha2").text(final);
                   $("#ventana").text("Ventana de analisis: "+ ventana+" días");
                   
                   $("#malasbuenas").load("estadisticas.php?inicio="+from+"&final="+to);
                                 
     
        	},
        });

        

    });

 
   
    
    setInterval(actualizar,1000);
   
        function actualizar(){
             var anticache=Math.floor((Math.random() * 10000) + 1);
             $("#humor").load("datasources/humor.html?anticache="+ anticache);
             $("#noticias_buenas").load("datasources/noticias_buenas.html?anticache="+ anticache);
             $("#noticias_malas").load("datasources/noticias_malas.html?anticache="+ anticache);
             $("#polaridad").load("datasources/polarizacion.html?anticache="+ anticache);
             $("#bigramas").load("datasources/bigramas.html?anticache="+ anticache);
             $("#trigramas").load("datasources/trigramas.html?anticache="+ anticache);
             $("#malasbuenas").load("datasources/evolucionpn.html?anticache="+ anticache);
             $("#totales").load("datasources/evolucion.html?anticache="+ anticache);

             

          

        }