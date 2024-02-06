# jats_to_ojs
JATS XML to OJS native XML (3.3.0)

# Introduction
Jats to OJS is a command line tool that will transform XML documents from JATS XML to OJS XML (version 3.3.0).
Detailed documentation is in the .README file

### Requirements
PHP 8.1
Php Composer
Unzip/7zip

### Installation
Clone repository locally
run composer update

    composer.phar update

### How to use
Package to convert issue JATS XML for a journal article to OJS XML
To generate OJS XML from a JATS XML document use 

    php jatsToOJS.php jatsToXML <ojsUserName> <inputDirectory> <outputDirectory>
    
To generate CSV from a JATS XML document use 

    php jatsToOJS.php jatsToCSV <ojsUserName> <inputDirectory> <outputDirectory>
    
To generate OJS XML representation of a CSV document generated from this program use 

    php jatsToOJS.php csvToXML <ojsUserName> <inputDirectory> <outputDirectory>
    
The JATS XML document must only contain a single article
