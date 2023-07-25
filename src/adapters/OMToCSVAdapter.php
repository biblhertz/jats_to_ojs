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

class OMToCSVAdapter {

 	/****************************************************************/
	/*	INSTANCE VARIABLES											*/
	/****************************************************************/
	private $article;					//object model
	private $inputDir;                  //input directory
    private $ojsUser;                   //OJS user name for any import
    private $verbose;                   //verbose logging
    private $fileName;
	
	/****************************************************************/
	/*	CLASS CONSTRUCTOR											*/
	/****************************************************************/
	public function __construct($article,$uri){	
		$this->article = $article;
        $this->fileName=$uri;
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

	/****************************************************************/
	/*	OTHER METHODS												*/
	/****************************************************************/
    public function generateCSV(){
        $csvFile=new FileCreator();
        $csvFile->setFileName($this->fileName);
        $csvFile->openFile();
        $csvFile->writeCSV(array("Journal Name",$this->article->getJournalName()));
        $csvFile->writeCSV(array("Journal Volume",$this->article->getVolume()));
        $csvFile->writeCSV(array("Journal Issue",$this->article->getIssue()));
        $csvFile->writeCSV(array("Section reference",$this->article->getSectionRef()));
        $csvFile->writeCSV(array("Start Page",$this->article->getStartPage()));
        $csvFile->writeCSV(array("End Page",$this->article->getEndPage()));
        $csvFile->writeCSV(array("Date",$this->article->getDate()));
        $csvFile->writeCSV(array("Year",$this->article->getYear()));
        $csvFile->writeCSV(array("Article Title",$this->article->getTitle()));
        $csvFile->writeCSV(array("Article Subtitle","\"".$this->article->getSubTitle()));
        $csvFile->writeCSV(array("DOI",$this->article->getDOI()));
        $csvFile->writeCSV(array("Abstract",$this->article->getAbstract()));
        $keyStr="";
        foreach($this->article->getKeyWords() as $keyword)$keyStr.=$keyword.";";
        $csvFile->writeCSV(array("Keywords",$keyStr));
        
        $authorEmail="";
        $c=1;
        foreach($this->article->getAuthors() as $author){
                $csvFile->writeCSV(array("Author $c",$author->getLastName().", ".$author->getFirstName()));
                $csvFile->writeCSV(array("Email $c",$author->getEmail()));
                $csvFile->writeCSV(array("Affiliation $c",$author->getFirstAffiliation()));
                if($c==1)$authorEmail=$author->getEmail();
                $c++;
        }
        $csvFile->writeCSV(array("Author Email",$authorEmail));
        
        $coverImage=$this->article->getCoverImageFile();
        if($coverImage){
            $csvFile->writeCSV(array("Cover Image",$coverImage->getGalleyFilePath()));
            $csvFile->writeCSV(array("Cover Image Alt Text",$coverImage->getGalleyFileAltText()));
        }

        foreach($this->article->getGalleyFiles() as $galley){
            if($galley->getType()!=GalleyFile::$COVER_IMAGE){
                $csvFile->writeCSV(array("Galley File",$galley->getGalleyFilePath()));
                $csvFile->writeCSV(array("Galley File Alt Text",$galley->getGalleyFileAltText()));
            }
        }



        $csvFile->closeFile();

    }
    


}

?>