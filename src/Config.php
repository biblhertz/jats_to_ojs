<?php

namespace Biblhertz\JatsToOjs;

class Config {
    static private $data;

    public static function load($configFile) {
        self::$data = parse_ini_file($configFile);
        //print_r(self::$data);
    }

    public static function get($key) {
        return self::$data[$key];
    }

    

}

?>