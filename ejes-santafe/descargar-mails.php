<?php 


// Make sure this script will keep on runing after we close the connection with it.
ignore_user_abort(TRUE);
ini_set('html_errors', false);
set_time_limit ( 0 );
if(isset($_GET["verbosetofile"])) include_once "../includes/verbosetofile.php";
else include_once "../includes/verboseconsole.php";    


// se crea el semaforo para la cola de ejecucion
$semaforo=basename(__file__,".php").".pid";
file_put_contents($semaforo, "true");


// para procesar los mails mimes
require_once("../includes/fMailbox.php");

// para procesar los attaach .doc .docx
// que vienen en los e-mails
require_once("../includes/parseWord.php");
 
// Connect to gmail
$imapPath = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX';

$username = 'auditoriasrosario@gmail.com';
$password = 'auditoriasrosario2016';
 
// try to connect
$inbox = imap_open($imapPath,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
 
   /* ALL - return all messages matching the rest of the criteria
    ANSWERED - match messages with the \\ANSWERED flag set
    BCC "string" - match messages with "string" in the Bcc: field
    BEFORE "date" - match messages with Date: before "date"
    BODY "string" - match messages with "string" in the body of the message
    CC "string" - match messages with "string" in the Cc: field
    DELETED - match deleted messages
    FLAGGED - match messages with the \\FLAGGED (sometimes referred to as Important or Urgent) flag set
    FROM "string" - match messages with "string" in the From: field
    KEYWORD "string" - match messages with "string" as a keyword
    NEW - match new messages
    OLD - match old messages
    ON "date" - match messages with Date: matching "date"
    RECENT - match messages with the \\RECENT flag set
    SEEN - match messages that have been read (the \\SEEN flag is set)
    SINCE "date" - match messages with Date: after "date"
    SUBJECT "string" - match messages with "string" in the Subject:
    TEXT "string" - match messages with text "string"
    TO "string" - match messages with "string" in the To:
    UNANSWERED - match messages that have not been answered
    UNDELETED - match messages that are not deleted
    UNFLAGGED - match messages that are not flagged
    UNKEYWORD "string" - match messages that do not have the keyword "string"
    UNSEEN - match messages which have not been read yet*/
 
// search and get unseen emails, function will return email ids
//$emails = imap_search($inbox,'ALL');
//$emails = imap_search($inbox,'RECENT');



// busca los e-mails de ejes
// desde una fecha determinada ( x dias atras)

consolelog("descargando e-mails...<br>"); 

$date = date ( "d M Y", strToTime ( "-5 days" ) );
$criterio='FROM "redaccion@agenciatextual.com.ar" SINCE "'.$date.'"';
$emails = imap_search($inbox, $criterio);


// se abre la base de datos
$db = new SQLite3('../data/rawmailssfe.db') or die('no se puede abrir la base de datos ../data/rawmailssfe.db');



$cantidad= count($emails);

$n=0;

foreach($emails as $mail) 
{
    
    $n++;
    $headerInfo = imap_headerinfo($inbox,$mail);
    
    
    // el ultimo parametro indica que parte del body
    // se debe extraer 
    //  (empty) - Entire message
    // 1 - MULTIPART/ALTERNATIVE (todo el body)
    // 1.1 - TEXT/PLAIN (solo la parte de texto plano)
    // 1.2 - TEXT/HTML (solo la parte de html)
    //2 - MESSAGE/RFC822 (entire attached message)
    //2.0 - Attached message header
    //2.1 - TEXT/PLAIN
    //2.2 - TEXT/HTML
    //2.3 - file.ext
    $email_body =imap_fetchbody($inbox,$mail,"") ;
    $subject=imap_utf8 ($headerInfo->subject);


   	$parsed_message = fMailbox::parseMessage($email_body);

	$hayTexto=FALSE;
	$hayHtml=FALSE;
	$hayAttach=FALSE;
	$bodytext="";
	$archivo="";

	if(isset($parsed_message['text']) && strlen($parsed_message['text'])>50 ) $hayTexto=TRUE;
	if(isset($parsed_message['html']) && strlen($parsed_message['text'])>50 ) $hayHtml=TRUE;
	if(isset($parsed_message['attachment'])) $hayAttach=TRUE;

	if($hayAttach){ 
		
		$attach=$parsed_message['attachment'][0];
		$archivo= $attach['filename'];
		$mimetype= $attach['mimetype'];
		$data= $attach['data'];
		
	    echo "<br><br>#########################################<br>";
	    echo "$subject<br>";
	    echo "$archivo<br>";
	    echo "$mimetype<br>";
	    
	    echo "#########################################<br>";

	    
	    $bodytext=parseWord($data, $archivo, $mimetype); 
		//echo "<br><br>--------------------------<br><br>";
	    //echo $bodytext;
	    //echo "<br><br>--------------------------<br><br>";
	   
	} 


    echo "<h1>$n</h1>";
    

    if($hayAttach==FALSE && $hayTexto==TRUE ) $bodytext=$parsed_message['text'];
    if($hayAttach==FALSE && $hayTexto==FALSE && $hayHtml==TRUE ) $bodytext=$parsed_message['html'];


    // para sacar los quoted printable =20=30
    $email_body =quoted_printable_decode($email_body) ;
    $email_body =SQLite3::escapeString( $email_body );
   
    
    $porcentaje= ($n / $cantidad)*100;
    $porcentaje=number_format($porcentaje,2);
    
    

    $uid=imap_uid ($inbox,$mail);
    $body_hash= SQLite3::escapeString( hash('sha256', $email_body));
    consolelog($n.") Subject: " . $subject, $porcentaje) ;  
    consolelog("date: " .$headerInfo->date, $porcentaje);  
    consolelog("From: " .$headerInfo->fromaddress, $porcentaje); 
    consolelog("email---> ". $mail, $porcentaje ); 
    consolelog("Unix Date---> ". $headerInfo->udate, $porcentaje );
    consolelog("UID---> ". $uid, $porcentaje );
    
       
   
    // se comprueba si el mail ya esta en la base de datos
    //$sqlquery= "SELECT 1 FROM emails WHERE bodyhash='$body_hash'";
    $sqlquery= "SELECT 1 FROM emails WHERE uid='$uid'";
    $results = $db->query($sqlquery);
    $row = $results->fetchArray();

    // si el mail no esta se agrega a la base de datos
    if ($row==false){
        consolelog("No esta", $porcentaje);
        $sqlquery="INSERT INTO emails (uid, udate, emailfrom, date, subject, bodytext, attachpath) VALUES ('$uid', '$headerInfo->udate', '$headerInfo->fromaddress', '$headerInfo->date', '$subject', '$bodytext','$archivo' ) " ;

        $results = $db->query($sqlquery);
     } else consolelog("YAAAAAA esta", $porcentaje);
   
      consolelog("------------------------------------", $porcentaje);
   
    
    
   
 
}


imap_expunge($inbox);
imap_close($inbox);

consolelog("", 'TERMINATE'); 

unlink($semaforo);

?>




