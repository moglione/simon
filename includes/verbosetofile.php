<?php

ini_set("log_errors", 1);
ini_set("error_log", "../errores/errores.log");


function consolelog( $message, $progress=0) { 

    $message="[".date("d/m/Y H:i:s")."] ".$message.PHP_EOL;	
    error_log($message,3, "../errores/simon.log");
	//error_log($message, 1, "moglione@gmail.com");

}



?>