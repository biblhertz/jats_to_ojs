<?php

require 'vendor/autoload.php';

use Biblhertz\JatsToOjs\om\Article;
use Biblhertz\JatsToOjs\adapters\OMToOJSNativeAdapter;
use Biblhertz\JatsToOjs\adapters\OMToOJSArticleAdapter;
use Biblhertz\JatsToOjs\adapters\OMToCSVAdapter;
use Biblhertz\JatsToOjs\adapters\JATSToOMAdapter;
use Biblhertz\JatsToOjs\adapters\CSVToOMAdapter;
use Biblhertz\JatsToOjs\Config;
use Biblhertz\JatsToOjs\utilities\Logger;
use Biblhertz\JatsToOjs\utilities\Utilities;



ini_set("memory_limit", "4096M");


class jatsToOJS {

    private $command;
    private $ojsUser;
    private $inputDir;
    private $outputDir;
    private $validCommands =array("jatsToXML","jatsToCSV","csvToXML","help");
    private $verbose=false;

    /**
     * jatsToOJS constructor.
     *
     * @param array $argv Command line arguments
     */

    function __construct($argv = array()) {
        $error = false;

        array_shift($argv);

        if (sizeof($argv) != 4) {
            $this->usage();
            $error=true;
        }
    
        Config::load("config.ini");
        $this->verbose=Config::get('verbose');
        
        if(!$error){
            $this->command = array_shift($argv);
            $this->ojsUser = array_shift($argv);
            $this->inputDir = array_shift($argv);
            $this->outputDir = array_shift($argv);  
            }
       
       
        if (!in_array($this->command, $this->validCommands)) {
            Logger::print( '[Error]: Valid commands are "jatsToXML", "jatsToCSV", "csvToXML" or "help"');
            Logger::print( '[Error]: To generate OJS XML from a JATS XML document use "php jatsToOJS.php jatsToXML <ojsUserName> <inputDirectory> <outputDirectory>"');
            Logger::print( '[Error]: To generate a CSV representation of the JATS XML document use "php jatsToOJS.php jatsToCSV <ojsUserName> <inputDirectory> <outputDirectory>"');
            Logger::print( '[Error]: To generate OJS XML representation of a CSV document generated from this program use "php jatsToOJS.php csvToXML <ojsUserName> <inputDirectory> <outputDirectory>"');
            Logger::print( '[Error]: The JATS XML document must only contain a single article');
            $error=true;
        }

        if (isset($this->inputDir)&&!is_dir($this->inputDir)) {
            Logger::print( "[Error]: <input_directory> must be a valid directory");
            $error=true;
        }

        if (isset($this->outputDir)&&!is_dir($this->outputDir)) {
            Logger::print( "[Error]: <destination_directory> must be a valid directory");
            $error=true;
        }

        if($error){
            Logger::print( "Errors encountered .... exiting");
            exit;
        }

    }

    /**
     * Prints CLI usage instructions to console
     */
    public function usage() {
        Logger::print( "Package to convert issue JATS XML for a journal article to OJS XML");
        Logger::print( 'To generate OJS XML from a JATS XML document use "php jatsToOJS.php jatsToXML <ojsUserName> <inputDirectory> <outputDirectory>"');
        Logger::print( 'To generate CSV from a JATS XML document use "php jatsToOJS.php jatsToCSV <ojsUserName> <inputDirectory> <outputDirectory>"');
        Logger::print( 'To generate OJS XML representation of a CSV document generated from this program use "php jatsToOJS.php csvToXML <ojsUserName> <inputDirectory> <outputDirectory>"');
        Logger::print( 'The JATS XML document must only contain a single article');
    }

    /**
     * Executes tasks associated with given command
     */
    public function execute() {
        switch ($this->command) {
            case "jatsToXML":
                $this->generateXML();
                break;
            case "jatsToCSV":
                $this->generateCSV();
                break;
            case "csvToXML":
                $this->generateCSVToXML();
                break;
            case "help":
                $this->usage();
                break;
        }

        Logger::writeOut($this->command, $this->ojsUser);
    }


    /**
     * Converts JATS XML file to OJS Native XML files
     *
     */
private function generateXML() {
       
        Logger::print("Running issue JATS-to-XML conversion...");
        Logger::println();
        Logger::print("Input Directory Detected :: ".$this->inputDir);

        $files = scandir($this->inputDir);
        
        foreach($files as $file){
            
            $filename=$this->inputDir.DIRECTORY_SEPARATOR.$file;
            $info=pathinfo($filename);
            if(!is_dir($filename)){
                Logger::println();
                Logger::print("Input File Detected :: ".$filename);
            }

            if(isset($info['extension'])&&!strcmp($info['extension'],"xml")){
                Logger::print("Found XML File :: ".$filename);
                $valid=$this->validateJATSXML($filename);

                if($valid){
                    $jatstoOM=new JATSToOMAdapter();
                    $jatstoOM->setInputDir($this->inputDir);
                    $jatstoOM->setJATSXMLPath($filename);
                    $jatstoOM->setOJSUser($this->ojsUser);
                    $jatstoOM->setVerbose($this->verbose);
                    $jatstoOM->generateObjectModel();
                    Logger::println();
                    Logger::print("Object Model has been constructed against :: $file");
                    Logger::println();
                    $outputFileName=$this->outputDir.DIRECTORY_SEPARATOR.str_replace(".xml","",$info['basename'])."_OJS_native.xml";
                    $omtoOJS=new OMToOJSArticleAdapter($jatstoOM->getArticle(),$outputFileName);
                    $omtoOJS->generateXml();
                    Logger::println();
                    Logger::print("OJS XML generated from Object Model and output to :: $outputFileName");
                    Logger::println();
                    $valid=$omtoOJS->validateXML(Config::get('ojs_xsd'));
                    exit;
                } else{
                    Logger::print("!!! Error ::  Could not validate input file as valid JATS XML :: ".$filename);
                }
            }

        }
    }

    private function generateCSV() {
       
        Logger::print("Running issue JATS-to-CSV conversion...");
        Logger::println();
        
        Logger::print("Input Directory Detected :: ".$this->inputDir);
        $files = scandir($this->inputDir);
        
        foreach($files as $file){
            $filename=$this->inputDir.DIRECTORY_SEPARATOR.$file;
            $info=pathinfo($filename);
            if(!is_dir($filename)){
                Logger::println();
                Logger::print("Input File Detected :: ".$filename);
            }

            if(isset($info['extension'])&&!strcmp($info['extension'],"xml")){
                Logger::print("Found XML File :: ".$filename);
                $valid=$this->validateJATSXML($filename);

                if($valid){
                    $jatstoOM=new JATSToOMAdapter();
                    $jatstoOM->setInputDir($this->inputDir);
                    $jatstoOM->setJATSXMLPath($filename);
                    $jatstoOM->setOJSUser($this->ojsUser);
                    $jatstoOM->setVerbose($this->verbose);
                    $jatstoOM->generateObjectModel();
                    Logger::println();
                    Logger::print("Object Model has been constructed against :: $file");
                    Logger::println();
                    $outputFileName=$this->outputDir.DIRECTORY_SEPARATOR.str_replace(".xml","",$info['basename'])."_".uniqid().".csv";
                    $omtoOJS=new OMToCSVAdapter($jatstoOM->getArticle(),$outputFileName);
                    $omtoOJS->generateCSV();
                    Logger::println();
                    Logger::print("CSV Article representation generated from Object Model and output to :: $outputFileName");
                    Logger::println();
                    break;
                }
            }

        }

    }

    private function generateCSVToXML() {
       
        Logger::print("Running issue CSV-to-XML conversion...");
        Logger::println();
        
        Logger::print("Input Directory Detected :: ".$this->inputDir);
        $files = scandir($this->inputDir);
        $csvDetected=false;

        foreach($files as $file){
            
            $filename=$this->inputDir.DIRECTORY_SEPARATOR.$file;
            $info=pathinfo($filename);
            if(!is_dir($filename)){
                Logger::println();
                Logger::print("Input File Detected :: ".$filename);
            }

            if(isset($info['extension'])&&!strcmp($info['extension'],"csv")){
                Logger::print("Found CSV File :: ".$filename);
                $csvDetected=true;
                
                $csv=array();
                $csvToOM=new CSVToOMAdapter();
                $csvToOM->setInputDir($this->inputDir);
               
                //load csv file
                if (($handle = fopen($filename, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                          $csv[$data[0]]=$data[1];
                        }
                    fclose($handle);
                 }
                
                Logger::print("Loaded CSV File :: ".$filename);
                $csvToOM->setCSVArray($csv);
                $csvToOM->setOJSUser($this->ojsUser);
                $csvToOM->setVerbose($this->verbose);
                $csvToOM->generateObjectModel();
                Logger::println();
                Logger::print("Object Model has been constructed against :: $file");
                Logger::println();
                $outputFileName=$this->outputDir.DIRECTORY_SEPARATOR.str_replace(".xml","",$info['basename'])."_OJS_native.xml";
                $omtoOJS=new OMToOJSArticleAdapter($csvToOM->getArticle(),$outputFileName);
                $omtoOJS->generateXml();
                Logger::println();
                Logger::print("OJS XML generated from Object Model and output to :: $outputFileName");
                Logger::println();
                $valid=$omtoOJS->validateXML(Config::get('ojs_xsd'));
                exit;
            } 
        }

        if(!$csvDetected){
            Logger::println();
            Logger::print("!!! ERROR :: No CSV file was found in input directory :: ".$this->inputDir);
            Logger::print("No file conversion can take place.... Exiting ....");
        }
    }

    /**
     * Validate an input file against the JATS schemas
     * Path of JATS schema files is stored in config.ini
     */

    private function validateJATSXML($filename){
        
        $fcontent=file_get_contents($filename);
        $doc = new DOMDocument();
        $doc->loadXML($fcontent); // load xml
        if($this->verbose){
            Logger::print("Loaded XML document from $filename");
            Logger::println();
            Logger::print("Starting JATS schema validation");
            Logger::println();
        }

        libxml_use_internal_errors(true);
        $is_valid_xml=false;

        Logger::println();
        $jats_xsd_path=Config::get('jats_xsd');

        foreach($jats_xsd_path as $key => $value){
            Logger::println();
            Logger::print("Trying XML document :: $filename against JATS V$key :: $value");
            libxml_use_internal_errors(true);
            $is_valid_xml = $doc->schemaValidate($value); // path to xsd file

            if(!$is_valid_xml){
                Logger::println();
                Logger::print("XML document :: $filename failed validation against JATS V$key :: $value");
                Logger::print("Error(s) as follows;");
                Utilities::printXMLErrors(libxml_get_errors());
                libxml_clear_errors();
                libxml_use_internal_errors(false);
            } 
            else{
                Logger::println();
                Logger::print("XML document :: $filename passed validation against JATS V$key :: $value");
                libxml_use_internal_errors(false);
                return true;
            }   
        }
    
        
        return false;
    }


    
 
}


$tool = new jatsToOJS(isset($argv) ? $argv : array());
$tool->execute();

?>