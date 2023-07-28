<?php
namespace Biblhertz\JatsToOjs\utilities;

use DateTime;
use DateTimeZone;
use Biblhertz\JatsToOjs\Config;

/********************************************************************/
/*		Logger Class                   							    */
/*                                                                  */
/*		generate our internal Object model from a JATS document		*/
/*      based on code from from github project ualbertalib/ojsxml   */
/*                                                                  */
/********************************************************************/


class Logger {

     
	/****************************************************************/
	/*	STATIC VARIABLES											*/
	/****************************************************************/
    public static array $messages = [];
    private static string $fileName = '';


     
	/****************************************************************/
	/*	STATIC CONSTRUCTOR 									        */
	/****************************************************************/
    /**
     * @throws \Exception
     */
    public static function __constructStatic() {
        $tz = Config::get('timezone');
        $timestamp = time();
        $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
        $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
        self::$fileName = $dt->format('d-m-Y@H:i:s'). '_log.txt';
    }

     
	/****************************************************************/
	/*	STATIC METHODS											    */
	/****************************************************************/
    public static function print(string $message) {
        array_push(self::$messages, $message);
        echo $message . PHP_EOL;
    }


    public static function writeOut($command, $user) {
        $file = fopen(Config::get('logLocation') . '/' . $command . '_' . $user . '_'. self::$fileName, 'w');
        if ($file !== false) {
            fwrite($file, self::formatToString(self::$messages));
            fclose($file);
        } else {
            echo 'Cannot write log to file' . PHP_EOL;
        }
    }

    /**
     * @param $string
     * @return string
     */
    private static function formatToString(array $messages) : string {
        $returner = '';
        foreach ($messages as $message) {
            $returner .= $message . PHP_EOL;
        }

        return $returner;
    }


    public static function printLn(){
        Logger::print("---------------------------------------------------------------------------------------------------------------------------------------");
    }
}

Logger::__constructStatic();

?>
