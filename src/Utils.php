<?php
namespace UsadosBR;


class Utils
{


	public static function convertHeaderCurl($array)
    {

    	foreach ($array as $key => $value) {
    		
    		$headers[] = $key. ': '. $value;

    	}
    	
        return $headers;
    }



    public static function getError($string){


    	switch ($string) {
    		
    		default:
    			return $string;
    			break;
    	}





    }


}