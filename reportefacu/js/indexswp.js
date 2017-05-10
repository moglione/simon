function funcionHeader() {
    alert("SIMON ver. 1.02");
}





function girar() {
    var	$pageBody = document.querySelector('.paneles');
    var	$about = document.querySelector('.about');

    if ($pageBody.classList.contains('open')) {
			$pageBody.classList.remove('open');
			$about.classList.add('sinimagen');
			
		} else {
			$pageBody.classList.add('open');
			$about.classList.remove('sinimagen');
			
		}
}


function cerrarAbout() {
	        var	$pageBody = document.querySelector('.paneles');
    var	$about = document.querySelector('.about');

            $pageBody.classList.remove('open');
			$about.classList.add('sinimagen');
			
			
}