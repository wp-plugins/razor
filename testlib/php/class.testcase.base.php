<?php

/**
 * RAZOR TESTCASE BASE CLASS
 * Extends the base PHPUnit class to make it easier to run unit-tests on a WordPress installation.
 *
 * @version 3.2
 * @since 0.1
 * @author adapted from http://svn.automattic.com/wordpress-tests/
 * @package Razor
 * @subpackage Core
 * @license GPL v2.0
 * @link http://code.google.com/p/wp-razor/
 *
 * ========================================================================================================
 */

class RAZ_testCase extends PHPUnit_Framework_TestCase {
    

	/**
	 *  Test cases should extend RAZ_testCase instead of PHPUnit_TestCase
	 *  ---------------------------------------------------------------------------------
    	 *  a. It makes it easier to switch to a different unit test framework if necessary
	 *  b. RAZ_testCase provides helper methods needed to run many kinds of tests
	 *  c. The bpm-test runner only runs tests that inherit from RAZ_testCase
	 *
	 *  ===========================================================================================
	 */


	protected $backupGlobals = FALSE;
	var $_time_limit = 120; // Max time in seconds for a single test function


	function suite(){
	    
	}

	function setUp() {

		// Error types taken from PHPUnit_Framework_TestResult::run

		$this->_phpunit_err_mask = E_USER_ERROR | E_NOTICE | E_STRICT;
		$this->_old_handler = set_error_handler(array(&$this, '_error_handler'));

		if (is_null($this->_old_handler)) {
			restore_error_handler();
		}
		_enable_wp_die();

		set_time_limit($this->_time_limit);
		
		// Set the enabled cache engines in class.memory.cache.php to the
		// settings passed to the test runner before each test run
		
		global $bpm, $razor;
		
		if($bpm){
		    
			$bpm->mCache->setActiveEngines($razor->cache_engine);			
		}

	}



	function tearDown() {

		if (!is_null($this->_old_handler)) {
			restore_error_handler();
		}
		_enable_wp_die();
	}
	

	// Treat any error that wasn't handled by PHPUnit as a failure
	function _error_handler($errno, $errstr, $errfile, $errline) {

		// @ in front of statement
		if (error_reporting() == 0) {
			return;
		}

		// Notices and strict warnings are passed on to the phpunit error handler but don't trigger an exception
		if ($errno | $this->_phpunit_err_mask) {
			PHPUnit_Util_ErrorHandler::handleError($errno, $errstr, $errfile, $errline);
		}
		
		// Warnings and errors trigger an exception, which is included in the test results
		else {
			error_log("Testing: $errstr in $errfile on line $errline");
			//TODO: we should raise custom exception here, sth like WP_PHPError
			throw new PHPUnit_Framework_Error(
				$errstr,
				$errno,
				$errfile,
				$errline,
				$trace
			);
		}
	}



	function _current_action() {

		global $wp_current_action;

		if (!empty($wp_current_action)){
			return $wp_current_action[count($wp_current_action)-1];
		}
	}



	function _query_filter($q) {

		$now = microtime(true);
		$delta = $now - $this->_q_ts;
		$this->_q_ts = $now;

		$bt = debug_backtrace();
		$caller = '';

		foreach ($bt as $trace) {

			if (strtolower(@$trace['class']) == 'wpdb')
				continue;
			elseif (strtolower(@$trace['function']) == __FUNCTION__)
				continue;
			elseif (strtolower(@$trace['function']) == 'call_user_func_array')
				continue;
			elseif (strtolower(@$trace['function']) == 'apply_filters')
				continue;

			$caller = $trace['function'];
			break;
		}

		// $this->_queries[] = array($caller, $q);
		$delta = sprintf('%0.6f', $delta);
		echo "{$delta} {$caller}: {$q}\n";
		@++$this->_queries[$caller];
		return $q;
	}



	// Call these to record and display db queries

	function record_queries() {

		global $wpdb;
		$wpdb->queries = array();
	}

	function dump_queries() {

		global $wpdb;
		dmp($wpdb->queries);
	}

	function dump_query_summary() {

		global $wpdb;

		$out = array();
		
		foreach ($wpdb->queries as $q) {
				@$out[$q[2]][0] += 1; // Number of queries
				@$out[$q[2]][1] += $q[1]; // Query time
		}

		dmp($out);
	}


	// Pretend that a given URL has been requested
	function http($url) {

		// Note: The WP and WP_Query classes like to silently fetch parameters
		// from all over the place (globals, GET, etc), which makes it tricky
		// to run them more than once without very carefully clearing everything

		$_GET = $_POST = array();

		foreach (array('query_string', 'id', 'postdata', 'authordata', 'day', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages', 'pagenow') as $v){
			unset($GLOBALS[$v]);
		}

		$parts = parse_url($url);

		if (isset($parts['scheme'])) {

			$req = $parts['path'];

			if (isset($parts['query'])) {
				$req .= '?' . $parts['query'];
				// Parse the url query vars into $_GET
				parse_str($parts['query'], $_GET);
			}
		}
		else {
			$req = $url;
		}

		$_SERVER['REQUEST_URI'] = $req;
		unset($_SERVER['PATH_INFO']);

		wp_cache_flush();

		unset($GLOBALS['wp_query'], $GLOBALS['wp_the_query']);
		$GLOBALS['wp_the_query'] =& new WP_Query();
		$GLOBALS['wp_query'] =& $GLOBALS['wp_the_query'];
		$GLOBALS['wp'] =& new WP();

		// Clean out globals to stop them polluting wp and wp_query

		foreach ($GLOBALS['wp']->public_query_vars as $v) {
			unset($GLOBALS[$v]);
		}

		foreach ($GLOBALS['wp']->private_query_vars as $v) {
			unset($GLOBALS[$v]);
		}

		$GLOBALS['wp']->main($parts['query']);
	}


	// Delete all posts and pages
	function _delete_all_posts() {

		global $wpdb;

		$all_posts = $wpdb->get_col("SELECT ID from {$wpdb->posts}");

		if($all_posts){
			foreach ($all_posts as $id){
				wp_delete_post($id);
			}
		}
	}

	// Insert a given number of trivial posts, each with predictable title, content and excerpt
	function _insert_quick_posts($num, $type='post', $more = array()) {

		for($i=0; $i<$num; $i++){

			$this->post_ids[] = wp_insert_post(array_merge(array(
				'post_author' => $this->author->ID,
				'post_status' => 'publish',
				'post_title' => "{$type} title {$i}",
				'post_content' => "{$type} content {$i}",
				'post_excerpt' => "{$type} excerpt {$i}",
				), $more));
		}
	}


	function _insert_quick_comments($post_id, $num=3){

		for($i=0; $i<$num; $i++){
			wp_insert_comment(array(
				'comment_post_ID' => $post_id,
				'comment_author' => "Commenter $i",
				'comment_author_url' => "http://example.com/$i/",
				'comment_approved' => 1,
				));
		}
	}


	// Insert a given number of trivial pages, each with predictable title, content and excerpt
	function _insert_quick_pages($num) {
		$this->_insert_quick_posts($num, 'page');
	}




	function _dump_tables($table) {

		$args = func_get_args();
		$table_list = join(' ', $args);
		system('mysqldump -u '.DB_USER.' --password='.DB_PASSWORD.' -cqnt '.DB_NAME.' '.$table_list);
	}
	

	// Add a user of the specified type
	function _make_user($role = 'administrator', $user_login = '', $pass='', $email='') {

		if(!$user_login){
			$user_login = rand_str();
		}

		if(!$pass){
			$pass = rand_str();
		}

		if(!$email){
			$email = rand_str().'@example.com';
		}

		// We're testing via the add_user()/edit_user() functions, which expect POST data
		$_POST = array(
			'role' => $role,
			'user_login' => $user_login,
			'pass1' => $pass,
			'pass2' => $pass,
			'email' => $email,
		);

		$this->user_ids[] = $id = add_user();
		$_POST = array();

		return $id;
	}


	/**
	 * Skips the current test if the PHP version is not high enough
	 */
	function checkAtLeastPHPVersion($ver) {

		if ( version_compare(PHP_VERSION, $ver, '<') ) {
			$this->markTestSkipped();
		}
	}
	
	
	// Convenience function: return the # of posts associated with a tag
	function _tag_count($name) {

		$t = get_term_by('name', $name, 'post_tag');
		if ($t)
			return $t->count;
	}
	
	// Convenience function: return the # of posts associated with a category
	function _category_count($name) {

		$t = get_term_by('name', $name, 'category');
		if ($t)
			return $t->count;
	}
	
}


// Simple functions for loading and running tests
function bpmtest_get_all_test_files($dir) {

	$tests = array();
	$dh = opendir($dir);

	while (($file = readdir($dh)) !== false) {

		if($file{0} == '.'){
			continue;
		}

		$path = realpath($dir . DIRECTORY_SEPARATOR . $file);

		$fileparts = pathinfo($file);

		if(is_file($path) and $fileparts['extension'] == 'php'){

			$tests[] = $path;
		}
		elseif(is_dir($path)){

			$tests = array_merge($tests, bpmtest_get_all_test_files($path));
		}
	}

	closedir($dh);

	return $tests;
}


function bpmtest_is_descendent($parent, $class) {

	$ancestor = strtolower(get_parent_class($class));

	while($ancestor){

		if( $ancestor == strtolower($parent) ){
			return true;
		}

		$ancestor = strtolower(get_parent_class($ancestor));
	}

	return false;
}


function bpmtest_get_all_test_cases() {

	$test_classes = array();
	$all_classes = get_declared_classes();

	// Only classes that extend RAZ_testCase and have names that don't start with '_' are included

	foreach( $all_classes as $class ){

		if( ($class{0} != '_' ) && bpmtest_is_descendent('RAZ_testCase', $class) ){
			$test_classes[] = $class;
		}
	}

	return $test_classes;
}


/**
 * Simple function to list out all the test cases for command line interfaces
 * 
 * @param $test_classes The test casses array as returned by bpmtest_get_all_test_cases()
 * @return none
 */
function bpmtest_listall_testcases($test_classes) {

	echo "\nWordPress Tests available TestCases:\n\n";	
	echo array_reduce($test_classes, create_function('$current, $item','return $current . $item . ", ";'));
	echo "\n\nUse -t TestCaseName to run individual test cases\n";	
}


function bpmtest_run_tests($classes, $classname='') {

	$suite = new PHPUnit_Framework_TestSuite();

	foreach ($classes as $testcase){

		if (!$classname or strtolower($testcase) == strtolower($classname)) {
			$suite->addTestSuite($testcase);
		}
	}

	// Return PHPUnit::run($suite);
	$cls = new PHPUnit_Framework_TestResult;

	require_once ( PHPU_BASE_PATH . '/TextUI/ResultPrinter.php' );

	$printer = new PHPUnit_TextUI_ResultPrinter( NULL, true, $colors = !((bool) stristr(PHP_OS, 'WIN')) );
	$cls->addListener($printer);

	$result = array(
	    "suite" => $suite->run($cls),
	    "printer" => $printer
	);

	return $result;
	
}


function bpmtest_print_result($printer, $result) {

	$printer->printResult($result, timer_stop());
}


?>