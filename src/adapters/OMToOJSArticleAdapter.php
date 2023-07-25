<?php

namespace Biblhertz\JatsToOjs\adapters;

use XmlWriter;
use Biblhertz\JatsToOjs\om\GalleyFile;

/********************************************************************/
/*		OJSNativeAdapter                   							*/
/*                                                                  */
/*		Author 	: 	Chris Tomlinson                             	*/
/*      Date	:	11th July 2023                               	*/
/*																	*/
/*		generate OJS Native XML	from Article object					*/
/*                                                                  */
/********************************************************************/

class OMToOJSArticleAdapter {

 	/****************************************************************/
	/*	INSTANCE VARIABLES											*/
	/****************************************************************/
	private $article;					//article object to render in XML
	private $xmlWriter;					//XML writer object
	private $uri;						//uri of output XML
	private $locale="en_US";			//locale info			
	
	/****************************************************************/
	/*	CLASS CONSTRUCTOR											*/
	/****************************************************************/
	public function __construct($article,$uri){	
		$this->article=$article;
		$this->uri=$uri;
		$this->xmlWriter = new XmlWriter();
	}
	
	/****************************************************************/
	/*	INTERFACE METHODS											*/
	/****************************************************************/

	/****************************************************************/
	/*	OTHER METHODS												*/
	/****************************************************************/
	
	public function generateXML(){
		
		$this->xmlWriter->openUri($this->uri);
        $this->xmlWriter->startDocument();
        $this->xmlWriter->setIndent(true);
		
        $this->writeArticle();
       
		$this->xmlWriter->endDocument();
        $this->xmlWriter->flush();

	}

	private function writeIssueMetadata() {
        $this->xmlWriter->startElement("issue_identification");

        $this->xmlWriter->startElement("volume");
        $this->xmlWriter->writeRaw($this->article->getVolume());
        $this->xmlWriter->endElement();
        
		$this->xmlWriter->startElement("number");
        $this->xmlWriter->writeRaw($this->article->getIssue());
        $this->xmlWriter->endElement();
        
        $this->xmlWriter->startElement("year");
        $this->xmlWriter->writeRaw($this->article->getYear());
        $this->xmlWriter->endElement();

        $this->xmlWriter->startElement("title");
        $this->addLocaleAttribute();
        $this->xmlWriter->writeRaw($this->article->getJournalName());
        $this->xmlWriter->endElement();
        
        //$this->xmlWriter->startElement("date_published");
        //$this->xmlWriter->writeRaw($this->article->getDate());
        //$this->xmlWriter->endElement();

        $this->xmlWriter->endElement();


		
    }


	/**
     * Writes out section metadata for an issue
     *
     * @param array $sectionData
     */
    function writeSection() {
        $this->xmlWriter->startElement("sections");
        $this->xmlWriter->startElement("section");
        $this->xmlWriter->writeAttribute("ref", "ART");

        $this->xmlWriter->startElement("abbrev");
        $this->addLocaleAttribute();
        $this->xmlWriter->writeRaw("ART");
        $this->xmlWriter->endElement();

        $this->xmlWriter->startElement("policy");
        $this->addLocaleAttribute();
        $this->xmlWriter->endElement();

        $this->xmlWriter->startElement("title");
        $this->addLocaleAttribute();
        $this->xmlWriter->writeRaw("Articles");
        $this->xmlWriter->endElement();

        $this->xmlWriter->endElement();
        $this->xmlWriter->endElement();
    }

	/**
     * Convert and store cover image as base64
     *
     * @param array $issueData
     */
    function writeCover() {
        $galley=$this->article->getCoverImageFile();
        if (!$galley) return;
       
        $this->xmlWriter->startElement("covers");
        $this->xmlWriter->startElement("cover");
        $this->addLocaleAttribute();

        $this->xmlWriter->startElement("cover_image");
        $this->xmlWriter->writeRaw($galley->getGalleyFileName());
        $this->xmlWriter->endElement();

        $this->xmlWriter->startElement("cover_image_alt_text");
        $this->xmlWriter->writeRaw($galley->getGalleyFileAltText());
        $this->xmlWriter->endElement();

        $this->xmlWriter->startElement("embed");
        $this->xmlWriter->writeAttribute("encoding", "base64");
        $this->xmlWriter->writeRaw($galley->getGalleyFileAsBase64());
        $this->xmlWriter->endElement();

        $this->xmlWriter->endElement();
        $this->xmlWriter->endElement();

    }


    /**
     * Write article and publication data for a single article
     *
     */
    private function writeArticle() {
        $this->xmlWriter->startElement("article");
        $this->xmlWriter->writeAttribute("xmlns","http://pkp.sfu.ca");
        $this->xmlWriter->writeAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
        $this->xmlWriter->writeAttribute("status", "3");
        $this->xmlWriter->writeAttribute("submission_progress", "0");
        $this->xmlWriter->writeAttribute("stage" ,"production");
        $this->xmlWriter->writeAttribute("current_publication_id", 1);
        //$this->xmlWriter->writeAttribute("schema_location", "http://pkp.sfu.ca native.xsd");
        //$this->xmlWriter->writeAttribute("locale" ,$this->locale);
        $this->xmlWriter->writeAttribute("date_submitted" ,$this->article->getDate());

        $this->writeIdElement(100);
        $this->writeSubmissionFiles();
        $this->writePublication();
        

        $this->xmlWriter->endElement();
        

    }

   private  function writeSubmissionFiles() {

    $id=100;
    
    foreach($this->article->getGalleyFiles() as $galley){
        if($galley->getType()!=GalleyFile::$COVER_IMAGE){
            $galley->setID($id);
            $this->xmlWriter->startElement("submission_file");
            $this->xmlWriter->writeAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
            $this->xmlWriter->writeAttribute("id", $id);
            $this->xmlWriter->writeAttribute("file_id", $id);
            $this->xmlWriter->writeAttribute("stage", "proof");
            $this->xmlWriter->writeAttribute("viewable", "false");
            $this->xmlWriter->writeAttribute("genre", 'Article Text');
            $this->xmlWriter->writeAttribute("uploader", $this->article->getOJSUserName());
            $this->xmlWriter->writeAttribute("xsi:schemaLocation", "http://pkp.sfu.ca native.xsd");

            $this->xmlWriter->startElement("name");
            $this->addLocaleAttribute();
            $this->xmlWriter->writeRaw($this->article->getOJSUserName() . ", " . $galley->getGalleyFileName());
            $this->xmlWriter->endElement();

            $this->xmlWriter->startElement("file");
            $this->xmlWriter->writeAttribute("id", $id);
            $this->xmlWriter->writeAttribute("filesize", $galley->getGalleyFileSize());
            $this->xmlWriter->writeAttribute("extension", $galley->getGalleyFileType());

            $this->xmlWriter->startElement("embed");
            $this->xmlWriter->writeAttribute("encoding", "base64");
            $this->xmlWriter->writeRaw($galley->getGalleyFileAsBase64());
            $this->xmlWriter->endElement();

            $this->xmlWriter->endElement();
            $this->xmlWriter->endElement();
            $id++;
            }
        }
       
        
    }

    /**
     * Write publication data for a single article
     *
     * @param array $articleData
     */
    function writePublication() {
        $this->xmlWriter->startElement("publication");
        $this->xmlWriter->writeAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
        $this->addLocaleAttribute();
        $this->xmlWriter->writeAttribute("version", "1");
        $this->xmlWriter->writeAttribute("status", "3");
        $this->xmlWriter->writeAttribute("date_published", $this->article->getDate());
        $this->xmlWriter->writeAttribute("section_ref", $this->article->getSectionRef());
        $this->xmlWriter->writeAttribute("seq", 0);

        $this->writeIdElement(100);

        $this->writePublicationMetadata();
        $this->writeAuthors();
        $this->writeArticleGalley();
        $this->writeIssueMetadata();

        $this->xmlWriter->startElement("pages");
        $this->xmlWriter->writeRaw($this->article->getStartPage()."-".$this->article->getEndPage());
        $this->xmlWriter->endElement();
        

        $this->writeCover();

       

        $this->xmlWriter->endElement();
    }

    /**
     * Writes out publication metadata, including, title, abstract, keywords, etc.
     *
     * @param array $articleData
     */
    function writePublicationMetadata() {

         if ($this->article->getDOI() != "") {
            $this->xmlWriter->startElement("id");
            $this->xmlWriter->writeAttribute("type", "doi");
            $this->xmlWriter->writeAttribute("advice", "update");
            $this->xmlWriter->writeRaw($this->article->getDOI());
            $this->xmlWriter->endElement();
        }

        $this->xmlWriter->startElement("title");
        $this->addLocaleAttribute();
        $this->xmlWriter->writeRaw($this->article->getTitle());
        $this->xmlWriter->endElement();

        $this->xmlWriter->startElement("subtitle");
        $this->addLocaleAttribute();
        $this->xmlWriter->writeRaw($this->article->getSubTitle());
        $this->xmlWriter->endElement();


        $this->xmlWriter->startElement("abstract");
        $this->addLocaleAttribute();
        $this->xmlWriter->writeRaw($this->article->getAbstract());
        $this->xmlWriter->endElement();

        $this->xmlWriter->startElement("licenseUrl");
        $this->xmlWriter->writeRaw($this->article->getLicenseUrl());
        $this->xmlWriter->endElement();

        $this->xmlWriter->startElement("copyrightHolder");
        $this->xmlWriter->writeRaw($this->article->getCopyrightHolder());
        $this->xmlWriter->endElement();

        $this->xmlWriter->startElement("copyrightYear");
        $this->xmlWriter->writeRaw($this->article->getCopyrightYear());
        $this->xmlWriter->endElement();

        $keywords=$this->article->getKeywords();
        if (count($keywords)) {
            $this->xmlWriter->startElement("keywords");
            $this->addLocaleAttribute();
            foreach ($keywords as $keyword) {
                $this->xmlWriter->startElement("keyword");
                $this->xmlWriter->writeRaw(trim($keyword));
                $this->xmlWriter->endElement();
            }
            $this->xmlWriter->endElement();
        }
    }

    /**
     * Adds all author objects
     *
     * @param array $articleData
     */
    function writeAuthors() {
       

        $this->xmlWriter->startElement("authors");
        $this->setXmlnsAttributes();

        $authorIndex = 0;
        foreach ($this->article->getAuthors() as $author) {
            $authorData["seq"] = $authorIndex;
            $authorData["currentId"] = 100;
            $this->writeAuthor($author,$authorIndex);
            $authorIndex += 1;
        }

        $this->xmlWriter->endElement();

    }

    /**
     * Adds an individual author
     *
     * @param array $autorData
     */
    function writeAuthor($author,$index) {
        $this->xmlWriter->startElement("author");
        $this->xmlWriter->writeAttribute("user_group_ref", "Author");
        // First author in list is considered primary contact
        if (!$index) {
            $this->xmlWriter->writeAttribute("primary_contact", "true");
        }
        $this->xmlWriter->writeAttribute("seq", $index);
        $this->xmlWriter->writeAttribute("id", 100);

        $this->xmlWriter->startElement("givenname");
        $this->addLocaleAttribute();
        $this->xmlWriter->writeRaw(trim($author->getFirstName()));
        $this->xmlWriter->endElement();

        $this->xmlWriter->startElement("familyname");
        $this->addLocaleAttribute();
        $this->xmlWriter->writeRaw(trim($author->getLastName()));
        $this->xmlWriter->endElement();

        $affiliation=$author->getFirstAffiliation();
        if ($affiliation) {
            $this->xmlWriter->startElement("affiliation");
            $this->addLocaleAttribute();
            $this->xmlWriter->writeRaw($affiliation);
            $this->xmlWriter->endElement();
        }

        /**$this->xmlWriter->startElement("country");
        $this->xmlWriter->writeRaw(trim($autorData["country"]));
        $this->xmlWriter->endElement();**/

        if (trim($author->getEmail()) != "") {
            $this->xmlWriter->startElement("email");
            $this->xmlWriter->writeRaw(trim($author->getEmail()));
            $this->xmlWriter->endElement();
        }

        $this->xmlWriter->endElement();
    }

    function writeArticleGalley() {
        // Disabled for OJS 3.2
        //$pdfUrl = Config::get("pdf_url");
    foreach($this->article->getGalleyFiles() as $galley){
        if($galley->getType()!=GalleyFile::$COVER_IMAGE){
            $this->xmlWriter->startElement("article_galley");
            $this->xmlWriter->writeAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
            $this->addLocaleAttribute();
            $this->xmlWriter->writeAttribute("approved", "false");
            $this->xmlWriter->writeAttribute("xsi:schemaLocation","http://pkp.sfu.ca native.xsd");

            $this->writeIdElement(100);

            $this->xmlWriter->startElement("name");
            $this->addLocaleAttribute();
            $this->xmlWriter->writeRaw(strtoupper($galley->getGalleyFileType()));
            $this->xmlWriter->endElement();

            $this->xmlWriter->startElement("seq");
            $this->xmlWriter->writeRaw("0");
            $this->xmlWriter->endElement();

            $this->xmlWriter->startElement("submission_file_ref");
            $this->xmlWriter->writeAttribute("id", $galley->getID());
            $this->xmlWriter->endElement();

            $this->xmlWriter->endElement();
            }
       
        }
    }


	/**
     * @param false $includeSchemaLocation Includes xsi schema location
     */
    private function setXmlnsAttributes($includeSchemaLocation = false) {
        $this->xmlWriter->writeAttribute("xmlns","http://pkp.sfu.ca");
        $this->xmlWriter->writeAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
        if ($includeSchemaLocation) {
            $this->xmlWriter->writeAttribute("xsi:schemaLocation","http://pkp.sfu.ca native.xsd");
        }
    }

	protected function addLocaleAttribute() {
        $this->xmlWriter->writeAttribute("locale", $this->locale);
    }


     /**
     * Writes an ID field for linking submissions/publications
     *
     * @param $currentId
     */
    private function writeIdElement($currentId) {
        $this->xmlWriter->startElement("id");
        $this->xmlWriter->writeAttribute("type", "internal");
        $this->xmlWriter->writeAttribute("advice", "ignore");
        $this->xmlWriter->writeRaw($currentId);
        $this->xmlWriter->endElement();
    }
}
?>