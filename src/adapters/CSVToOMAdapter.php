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
        $this->article->setKeywords(explode(";",$this->csvArray["Keywords"]));

        $c=1;
        $authors=array();
        while(isset($this->csvArray["Author $c"])){
            $this->getAuthor($c,$authors);
            $c++;
        }


    }

    private function getAuthor($c){
        $author=new Author();
        
    }
    


}

?>