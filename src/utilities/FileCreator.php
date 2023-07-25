<?php
namespace Biblhertz\JatsToOjs\utilities;


/********************************************************************/
/*		FILE CREATOR                   								*/
/*                                                                  */
/*		Author 	: 	Chris Tomlinson                             	*/
/*      Date	:	March 2023                                  	*/
/*																	*/
/*		Class to facilitate File Creation							*/
/*      very simple class for creating ascii files                  */
/*                                                                  */
/********************************************************************/

class FileCreator {

 	/****************************************************************/
	/*	INSTANCE VARIABLES											*/
	/****************************************************************/
	private $fileName="";
	private $dirName="";
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
		$this->dirName=dirname($this->fileName);
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
		//echo "Directory :: ".$this->dirName."\n";
		 if(!is_dir($this->dirName)){
		 	mkdir($this->dirName,0755,true);
		 	chown($this->dirName, "root");
		 }
		//$this->fileHandle=fopen($this->fileName,'rwx') or die ("Could not open file : ".$this->fileName." (".$this->getBaseFileName().")");
		$this->fileHandle=fopen($this->fileName,'wx') or die ("Could not open file : ".$this->fileName." (".$this->getBaseFileName().")");
	}
	
	public function write($s){
		fwrite($this->fileHandle,$s);	
	}
	
	public function writeLn($s=""){
		fwrite($this->fileHandle,	$s.PHP_EOL);	
	}

	public function writeCSV($s=""){
		fputcsv($this->fileHandle,	$s);	
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