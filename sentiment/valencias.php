<?php
/*
 *
 * Analizador de sentimiento de un string
 * @package   sentimiento
 * @author    Marcelo Moglione <moglione@gmail.com>
 * @copyright 2016 Marcelo Moglione <moglione@gmail.com>
 * @since     octubre 2016
 *
 */





class SentimentAnalyzer
	{
		protected $valencias;
		protected $ngrams;


////////////////////////////////////////////////////////////////////////////////////

public function __construct()
		{
			
			$pathDiccionario=dirname(__FILE__)."/valencias.json";
			$this->valencias = json_decode(file_get_contents($pathDiccionario), true); 
            foreach ($this->valencias as $key => $value) {
            	if (strpos($key,"_")>0) $this->ngrams[$key]=$value;
            }
		}

		



////////////////////////////////////////////////////////////////////////////////////

public function analyzeSentence($sentence)
		{
			


   

            $salida="";
            $valor=0;
            $sentiment="neu";
			$words = self::tokenise($sentence);
	

             
			


			$encontradas=0;
			$normalizado=0;
			foreach($words as $word)
				{

					//$word=trim($word,'"');
					  
					//echo $word."<br>";
					                    
					if (isset($this->valencias[$word]))
					{
						$valor+= $this->valencias[$word];
                        $salida.=$word ."(". $this->valencias[$word].") ";
                        $encontradas++;

					}
					
					
				}
				
			

				
			
			if($valor<0)$sentiment="neg";
            if($valor>0)$sentiment="pos";

            if($valor>=-0.2 && $valor <=0.2)$sentiment="neu";
			
            // si no se valoro porque no se encontro ninguna palabra
            // se califica de dudosa 
			if ($encontradas==0) $sentiment="dud";
			
            // se calcula el valor normalizado
			if($encontradas > 0) $normalizado=(($valor / $encontradas)/5)*100;

            
			
			
			return array('sentimiento'=>$sentiment, 'internal'=>$salida ,'valor'=>$valor,'normalizado'=>$normalizado);
			
		}


////////////////////////////////////////////////////////////////////////////////////


private function tokenise($oracion) 
		{
	        
	        setlocale(LC_ALL, 'es_ES');
	        $oracion = strtolower($oracion);

            // se remueven los espacios extras entre palabras
	        $oracion = preg_replace('/\s+/', ' ', $oracion);

          
            // antes de tokenizar se reemplaza los ngrams (bigramas, trigramas, etc)
            foreach ($this->ngrams as $key => $value) {
            	$reemplazar=str_replace("_"," ", $key);
            	$oracion = str_replace($reemplazar, $key, $oracion);
            }


            
	        preg_match_all('/[\w]+/iu', $oracion, $matches);
	        //preg_match_all('/([a-zA-Z]|\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|\xC5[\x92\x93\xA0\xA1\xB8\xBD\xBE]){2,}/', $oracion, $matches);


	        // se extraen los tokens que son stopwords 
            $palabras=self::removeStopwords($matches[0]);
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
	     
          $vocabularioCorto= array( '' );
         
	      // se fusionan las stopwords con las palabras "extrañas" extraidas del vocabulario 
	      $stopwords=array_merge ( $stopwords , $especiales, $vocabularioCorto );

	      //se ret
	      return array_diff($input, $stopwords);
	       
      }


}
?>