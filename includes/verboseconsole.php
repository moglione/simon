
<?php

function consolelog( $message, $progress=0) { 

	echo $message ."<br>". PHP_EOL; 
	echo PHP_EOL; 
	ob_flush();  
	flush();  

}



?>
