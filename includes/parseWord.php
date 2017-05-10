<?php



function parseWord($data,$filename, $mimetype = null) {

	$filename = 'attachs/' . $filename;
    file_put_contents($filename, $data);
    if (!$mimetype) $mimetype = mime_content_type($filename);
    if (preg_match("/^text\/*/", $mimetype)) return file_get_contents($filename);
    
    if ($mimetype === 'application/msword') return parseDoc($filename);
    if ($mimetype === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') return parseDocx($filename);
    return "Error: el archivo $filename no es doc ni docx internamente es --> ". $mimetype; 	
    
       
}



function parseDocx($filename) {
	 $striped_content = '';
    $content = '';

    if(!$filename || !file_exists($filename)) return false;

    $zip = zip_open($filename);
    if (!$zip || is_numeric($zip)) return false;

    while ($zip_entry = zip_read($zip)) {

        if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

        if (zip_entry_name($zip_entry) != "word/document.xml") continue;

        $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

        zip_entry_close($zip_entry);
    }
    zip_close($zip);      
    $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
    $content = str_replace('</w:r></w:p>', "\r\n", $content);
    $striped_content = strip_tags($content);

    return $striped_content;
}



function parseDoc($filename) {
    
    $fh = fopen($filename, 'r');
    
    $archivo = fread($fh, filesize($filename));  

    // modificado por Marcelo Moglione (2 mayo de 2017)
    // se busca la marca "EC A5" que indica el comienzo
    // de el stream windword
    $marca=chr(0xEC).chr(0xA5);
    $pos=strpos($archivo, $marca);

    // a la posicion de la marca se le suma 2048
    // para obtener el tamaño del encabezado 
    $headersize=$pos+2048;

    //echo "<h1>$headersize</h1>";
    //echo "<h1>$pos</h1>";

    // se rebobina el puntero de lectura 
    rewind($fh);
    $p1=$pos+28;
    $p2=$pos+29;
    $p3=$pos+30;
    $p4=$pos+31;

    $archivo="";

    $headers = fread($fh, $headersize);
    $n1 = ( ord($headers[$p1]) - 1 );
    $n2 = ( ( ord($headers[$p2]) - 8 ) * 256 );
    $n3 = ( ( ord($headers[$p3]) * 256 ) * 256 );
    $n4 = ( ( ( ord($headers[$p4]) * 256 ) * 256 ) * 256 );
    $textLength = ($n1 + $n2 + $n3 + $n4);
    $text = fread($fh, $textLength);
    //$extracted_plaintext = mb_convert_encoding($text ,'UTF-8');
    // if you want to see your paragraphs in a new line, do this
    // return nl2br($text);
    
    
    $text=str_replace(chr(0x00), "", $text);
    
     //$outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
     //return $outtext;


    $text =utf8_encode($text );
    return ($text );
}






 

?>