<?php


/********************************************************************/
/*		FILE CREATOR                   								*/
/*                                                                  */
/*		Author 	: 	Chris Tomlinson                             	*/
/*      Date	:	19th June 2009                               	*/
/*																	*/
/*		Class to facilitate File Creation							*/
/*                                                                  */
/********************************************************************/

class fileCreator {

 	/****************************************************************/
	/*	INSTANCE VARIABLES											*/
	/****************************************************************/
	private $fileName="";
	private $fileHandle;
	 
	/****************************************************************/
	/*	CLASS CONSTRUCTOR											*/
	/****************************************************************/
 	public function __construct(){	
 	}
 
 	/****************************************************************/
	/*	INTERFACE METHODS											*/
	/****************************************************************/
	
	public function setFileName($s){
		$this->fileName=$s;
	}
	
	public function getFileName(){
		return $this->fileName;
	}
	
	public function getBaseFileName(){
		$parts=explode("/",$this->fileName);
		return $parts[count($parts)-1];
	}
	
	public function openFile(){
		if(file_exists($this->fileName))unlink($this->fileName);
		//$this->fileHandle=fopen($this->fileName,'rwx') or die ("Could not open file : ".$this->fileName." (".$this->getBaseFileName().")");
		$this->fileHandle=fopen($this->fileName,'wx') or die ("Could not open file : ".$this->fileName." (".$this->getBaseFileName().")");
	}
	
	public function write($s){
		fwrite($this->fileHandle,$s);	
	}
	
	public function writeLn($s=""){
		fwrite($this->fileHandle,"$s\n");	
	}
	
	public function closeFile(){
		fclose($this->fileHandle);
	}
	
	public function getName(){
		$parts=explode("/",$this->fileName);
		return $parts[count($parts)-1];
	}
	
	/****************************************************************/
	/*	OTHER METHODS												*/
	/****************************************************************/
	/**
	 * recursively delete a directory structure
	 */
	public static function deleteDirectory($dirname) {
    if (is_dir($dirname))
        $dir_handle = opendir($dirname);
    if (!isset($dir_handle))
        return false;
    while($file = readdir($dir_handle)) {
        if ($file != "." && $file != "..") {
            if (!is_dir($dirname."/".$file))
                unlink($dirname."/".$file);
            else
                fileCreator::deleteDirectory($dirname.'/'.$file);           
        }
    }
    closedir($dir_handle);
    rmdir($dirname);
    return true;
}
	
	
}
?>