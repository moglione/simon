<?php

session_start();


$file="../cron/cron.lock";

if(file_exists($file)){

	
	$tiempo=filemtime($file);
	$diferencia=time()-$tiempo;
	echo $diferencia;  
    

} else {

echo "cron no esta activo";	
}


?>