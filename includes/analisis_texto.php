<?php


///////////////////////////////////////////////////////////////////////////////
//
//  Funcion para extraer las palabaras vacias (stopwords) de una string
//
//  Autor: Marcelo Moglione moglione@gmail.com
//
///////////////////////////////////////////////////////////////////////////////

// se carga la lista de stopwords (spanish)
$stopwords = file('../definiciones/stopwords.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
// se trimea cada unade las stopwords por las dudas
$stopwords =array_map('trim',$stopwords );

function stopwords($entrada){
	
	global $stopwords;
	// se normaliza a minusculas
	$entrada=strtolower($entrada);
	// se tokeniza
	$texto=tokenizar($entrada);
	
	
	// se sacan las stopwords de la string
  $result = array_diff($texto, $stopwords);
  // se vuelve a armar una string a partir de la array  
	$salida=implode(" ", $result);
	return $salida;
}

///////////////////////////////////////////////////////////////////////////////
//
//  Funcion para "tokenizar" una string. I
//
//  Autor: Marcelo Moglione moglione@gmail.com
//
///////////////////////////////////////////////////////////////////////////////
function tokenizar ($entrada){
	
	// signos de puntuacion que se van a extraer
	$punctoations = array( 
	      ',', '.', ';', ':', '¿', '?', '¡', '!', '"', '(', ')', '\'',
        '[', ']', '+', '=', '*', '&', '^', '%', '$', '#',
        '@', '~', '`', '{', '}', '\\', '|', '>', '<','—','…',  );
	
	// se reemplazan los signos de puntucion por nada (se quitan)
	$entrada= str_replace($punctoations, "", $entrada);
  // se tokeniza separando por espacios
  $texto=explode(" ",$entrada );
  // se trimea por las dudas
	$texto =array_map('trim',$texto );
		// se retorna la array de tokens
	return $texto;
}


///////////////////////////////////////////////////////////////////////////////
//
//  Funcion para extraer entidades: nombres propios, de personas, ciudadaes, etc
//  se buscan frase en la forma Mayuscula minuscula
//
//  Autor: Marcelo Moglione moglione@gmail.com
//
///////////////////////////////////////////////////////////////////////////////
function extraer_entidades ($entrada, $cantidad, $likeString=false){
	
	
    //$entrada=utf8_decode ($entrada);
	
	// para extraer entidades la string de entrada no tiene que estar normalizada a minusculas
	
	// asi solo busca un nombre y un apellido
	//preg_match_all('([A-Z][a-zA-Záéíñóú]+(?=\s[A-Z])(?:\s[A-Z][a-zA-Záéíñóú]+))',$entrada,$matches);

    // asi busca entidades Mayuscula Minuscula de cualquier cantidad de palabras
    preg_match_all('([A-Z][a-zA-Záéíñóú]+(?=\s[A-Z])(?:\s[A-Z][a-zA-Záéíñóú]+)+)',$entrada,$matches);
	                    

	
    /*
    $salida=array();
    foreach ($matches[0] as $key => $value) {
            
        $partes= explode(" ", $value);
        $nombre=strtolower(trim($partes[0]));

        if(isset($nombres[$nombre])) $salida[]=$partes[0]." ".$partes[1];

    } 
    */

    $salida=$matches[0];

    $salida= array_count_values ($salida);
	
	arsort($salida);
		
	$salida = array_slice($salida, 0, $cantidad);

    if ($likeString==true){
            $salida = array_flip($salida);
            $salida=implode(" , ",$salida);
        }

    echo "<pre>";
    print_r($salida);
    echo "</pre>";
        
        
    	
	return $salida;
}

///////////////////////////////////////////////////////////////////////////////
//
//  Funcion para extraer keywords de un texto (una string)
//  devuelve una array con las keyword ordeadas por frecuencia
//
//  Autor: Marcelo Moglione moglione@gmail.com
//
///////////////////////////////////////////////////////////////////////////////

function crear_keywords($contenido, $cantidad  ){
		
				$wordLengthMin=5;
				
				//tokeniza por espacios
				$s = explode(" ", $contenido);
				//initialize array
				$k = array();
				
				//iterate inside the array
				foreach( $s as $key=>$val ) {
					//delete single or two letter words and
					//Add it to the list if the word 
					if(mb_strlen(trim($val)) >= $wordLengthMin  &&  !is_numeric(trim($val))) {
						$k[] = trim($val);
					}
				}
				//count the words
				$k = array_count_values($k);
				arsort($k);
							
			
				$salida = [];
				foreach($k as $key=>$val) {
					$salida[] .= $key;
				}
				
				
				//release unused variables
				unset($k);
				unset($s);
				
				$salida = array_slice($salida, 0, $cantidad);
		
				return $salida;
	}


///////////////////////////////////////////////////////////////////////////////
//
//  Funcion para extraer keywords de una array 
//  devuelve una array con las keyword ordeadas por frecuencia
//
//  Autor: Marcelo Moglione moglione@gmail.com
//
///////////////////////////////////////////////////////////////////////////////

function keywords_de_array($contenido, $cantidad  ){
		
				$wordLengthMin=5;
				
				
				//initialize array
				$k = array();
				
				//iterate inside the array
				foreach( $contenido as $key=>$val ) {
					//delete single or two letter words and
					//Add it to the list if the word 
					if(mb_strlen(trim($val)) >= $wordLengthMin  &&  !is_numeric(trim($val))) {
						$k[] = trim($val);
					}
				}
				//count the words
				$k = array_count_values($k);
				arsort($k);
							
			   

				$salida = [];
				foreach($k as $key=>$val) {
					$salida[] .= $key;
				}
				
				
				//release unused variables
				unset($k);
			
				
				$salida = array_slice($salida, 0, $cantidad);
		
				return $salida;
	}




// ####################################################################
// # FUNCION PARA TOKENIZAR UNA STRING                                #
// ####################################################################

function tokenise($oracion, $removerSTP=TRUE) 
        {
            
            
            setlocale(LC_ALL, 'es_ES');
            $oracion = strtolower($oracion);
            
            // antes de tokenizar se reemplaza santa fe por santa_fe
            $oracion = str_replace("santa fe", "santa_fe", $oracion);

            // antes de tokenizar se reemplaza gobierno nacional por gobierno_nacional
            $oracion = str_replace("gobierno nacional", "gobierno_nacional", $oracion);

            // antes de tokenizar se reemplaza gobierno provincial por gobierno_provincial
            $oracion = str_replace("gobierno provincial", "gobierno_provincial", $oracion);


            preg_match_all('/[\w]+/iu', $oracion, $matches);

            $palabras=$matches[0];

            // se extraen los tokens que son stopwords 
            if ($removerSTP) $palabras=removeStopwords($matches[0]);
            return $palabras;
 
        }


// ####################################################################
// # FUNCION EXTRAER LAS STOPWORDS                                   #
// # no solo quita los stoprwords del español, tambien quita algunas #
// # palabras "extrañas" que se fueron encontrado en el vocabulario  #
// ####################################################################
function removeStopwords ($input)
       {
        
        $stopwords = array('de','la','que','el','en','y','a','los','del','se','las','por','un','para','con','no','una','su','al','lo','como','más','pero','sus','le','ya','o','este','sí','porque','esta','entre','cuando','muy','sin','sobre','también','me','hasta','hay','donde','quien','desde','todo','nos','durante','todos','uno','les','ni','contra','otros','ese','eso','ante','ellos','e','esto','mí','antes','algunos','qué','unos','yo','otro','otras','otra','él','tanto','esa','estos','mucho','quienes','nada','muchos','cual','poco','ella','estar','estas','algunas','algo','nosotros','mi','mis','tú','te','ti','tu','tus','ellas','nosotras','vosostros','vosostras','os','mío','mía','míos','mías','tuyo','tuya','tuyos','tuyas','suyo','suya','suyos','suyas','nuestro','nuestra','nuestros','nuestras','vuestro','vuestra','vuestros','vuestras','esos','esas','estoy','estás','está','estamos','estáis','están','esté','estés','estemos','estéis','estén','estaré','estarás','estará','estaremos','estaréis','estarán','estaría','estarías','estaríamos','estaríais','estarían','estaba','estabas','estábamos','estabais','estaban','estuve','estuviste','estuvo','estuvimos','estuvisteis','estuvieron','estuviera','estuvieras','estuviéramos','estuvierais','estuvieran','estuviese','estuvieses','estuviésemos','estuvieseis','estuviesen','estando','estado','estada','estados','estadas','estad','he','has','ha','hemos','habéis','han','haya','hayas','hayamos','hayáis','hayan','habré','habrás','habrá','habremos','habréis','habrán','habría','habrías','habríamos','habríais','habrían','había','habías','habíamos','habíais','habían','hube','hubiste','hubo','hubimos','hubisteis','hubieron','hubiera','hubieras','hubiéramos','hubierais','hubieran','hubiese','hubieses','hubiésemos','hubieseis','hubiesen','habiendo','habido','habida','habidos','habidas','soy','eres','es','somos','sois','son','sea','seas','seamos','seáis','sean','seré','serás','será','seremos','seréis','serán','sería','serías','seríamos','seríais','serían','era','eras','éramos','erais','eran','fui','fuiste','fue','fuimos','fuisteis','fueron','fuera','fueras','fuéramos','fuerais','fueran','fuese','fueses','fuésemos','fueseis','fuesen','sintiendo','sentido','sentida','sentidos','sentidas','siente','sentid','tengo','tienes','tiene','tenemos','tenéis','tienen','tenga','tengas','tengamos','tengáis','tengan','tendré','tendrás','tendrá','tendremos','tendréis','tendrán','tendría','tendrías','tendríamos','tendríais','tendrían','tenía','tenías','teníamos','teníais','tenían','tuve','tuviste','tuvo','tuvimos','tuvisteis','tuvieron','tuviera','tuvieras','tuviéramos','tuvierais','tuvieran','tuviese','tuvieses','tuviésemos','tuvieseis','tuviesen','teniendo','tenido','tenida','tenidos','tenidas','tened' );
      
      
          $especiales= array( 'ñ','Ó', 'á','í','ó', 'xi', 'xiv', 'xix', 'xl', 'xn', 'xvi', 'xvii', 'xxi', 'xxl', 'xxx', 'xxxi', 'xxxii' );
         
          //$vocabularioCorto= array( 'nota','comunicación','telefónica', 'móvil', 'espectáculos', 'grabada','años','dos','deportes' );
         
          // se fusionan las stopwords con las palabras "extrañas" extraidas del vocabulario 
          //$stopwords=array_merge ( $stopwords , $especiales, $vocabularioCorto );
          $stopwords=array_merge ( $stopwords , $especiales );

          //se ret
          return array_values(array_diff($input, $stopwords));
           
      }



// ####################################################################
// # FUNCION PARA CONTAR LOS MONOGRAMAS                                #
// ####################################################################
function contarMonogramas($texto, $cantidad=0){

        $vocabulario=array();

        $words=tokenise($texto);
        foreach ($words as $word)
        {
                    
                  
                    // si la palabra comienza con un numero no se suma al vocabulario
                    if (preg_match('/^[0-9]{1,}/', $word)) continue;

                    // si empieza con el caracter º y despues un numero no se suma al vocabulario
                    if (preg_match('/^º[0-9]{1,}/', $word)) continue;

                    // si empieza con el caracter nº  no se suma al vocabulario
                    if (preg_match('/^nº/', $word)) continue;

                    // si empieza con www no se suma al vocabulario
                    if (preg_match('/^www/', $word)) continue;
                    

                    // si la palabra tiene menos de dos caracteres no se suma
                    if (strlen(utf8_decode($word))<2) continue;
                    
                    // si la palabra no esta en el vocabulario se agrega
                    if (!isset($vocabulario[$word]) ) $vocabulario[$word]= 0;
                    if (isset($vocabulario[$word])) $vocabulario[$word]++;
                                     
                   
        }

       

        // se ordenan los bigramas
        // de mayor a menor por frecuencia
        arsort($vocabulario);   
      
        if($cantidad>0) $vocabulario=array_slice($vocabulario, 0, $cantidad, true);
        
        return $vocabulario;

}


// ####################################################################
// # FUNCION PARA CONTAR LOS BIGRAMAS                                #
// ####################################################################
function contarBigramas($texto, $cantidad=0){

        $vocabulario=array();

        $words=tokenise($texto);
        $wordAnterior=""; 
        foreach ($words as $word)
        {
                    
                  
                    // si la palabra comienza con un numero no se suma al vocabulario
                    if (preg_match('/^[0-9]{1,}/', $word)) continue;

                    // si empieza con el caracter º y despues un numero no se suma al vocabulario
                    if (preg_match('/^º[0-9]{1,}/', $word)) continue;

                    // si empieza con el caracter nº  no se suma al vocabulario
                    if (preg_match('/^nº/', $word)) continue;

                    // si empieza con www no se suma al vocabulario
                    if (preg_match('/^www/', $word)) continue;
                    

                    // si la palabra tiene menos de dos caracteres no se suma
                    if (strlen(utf8_decode($word))<2) continue;
                    
                    $bigrama=$wordAnterior." ".$word;
                     // si la palabra no esta en el vocabulario se agrega
                    if (!isset($vocabulario[$bigrama]) && $wordAnterior!="") $vocabulario[$bigrama]= 0;
                    if (isset($vocabulario[$bigrama])) $vocabulario[$bigrama]++;
                   
                  
                    $wordAnterior=$word;
        }


        


       

        // se ordenan los bigramas
        // de mayor a menor por frecuencia
        arsort($vocabulario);   
      
        if($cantidad>0) $vocabulario=array_slice($vocabulario, 0, $cantidad, true);

        return $vocabulario;

}


// ####################################################################
// # FUNCION PARA CONTAR LOS TRIGRAMAS                                #
// ####################################################################
function contarTrigramas($texto, $cantidad=0){
        
        $vocabulario=array();

        $words=tokenise($texto);
        $wordAnterior1="";
        $wordAnterior2="";
         

        foreach ($words as $word)
        {
                    
                  
                    // si la palabra comienza con un numero no se suma al vocabulario
                    if (preg_match('/^[0-9]{1,}/', $word)) continue;

                    // si empieza con el caracter º y despues un numero no se suma al vocabulario
                    if (preg_match('/^º[0-9]{1,}/', $word)) continue;

                    // si empieza con el caracter nº  no se suma al vocabulario
                    if (preg_match('/^nº/', $word)) continue;

                    // si empieza con www no se suma al vocabulario
                    if (preg_match('/^www/', $word)) continue;
                    

                    // si la palabra tiene menos de dos caracteres no se suma
                    if (strlen(utf8_decode($word))<2) continue;
                    
                    $trigrama=$wordAnterior1." ".$wordAnterior2." ".$word;
                     // si la palabra no esta en el vocabulario se agrega
                    if (!isset($vocabulario[$trigrama]) && $wordAnterior1!="" && $wordAnterior2!="" ) $vocabulario[$trigrama]= 0;
                    if (isset($vocabulario[$trigrama])) $vocabulario[$trigrama]++;
                   
                  
                    $wordAnterior1=$wordAnterior2;
                    $wordAnterior2=$word;
                                 
        }

        // se ordenan los trigramas
        // de mayor a menor por frecuencia
        arsort($vocabulario);   
        
        if($cantidad>0) $vocabulario=array_slice($vocabulario, 0, $cantidad, true);
               
        return $vocabulario;



}


// ####################################################################
// # FUNCION PARA CONTAR LOS TRIGRAMAS                                #
// ####################################################################
function contarTetragramas($texto, $cantidad=0){
        
        $vocabulario=array();

        $words=tokenise($texto);
        $wordAnterior1="";
        $wordAnterior2="";
        $wordAnterior3="";
       

        foreach ($words as $word)
        {
                    
                  
                    // si la palabra comienza con un numero no se suma al vocabulario
                    if (preg_match('/^[0-9]{1,}/', $word)) continue;

                    // si empieza con el caracter º y despues un numero no se suma al vocabulario
                    if (preg_match('/^º[0-9]{1,}/', $word)) continue;

                    // si empieza con el caracter nº  no se suma al vocabulario
                    if (preg_match('/^nº/', $word)) continue;

                    // si empieza con www no se suma al vocabulario
                    if (preg_match('/^www/', $word)) continue;
                    

                    // si la palabra tiene menos de dos caracteres no se suma
                    if (strlen(utf8_decode($word))<2) continue;
                    
                    $tetragrama=$wordAnterior1." ".$wordAnterior2." ".$wordAnterior3." ".$word;
                     // si la palabra no esta en el vocabulario se agrega
                    if (!isset($vocabulario[$tetragrama]) && $wordAnterior1!="" && $wordAnterior2!="" && $wordAnterior3!="") $vocabulario[$tetragrama]= 0;
                    if (isset($vocabulario[$tetragrama])) $vocabulario[$tetragrama]++;
                   
                  
                    $wordAnterior1=$wordAnterior2;
                    $wordAnterior2=$wordAnterior3;
                    $wordAnterior3=$word;
                                 
        }

        // se ordenan los trigramas
        // de mayor a menor por frecuencia
        arsort($vocabulario);   
        
        if($cantidad>0) $vocabulario=array_slice($vocabulario, 0, $cantidad, true);
        
        return $vocabulario;



}


// ####################################################################
// # FUNCION PARA CONTAR LOS PERSONAJES                               #
// ####################################################################
function contarPersonajes($texto, $cantidad=0, $likeString=false){

        $defNombres=file_get_contents("../definiciones/nombres.json");
        $nombres=json_decode($defNombres, TRUE); 

        $vocabulario=array();

        $words=tokenise($texto, FALSE);
        $tope=count($words);

        

        $salida=array();

        for ($n=0; $n<$tope;$n++)
        {
                    
                    $word=$words[$n];

                    $word=limpiarPalabra($word);

                    if($word=="")continue;
                    
                    // si se encuentra un nombre propio como palabra
                    if(isset($nombres[$word])) 
                    {
                    	$n++;
                    	$word2=$words[$n];
                    	$word2=limpiarPalabra($word2);
                    	if($word2=="")continue;
                        $personaje=$word." ".$word2;
 
                        // 1)si la segunda palabra tambien es un nombre
                        //   el modelo es nombre+nombre+apellido 
                        // 2) Si la segunda palabra es de dos caracteres
                        //    p.ej: "di" o "de" es un appellido tipo "di pollina"
                        // 3) si la segunda palabar es un "del" es un apellido tipo "del frade"
                        // 4) si la segunda palabra esta entre comillas es un apodo  que
                        //    se puso entre el nombre y el apellido p. ej: maxiliano "quemadito" rodriguez   
                        if(isset($nombres[$word2]) or strlen($word2)==2 or $word2=="del" or $word2[0]=='"'){
                        	$n++;
                        	$word3=$words[$n];
                        	$word3=limpiarPalabra($word3);
                        	if($word3=="")continue;
                        	$personaje=$word." ".$word2." ".$word3;
                        } 

                        

                        //$salida[]=ucwords($personaje);
                        $salida[]=$personaje;

                    }        
        
        }


        $salida= array_count_values ($salida);

        arsort($salida); 

        
        if($cantidad>0) $salida=array_slice($salida, 0, $cantidad, true);

        if ($likeString==true){
            $salida = array_flip($salida);
            $salida=implode(" , ",$salida);
        }

        //echo "<pre>";
        //print_r($salida);
        //echo "</pre>";
        
        return $salida;

}




function limpiarPalabra($word){

	                 $word=trim($word); 

	                // si la palabra comienza con un numero no se suma al vocabulario
                    if (preg_match('/^[0-9]{1,}/', $word)) return "";

                    // si empieza con el caracter º y despues un numero no se suma al vocabulario
                    if (preg_match('/^º[0-9]{1,}/', $word)) return "";

                    // si empieza con el caracter nº  no se suma al vocabulario
                    if (preg_match('/^nº/', $word)) return "";

                    // si empieza con www no se suma al vocabulario
                    if (preg_match('/^www/', $word)) return "";
                    

                    // si la palabra tiene menos de dos caracteres no se suma
                    if (strlen(utf8_decode($word))<2) return "";

                    // se extraen los acentos
                    // porque la base de datos de nombres no los tiene    
                    $word=str_replace(array('á','é','í','ó','ú'), array('a','e','i','o','u'), $word);

                    return $word;
}


?>