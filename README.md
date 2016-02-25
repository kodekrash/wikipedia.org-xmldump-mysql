wikipedia.org-xmldump-mysql
===========================

This code works, but will not be maintained after Feb 2016. Please refer to https://github.com/kodekrash/wikipedia.org-xmldump-importer for future updates.

Overview
--------

Wikipedia.org XML Dump Importer is a script to import the standard Wikipedia XML dump into a simple MySQL database structure, useful as a local cache for searching and manipulating Wikipedia articles. The database structure is designed for ease of use, and is not mediawiki-compatible.

Dataset Source
--------------

URL: http://dumps.wikimedia.org/

Updates: monthly

Environment
-----------

* GNU/Linux
* PHP 5.4 + (with simplexml, mysqli, bzip2)
* MySQL 5.4 + (optional fulltext index option)

Notes
-----

* This script is designed to run on the command line - do not load via a web server!
* enwiki download is approximately 12GB compressed and will require another (approx.) 50GB of storage for the database - a total of approximately 62GB.
* This script reads the compressed file.
* Import process requires approximately 4 hours on a well configured quad core with 4GB of memory. 

Howto
-----

* Download the proper pages-articles XML file - for example, enwiki-20160204-pages-articles.xml.bz2.
* Create the database:

		echo "CREATE DATABASE IF NOT EXISTS my_database_name DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_bin;" | mysql

* Import schema:

		mysql my_database_name < schema.sql

* Run the script, specifying database config and import file via command line options:

		./import.php --host=localhost --user=dbuser --pass=mysecret --name=my_database_name --file=enwiki-20160204-pages-articles.xml.bz2

* Create the indexes (optional):

		mysql my_database_name < indexes.sql

License
-------

This project is BSD (2 clause) licensed.
