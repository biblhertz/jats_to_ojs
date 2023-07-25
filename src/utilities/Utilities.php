<?php
namespace Biblhertz\JatsToOjs\utilities;

class Utilities {

    public static function to_utf($text){
        if(!isset($text)||!strcmp("",$text))return "";
        $text=trim($text);
        $text= iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text);
        $text = iconv('utf-8', 'ascii//TRANSLIT', $text);
        $text=str_replace("&","and",$text);
    
        return $text;
    }   

}

?>
