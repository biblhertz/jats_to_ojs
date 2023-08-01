<?php
namespace Biblhertz\JatsToOjs\adapters;

use Biblhertz\JatsToOjs\om\GalleyFile;
use Biblhertz\JatsToOjs\om\Article;
use Biblhertz\JatsToOjs\om\Author;
use Biblhertz\JatsToOjs\om\Affiliation;
use Biblhertz\JatsToOjs\utilities\Logger;
use Biblhertz\JatsToOjs\utilities\Utilities;

/********************************************************************/
/*		JATSToOMAdapter                   							*/
/*                                                                  */
/*		Author 	: 	Chris Tomlinson                             	*/
/*      Date	:	11th July 2023                               	*/
/*																	*/
/*		generate our internal Object model from a JATS document		*/
/*                                                                  */
/********************************************************************/

class JATSToOMAdapter {

 	/****************************************************************/
	/*	INSTANCE VARIABLES											*/
	/****************************************************************/
	private $article;					//object model
	private $inputDir;                  //input directory
    private $jatsXMLPath;               //path to jats xml input file
    private $ojsUser;                   //OJS user name for any import
    private $verbose;                   //verbose logging
	
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

    public function setJATSXMLPath($path){
        $this->jatsXMLPath=$path;
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

    public function generateObjectModel(){
        
        //first add JATS XML galley file, this is added to every article regardless
        $id=100;
        Logger::print("Adding JATS XML Galley File to OM :: ".$this->jatsXMLPath);
        $galley = new GalleyFile();
        $galley->setGalleyFilePath($this->jatsXMLPath);
        $galley->setGalleyFileAltText("JATS XML Galley File for this article");
        $galley->setType(GalleyFile::$XML);
        $galley->setID($id);
        $this->article->addGalleyFile($galley);
        $id++;
        

        //check the input directory for other galley files version in the directory
        //pdf and html files will be added a galley files
        //any image file found in the directory (jpg,jpeg,png,gif formats)
        //will be added as an article cover image
        $files = scandir($this->inputDir);
        foreach($files as $file){
            $fname=$this->inputDir.DIRECTORY_SEPARATOR.$file;
            $info=pathinfo($fname);

            if(!strcmp($info['extension'],"pdf")){
                Logger::print("Added PDF Galley File to OM :: ".$file);
                $galley = new GalleyFile();
                $galley->setGalleyFilePath($fname);
                $galley->setGalleyFileAltText("PDF Galley File for this article");
                $galley->setType(GalleyFile::$PDF);
                $galley->setID($id);
                $this->article->addGalleyFile($galley);
            }
            else if(!strcmp($info['extension'],"html")){
                Logger::print("Added HTML Galley File to OM :: ".$file);
                $galley = new GalleyFile();
                $galley->setGalleyFilePath($fname);
                $galley->setGalleyFileAltText("HTML Galley File for this article");
                $galley->setType(GalleyFile::$HTML);
                $galley->setID($id);
                $this->article->addGalleyFile($galley);
            } 
            else if(!strcmp($info['extension'],"png") ||
                    !strcmp($info['extension'],"jpg") ||
                    !strcmp($info['extension'],"jpeg")||
                    !strcmp($info['extension'],"gif")){
                
               
                $galley = new GalleyFile();
                $galley->setGalleyFilePath($fname);
                
                if(!$this->article->getCoverImageFile()){
                    $galley->setType(GalleyFile::$COVER_IMAGE);
                    $galley->setGalleyFileAltText("Cover Image Galley File for this article");
                    Logger::print("Added Cover Image File to OM :: ".$file);
                }
                else {
                    $galley->setType(GalleyFile::$IMAGE);
                    $galley->setGalleyFileAltText("Image Galley File for this article");
                    Logger::print("Added Image File to OM :: ".$file);
                }

                $galley->setID($id);
                $this->article->addGalleyFile($galley);
            }

            $id++;
        }
        

        $this->article->setOJSUserName($this->ojsUser);
        $this->article->setSectionRef("CONF"); 
        //import the article from the XML
        $this->importArticle(file_get_contents($this->jatsXMLPath));

    }



private function importArticle($fcontent){

try{						
    $xml=simplexml_load_string($fcontent) or die("Error: Cannot create XML object");

    $c=0;
    foreach($xml->children() as $xmlarticle) { 

        if($c==0){
            $this->article->setJournalName((string)$xmlarticle->{'journal-meta'}->{'journal-title-group'}->{'journal-title'});
            $this->article->setTitle(Utilities::to_utf((string)$xmlarticle->{'article-meta'}->{'title-group'}->{'article-title'}));
            $this->article->setSubTitle(Utilities::to_utf((string)$xmlarticle->{'article-meta'}->{'title-group'}->{'alt-title'}));
            
            $authors=$xmlarticle->{'article-meta'}->{'contrib-group'};
            $affiliations=$xmlarticle->{'article-meta'}->{'aff'};
            
            $affils=(string)$affiliations;
            if(!strcmp($affils,"")){
                $affiliations=$xmlarticle->{'article-meta'}->{'contrib-group'}->{'aff'};
            }

            $keywords=$xmlarticle->{'article-meta'}->{'kwd-group'}->{'kwd'};
            if(isset($keywords))
                foreach($keywords as $keyword){
                    $this->article->setKeyword($keyword);
                }
            

            $this->article->setAbstract(Utilities::to_utf((string)$xmlarticle->{'article-meta'}->{'abstract'}->{'p'}));
            $this->article->setAuthors($this->getAuthors($authors,$affiliations));
            $this->article->setDate(       (string)$xmlarticle->{'article-meta'}->{'pub-date'}->{'year'}."-".
                                        (string)$xmlarticle->{'article-meta'}->{'pub-date'}->{'month'}."-".
                                        (string)$xmlarticle->{'article-meta'}->{'pub-date'}->{'day'});
            $this->article->setVolume((string)$xmlarticle->{'article-meta'}->{'volume'});
            $this->article->setYear((string)$xmlarticle->{'article-meta'}->{'pub-date'}->{'year'});
            //$license=$xmlarticle->{'article-meta'}->{'permissions'}->{'license'}->children("xlink",true);

            $this->article->setCopyRightHolder(Utilities::to_utf((string)$xmlarticle->{'article-meta'}->{'permissions'}->{'copyright-holder'}));
            $this->article->setCopyRightYear(Utilities::to_utf((string)$xmlarticle->{'article-meta'}->{'permissions'}->{'copyright-year'}));
            $this->article->setLicenseUrl("https://creativecommons.org/licenses/by/4.0/"); //hard coded because JATS XML won't parse with simple_xml element

            $issue=$startPage=$endPage="";

            $dois=$xmlarticle->{'article-meta'}->{'article-id'};

            $doi="";
            foreach($dois as $doirec){
                $doistr=$doirec->attributes()->{'pub-id-type'};
                if(!strcmp($doistr,"doi"))$doi=str_replace("https://doi.org/","",(string)$doirec);
            }
            $this->article->setDOI($doi);
        }

        $c++;
        }


        if($this->verbose){
            Logger::println();
            Logger::print( "Created Article Object");
            Logger::println();
            print_r($this->article);
            Logger::println();
        }

        } catch(Exception $e){
        Logger::print( "!!! ERROR :: in ".$e->getFile()." on line ".$e->getLine()."::".$e->getMessage());
        }

}


private function getAuthors($authorxml,$affxml){

    $authors=array();
    foreach($authorxml->children() as $author) { 
        $authorObj=new Author();

        $authorObj->setFirstName(Utilities::to_utf((string)$author->{'name'}->{'given-names'}));
        $authorObj->setLastName(Utilities::to_utf((string)$author->{'name'}->{'surname'}));
        $authorObj->setID(Utilities::to_utf((string)$author->{'contrib-id'}));
        $authorObj->setEmail(Utilities::to_utf((string)$author->{'email'}));

       foreach($author->{'xref'} as $xref){
            if(!strcmp($xref->attributes()->{'ref-type'},"aff")){
                $affKey=(string)$xref->attributes()->{'rid'};
                foreach($affxml as $aff){
                    $affid=$aff->attributes()->{'id'};
                    if($this->verbose)Logger::print( "Affiliation Keys :: $affKey :: $affid");
                    if(!strcmp($affid,$affKey)){
                        $affiliationObj=new Affiliation();
                        foreach($aff->{'institution'} as $inst){
                            if(!strcmp($inst->attributes()->{'content-type'},"orgname"))
                                $affiliationObj->setName(Utilities::to_utf((string)$inst));
                            else if(!strcmp($inst->attributes()->{'content-type'},"orgdiv1"))
                                $affiliationObj->setDivision(Utilities::to_utf((string)$inst));
                        }
                        $authorObj->addAffiliation($affiliationObj);
                    }
                }
            }
        }

       

        if(strcmp("",$authorObj->getFirstName())||strcmp("",$authorObj->getLastName()))
            array_push($authors,$authorObj);
    }

    return $authors;
}


}

?>