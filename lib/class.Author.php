<?php


/********************************************************************/
/*		AUTHOR                   									*/
/*                                                                  */
/*		Author 	: 	Chris Tomlinson                             	*/
/*      Date	:	10th July 2023                               	*/
/*																	*/
/*		Journal Author Class to facilitate transformations
		to different representations								*/
/*                                                                  */
/********************************************************************/

class Author {

 	/****************************************************************/
	/*	INSTANCE VARIABLES											*/
	/****************************************************************/
	private $id="";				//identifier
	private $firstName="";		//First name
	private $lastName;			//Last Name
	private $email="";			//email
	private $orcID="";			//orcid
	private $affiliations=array();	//affiliations collection
	
	 
	/****************************************************************/
	/*	CLASS CONSTRUCTOR											*/
	/****************************************************************/
	public function __construct(){	
	}
	
	/****************************************************************/
	/*	INTERFACE METHODS											*/
	/****************************************************************/
	public function setFirstName($s){
		$this->firstName=$s;
	}

	public function getFirstName(){
		return $this->firstName;
	}

	public function setLastName($s){
		$this->lastName=$s;
	}

	public function getLastName(){
		return $this->lastName;
	}

	public function setEmail($s){
		$this->email=$s;
	}

	public function getEmail(){
		return $this->email;
	}

	public function setID($s){
		$this->id=$s;
	}

	public function getID(){
		return $this->id;
	}

	public function setOrcID($s){
		$this->orcID=$s;
	}

	public function setAffiliations($s){
		$this->affiliations=$s;
	}
	
	public function getAffiliations(){
		return $this->affiliations;
	}

	public function getFirstAffiliation(){
		if(count($this->affiliations)) {
			return $this->affiliations[0]->getAffiliation();
		};
		return false;
	}
}
?>