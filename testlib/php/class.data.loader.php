<?php
/**
 * RAZOR UNIT TEST DATA LOADERS
 * These functions load database images from disk into the SQL server
 *
 * @version 3.1
 * @since 0.1
 * @author original version from http://svn.automattic.com/wordpress-tests/
 * @package Razor
 * @subpackage Core
 * @license GPL v2.0
 * @link http://code.google.com/p/wp-razor/
 *
 * ========================================================================================================
 */


/**
 * Drops all tables from the test database
 */
function drop_tables($db_object) {

	$tables = $db_object->get_col('SHOW TABLES;');

	foreach ($tables as $table){
		$db_object->query("DROP TABLE IF EXISTS {$table}");
	}

}


function load_sql_dump($db_object, $file) {

	$lines = file($file);

	$sql = "";

	foreach ($lines as $line) {

		// Remove empty lines
		if( !trim($line)){
			continue;
		}

		// Remove "--" prefixed comments
		if( substr($line, 0, 2) == "--"){
		    continue;
		}

		// Remove "/*" prefixed comments
		if( substr($line, 0, 2) == "/*"){
		    continue;
		}

		$line = trim($line);

		// Remove "--" (without comment text)
		if($line == "--"){
		    continue;
		}

		// Combine successive lines until we find the ";" end token
		$sql .= $line . " ";

		// When we find the end token, run the query, then reset it to null for
		// the next group of lines
		$end = strlen($line) - 1;

		if($line[$end] == ";"){

			$result = $db_object->query($sql);
			$sql = "";
		}

	}
}


?>