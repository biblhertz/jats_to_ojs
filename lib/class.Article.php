<?php


/********************************************************************/
/*		ARTICLE                   									*/
/*                                                                  */
/*		Author 	: 	Chris Tomlinson                             	*/
/*      Date	:	10th July 2023                               	*/
/*																	*/
/*		Journal Article Class to facilitate transformations
		to different representations								*/
/*                                                                  */
/********************************************************************/

class Article {

 	/****************************************************************/
	/*	INSTANCE VARIABLES											*/
	/****************************************************************/
	private $journal="";		//Journal Name
	private $title;				//Article Title
	private $volume="";			//journal volume
	private $issue="";			//journal issue
	private $authors=array();	//collection of author objects
	private $date;				//date of issue
	private $year;				//year of publication
	private $doi;				//doi of article
	private $startPage;			//start page
	private $endPage;			//end page
	private $authorEmail;		//author email address
	private $abstract;			//abstract of article
	private $coverImage;		//path to cover image image file
	private $keywords=array();	//article keywords
	private $coverImageFilePath;//cover image file path
	private $coverImageAltText;	//cover image alt text
	private $galleyFilePath;	//galley file path
	private $galleyFileAltText;	//galley alt text
	private $ojsUserName;		//OJS USER Name
	private $journalName;		//OJS Journal Name
	private $sectionRef;		//OJS section refernce name
	 
	/****************************************************************/
	/*	CLASS CONSTRUCTOR											*/
	/****************************************************************/
	public function __construct(){	
	}
	
	/****************************************************************/
	/*	INTERFACE METHODS											*/
	/****************************************************************/
	public function setJournal($s){
		$this->journal=$s;
	}

	public function getJournal(){
		return $this->journal;
	}

	public function setTitle($s){
		$this->title=$s;
	}

	public function getTitle(){
		return $this->title;
	}

	public function setJournalName($s){
		$this->journalName=$s;
	}

	public function getJournalName(){
		return $this->journalName;
	}

	public function setAbstract($s){
		$this->abstract=$s;
	}

	public function getAbstract(){
		return $this->abstract;
	}

	public function setYear($s){
		$this->year=$s;
	}

	public function getYear(){
		return $this->year;
	}

	public function setVolume($s){
		$this->volume=$s;
	}

	public function getVolume(){
		return $this->volume;
	}

	public function setIssue($s){
		$this->issue=$s;
	}

	public function getIssue(){
		return $this->issue;
	}

	public function setDate($s){
		$this->date=$s;
	}

	public function getDate(){
		return $this->date;
	}

	public function setAuthors($authors){
		$this->authors=$authors;
	}

	public function getAuthors(){
		return $this->authors;
	}

	public function setDOI($doi){
		$this->doi=$doi;
	}

	public function getDOI(){
		return $this->doi;
	}

	public function setOJSUserName($name){
		$this->ojsUserName=$name;
	}

	public function getOJSUserName(){
		return $this->ojsUserName;
	}

	public function setStartPage($s){
		$this->startPage=$s;
	}

	public function getStartPage(){
		return $this->startPage;
	}

	public function setEndPage($s){
		$this->endPage=$s;
	}

	public function getEndPage(){
		return $this->endPage;
	}

	public function setKeyword($s){
		array_push($this->keywords,$s);
	}

	public function getKeywords(){
		return $this->keywords;
	}

	public function setSectionRef($s){
		$this->sectionRef=$s;
	}

	public function getSectionRef(){
		return $this->sectionRef;
	}

	/**
	 * Cover Image File Inteface Methods
	 */
	public function setCoverImageFilePath($path){
		$this->coverImageFilePath=$path;
	}

	public function getCoverImageFilePath(){
		return $this->coverImageFilePath;
	}

	public function setCoverImageAltText($text){
		$this->coverImageAltText=$text;
	}

	public function getCoverImageAltText(){
		return $this->coverImageAltText;
	}

	public function getCoverImageFileName(){
		return pathinfo($this->getCoverImageFilePath(), PATHINFO_BASENAME);
	}

	public function getCoverImageFileType(){
		return pathinfo($this->getCoverImageFilePath(), PATHINFO_EXTENSION);
	}

	public function getCoverImageAsBase64(){
		$data = file_get_contents($this->getCoverImageFilePath());
       	return base64_encode($data);
	}


	/**
	 * Galley File Inteface Methods
	 */

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
}
?>