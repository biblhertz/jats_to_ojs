<?php

require_once('./lib/class.fileCreator.php');
ini_set("memory_limit", "4096M");

$verbose=true;
$file_dir="./xml";
$jats_xsd_path="./xsd/jats_1_0.xsd";

try{
		set_time_limit(0);
        $outFile=new fileCreator();
        $name=uniqid("outFile_").".csv";
        $outFile->setFileName($name);
        $outFile->openFile();
        $outFile->writeLn(getHeaderString());

        $files = scandir($file_dir);
        foreach($files as $file){
            $filename=$file_dir.DIRECTORY_SEPARATOR.$file;
            $info=pathinfo($filename);
            
            if(!strcmp($info['extension'],"xml")){
                $fcontent=file_get_contents($filename);
                $doc = new DOMDocument();
                $doc->loadXML($fcontent); // load xml
                //$is_valid_xml = $doc->schemaValidate($jats_xsd_path); // path to xsd file
                $is_valid_xml=true;
                
                if(!$is_valid_xml){
                    throw new Exception("XML document :: $file failed validation against :: $jats_xsd_path");
                }
                println();
                if($verbose){
                    echo "XML document\n$file\npassed validation against\n$jats_xsd_path\n";
                    printLn();
                    echo "Starting JATS To CSV Import\n";
                    printLn();
                }

                //import the article
                importArticle($fcontent,$outFile);
            }

        }

        $outFile->closeFile();
       
		
		
	}
	catch(Exception $e){
		echo $e->getMessage();
	}

	
function importArticle($fcontent,$outFile){
	global $verbose;	

	try{						
		$xml=simplexml_load_string($fcontent) or die("Error: Cannot create XML object");
		
        $c=0;
		foreach($xml->children() as $article) { 
		
        if($c==0){
            $journal=$article->{'journal-meta'}->{'journal-title-group'}->{'journal-title'};
            $title=$article->{'article-meta'}->{'title-group'}->{'article-title'};
            $authors=$article->{'article-meta'}->{'contrib-group'};
            $affiliations=$article->{'article-meta'}->{'aff'};
            
            $affils=(string)$affiliations;
            if(!strcmp($affils,"")){
                $affiliations=$article->{'article-meta'}->{'contrib-group'}->{'aff'};
            }

            $abstract=$article->{'article-meta'}->{'abstract'}->{'p'};
            $authors=getAuthors($authors,$affiliations);
            $date=      $article->{'article-meta'}->{'pub-date'}->{'year'}."-".
                        $article->{'article-meta'}->{'pub-date'}->{'month'}."-".
                        $article->{'article-meta'}->{'pub-date'}->{'day'};
            $volume=$article->{'article-meta'}->{'volume'};
            $year=$article->{'article-meta'}->{'pub-date'}->{'year'};

            $issue=$startPage=$endPage="";

            //$doi = $article->xpath('article-id[@pub-id-type="doi"]');
            $dois=$article->{'article-meta'}->{'article-id'};
           
            $doi="";
            foreach($dois as $doirec){
                $doistr=$doirec->attributes()->{'pub-id-type'};
                echo "DOI String :: ".$doistr."\n";
                if(!strcmp($doistr,"doi"))$doi=str_replace("https://doi.org/","",(string)$doirec);
            }
        }


              
            $c++;
        }

        if($verbose){
            echo "Journal Title :: ".$journal."\n";
            echo "Article Title :: ".$title."\n";
            echo "Abstract :: ".$abstract."\n";
            echo "DOI :: $doi\n";
            echo "Date :: $date\n";
            echo "Volume :: $volume\n";
            print_r($authors);
        }

        $outString="\"".$journal."\",Articles,ART,";

        $astring=$affstring=$email="";
        foreach($authors as $author){
            if($verbose)echo "Writing Author :: ".$author['last_name'].", ".$author['first_name'].";\n";
            $astring.=to_utf($author['last_name']).", ".to_utf($author['first_name']).";";
            if(isset($author['email'])&&!strcmp($email,""))
                if(filter_var($author['email'], FILTER_VALIDATE_EMAIL))$email=$author['email'];

            if(isset($author['affiliations'])){
                foreach($author['affiliations'] as $affiliation){
                    if($verbose)echo "Writing Affiliation :: ".$affiliation['division'].", ".$affiliation['name'].";".";\n";
                    $affstring.=$affiliation['division'].", ".$affiliation['name'].";";
                }
            }
        }

        if(strlen($astring))$astring=substr($astring,0,strlen($astring)-1);
        if(strlen($affstring))$affstring=substr($affstring,0,strlen($affstring)-1);

        $galleyLabel=$fileName=$keywords=$coverImageFileName=$coverImageAltText="";
        
        $outString.="\"".Normalizer::normalize(to_utf($astring))."\",";
        $outString.="\"".Normalizer::normalize(to_utf($affstring))."\",";
        $outString.="\"".to_utf($doi)."\",";
        $outString.="\"".Normalizer::normalize(to_utf($title))."\",";
        $outString.="\"".$year."\",";
        $outString.="\"".$date."\",";
        $outString.="\"".$volume."\",";
        $outString.="\"".$issue."\",";
        $outString.="\"".$startPage."\",";
        $outString.="\"".$endPage."\",";
        $outString.="\"".Normalizer::normalize(to_utf($abstract))."\",";
        $outString.="\"".$galleyLabel."\",";
        $outString.="\"".$email."\",";
        $outString.="\"".$fileName."\",";
        $outString.="\"".$keywords."\",";
        $outString.="\"".$coverImageFileName."\",";
        $outString.="\"".$coverImageAltText."\",";



        $outFile->writeLn($outString);

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
        $first_name=(string)$author->{'name'}->{'given-names'};
        $last_name=(string)$author->{'name'}->{'surname'};
        $id=(string)$author->{'contrib-id'};
        $email=(string)$author->{'email'};

        $affiliations=array();
        foreach($author->{'xref'} as $xref){
            if(!strcmp($xref->attributes()->{'ref-type'},"aff")){
                $affKey=(string)$xref->attributes()->{'rid'};
                foreach($affxml as $aff){
                    $affid=$aff->attributes()->{'id'};
                    if($verbose)echo "Affiliation Keys :: $affKey :: $affid\n";
                    if(!strcmp($affid,$affKey)){
                        $orgname=$division="";
                        foreach($aff->{'institution'} as $inst){
                            if(!strcmp($inst->attributes()->{'content-type'},"orgname"))
                                $orgname=(string)$inst;
                            else if(!strcmp($inst->attributes()->{'content-type'},"orgdiv1"))
                                $division=(string)$inst;
                        }
                        array_push($affiliations, array("key"=>$affKey,"name"=>$orgname,"division"=>$division));
                    }
                }

                

            }
        }

        if(strcmp("",$last_name)||strcmp("",$first_name))
            array_push($authors,array("last_name"=>$last_name,"first_name"=>$first_name,"id"=>$id,"email"=>$email, "affiliations"=>$affiliations));
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