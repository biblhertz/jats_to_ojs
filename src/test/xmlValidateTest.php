<?php
namespace Biblhertz\JatsToOjs\test;

use DomDocument;


$file="./src/test/article.xml";
libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadXML(file_get_contents($file));
$valid=$doc->schemaValidate("./xsd/ojs_xsd/native.xsd");
foreach(libxml_get_errors() as $err) echo $err->message.PHP_EOL;
libxml_use_internal_errors(false);

$file="./src/test/jatstest.xml";
libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadXML(file_get_contents($file));

$is_valid_xml = $doc->schemaValidate("./xsd/1.3/JATS-journalpublishing1-3.xsd"); // path to xsd file
foreach(libxml_get_errors() as $err) echo $err->message.PHP_EOL;
libxml_use_internal_errors(false);

?>
