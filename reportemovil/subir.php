<?php


// se crea el semaforo para la cola de ejecucion
$semaforo=basename(__file__,".php").".pid";
file_put_contents($semaforo, "true");

ignore_user_abort(true); // Ignore user aborts and allow the script to run forever
set_time_limit(0); // for scripts that run really long

require_once ('../includes/proxy.php');

$target_url = 'http://robotcountry.net/simon/auditoria/reportemovil/upload.php';

$directorios=array("cache/cache7/", "cache/cache3/", "cache/cache24/");


foreach ($directorios as $key => $value) {

		$dir    = $value;
		$files = scandir($dir);

		

		// se saca el punto y el punto punto de la lista
		// (directorio actual y directorio padre)
		$files = array_diff($files, array('..', '.'));
		// se mueven cada uno de los archivos que estan en la carpeta cache
		foreach ($files as $key => $value) {
			$archivo=$dir.$value;
			$file_name_with_full_path = realpath($archivo);
		    $post = array('file' => new CurlFile($file_name_with_full_path, 'application/x-binary' , $file_name_with_full_path ),'cache'=> $dir);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$target_url);
			if($proxy!=="") curl_setopt($ch, CURLOPT_PROXY, $proxy);
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			$result=curl_exec ($ch);
			curl_close ($ch);
			echo $result;
		}

}


// se mueve el archivo movil.html
$archivo="movil.html";
$file_name_with_full_path = realpath($archivo);
$post = array('file' => new CurlFile($file_name_with_full_path, 'text/plain' , $file_name_with_full_path ));
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$target_url);
if($proxy!=="") curl_setopt($ch, CURLOPT_PROXY, $proxy);
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
$result=curl_exec ($ch);
curl_close ($ch);
echo $result;


unlink($semaforo);


?>



