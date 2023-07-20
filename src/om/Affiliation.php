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

class Affiliation {

 	/****************************************************************/
	/*	INSTANCE VARIABLES											*/
	/****************************************************************/
	private $name="";			//name
	private $division;			//division
	
	
	 
	/****************************************************************/
	/*	CLASS CONSTRUCTOR											*/
	/****************************************************************/
	public function __construct(){	
	}
	
	/****************************************************************/
	/*	INTERFACE METHODS											*/
	/****************************************************************/
	public function setName($s){
		$this->name=$s;
	}

	public function getName(){
		return $this->name;
	}

	public function setDivision($s){
		$this->division=$s;
	}

	public function getDivision(){
		return $this->division;
	}

	public function getAffiliation(){
		return $this->getDivision().", ".$this->getName();
	}
}
?>