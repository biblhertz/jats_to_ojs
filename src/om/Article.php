<?php

namespace Biblhertz\JatsToOjs\om;

/********************************************************************/
/*		ARTICLE                   									*/
/*                                                                  */
/*		Author 	: 	Chris Tomlinson                             	*/
/*      Date	:	10th July 2023                               	*/
/*																	*/
/*		Journal Article Class to facilitate transformations			*/
/*		to different representations								*/
/*                                                                  */
/********************************************************************/

class Article {

 	/****************************************************************/
	/*	INSTANCE VARIABLES											*/
	/****************************************************************/
	private $journalName;		//OJS Journal Name
	private $title;				//Article Title
	private $subTitle;			//Article SubTitle
	private $volume="";			//journal volume
	private $issue="";			//journal issue
	private $authors=array();	//collection of author objects
	private $date;				//date of issue
	private $year;				//year of publication
	private $doi;				//doi of article
	private $startPage;			//start page
	private $endPage;			//end page
	private $abstract;			//abstract of article
	private $keywords=array();	//article keywords
	private $ojsUserName;		//OJS USER Name
	private $sectionRef;		//OJS section refernce name
	private $galleyFiles=array();	//galley image files for this article
									//includes cover images
	private $licenseUrl;		//license used for this article
	private $copyRightHolder;	//copyright holder
	private $copyRightYear;		//copyright year
	 
	/****************************************************************/
	/*	CLASS CONSTRUCTOR											*/
	/****************************************************************/
	public function __construct(){	
	}
	
	/****************************************************************/
	/*	INTERFACE METHODS											*/
	/****************************************************************/

	public function setTitle($s){
		$this->title=$s;
	}

	public function getTitle(){
		return $this->title;
	}

	public function setSubTitle($s){
		$this->subTitle=$s;
	}

	public function getSubTitle(){
		return $this->subTitle;
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

	public function addAuthor($author){
		//assume that first author added is corresponding author
		if(count($this->authors)==0)$author->setCorrespondingAuthor(true);	
		array_push($this->authors,$author);
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

	public function setKeywords($a){
		$this->keywords=$a;
	}

	public function setSectionRef($s){
		$this->sectionRef=$s;
	}

	public function getSectionRef(){
		return $this->sectionRef;
	}

	public function addGalleyFile($s){
		array_push($this->galleyFiles,$s);
	}

	public function getGalleyFiles(){
		return $this->galleyFiles;
	}

	public function getCoverImageFile(){
		foreach($this->galleyFiles as $galley){
			if($galley->getType()==GalleyFile::$COVER_IMAGE)
				return $galley;
		}
		return false;
	}

	public function setLicenseUrl($s){
		$this->licenseUrl=$s;
	}

	public function getLicenseUrl(){
		return $this->licenseUrl;
	}

	public function setCopyRightHolder($s){
		$this->copyRightHolder=$s;
	}

	public function getCopyRightHolder(){
		return $this->copyRightHolder;
	}

	public function setCopyRightYear($s){
		$this->copyRightYear=$s;
	}

	public function getCopyRightYear(){
		return $this->copyRightYear;
	}
}
?>