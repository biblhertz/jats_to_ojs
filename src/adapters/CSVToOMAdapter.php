<?php
namespace Biblhertz\JatsToOjs\adapters;

use Biblhertz\JatsToOjs\om\GalleyFile;
use Biblhertz\JatsToOjs\om\Article;
use Biblhertz\JatsToOjs\om\Author;
use Biblhertz\JatsToOjs\om\Affiliation;
use Biblhertz\JatsToOjs\utilities\Logger;
use Biblhertz\JatsToOjs\utilities\Utilities;
use Biblhertz\JatsToOjs\utilities\FileCreator;

/********************************************************************/
/*		JATSToOMAdapter                   							*/
/*                                                                  */
/*		Author 	: 	Chris Tomlinson                             	*/
/*      Date	:	11th July 2023                               	*/
/*																	*/
/*		generate our internal Object model from a JATS document		*/
/*                                                                  */
/********************************************************************/

class CSVToOMAdapter {

 	/****************************************************************/
	/*	INSTANCE VARIABLES											*/
	/****************************************************************/
	private $article;					//object model
	private $inputDir;                  //input directory
    private $ojsUser;                   //OJS user name for any import
    private $verbose;                   //verbose logging
    private $csvArray;                  //csv array from file
	
	/****************************************************************/
	/*	CLASS CONSTRUCTOR											*/
	/****************************************************************/
	public function __construct(){	
		$this->article = new Article();
	}
	
	/****************************************************************/
	/*	INTERFACE METHODS											*/
	/****************************************************************/
    public function getArticle(){
        return $this->article;
    }

    public function setInputDir($dir){
        $this->inputDir=$dir;
    }

    public function setFileName($file){
        $this->fileName=$file;
    }

    public function setOJSUser($user){
        $this->ojsUser=$user;
    }

    public function setVerbose($v){
        $this->verbose=$v;
    }

    public function setCSVArray($a){
        $this->csvArray=$a;
    }

	/****************************************************************/
	/*	OTHER METHODS												*/
	/****************************************************************/
    public function generateObjectModel(){

        print_r($this->csvArray);

        $this->article->setOJSUserName($this->ojsUser);
        $this->article->setJournalName($this->csvArray["Journal Name"]);
        $this->article->setVolume($this->csvArray["Journal Volume"]);
        $this->article->setIssue($this->csvArray["Journal Issue"]);
        $this->article->setSectionRef($this->csvArray["Section reference"]);
        $this->article->setStartPage($this->csvArray["Start Page"]);
        $this->article->setEndPage($this->csvArray["End Page"]);

        $this->article->setDate($this->csvArray["Date"]);
        $this->article->setYear($this->csvArray["Year"]);
        $this->article->setTitle($this->csvArray["Article Title"]);
        $this->article->setSubTitle($this->csvArray["Article Subtitle"]);
        $this->article->setDOI($this->csvArray["DOI"]);
        $this->article->setAbstract($this->csvArray["Abstract"]);
        $keywords=explode(";",$this->csvArray["Keywords"]);
        $keyArr=array();
        foreach($keywords as $key)
            if(strlen($key))$keyArr[]=$key;
        $this->article->setKeywords($keyArr);

        $c=1;
        $authors=array();
        while(isset($this->csvArray["Author $c"])){
            $this->addAuthor($c,$authors);
            $c++;
        }

        $galleys=array();
        if(isset($this->csvArray["Cover Image"])){
            $galley=new GalleyFile();
            $galley->setGalleyFilePath($this->csvArray["Cover Image"]);
            $galley->setGalleyFileAltText($this->csvArray["Cover Image Alt Text"]);
            $galley->setType(GalleyFile::$COVER_IMAGE);
            $this->article->addGalleyFile($galley);
        }

        $c=1;
        while(isset($this->csvArray["Galley File $c"])){
            $galley=new GalleyFile();
            $galley->setGalleyFilePath($this->csvArray["Galley File $c"]);
            $galley->setGalleyFileAltText($this->csvArray["Galley File Alt Text $c"]);  
            if(isset($this->csvArray["Galley File Genre $c"]))$galley->setGenre($this->csvArray["Galley File Genre $c"]);         
            $galley->setTypeFromFileType();
            $this->article->addGalleyFile($galley);
            $c++;
        }

        
    }

    private function addAuthor($c){
        $author=new Author();
        
        $names=explode(",",$this->csvArray["Author $c"]);
        if(count($names)==2){
            $author->setFirstName($names[1]);
            $author->setLastName($names[0]);
        }
        
        if(isset($this->csvArray["Email $c"]))
            $author->setEmail($this->csvArray["Email $c"]);
        
        if(isset($this->csvArray["Affiliation $c"])){
            $affiliation = new Affiliation();
            $names=explode(",",$this->csvArray["Affiliation $c"]);
            if(isset($names[0]))$affiliation->setName($names[0]);
            if(isset($names[1]))$affiliation->setDivision($names[1]);
            $author->setAffiliations(array($affiliation));
        }

        $this->article->addAuthor($author);

    }
    


}

?>