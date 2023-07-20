<?php

require 'vendor/autoload.php';

use Biblhertz\JatsToOjs\om\Article;
use Biblhertz\JatsToOjs\om\Author;
use Biblhertz\JatsToOjs\om\GalleyFile;
use Biblhertz\JatsToOjs\om\Affiliation;
use Biblhertz\JatsToOjs\adapters\OJSNativeAdapter;
use Biblhertz\JatsToOjs\Config;
use Biblhertz\JatsToOjs\utilities\Logger;



ini_set("memory_limit", "4096M");


class jatsToOJS {

    private $command;
    private $ojsUser;
    private $inputDir;
    private $outputDir;
    private $validCommands =array("jatsToXML","help");
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

        $this->command = array_shift($argv);
        $this->ojsUser = array_shift($argv);
        $this->inputDir = array_shift($argv);
        $this->outputDir = array_shift($argv);
        Config::load("config.ini");
       
       
        if (!in_array($this->command, $this->validCommands)) {
            Logger::print( '[Error]: Valid commands are "jatsToXML" or "help"' . PHP_EOL);
            Logger::print( '[Error]: To generate OJS XML from a JATS XML document use "php jatsToOJS.php jatsToXML <ojsUserName> <inputDirectory> <outputDirectory>"' . PHP_EOL);
            Logger::print( '[Error]: The JATS XML document must only contain a single article' . PHP_EOL);
            $error=true;
        }

        if (isset($this->inputDir)&&!is_dir($this->inputDir)) {
            Logger::print( "[Error]: <input_directory> must be a valid directory" . PHP_EOL);
            $error=true;
        }

        if (isset($this->outputDir)&&!is_dir($this->outputDir)) {
            Logger::print( "[Error]: <destination_directory> must be a valid directory" . PHP_EOL);
            $error=true;
        }

        if($error){
            Logger::print( "Errors encountered .... exiting" . PHP_EOL);
            exit;
        }

    }

    /**
     * Prints CLI usage instructions to console
     */
    public function usage() {
        Logger::print( "Package to convert issue JATS XML for a journal article to OJS XML" . PHP_EOL);
        Logger::print( 'To generate OJS XML from a JATS XML document use "php jatsToOJS.php jatsToXML <ojsUserName> <inputDirectory> <outputDirectory>"' . PHP_EOL);
        Logger::print( 'The JATS XML document must only contain a single article' . PHP_EOL);
    }

    /**
     * Executes tasks associated with given command
     */
    public function execute() {
        switch ($this->command) {
            case "jatsToXML":
                $this->generateXml();
                break;
            case "help":
                $this->usage();
                break;
        }

        Logger::writeOut($this->command, $this->ojsUser);
    }


    /**
     * Converts issue CSV data to OJS Native XML files
     *
     * @param string $sourceDir Location of CSV files
     * @param string $destinationDir Target directory for XML files
     */
private function generateXml() {
       
        Logger::print("Running issue JATS-to-XML conversion...");
        $this->println();

    
        Logger::print("Input Directory Detected :: ".$this->inputDir);
        $files = scandir($this->inputDir);

        foreach($files as $file){
            $this->println();
            $filename=$this->inputDir.DIRECTORY_SEPARATOR.$file;
            $info=pathinfo($filename);

            if(isset($info['extension'])&&!strcmp($info['extension'],"xml")){
                Logger::print("Found XML File :: ".$file);

                $fcontent=file_get_contents($filename);
            
                $doc = new DOMDocument();
                $doc->loadXML($fcontent); // load xml
                if($this->verbose){
                    Logger::print("Loaded XML document\n$file");
                    $this->println();
                    Logger::print("Starting JATS schema validation");
                    $this->println();
                }

                libxml_use_internal_errors(true);
                $is_valid_xml=false;

                $this->println();
                $jats_xsd_path=Config::get('jats_xsd');

                foreach($jats_xsd_path as $key => $value){
                    $this->println();
                    Logger::print("Trying XML document :: $file against JATS V$key :: $value");;
                    $is_valid_xml = $doc->schemaValidate($value); // path to xsd file

                    if(!$is_valid_xml){
                        $this->println();
                        Logger::print("XML document :: $file failed validation against JATS V$key :: $value");
                        Logger::print("Error(s) as follows;");
                        $this->println();
                        $errors = libxml_get_errors();
                        foreach ($errors as $error) {
                                Logger::print("!!! XML Parse Error :: Line ".$error->line." :: Message = ".$error->message);
                                }
                        libxml_clear_errors();
                        $this->println();
                    } 
                    else{
                        $this->println();
                        Logger::print("XML document :: $file passed validation against JATS V$key :: $value");
                        break;
                    }   
                }

                libxml_use_internal_errors(false);

                if($is_valid_xml){
                    $this->println();
                    if($this->verbose){
                        Logger::print( "Starting JATS To OJS XML Import");
                        $this->println();
                    }

                    $article = new Article();
                    
                    $id=100;
                    Logger::print("Adding JATS XML Galley File :: ".$filename);
                    $galley = new GalleyFile();
                    $galley->setGalleyFilePath($filename);
                    $galley->setGalleyFileAltText("JATS XML Galley File for this article");
                    $galley->setType(GalleyFile::$XML);
                    $galley->setID($id);
                    $article->addGalleyFile($galley);
                    $id++;
                    //import the article
                    

                    //check for pdf version in the directory
                    $files = scandir($this->inputDir);
                    foreach($files as $file){
                        $fname=$this->inputDir.DIRECTORY_SEPARATOR.$file;
                        $info=pathinfo($fname);
            
                        if(!strcmp($info['extension'],"pdf")){
                            Logger::print("Found PDF Galley File :: ".$file);
                            $galley = new GalleyFile();
                            $galley->setGalleyFilePath($fname);
                            $galley->setGalleyFileAltText("PDF Galley File for this article");
                            $galley->setType(GalleyFile::$PDF);
                            $galley->setID($id);
                            $article->addGalleyFile($galley);
                        }
                        else if(!strcmp($info['extension'],"html")){
                            Logger::print("Found HTML Galley File :: ".$file);
                            $galley = new GalleyFile();
                            $galley->setGalleyFilePath($fname);
                            $galley->setGalleyFileAltText("HTML Galley File for this article");
                            $galley->setType(GalleyFile::$HTML);
                            $galley->setID($id);
                            $article->addGalleyFile($galley);
                        } 
                        else if(!strcmp($info['extension'],"png") ||
                                !strcmp($info['extension'],"jpg") ||
                                !strcmp($info['extension'],"jpeg")||
                                !strcmp($info['extension'],"gif")){
                            
                            Logger::print("Found Cover Image File :: ".$file);
                            $galley = new GalleyFile();
                            $galley->setGalleyFilePath($fname);
                            $galley->setGalleyFileAltText("Cover Image Galley File for this article");
                            $galley->setType(GalleyFile::$COVER_IMAGE);
                            $galley->setID($id);
                            $article->addGalleyFile($galley);
                        }

                        $id++;
                    }
                    

                    $article->setOJSUserName($this->ojsUser);
                    $article->setSectionRef("ART");

                    $this->importArticle($fcontent,$article);

                    $xmlFileName=str_replace(".xml","_OJS_native",(pathinfo($filename,PATHINFO_BASENAME))).".xml";
                    $xmlFileName=$this->outputDir.DIRECTORY_SEPARATOR.$xmlFileName;
                    $xmlWriter = new OJSNativeAdapter($article,$xmlFileName);
                    $xmlWriter->generateXML();
                    Logger::print( "Written OJS Native XML File to :: ".$xmlFileName);
                    println();
                    Logger::print( "Exiting .....");
                }
            }
        }
    
}


private function importArticle($fcontent,$articleObj){
	global $verbose;	

	try{						
		$xml=simplexml_load_string($fcontent) or die("Error: Cannot create XML object");
		
        $c=0;
		foreach($xml->children() as $article) { 
		
            if($c==0){
                $articleObj->setJournalName((string)$article->{'journal-meta'}->{'journal-title-group'}->{'journal-title'});
                $articleObj->setTitle($this->to_utf((string)$article->{'article-meta'}->{'title-group'}->{'article-title'}));
                $articleObj->setSubTitle($this->to_utf((string)$article->{'article-meta'}->{'title-group'}->{'alt-title'}));
                
                $authors=$article->{'article-meta'}->{'contrib-group'};
                $affiliations=$article->{'article-meta'}->{'aff'};
                
                $affils=(string)$affiliations;
                if(!strcmp($affils,"")){
                    $affiliations=$article->{'article-meta'}->{'contrib-group'}->{'aff'};
                }

                $keywords=$article->{'article-meta'}->{'kwd-group'}->{'kwd'};
                if(isset($keywords))
                    foreach($keywords as $keyword){
                        $articleObj->setKeyword($keyword);
                    }
                

                $articleObj->setAbstract($this->to_utf((string)$article->{'article-meta'}->{'abstract'}->{'p'}));
                $articleObj->setAuthors($this->getAuthors($authors,$affiliations));
                $articleObj->setDate(       (string)$article->{'article-meta'}->{'pub-date'}->{'year'}."-".
                                            (string)$article->{'article-meta'}->{'pub-date'}->{'month'}."-".
                                            (string)$article->{'article-meta'}->{'pub-date'}->{'day'});
                $articleObj->setVolume((string)$article->{'article-meta'}->{'volume'});
                $articleObj->setYear((string)$article->{'article-meta'}->{'pub-date'}->{'year'});

                $issue=$startPage=$endPage="";

                $dois=$article->{'article-meta'}->{'article-id'};
            
                $doi="";
                foreach($dois as $doirec){
                    $doistr=$doirec->attributes()->{'pub-id-type'};
                    if(!strcmp($doistr,"doi"))$doi=str_replace("https://doi.org/","",(string)$doirec);
                }
                $articleObj->setDOI($doi);
            }

            $c++;
        }


        if($this->verbose){
            $this->println();
            Logger::print( "Created Article Object");
            $this->println();
            print_r($articleObj);
            $this->println();
        }
  
    } catch(Exception $e){
			Logger::print( "!!! ERROR :: in ".$e->getFile()." on line ".$e->getLine()."::".$e->getMessage());
		}

}

    private function printLn(){
        Logger::print("---------------------------------------------------------------------------------------------------------------------------------------");
    }

    function getAuthors($authorxml,$affxml){
        global $verbose;
    
        $authors=array();
        foreach($authorxml->children() as $author) { 
            $authorObj=new Author();
    
            $authorObj->setFirstName($this->to_utf((string)$author->{'name'}->{'given-names'}));
            $authorObj->setLastName($this->to_utf((string)$author->{'name'}->{'surname'}));
            $authorObj->setID($this->to_utf((string)$author->{'contrib-id'}));
            $authorObj->setEmail($this->to_utf((string)$author->{'email'}));
    
            $affiliations=array();
            foreach($author->{'xref'} as $xref){
                if(!strcmp($xref->attributes()->{'ref-type'},"aff")){
                    $affKey=(string)$xref->attributes()->{'rid'};
                    foreach($affxml as $aff){
                        $affid=$aff->attributes()->{'id'};
                        if($this->verbose)Logger::print( "Affiliation Keys :: $affKey :: $affid");
                        if(!strcmp($affid,$affKey)){
                            $affiliationObj=new Affiliation();
                            $orgname=$division="";
                            foreach($aff->{'institution'} as $inst){
                                if(!strcmp($inst->attributes()->{'content-type'},"orgname"))
                                    $affiliationObj->setName($this->to_utf((string)$inst));
                                else if(!strcmp($inst->attributes()->{'content-type'},"orgdiv1"))
                                    $affiliationObj->setDivision($this->to_utf((string)$inst));
                            }
                            array_push($affiliations, $affiliationObj);
                        }
                    }
                }
            }
    
            $authorObj->setAffiliations($affiliations);
    
            if(strcmp("",$authorObj->getFirstName())||strcmp("",$authorObj->getLastName()))
                array_push($authors,$authorObj);
        }
    
        return $authors;
    }

    private function to_utf($text){
        if(!isset($text))return "";
        $text=trim($text);
        $text= iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text);
        $text = iconv('utf-8', 'ascii//TRANSLIT', $text);
        $text=str_replace("&","and",$text);
       
        return $text;
    }

   
}


$tool = new jatsToOJS(isset($argv) ? $argv : array());
$tool->execute();

?>