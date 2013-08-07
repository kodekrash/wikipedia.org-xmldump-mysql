wikipedia.org-xmldump-mysql
===========================

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
* PHP 5.4 + (with mbstring, simplexml, mysqli extensions)
* MySQL 5.4 + (optional fulltext index option)

Notes
-----

* This script is designed to run on the command line - not a web browser
* enwiki download is approximately 9.5GB compressed and will require another (approx.) 45GB of storage for the database - a total of approximately 55GB.
* This script reads the compressed file.
* Import process required approximately 4 hours on a well configured quad core with 4GB of memory. 

Howto
-----

* Download the proper pages-articles XML file - for example, enwiki-20130708-pages-articles.xml.bz2.
* Create the database and tables:

		CREATE DATABASE IF NOT EXISTS my_database_name DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_bin;
		
		USE my_database_name;
	
		CREATE TABLE IF NOT EXISTS t_namespace (
		    c_id INT(3) SIGNED PRIMARY KEY,
		    c_name VARCHAR(64) DEFAULT NULL
		) ENGINE=MyISAM;
	
		CREATE TABLE IF NOT EXISTS t_page (
		    c_id BIGINT UNSIGNED PRIMARY KEY,
		    c_namespace INT(3) SIGNED DEFAULT NULL,
		    c_search VARCHAR(255) DEFAULT NULL,
		    c_redirect VARCHAR(255) DEFAULT NULL,
		    c_title VARCHAR(255) DEFAULT NULL
		) ENGINE=MyISAM;
	
		CREATE TABLE IF NOT EXISTS t_contrib (
		    c_id BIGINT UNSIGNED PRIMARY KEY,
		    c_name VARCHAR(64) DEFAULT NULL
		) ENGINE=MyISAM;
		
		CREATE TABLE IF NOT EXISTS t_revision (
		    c_id BIGINT UNSIGNED PRIMARY KEY,
		    c_page BIGINT UNSIGNED DEFAULT NULL,
		    c_contrib BIGINT UNSIGNED DEFAULT NULL,
		    c_parent BIGINT UNSIGNED DEFAULT NULL,
		    c_datetime DATETIME DEFAULT NULL,
		    c_length INT DEFAULT NULL,
		    c_minor BOOLEAN DEFAULT NULL,
		    c_comment VARCHAR(255) DEFAULT NULL,
		    c_sha1 VARCHAR(40) DEFAULT NULL,
		    c_body LONGTEXT
		) ENGINE=MyISAM;

* Download the script.
* Edit the configuration variables at the beginning of the script depending on which file you download, and where you want the log file to be placed:

		$dbc = [
			'host' => 'localhost',
			'port' => null,
			'user' => null,
			'pass' => null,
			'name' => null
		];
		$file = 'enwiki-20130708-pages-articles.xml.bz2';
		$logpath = './';

* Run the script -- this may take several hours.
* Create the indexes:

		CREATE INDEX i_namespace ON t_page (c_namespace);
		CREATE INDEX i_page ON t_revision (c_page);
		CREATE INDEX i_contrib ON t_revision (c_contrib);
		CREATE FULLTEXT INDEX i_search ON t_page (c_search);

License
-------

This project is BSD (2 clause) licensed.
