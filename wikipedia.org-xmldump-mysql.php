#!/bin/env php
<?php

/**
 * @copyright 2013 James Linden <kodekrash@gmail.com>
 * @author James Linden <kodekrash@gmail.com>
 * @link http://jameslinden.com/dataset/wikipedia.org/xml-dump-import-mysql
 * @link https://github.com/kodekrash/wikipedia.org-xmldump-mysql
 * @license BSD (2 clause) <http://www.opensource.org/licenses/BSD-2-Clause>
 */

$dbc = [
	'host' => 'localhost',
	'port' => null,
	'user' => null,
	'pass' => null,
	'name' => null
];
$file = 'enwiki-20130708-pages-articles.xml.bz2';
$logpath = './';

/*************************************************************************/

date_default_timezone_set( 'UTC' );

function abort( $s ) {
	die( 'Aborting. ' . trim( $s ) . PHP_EOL );
}

if( !is_file( $file ) || !is_readable( $file ) ) {
	abort( 'Data file is missing or not readable.' );
}

if( !is_dir( $logpath ) || !is_writable( $logpath ) ) {
	abort( 'Log path is missing or not writable.' );
}

$in = bzopen( $file, 'r' );
if( !$in ) {
	abort( 'Unable to open input file.' );
}

$out = fopen( rtrim( $logpath, '/' ) . '/wikipedia.org_xmldump-' . date( 'YmdH' ) . '.log', 'w' );
if( !$out ) {
	abort( 'Unable to open log file.' );
}

function q( $str ) {
	global $db;
	return "'" . $db->real_escape_string( $str ) . "'";
}

$sql_ns = 'INSERT INTO t_namespace (c_id,c_name) VALUES (%s,%s)';
$sql_page = 'INSERT INTO t_page (c_id,c_namespace,c_redirect,c_title,c_search) VALUES (%d,%s,%s,%s,%s)';
$sql_contrib = 'INSERT INTO t_contrib (c_id,c_name) VALUES (%d,%s)';
$sql_rev = "INSERT INTO t_revision (c_id,c_page,c_contrib,c_parent,c_datetime,c_length,c_minor,c_comment,c_sha1,c_body) VALUES (%d,%d,%d,%d,'%s',%d,%s,%s,'%s',%s)";

$db = mysqli_connect( $dbc['host'], $dbc['user'], $dbc['pass'], $dbc['name'], $dbc['port'] );
if( $db->connect_error ) {
	abort( 'Unable to connect to database.' );
}

$start = false;
$chunk = null;
$count_p = $count_r = $find_p = $find_r = 0;
$line = null;
while( !feof( $in ) ) {
	$l = bzread( $in, 1 );
	if( $l === false ) {
		abort( 'Error reading compressed file.' );
	}
	if( $l == PHP_EOL ) {
		$line = trim( $line );
		if( $line == '<namespaces>' || $line == '<page>' ) {
			$start = true;
		}
		if( $start === true ) {
			$chunk .= $line . PHP_EOL;
		}
		if( $line == '</namespaces>' ) {
			$start = false;
			$chunk = str_replace( [ 'letter">', '</namespace>' ], [ 'letter" name="', '" />' ], $chunk );
			$x = simplexml_load_string( $chunk );
			$chunk = null;
			if( $x ) {
				foreach( $x->namespace as $y ) {
					$y = (array)$y;
					$ni = (int)$y['@attributes']['key'];
					$nn = null;
					if( array_key_exists( 'name', $y['@attributes'] ) ) {
						$nn = (string)$y['@attributes']['name'];
					}
					$db->query( sprintf( $sql_ns, q( $ni ), q( $nn ) ) );
				}
			} else {
				abort( 'Unable to parse namespaces.' );
			}
		} else if( $line == '</page>' ) {
			$start = false;
			$x = simplexml_load_string( $chunk );
			$chunk = $line = null;
			if( $x ) {
				$find_p ++;
				$pi = (string)$x->id;
				$pt = (string)$x->title;
				$ps = strtolower( $pt );
				$pn = (string)$x->ns;
				$pr = 'NULL';
				if( $x->redirect ) {
					$y = (array)$x->redirect;
					$pr = q( $y['@attributes']['title'] );
				}
				if( $db->query( sprintf( $sql_page, $pi, $pn, $pr, q( $pt ), q( $ps ) ) ) ) {
					$count_p ++;
					if( $x->revision ) {
						$find_r ++;
						$ci = 0;
						if( $x->revision->contributor ) {
							$ci = (string)$x->revision->contributor->id;
							$cu = (string)$x->revision->contributor->username;
							$db->query( sprintf( $sql_contrib, $ci, q( $cu ) ) );
						}
						$ri = (string)$x->revision->id;
						$rp = (string)$x->revision->parentid;
						$rd = date( 'Y-m-d H:i:s', strtotime( (string)$x->revision->timestamp ) );
						$rm = $x->revision->minor ? true : false;
						$rc = (string)$x->revision->comment;
						$rs = (string)$x->revision->sha1;
						$rt = (string)$x->revision->text;
						$rl = strlen( $rt );
						if( $db->query( sprintf( $sql_rev, $ri, $pi, $ci, $rp, $rd, $rl, q( $rm ), q( $rc ), $rs, q( $rt ) ) ) ) {
							$count_r ++;
						}
					}
					$m = date( 'Y-m-d H:i:s' ) . chr(9) . $find_p . '/' . $count_p . chr(9) . $find_r . '/' . $count_r . chr(9) . $pt . PHP_EOL;
					fwrite( $out, $m );
					echo $m;
				}
			}
		}
		$line = null;
	} else {
		$line .= $l;
	}
}

fclose( $out );
bzclose( $in );

echo PHP_EOL;

?>