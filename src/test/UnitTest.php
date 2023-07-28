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
use Biblhertz\JatsToOjs\adapters\OMToOJSArticleAdapter;

class UnitTest extends TestCase
{
 
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
        $file="./src/test/article.xml";
        $omtoojs=new OMToOJSArticleAdapter($article,$file);
        $omtoojs->generateXML();
        
        libxml_use_internal_errors(true);
        Config::load("config.ini");
        $doc = new DOMDocument();
        $doc->loadXML(file_get_contents($file));
        $xsdLocation=Config::get("ojs_xsd");
        $valid=$doc->schemaValidate($xsdLocation);
        foreach(libxml_get_errors() as $err) echo $err->message.PHP_EOL;
        libxml_use_internal_errors(false);
        $this->assertTrue($valid);

    }

    public function testJATSToOMAdapter(){
        

    }

}

?>
