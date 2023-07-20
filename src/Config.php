<?php

namespace Biblhertz\JatsToOjs;

class Config {
    static private $data;

    static public function load($configFile) {
        self::$data = parse_ini_file($configFile);
        //print_r(self::$data);
    }

    static public function get($key) {
        return self::$data[$key];
    }

    

}

?>