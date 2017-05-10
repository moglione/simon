<?php

ini_set("log_errors", 1);
ini_set("error_log", "errores.log");

$uploaddir =$_POST['cache'];

$uploadfile = $uploaddir . basename($_FILES['file']['name']);

if (basename($_FILES['file']['name'])=="movil.html"){ 
	        
            $uploaddir = realpath('.') . '/';
	        $uploadfile = $uploaddir . "movil.html";
	        }

echo '<pre>';
	
	echo "mover a --> ". $uploadfile ."\n";

	if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) echo "Upload del archivo OK.\n";
	else echo "Hubo algun problema!\n";
	echo 'Info de debug:'."\n\n";
	
	echo "<br>---FILES VARIABLE----<br>\n";
	print_r($_FILES);
	
    echo "<br>---POST VARIABLE----<br>\n";
	print_r($_POST);

echo "</pre>\n";
echo "\n<hr />\n";
	
?>