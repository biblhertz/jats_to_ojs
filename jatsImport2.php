<?php
require_once('./lib/class.fileCreator.php');
require_once('./lib/class.Article.php');
require_once('./lib/class.Author.php');
require_once('./lib/class.Affiliation.php');
require_once('./lib/class.OJSNativeAdapter.php');

ini_set("memory_limit", "4096M");

$verbose=true;
$file_dir="./xml";
$jats_xsd_path=array(   "1.0"=>"C:\\git\\jats_to_ojs\\xsd\\1.0\\JATS-journalpublishing1.xsd",
                        "1.1"=>"C:\\git\\jats_to_ojs\\xsd\\1.1\\JATS-journalpublishing1.xsd",
                        "1.2"=>"C:\\git\\jats_to_ojs\\xsd\\1.2\\JATS-journalpublishing1.xsd",
                        "1.3"=>"C:\\git\\jats_to_ojs\\xsd\\1.3\\JATS-journalpublishing1-3.xsd"
                    );

try{
		set_time_limit(0);
        

        $files = scandir($file_dir);
        foreach($files as $file){
            $filename=$file_dir.DIRECTORY_SEPARATOR.$file;
            $info=pathinfo($filename);
            
            if(!strcmp($info['extension'],"xml")){
                $fcontent=file_get_contents($filename);
                $doc = new DOMDocument();
                $doc->loadXML($fcontent); // load xml
                if($verbose){
                    echo "Loaded XML document\n$file\n";
                    printLn();
                    echo "Starting JATS schema validation\n";
                    printLn();
                }

                libxml_use_internal_errors(true);
                $is_valid_xml=false;
                
                foreach($jats_xsd_path as $key => $value){
                    println();
                    echo "Trying XML document :: $file against JATS V$key :: $value\n";
                    $is_valid_xml = $doc->schemaValidate($value); // path to xsd file

                    if(!$is_valid_xml){
                       
                        println();
                        echo "XML document :: $file failed validation against JATS V$key :: $value\n";
                        $errors = libxml_get_errors();
                        foreach ($errors as $error) {
                                printf('XML error "%s" [%d] (Code %d) in %s on line %d column %d' . "\n",
                                    $error->message, $error->level, $error->code, $error->file,
                                    $error->line, $error->column);
                                }
                        libxml_clear_errors();
                        println();
                    } 
                    else{
                        println();
                        echo "XML document :: $file passed validation against JATS V$key :: $value\n";
                        break;
                    }   
                }

                libxml_use_internal_errors(false);
               
                

                if($is_valid_xml){
                        println();
                        if($verbose){
                            echo "Starting JATS To CSV Import\n";
                            printLn();
                        }

                        $article = new Article();
                        //import the article
                        $article->setCoverImageFilePath("C:\\Users\\BHCT7767\\Downloads\\02_Goerz-000.png");
                        $article->setCoverImageAltText("Cover Image for this article");

                        $article->setGalleyFilePath("C:\Users\BHCT7767\Downloads\Costa dei Trabocchi Map.pdf");
                        $article->setGalleyFileAltText("Galley File for this article");

                        $article->setOJSUserName("ojschris");
                        $article->setVolume(1);
                        $article->setIssue(2);
                        $article->setJournalName("Hertziana Studies in Art History");
                        $article->setKeyword("OJS Upload Tester");
                        $article->setKeyword("Chris Uploader");
                        $article->setKeyword("Native XML Importer");
                        $article->setStartPage(55);
                        $article->setEndPage(72);
                        $article->setSectionRef("ART");


                        importArticle($fcontent,$article);

                        $xmlFileName=str_replace(".","_",(pathinfo($filename,PATHINFO_BASENAME))).".xml";
                        $xmlWriter = new OJSNativeAdapter($article,$xmlFileName);
                        $xmlWriter->generateXML();
                    }
            }

        }

		
		
	}
	catch(Exception $e){
		echo $e->getMessage();
	}

	
function importArticle($fcontent,$articleObj){
	global $verbose;	

	try{						
		$xml=simplexml_load_string($fcontent) or die("Error: Cannot create XML object");
		
        $c=0;
		foreach($xml->children() as $article) { 
		
        if($c==0){
            $articleObj->setJournal((string)$article->{'journal-meta'}->{'journal-title-group'}->{'journal-title'});
            $articleObj->setTitle((string)$article->{'article-meta'}->{'title-group'}->{'article-title'});
            
            $authors=$article->{'article-meta'}->{'contrib-group'};
            $affiliations=$article->{'article-meta'}->{'aff'};
            
            $affils=(string)$affiliations;
            if(!strcmp($affils,"")){
                $affiliations=$article->{'article-meta'}->{'contrib-group'}->{'aff'};
            }

            $articleObj->setAbstract((string)$article->{'article-meta'}->{'abstract'}->{'p'});
            $articleObj->setAuthors(getAuthors($authors,$affiliations));
            $articleObj->setDate(       (string)$article->{'article-meta'}->{'pub-date'}->{'year'}."-".
                                        (string)$article->{'article-meta'}->{'pub-date'}->{'month'}."-".
                                        (string)$article->{'article-meta'}->{'pub-date'}->{'day'});
            $articleObj->setVolume((string)$article->{'article-meta'}->{'volume'});
            $articleObj->setYear((string)$article->{'article-meta'}->{'pub-date'}->{'year'});

            $issue=$startPage=$endPage="";

            //$doi = $article->xpath('article-id[@pub-id-type="doi"]');
            $dois=$article->{'article-meta'}->{'article-id'};
           
            $doi="";
            foreach($dois as $doirec){
                $doistr=$doirec->attributes()->{'pub-id-type'};
                echo "DOI String :: ".$doistr."\n";
                if(!strcmp($doistr,"doi"))$doi=str_replace("https://doi.org/","",(string)$doirec);
            }
            $articleObj->setDOI($doi);
        }


              
            $c++;
        }


        if($verbose){
            printLn();
            echo "Created Article Object\n";
            printLn();
            print_r($articleObj);
            printLn();
        }
  

		} catch(Exception $e){
			echo "!!! ERROR :: in ".$e->getFile()." on line ".$e->getLine()."\n".$e->getMessage();
		}

}

function printLn(){
	echo "+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
}



function getAuthors($authorxml,$affxml){
    global $verbose;

    $authors=array();
    foreach($authorxml->children() as $author) { 
        $authorObj=new Author();

        $authorObj->setFirstName((string)$author->{'name'}->{'given-names'});
        $authorObj->setLastName((string)$author->{'name'}->{'surname'});
        $authorObj->setID((string)$author->{'contrib-id'});
        $authorObj->setEmail((string)$author->{'email'});

        $affiliations=array();
        foreach($author->{'xref'} as $xref){
            if(!strcmp($xref->attributes()->{'ref-type'},"aff")){
                $affKey=(string)$xref->attributes()->{'rid'};
                foreach($affxml as $aff){
                    $affid=$aff->attributes()->{'id'};
                    if($verbose)echo "Affiliation Keys :: $affKey :: $affid\n";
                    if(!strcmp($affid,$affKey)){
                        $affiliationObj=new Affiliation();
                        $orgname=$division="";
                        foreach($aff->{'institution'} as $inst){
                            if(!strcmp($inst->attributes()->{'content-type'},"orgname"))
                                $affiliationObj->setName((string)$inst);
                            else if(!strcmp($inst->attributes()->{'content-type'},"orgdiv1"))
                                $affiliationObj->setDivision((string)$inst);
                        }
                        array_push($affiliations, $affiliationObj);
                    }
                }
            }
        }

        $authorObj->setAffiliations($affiliations);

        if(strcmp("",$authorObj->getFirstName())||strcmp("",$authorObj->getLastName()))
            array_push($authors,$authorObj);
    }

    return $authors;
}


function getHeaderString(){
    return "issueTitle,sectionTitle,sectionAbbrev,authors,affiliation,DOI,articleTitle,year,datePublished,volume,issue,startPage,endPage,articleAbstract,galleyLabel,authorEmail,fileName,keywords,cover_image_filename,cover_image_alt_text";
}

function to_utf($text){
    if(!isset($text))return "";
    $text= iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text);
    $text = iconv('utf-8', 'ascii//TRANSLIT', $text);
    $text=str_replace("\"","'",$text);
    return $text;
}
?>