jQuery(document).ready(function($) {
  $(document).ready(function() {

    
    snd = new Audio("sonidos/0.wav");
   

    // http://www.jsfuck.com/
    var pin =[+!+[]]+[!+[]+!+[]]+[!+[]+!+[]+!+[]]+[!+[]+!+[]+!+[]+!+[]];

    var enterCode = "";
    enterCode.toString();

    $("#numbers button").click(function() {

      var clickedNumber = $(this).text().toString();
      
      snd.play();
      enterCode = enterCode + clickedNumber;
      var lengthCode = parseInt(enterCode.length);
      lengthCode--;
      $("#fields .numberfield:eq(" + lengthCode + ")").addClass("active");

      if (lengthCode == 3) {

        // Check the PIN
        if (enterCode == pin) {
          // Right PIN!
          $("#fields .numberfield").addClass("right");
          $("#numbers").addClass("hide");
          $("#anleitung p").html("Ingresando<br>el codigo es correcto.");
          window.location.replace("movil.html");
          //goFullscreen();

        } else {
          // Wrong PIN!
          $("#anleitung p").html("<strong>intentelo nuevamente</strong><br>el codigo no es correcto.");
          $("#fields").addClass("miss");
          enterCode = "";
          setTimeout(function() {
            $("#fields .numberfield").removeClass("active");
          }, 200);
          setTimeout(function() {
            $("#fields").removeClass("miss");
          }, 500);

        }

      } else {}

    });
    
    $("#restartbtn").click(function(){
      enterCode = "";
      $("#fields .numberfield").removeClass("active");
      $("#fields .numberfield").removeClass("right");
      $("#numbers").removeClass("hide");
      $("#anleitung p").html("<strong>Introduzca el código PIN correcto</strong><br>para ingresar SIMON");
    });

  });
});

function goFullscreen() {
      // Must be called as a result of user interaction to work
      mf = document.getElementById("main_frame");
      mf.webkitRequestFullscreen();
      mf.style.display="";
    }

    function fullscreenChanged() {
      if (document.webkitFullscreenElement == null) {
        mf = document.getElementById("main_frame");
        mf.style.display="none";
      }
    }

    document.onwebkitfullscreenchange = fullscreenChanged;
    //document.documentElement.onclick = goFullscreen;
    //document.onkeydown = goFullscreen;