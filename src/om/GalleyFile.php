<?php

namespace Biblhertz\JatsToOjs\om;

/********************************************************************/
/*		AUTHOR                   									*/
/*                                                                  */
/*		Author 	: 	Chris Tomlinson                             	*/
/*      Date	:	10th July 2023                               	*/
/*																	*/
/*		Author Affiliation Class to facilitate transformations		*/
/*		to different representations								*/
/*                                                                  */
/********************************************************************/

class GalleyFile {

    /****************************************************************/
	/*	STATIC VARIABLES											*/
	/****************************************************************/
    /*
    * Galley File Types
    */
    public static $XML=1;
    public static $PDF=2;
    public static $HTML=3;
    public static $COVER_IMAGE=4;
	public static $IMAGE=5;

 	/****************************************************************/
	/*	INSTANCE VARIABLES											*/
	/****************************************************************/
	private $galleyFilePath="";			//file path
	private $galleyFileAltText="";		//alt text
    private $galleyFileType=0;		    //type
    private $id;                        //id in OJS XML
	private $genre="Article Text";		//genre setting in OJS XML
	
	
	 
	/****************************************************************/
	/*	CLASS CONSTRUCTOR											*/
	/****************************************************************/
	public function __construct(){	
	}
	
	/****************************************************************/
	/*	INTERFACE METHODS											*/
	/****************************************************************/
    public function setID($id){
		$this->id=$id;
	}

    public function getID(){
		return $this->id;
	}

	public function setType($type){
		$this->galleyFileType=$type;
	}

    public function getType(){
		return $this->galleyFileType;
	}

	public function setGenre($genre){
		$this->genre=$genre;
	}

    public function getGenre(){
		return $this->genre;
	}

    public function setGalleyFilePath($path){
		$this->galleyFilePath=$path;
	}

	public function getGalleyFilePath(){
		return $this->galleyFilePath;
	}

	public function setGalleyFileAltText($text){
		$this->galleyFileAltText=$text;
	}

	public function getGalleyFileAltText(){
		return $this->galleyFileAltText;
	}

	public function getGalleyFileSize(){
		return filesize($this->getGalleyFilePath());
	}

	public function getGalleyFileName(){
		return pathinfo($this->getGalleyFilePath(), PATHINFO_BASENAME);
	}

	public function getGalleyFileType(){
		return pathinfo($this->getGalleyFilePath(), PATHINFO_EXTENSION);
	}

	public function getGalleyFileAsBase64(){
		$data = file_get_contents($this->getGalleyFilePath());
       	return base64_encode($data);
	}

	public function setTypeFromFileType(){
		$type=$this->getGalleyFileType();
		switch($type){
			case "pdf":
				$this->galleyFileType=GalleyFile::$PDF;

			case "xml":
				$this->galleyFileType=GalleyFile::$XML;
			
			case "html":
				$this->galleyFileType=GalleyFile::$HTML;
		}
		
	}

}
?>