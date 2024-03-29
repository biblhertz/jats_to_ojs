# jats_to_ojs
JATS XML to OJS native XML (3.3.0)

### Introduction
Jats to OJS is a command line tool that will transform XML documents from JATS XML to OJS XML (version 3.3.0).
Detailed documentation is in the .README file (this file is .README.md, detailed info in .README with no file extension)

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

This is a command line tool for generating OJS native XML journal articles from JATS XML article data
The OJS output can then be uploaded to OJS with the OJS native importer plugin.

To generate OJS XML representation of a CSV document generated from this program use 

    php jatsToOJS.php csvToXML <ojsUserName> <inputDirectory> <outputDirectory>
    
The JATS XML document must only contain a single article

## Detailed Description
### Requirements
PHP >= 8.1 installed on your system

It has been tested with OJS version 3.3.0.10 and the documents produced will upload to this version
We have also used it with slightly later versions of OJS.


### XML validation

The xsd file that output documents are validated against (in ./xsd/ojs/) is for version 3.3.0.10 of OJS. This can be updated if need be. The directory of the xsd file is set in the config.ini file.
Input JATS files are also validated against the JATS dtd schema (in the order v1.3, v1.2, v1.1 and v1.0). The validation process stops when a document passes validation against one of the xsd files. These are stored in the appropriate directory ./xsd/jats_vx.x.
The output files produced can work against later versions of OJS. 
The JATS xml validation order can be changed by changing the ordering of the files in config.ini.


### Usage
There are three modes of usage for the package
1) Input an XML file in JATS format and output an XML file in OJS format
2) Input an XML file in JATS format and output a CSV file in our own proprietry format
3) Input a CSV file in our proprietry format and output an XML file in OJS format

Use cases 2) and 3) are so that users can inspect and alter the spreadsheet, which should be easier than inspecting and altering xml documents.

In addition to generating the XML from the JATS file the package will add galley and cover files to the generated OJS XML.
Galley files should be put in a subfolder of the input folder called galley_files. Files in this folder will be added as a galley file in the generated OJS XML.
Supported formats for galley files are xml, html, pdf, png, gif and jpeg.
The original JATS format XML file will also be added as a galley file to the output OJS XML file.
The cover image file should be put in a subdirectory of the input directory called cover_image and be in one of the following image formats jpg, jpeg, gif or png.
Files of other formats will be ignored.

Usage is as follows (from root directory of package and assuming that PHP => 8.1 is installed on the system);

Case 1) JATS XML => OJS XML
php jatsToOJS.php jatsToXML <ojsUserName> <inputDirectory> <outputDirectory>

Case 2) JATS XML => CSV
php jatsToOJS.php jatsToCSV <ojsUserName> <inputDirectory> <outputDirectory>

Case 3) CSV => OJS XML
php jatsToOJS.php csvToXML <ojsUserName> <inputDirectory> <outputDirectory>

<ojsUserName> is the user name in OJS that will upload the documents, this is a requirement of OJS
<inputDirectory> is a directory containing the input files. It should contain a single XML file in JATS format, a single image cover file (if required) and galley files in HTML and / or PDF format (if required)
If the csvTOXML option is chosen then the CSV input file should also be in the input directory
Subdirectories of the input directory are <galley_files> and <cover_image> as described above.
<outputDirectory> is the directory where the generated OJS XML file (or generated CSV file) will be put.

For example;
php .\jatsToOJS.php csvToXML ojschris .\examples\xml_3\ .\examples\xml_3\

csv to OJS XML transformation for username ojschris using input directory .\examples\xml_3\ and outputting to the same directory.

### Testing
Some simple unit tests using the package PHPUnit are included in the directory ./src/tests in the class UnitTest.php.
To run these;

./vendor/bin/phpunit -v ./src/test/UnitTest.php

These should all pass.


### Software Design
The software is designed to be extensible so that other output representations of the data can be produced if needed and other types of input data representation can be added if needed.
There are two main components to the software;

1) An object model that represents an article. This is designed to be an intermediate representation of the Article which input formats are translated into and output formats are generated from.
The components of this are contained in the directory ./src/om.
The object model itself is very simple; the Article object contains the various properties of an Article along with a collection of Authors and a collection of Galley Files. 
The Author object contains the various properties of an Author along with a collection of Affiliations for an author. Affiliations are very simple, institution and division.

2) A collection of adapters that transform other data representations to and from the object model representation (./src/adapters).
Transformations to and from other un-supported representations can be added in this by writing a new adapter class and adjusting the jatsToOJS.php class to include the new functionality.

The jatsToOJS.php class reads input from the command line and calls the appropriate adapters to perform the transformations

### Acknowledgements
The software has used some code from an open-source package for CSV input to OJS native XML by Jeremy Hennig and ewhanson of the Univesrsity of Alberta;
https://github.com/ualbertalib/ojsxml


### Limitations
There is a limit on the size of the files that can be uploaded to OJS (3.3.0.10) of around 2MB. Generated files that are greater than this will not upload.
A strategy to mitigate this could be to make the size of any image files to be included smaller (either through compression or cutting the image).


    

