<?php
namespace Biblhertz\JatsToOjs\test;

use DomDocument;

$file="./src/test/article.xml";
libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadXML(file_get_contents($file));
$valid=$doc->schemaValidate("./jats_xsd/ojs_xsd/native.xsd");
foreach(libxml_get_errors() as $err) echo $err->message.PHP_EOL;
libxml_use_internal_errors(false);



?>
