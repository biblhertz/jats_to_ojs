<?php
namespace Biblhertz\JatsToOjs\utilities;

class Utilities {

    public static function to_utf($text){
        if(!isset($text)||!strcmp("",$text))return "";
        $text=trim($text);
        $text= iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text);
        $text = iconv('utf-8', 'ascii//TRANSLIT', $text);
        $text=str_replace("&","and",$text);
        $text=htmlspecialchars_decode(htmlentities($text));
        return $text;
    }  
    
    /**
     * output XML parse errors to Logger
     */
    public static function printXMLErrors($errors){
       Logger::println();
       foreach ($errors as $error) {
                Logger::print("!!! XML Parse Error :: Line ".$error->line);
                Logger::print($error->message);
            }
        Logger::println();
    }

}

?>
