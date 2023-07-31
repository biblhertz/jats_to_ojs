<?php
namespace Biblhertz\JatsToOjs\test;

use DomDocument;
use PHPUnit\Framework\TestCase;
use Biblhertz\JatsToOjs\Config;
use Biblhertz\JatsToOjs\utilities\Utilities;
use Biblhertz\JatsToOjs\om\Article;
use Biblhertz\JatsToOjs\om\Author;
use Biblhertz\JatsToOjs\adapters\OMToCSVAdapter;
use Biblhertz\JatsToOjs\adapters\CSVToOMAdapter;
use Biblhertz\JatsToOjs\adapters\JATSToOMAdapter;
use Biblhertz\JatsToOjs\adapters\OMToOJSArticleAdapter;

class UnitTest extends TestCase
{
 
    /**
     * @before
     */
    public function setupConfig(): void
    {
        Config::load("config.ini");
    }



    public function testArticle(){
        $article=new Article();
        $article->setTitle("Test Title");
        $article->setAbstract("Abstract goes in here");

        $this->assertSame('Test Title', $article->getTitle());
        $this->assertSame('Abstract goes in here', $article->getAbstract());
        $this->assertEmpty($article->getJournalName());
        $this->assertEmpty($article->getAuthors());
       
    }

    public function testAuthor(){
        $author=new Author();
        $author->setFirstName("Joe");
        $author->setLastName("Bloggs");

        $this->assertSame('Joe', $author->getFirstName());
        $this->assertSame('Bloggs', $author->getLastName());
        $this->assertEmpty($author->getEmail());
        $this->assertEmpty($author->getAffiliations());   

        $article=new Article();
        $article->addAuthor($author);
        $this->assertSame(1,count($article->getAuthors()));

        $article->addAuthor($author);
        $this->assertSame(1,count($article->getAuthors()));
    }

    public function testOMToCSVAdapter(){
        $file="./src/test/out.csv";
        $article=new Article();
        $article->setTitle("Test");
        $article->setAbstract("Test Abstract");
        $csv=new OMToCSVAdapter($article,$file);
        $csv->generateCSV();
        $this->assertTrue(file_exists($file));

       $csvArr=array();
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                  $csvArr[$data[0]]=$data[1];
                }
            fclose($handle);
         }
        
        $csv=new CSVToOMAdapter();
        $csv->setCSVArray($csvArr);
        $csv->generateObjectModel();
        $article=$csv->getArticle();
        $this->assertSame("Test", $article->getTitle());
        $this->assertSame("Test Abstract", $article->getAbstract());

    }


    public function testCSVToOMAdapter(){
        $csv=new CSVToOMAdapter();
        $filename="./src/test/intest.csv";
        $csvArr=array();
        if (($handle = fopen($filename, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                  $csvArr[$data[0]]=$data[1];
                }
            fclose($handle);
         }
       $csv->setCSVArray($csvArr);
       $csv->generateObjectModel();
       $article=$csv->getArticle();

       $this->assertSame('10.48431/hsah.xxxx', $article->getDoi());
       $this->assertSame('22', $article->getStartPage());
       $this->assertSame(2,count($article->getAuthors()));
       $this->assertSame(0,count($article->getGalleyFiles()));
       $this->assertSame("2022-12-01",$article->getDate());

    }

    public function testOMToOJSAdapter(){
        $filename="./src/test/intest.csv";
        $csvArr=array();
        if (($handle = fopen($filename, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                  $csvArr[$data[0]]=$data[1];
                }
            fclose($handle);
         }
        
        $csv=new CSVToOMAdapter();
        $csv->setCSVArray($csvArr);
        $csv->generateObjectModel();
        $article=$csv->getArticle();
        $this->assertSame('10.48431/hsah.xxxx', $article->getDoi());
        $this->assertSame('22', $article->getStartPage());
        $this->assertSame(2,count($article->getAuthors()));
        $this->assertNotTrue(1,count($article->getGalleyFiles()));
        $this->assertSame("2022-12-01",$article->getDate());


        $file="./src/test/article.xml";
        $omtoojs=new OMToOJSArticleAdapter($article,$file);
        $omtoojs->generateXML();
        
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadXML(file_get_contents($file));
        $xsdLocation=Config::get("ojs_xsd");
        $valid=$doc->schemaValidate($xsdLocation);
        foreach(libxml_get_errors() as $err) echo $err->message.PHP_EOL;
        libxml_use_internal_errors(false);
        $this->assertTrue($valid);

    }

    public function testJATSToOMAdapter(){
        $filename="./src/test/jatstoom/jatstest.xml";
        $fcontent=file_get_contents($filename);
        $doc = new DOMDocument();
        $doc->loadXML($fcontent); // load xml
        libxml_use_internal_errors(true);
        $is_valid_xml=false;
        
        
        $jats_xsd_path=Config::get('jats_xsd');

        $is_valid_xml=false;
        libxml_use_internal_errors(true);
        foreach($jats_xsd_path as $key => $value){   
            $is_valid_xml = $doc->schemaValidate($value); // path to xsd file

            if($is_valid_xml){
                break;
            } 
        }
        libxml_use_internal_errors(false);
        $this->assertTrue($is_valid_xml);
        
        $jatstoOM=new JATSToOMAdapter();
        $jatstoOM->setInputDir("./src/test/jatstoom/");
        $jatstoOM->setJATSXMLPath("./src/test/jatstoom/jatstest.xml");
        $jatstoOM->setOJSUser("tester");
        $jatstoOM->generateObjectModel();
        $article=$jatstoOM->getArticle();

        $this->assertSame('10.48431/xxxxxxx', $article->getDoi());
        $this->assertSame('Interesting Title', $article->getTitle());
        $this->assertSame(2,count($article->getAuthors()));
        $this->assertNotTrue(1,count($article->getGalleyFiles()));
        $this->assertSame("2022-05-31",$article->getDate());

    }
}

?>
