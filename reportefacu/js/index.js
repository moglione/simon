
    var header = document.querySelector('.header');
    var paneles = document.querySelector('.paneles');
    
    
    
    header.onclick = function() {
        header.classList.toggle('menu-opened');
       

    }


  var ventana = document.querySelector('.window');
  ventana.onscroll = function (e) {  
  	var header = document.querySelector('.header');
    header.classList.remove('menu-opened');
    console.log("si");
  } 


    var $girar = document.querySelector('#girar'),
	$pageBody = document.querySelector('.window');
	$about = document.querySelector('.about');
	$girar.onclick = function () {
		if ($pageBody.classList.contains('open')) {
			$pageBody.classList.remove('open');
			$about.classList.add('sinimagen');
			
		} else {
			$pageBody.classList.add('open');
			$about.classList.remove('sinimagen');
			
		}
	};

	$about.onclick = function () {
		
			$pageBody.classList.remove('open');
			$about.classList.add('sinimagen');
			
		
			
		
	};

