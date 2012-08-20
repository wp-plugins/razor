<?php
/**
 * RAZOR CORE TEST OBJECT
 * Runs a user-configurable panel of unit tests against a BP-Media installation
 *
 * @version 3.1
 * @since 0.1
 * @author inspired by http://svn.automattic.com/wordpress-tests/
 * @package Razor
 * @subpackage Core
 * @license GPL v2.0
 * @link http://code.google.com/p/wp-razor/
 *
 * ========================================================================================================
 */


class RAZ_test_core {

	var $razor_version = "3.1";		    // Version number for Razor
	var $razor_build_date = "2012-08-19";	    // The build date for this version
	
	var $expert_mode = false;		    // Set true to disable all script safety interlocks

	var $db_name = "unit_test";		    // Name of database to connect to. Will be DESTROYED during testing.
	var $db_login = "test";			    // Database login. Make sure user has their access ip set to %
	var $db_pass = "test";			    // Database password
	var $db_host = "localhost";		    // Database host	
	var $db_prefix = "wp_";			    // Prefix used in front of all database tables
							
	var $mode_wp = "single";		    // Mode to run WordPress install in: "single" or "network"

	var $path_wp;				    // Path to WordPress install to run tests on
	var $path_plugins;			    // Path to the WordPress plugins folder
	var $path_plugin;			    // Path to the plugin that's currently running unit tests
	var $path_test_runner;			    // Path to test runner file ("test.php")
	var $path_testplan;			    // Path to folder containing the current testplan
	var $path_log;				    // Path to save test results file to

	var $os_name;				    // Name of operating system test script is running on
	var $platform;				    // Operating system platform: "unix" or "windows".

	var $error_mode;			    // Error logging mode
	var $test_groups;			    // Test groups to run
	var $cache_engine;			    // Array of cache engines to enable during tests		

	var $options;				    // Array of "option_name"=>"string" as set at command prompt by user
	
	var $testplan;				    // The test plan currently being run
	var $testpanel;				    // The test panel currently being run	
	
	var $load_db_image;			    // True to load db image from a SQL file
	var $db_image_file;			    // Path to SQL file
	
	var $remap_folder;			    // True to remap folder name of the plugin currently running tests
	var $folder_remap_function;		    // Name of function used to remap plugin folder


	// ============================================================================================================ //


	var $cmd_options = array(

		"help" =>	array(	"required"=>false,
					"has_value"=>true,
					"short_desc"=>"Displays help. Use --help=optionname to display detailed help for a specific option.",
					"long_desc"=>"",
					"values"=>null,
					"example"=>""
				),
		"platform" =>	array(	"required"=>	false,
					"has_value"=>	true,
					"short_desc"=>	"Forces the script to operate as if its running on the specified operating system.",
					"long_desc"=>	"Forces the script to operate as if its running on the specified operating system,
							instead of auto-detecting the operating system.",
					"values"=>	array("Linux", "Darwin", "Windows"),
					"default"=>	'"Auto-detect"',
					"example"=>	"--platform=Windows"
				),
		"expertmode" =>	array(	"required"=>	false,
					"has_value"=>	false,
					"short_desc"=>	"Disables all of the test script's safety interlocks.",
					"long_desc"=>	"Disables the test script's safety interlocks, allowing arbitrary databases,
							and any other potentially dangerous actions included in the testcase files.",
					"values"=>	null,
					"default"=>	'"Off"',
					"example"=>	"--expertmode"
				),
		"dbname" =>	array(	"required"=>	false,
					"has_value"=>	true,
					"short_desc"=>	"Sets the database name to a custom value. Database is DESTROYED during testing.",
					"long_desc"=>	"Sets the database name that the test script uses to a custom value. The database
							set here will be DESTROYED during testing. To use this option, you must also
							set the '--expertmode' option.",
					"values"=>	null,
					"default"=>	'"bpm_test"',
					"example"=>	"--database=my_database --expertmode"
				),
		"dblogin" =>	array(	"required"=>	false,
					"has_value"=>	true,
					"short_desc"=>	"Sets the login used when connecting to the test database.",
					"long_desc"=>	"Sets the login used when connecting to the test database. The user specified must
							have root access to the database, and their ip access must be set to %.",
					"values"=>	null,
					"default"=>	'"test"',
					"example"=>	"--dblogin=test_user"
				),
		"dbpass" =>	array(	"required"=>	false,
					"has_value"=>	true,
					"short_desc"=>	"Sets the password used when connecting to the test database.",
					"long_desc"=>	"Sets the password used when connecting to the test database. The user specified must
							have root access to the database, and their ip access must be set to %.",
					"values"=>	null,
					"default"=>	'"test"',
					"example"=>	"--dbpass=my_password"
				),
		"dbhost" =>	array(	"required"=>	false,
					"has_value"=>	true,
					"short_desc"=>	"Sets the host used when connecting to the test database.",
					"long_desc"=>	"Sets the host domain name or ip address used when connecting to the test database.",
					"values"=>	null,
					"default"=>	'"localhost"',
					"example"=>	"--dbpass=example.com --dbpass=192.168.1.1"
				),	    
		"dbprefix" =>	array(	"required"=>	false,
					"has_value"=>	true,
					"short_desc"=>	"Sets the prefix used in front of all database tables.",
					"long_desc"=>	"Sets the prefix used in front of all database tables.",
					"values"=>	null,
					"default"=>	'"wp_"',
					"example"=>	"--dbprefix=my-prefix_"
				),
		"pathwp" =>	array(	"required"=>	false,
					"has_value"=>	true,
					"short_desc"=>	"Sets the path to the WordPress installation used during testing.",
					"long_desc"=>	"Sets the path to the WordPress installation used during testing. It must
							have BuddyPress and BP-Media installed. Any other installed plugins will be
							disabled during testing. The script will use wp-config.php from the test
							folder instead of the wp install.",
					"values"=>	null,
					"default"=>	"WordPress install containing the plugin the unit tests are run from.",
					"example"=>	"--pathwp=C:\\xampp\htdocs\\"
				),
		"pathlog" =>	array(	"required"=>	false,
					"has_value"=>	true,
					"short_desc"=>	"Sets the path to save the error log file to.",
					"long_desc"=>	"Sets the path to save the error log file to. This path must be writable by
							the web server.",
					"values"=>	null,
					"default"=>	'"\\unit-test\\"',
					"example"=>	"--pathlog=C:\\xampp\htdocs\\my_log_folder\\"
				),
		"modewp" =>	array(	"required"=>	false,
					"has_value"=>	true,
					"short_desc"=>	"Sets the operation mode for the test WordPress installation.",
					"long_desc"=>	"Sets the operation mode for the test WordPress installation. Use 'single'
							to test on a single-blog WP install. Use 'network' to test on a multi-blog
							network WP install.",
					"values"=>	array("single", "network"),
					"default"=>	'"Network"',
					"example"=>	"--modewp=single"
				),
		"errors" =>	array(	"required"=>	false,
					"has_value"=>	true,
					"short_desc"=>	"Sets the type of PHP errors that the script tests for.",
					"long_desc"=>	"Sets the type of PHP errors that the script tests for.",
					"values"=>	array("single", "network"),
					"default"=>	'"Network"',
					"example"=>	"--modewp=single"
				),
		"plugin" =>	array(	"required"=>	true,
					"has_value"=>	true,
					"short_desc"=>	"Sets the plugin to run tests for.",
					"long_desc"=>	"Sets the plugin or plugins to run tests for. To guarantee test isolation, the test framework
							can only run tests for one plugin per run.",
					"values"=>	false,
					"default"=>	'"all"',
					"example"=>	"--plugin=buddypress"
				),
		"panel" =>	array(	"required"=>	false,
					"has_value"=>	true,
					"short_desc"=>	"Sets the test panel to use.",
					"long_desc"=>	"Sets the test panel to use during the run. To guarantee test isolation, the test framework
							can only run tests against one panel per run.",
					"values"=>	false,
					"default"=>	"(first enabled panel in the test plan panels array)",
					"example"=>	"--panel=A"
				),	    
		"group" =>	array(	"required"=>	false,
					"has_value"=>	true,
					"short_desc"=>	"Sets the groups of tests to run.",
					"long_desc"=>	"Sets the groups of tests to run. If you specify more than one group, you must
							enclose them in quotes, with one space between each group name. This option is only valid
							when a SINGLE plugin name has been specified.",
					"values"=>	false,
					"default"=>	'"all"',
					"example"=>	'--group=core --group="system cache store album"'
				),
		"cache" =>	array(	"required"=>	false,
					"has_value"=>	true,
					"short_desc"=>	"Enables or disables caching engines during testing",
					"long_desc"=>	"Enables or disables caching engines during testing. To use multiple engines simultaneously, 
							list them in quotes, with a space between each engine's name. APC *must be disabled* if the test runner
							is called by another PHP script. See class.memory.cache.php for details.",
					"values"=>	array("all", "off", "apc", "memcache", "redis"),
					"default"=>	'"all"',
					"example"=>	'--cache=off --cache="apc redis"'
				)	    


	);

	var $cmd_alias = array(

		"h" => "help"
	);


	// ============================================================================================================ //


	function RAZ_test_core(){
		
	    
		$this->path_test_runner = dirname(dirname( dirname(__FILE__) ) );
		$this->path_plugins = realpath($this->path_test_runner . '/../');
	}

	
	/**
         * Parses options passed at the command line
         *
         * @version 0.1.9
         * @since 0.1.9
         *
	 * @param bool $check | set false to disable *all* query error checking.
	 *
         * @return ?
         */

	public function parseOptions(){


		// Create full length option names
		// ==========================================================

		$long_names = array();

		foreach( $this->cmd_options as $key => $data){


			if( $data["required"] == true ){

				$long_names[] = $key . ":";
			}
			else {

				if( $data["has_value"] == true ){

					$long_names[] = $key . "::";

				}
				else {

					$long_names[] = $key;
				}
			}

		}
		unset($key, $data);


		// Create single-character option name aliases
		// ==========================================================

		$short_names = "";

		foreach( $this->cmd_alias as $key => $alias){


			if( $this->cmd_options[$alias]["required"] == true ){

				$short_names .= $key . ":";
			}
			else {

				if( $this->cmd_options[$alias]["has_value"] == true ){

					$short_names .= $key . "::";

				}
				else {

					$short_names .= $key;
				}

			}

		}
		unset($key, $alias);


		// Scrape options from the command line
		// ==========================================================

		if (is_callable('getopt')) {

			$raw_options = getopt($short_names, $long_names);
		}
		else {

			// Handle get_opt() not included on PHP for Windows prior to PHP 5.3

			include( dirname(__FILE__) . '/testlib/class.getopt.php' );
			$raw_options = getoptParser::getopt($short_names, $long_names);

		}


		// Re-map all options to their full names
		// ==========================================================

		$this->options = array();

		if( count($raw_options) > 0 ){	// Prevents array_key_exists from crashing on
						// null $raw_options array

			// Scan the raw results array for any option aliases and save them
			// to the processed array as their full-length names

			foreach( $this->cmd_alias as $key => $alias){

				if( array_key_exists($key, $raw_options) ){

					$this->options[$alias] = $raw_options[$key];
				}
			}
			unset($key, $alias);

			// Scan the raw results array for any full-length option names and
			// save them to the processed array. If a user specifies an option
			// twice, using both the short and long names, the long name wins

			foreach( $this->cmd_options as $key => $data){

				if( array_key_exists($key, $raw_options) ){

					$this->options[$key] = $raw_options[$key];
				}
			}
			unset($key, $data);

		}

		// Check that all required options are set ...getopt() either fails to
		// enforce 'required' options, or their documentation fails to explain 
		// their unique interpretation of the word 'required'.
		// ==========================================================
		
		$missing_options = array();
		
		foreach( $this->cmd_options as $key => $data ){
		    
			if( $data['required'] == true ){
			    
				if( !array_key_exists($key, $this->options) ){

					$missing_options[] = $key;
				}
			}		    
		}
		unset($key, $data);
				
		if( count($missing_options) > 0 ){
		    
			echo "\nMISSING REQUIRED OPTIONS: ";		
			
			$items_remaining = count($missing_options) - 1;
			
			foreach( $missing_options as $key ){

				echo "'$key'";
				
				if($items_remaining > 0){
				    
					echo ", ";
					$items_remaining--;
				}
				
			}
			unset($key, $items_remaining);
				
			echo "\n\n";	
			
			die;
		    
		}
		 
						
		// Echo back all the options the user has set
		// ==========================================================

		if( count($this->options) > 0){
			
			if( !array_key_exists('help', $this->options) ){
				
				echo "\nUSING OPTIONS:\n";
				
				foreach( $this->options as $key => $val){

					echo "$key -> $val\n";
				}
				unset($key, $val);				
								
			}
			
		}
		

	}


	/**
         * Displays help for the unit test runner
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param bool $check | set false to disable *all* query error checking.
	 *
         * @return ?
         */

	public function displayHelp() {


		if( array_key_exists('help', $this->options) ){
		    		    			
			if($this->options['help']){
			 
				echo "\nBP-Media Help\n";
				echo "========================================================================\n\n";
				
				echo "  --" . $this->options['help'] . "\n\n";
				
				// Description
				// =======================================
				
				$body = $this->cmd_options[$this->options['help']]['long_desc'];
				
				// Remove any long groups of spaces and newline characters that may have been introduced
				// by the way our text blocks are formatted in the $cmd_options array
				
				$body = preg_replace('|\s+|', ' ', $body);
				$body = preg_replace('|\n+|', '', $body);				
				
				$formatted = self::formatText($term_width=70, $left_pad="    ", $right_pad="", $body);

				echo $formatted . "\n";
				
				// Accepted Values
				// =======================================

				if( $this->cmd_options[$this->options['help']]['values'] ){

					echo "  options:\n\n";

					$body = '"' . implode( '", "', $this->cmd_options[$this->options['help']]['values'] ) . '"';

					// Remove any long groups of spaces and newline characters that may have been introduced
					// by the way our text blocks are formatted in the $cmd_options array

					$body = preg_replace('|\s+|', ' ', $body);
					$body = preg_replace('|\n+|', '', $body);

					$formatted = self::formatText($term_width=70, $left_pad="    ", $right_pad="", $body);

					echo $formatted . "\n";

				}
								
				// Default Value
				// =======================================

				echo "  default value:\n\n";
				
				$body = $this->cmd_options[$this->options['help']]['default'];
				
				$body = preg_replace('|\s+|', ' ', $body);
				$body = preg_replace('|\n+|', '', $body);				
				
				$formatted = self::formatText($term_width=70, $left_pad="    ", $right_pad="", $body);
				
				echo $formatted . "\n";					

				// Example
				// =======================================

				echo "  example:\n\n";
				
				$body = $this->cmd_options[$this->options['help']]['example'];
				
				$body = preg_replace('|\s+|', ' ', $body);
				$body = preg_replace('|\n+|', '', $body);				
				
				$formatted = self::formatText($term_width=70, $left_pad="    ", $right_pad="", $body);
				
				echo $formatted . "\n";							
				
				echo "========================================================================\n";				
				
			}
			else {
				
				echo "\nBP-Media Help\n";
				echo "========================================================================\n\n";
				
				foreach( $this->cmd_options as $option => $data){
					
					echo "  --" . $option . "\n";
										
					$body = $data['short_desc'];

					$body = preg_replace('|\s+|', ' ', $body);
					$body = preg_replace('|\n+|', '', $body);				

					$formatted = self::formatText($term_width=70, $left_pad="    ", $right_pad="", $body);

					echo $formatted . "\n";						
																				
				}
				
				echo "========================================================================\n";												
				
			}
			
			die;

		}

	}


	/**
         * Formats a text string for printing in the terminal window
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param int $term_width | Total width of the terminal window (typically 80 columns)
	 * @param string $left_pad | Character string to pad start of each line with
	 * @param string $right_pad | Character string to pad end of each line with
	 * @param string $text | Text string to process
	 *
         * @return string $result | Formatted text
         */

	public function formatText($term_width, $left_pad, $right_pad, $text) {
		
		// In most situations, the PHP wordwrap() function is sufficient for this 
		// job @link http://php.net/manual/en/function.wordwrap.php but it doesn't
		// indent text
		
		if( strlen($text) == 0 ){			
			return null;			
		}
		
		// The printable line width is the terminal width minus the width of the 
		// padding strings
		
		$max_line_width = $term_width - ( strlen($left_pad) + strlen($right_pad) );
				
		$raw_words = explode(" ", $text);
		$words = array();
		
		// Handle "words" that are longer than $max_line_width (this can happen
		// when printing file path strings)
		
		foreach($raw_words as $check_word){
			
			if( strlen($check_word) <= $max_line_width ){
				
				$words[] = $check_word;
			}
			else {
				
				$word_split = str_split($check_word, $max_line_width);
				$words = array_merge($words, $word_split);
				unset($word_split);
			}
			
		}
		unset($check_word);
		
		$total_words = count($words) - 1;
		$current_word = 0;		
		
		// Fill a line with words until the current length + the length of the next word
		// in the array would exceed the max line width, then drop down to the next line
		
		while( $current_word <= $total_words ){
			
			$current_width = 0;
			$current_line = "";
			
			while( ($current_width <= $max_line_width) && ($current_word <= $total_words) ){
				
				if( ( strlen($words[$current_word]) + $current_width) > $max_line_width ){
														
					break;
				}
				else{															
					$current_line .= $words[$current_word] . " ";
					$current_width += strlen($words[$current_word]) + 1;
					$current_word++;					
				}			
				
			}
				
			$result .= $left_pad . $current_line;
				
			// When jumping down to a new line, pad the end of the line with spaces 
			// so the $right_pad characters on each line align with each other
			
			$align_spaces = $max_line_width - $current_width;

			for($i=0; $i<=$align_spaces; $i++){

				$result .= " ";
			}
					
			$result .= $right_pad . "\n";
						
		}		
		
		return $result;

	}
		
	
	/**
         * Displays test core version info
         *
         * @version 0.1.9
         * @since 0.1.9
         */

	public function init() {
	    
	    echo "\n### RAZOR v" . $this->razor_version . " (" . $this->razor_build_date . ") #####################################\n"; 
	    
	}
	    
	    
	/**
         * Sets the credentials used to access the database
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param bool $check | set false to disable *all* query error checking.
	 *
         * @return ?
         */

	public function setDatabase() {


		// Custom database name		
		if( array_key_exists('dbname', $this->options) ){
			
			
			if( !array_key_exists('expertmode', $this->options) ){

				echo "\nDATABASE SAFETY INTERLOCK TRIGGERED";
				echo "\n##############################################################################\n";
				echo "\nYou set the database name to be '" . $this->options['dbname'] . "' when you ran the BP-Media";
				echo "\nunit test program. This database will be DESTROYED during the testing ";
				echo "\nprocess. If you're sure that's what you want to do, run the test ";
				echo "\nprogram with the option --expertmode to disable this interlock.\n";
				echo "\n##############################################################################\n\n";

				die;
			}
			else {
				
				$this->db_name = $this->options['db_name'];								
			}
			
		}
		
		// Custom database login
		if( array_key_exists('dblogin', $this->options) ){
							
			$this->db_login = $this->options['dblogin'];		
		}
		
		// Custom database pass		
		if( array_key_exists('dbpass', $this->options) ){
							
			$this->db_pass = $this->options['dbpass'];			
		}
		
		// Custom database host	
		if( array_key_exists('dbhost', $this->options) ){
							
			$this->db_host = $this->options['dbhost'];		
		}
		
		// Custom table prefix	
		if( array_key_exists('dbprefix', $this->options) ){
							
			$this->db_prefix = $this->options['dbprefix'];										
		}		
		
			
	}
	

	/**
         * Sets the operating system platform the script runs on
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param bool $check | set false to disable *all* query error checking.
	 *
         * @return ?
         */

	public function setPlatform() {


		// Determine operating system family
		// =================================================================

		if( array_key_exists('platform', $this->options) ){

			$platform_name = $this->options['platform'];
		}
		else {

			$platform_name = PHP_OS;
		}

		$unix_os_names = array(

			"Darwin", "Linux", "Unix", "Ubuntu", "FreeBSD", "IRIX64", "SunOS", "AIX", "Minix", "DragonFly"
		);

		if( array_search($platform_name, $unix_os_names) !== false ){

			$this->platform = "unix";
		}
		else {
			$this->platform = "windows";
		}


	}


	/**
         * Sets the version of WordPress that the script tests on
         *
         * @version 0.1.9
         * @since 0.1.9
         */

	public function setModeWP() {		
		
		
		if( array_key_exists('modewp', $this->options) ){

			if($this->options['modewp'] == 'network'){		
			    
				$this->mode_wp = 'network';								
			}
			else {
				
				$this->mode_wp = 'single';					
			}
		}
		else {
			
			$this->mode_wp = 'single';						
		}		
		
	}


	/**
         * Sets the path to the WordPress install the script runs on
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param bool $check | set false to disable *all* query error checking.
	 *
         * @return ?
         */

	public function setPathWP() {

	
		if( array_key_exists('pathwp', $this->options) ){

			$this->path_wp = $this->options['pathwp'];
		}
		else {

			$this->path_wp = realpath($this->path_test_runner . '/../../../');
		}
		
	}


	/**
         * Sets the error reporting mode 
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param bool $check | set false to disable *all* query error checking.
	 *
         * @return ?
         */

	public function setErrorMode() {

		error_reporting(E_ALL & ~E_DEPRECATED);     // Make sure all errors are displayed
		ini_set('display_errors', true);

	}

	/**
         * Allows razor testplan classes to register themselves with the test core. We've built
	 * it this way to make multi-plugin test runs easier to implement in future versions.
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
         */

	public function registerTestPlan($cls){
	    
		$this->testplan = $cls;
	    
	}
	
	
	/**
         * Loads the test plan specified in the command line args
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
         */

	public function loadTestPlan(){
	    
	    
		require_once($this->path_test_runner . '/testlib/php/abstract.class.testplan.base.php');	
		
		$this->path_plugin = $this->path_plugins . '/' . $this->options['plugin'];		
		$this->path_testplan = $this->path_plugin . '/unit-test/testcase/php';	
	
		require_once($this->path_testplan . '/testplan.php');		
		
	}
	
	
	/**
         * Loads the test platform libraries
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param bool $check | set false to disable *all* query error checking. 
         * @return ?
         */	
	
	public function loadTestPlatform() {
		
		
		echo "\nRUNNING TESTS FROM:\n";
		echo $this->path_testplan . "/\n";

		echo "\nLoading PHPUnit Portable...";

		require_once($this->path_test_runner . '/phpunit-portable/Autoload.php');
		require_once($this->path_test_runner . '/phpunit-portable/Util/ErrorHandler.php');

		$prv = new PHPUnit_Runner_Version();
		$phpunit_ver = $prv->id();

		echo " OK - Version: '$phpunit_ver'\n";

		require_once($this->path_test_runner . '/testlib/php/class.testcase.base.php');
		require_once($this->path_test_runner . '/testlib/php/class.utils.php');
		require_once($this->path_test_runner . '/testlib/php/class.test.db.php');
		
		require_once($this->path_wp .'/wp-includes/class-phpmailer.php');		
		require_once($this->path_test_runner . '/testlib/php/class.data.loader.php');
		require_once($this->path_test_runner . '/testlib/php/class.mock.mailer.php');

	}
	
	
	public function registerTestPanel($cls){
	    
		$this->testpanel = $cls;
	    
	}
	
	
	/**
         * Loads the test panel specified in the command line args
         *
         * @version 0.1.9
         * @since 0.1.9
         */

	public function loadTestPanel(){
	    	    	
		$panel = null;
		
		if( array_key_exists('panel', $this->options) ){

			$panel = $this->options['panel'];
		}		
		
		require_once($this->path_test_runner . '/testlib/php/abstract.class.testpanel.base.php');		
		$this->testplan->getTestPanels($panel);
		
	}	


	/**
         * Sets up global constants and variables used by WordPress
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param bool $check | set false to disable *all* query error checking.
	 *
         * @return ?
         */

	public function setupGlobals() {


		define('DB_NAME', $this->db_name);
		define('DB_USER', $this->db_login);
		define('DB_PASSWORD', $this->db_pass);
		define('DB_HOST', $this->db_host);
		define('DB_CHARSET', 'utf8');
		define('DB_COLLATE', '');

		define('AUTH_KEY',         'ug|O,+Tnh-p+(dd0w:,=mOYtesp[a3:8IevPA/fxp$)q02[ttMN2Q=P-M4OB}W@J');
		define('SECURE_AUTH_KEY',  '*PJy_vCaQ(wE&1[z3B|O0+%_G#V-qyl%iJ0a,gjZPgo9ndM2M?;I2nk[?;b+/P|I');
		define('LOGGED_IN_KEY',    '?t%o+ @l41.3FCOYBV|]O`_pIM@K}q-f;|bvJfB95;Do}0.o*IUPOK04@DB,f&nJ');
		define('NONCE_KEY',        'P_vG_Bn<GR?NM]Xy0 qME,a38pA1?b|RF,Rh+~uY Nn/&/3^$GRI|.Jqw0<xC.sS');
		define('AUTH_SALT',        'pJ(EvMRv9 kNn|,Ox,*eOq`N3DsIv8l*!A_ciI7h[[7E${~n/feBk/iBfImXC-BB');
		define('SECURE_AUTH_SALT', 'QHXYK$hI4X}7)M4@W#KHE+T+VsoNCA!d&h*GddH|ESh+Ot-{<l?V=E,]KL#j-Ym,');
		define('LOGGED_IN_SALT',   '3Ud>[wqQp$s:tKwA6(gJrb}^H;k-SO%-DZ`WeP:IJ,[ mz$M%k#yjG-40wt mQ[,');
		define('NONCE_SALT',       'QR!Wk0dL[%qLxK-hB$ude%7I5We=%XwZT|+4|NHw+kDCi+u;o+^T`N+hC4)d*EQ9');

		global $table_prefix;
		$table_prefix  = $this->db_prefix;

		define('WPLANG', '');
		define('WP_DEBUG', false);
		
		// Override the global constant set in the WP core
		define('ABSPATH', $this->path_wp . '/');		
		
		// Let the test panel have the final say in how globals are set up
		$this->testpanel->setupGlobals();

	}


	/**
         * Sets up the database
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param bool $check | set false to disable *all* query error checking.
	 *
         * @return ?
         */

	public function setupDB() {	   
		
		 	 	    	    
		// Connect to the SQL server
		// =================================================================

		echo "Connecting to SQL server...";
		$bpm_db = new RAZ_test_db(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );

		// Trap db user not existing on the SQL server, or not being allowed to
		// connect from localhost
		if(!$bpm_db->dbh){

			$dbhost = DB_HOST;
			$dbuser = DB_USER;
			$dbpassword = DB_PASSWORD;
			$dbname = DB_NAME;

			$error = " FAIL\n\n";
			$error .= "Couldn't establish a SQL server connection\n\n";
			$error .= "Host: $dbhost\n";
			$error .= "Database: $dbname\n";
			$error .= "User: $dbuser\n";
			$error .= "Pass: $dbpassword\n\n";
			$error .= "1) Check that the database '$dbname' exists on the server.\n";
			$error .= "2) Check that user '$dbuser' with password '$dbpassword' has ALL PRIVLEGES on '$dbname'\n";
			$error .= "3) Set user '$dbuser''s allowed login IP to 'localhost' ('%' does not always work)\n\n";

			echo $error; die;

		}
		else {

			echo " OK - Handle: '$bpm_db->dbh' \n";
		}

		// Open the bpm_test database
		// =================================================================

		echo "Checking if database can be written to...";

		// Trap user existing but database not existing on SQL server
		if( !@mysql_select_db( DB_NAME, $bpm_db->dbh ) ){

			$dbhost = DB_HOST;
			$dbuser = DB_USER;
			$dbpassword = DB_PASSWORD;
			$dbname = DB_NAME;

			$error = " FAIL\n\n";
			$error .= "Couldn't open database '$dbname' on the SQL server\n\n";
			$error .= "Host: $dbhost\n";
			$error .= "Database: $dbname\n";
			$error .= "User: $dbuser\n";
			$error .= "Pass: $dbpassword\n\n";
			$error .= "1) Check that the database '$dbname' exists on the server.\n";
			$error .= "2) Check that user '$dbuser' with password '$dbpassword' has ALL PRIVLEGES on '$dbname'\n";
			$error .= "3) Set user '$dbuser''s allowed login IP to 'localhost' ('%' does not always work)\n\n";

			echo $error; die;
		}
		else {
			echo " OK\n";
		}

	        // Let the test panel set any database options
		$this->testpanel->setupDB();
		
		
		// Handle database load from image file
		// =================================================================
		
		if($this->load_db_image){

			// Clear all existing db tables

			echo "Dropping old tables...";

			drop_tables($bpm_db);

			echo " OK\n";

			echo "Loading test db into SQL server...";
			load_sql_dump($bpm_db, $this->db_image_file);
			echo " OK\n";

			// Handle remap plugin folder to new name
			// ==================================================================
			
			if($this->remap_folder){
			    
				// Determine the stub for the plugin's folder. If the test script is running
				// from "c:/xampp/htdocs/wp-content/plugins/foo_plugin/unit-test/" we want "foo_plugin"

				$stub_start = strrpos($this->path_plugin, "/");

				if(!$stub_start){
					$stub_start = strrpos($this->path_plugin, "\\");
				}

				$stub_end = strlen($this->path_plugin);
				$stub = substr($this->path_plugin, $stub_start + 1, $stub_end - 1);
			
				// Apply directory stub mapping solution to the SQL database
				// (function is located in bpm-remap-directory.php and needs to be updated for each BP release)

				echo "Remapping active directory...";

				$remap_ok = call_user_func($this->folder_remap_function, $bpm_db, $stub);
				//$remap_ok = remap_directory($bpm_db, $stub, $error);

				if($remap_ok){
					echo " OK\n";
				}
				else {
					echo " FAIL\n";
					echo $error;
					die;
				} 
			
			}
		
		}

	}

	
	/**
         * Sets the caching engines to use during tests. Must be run after the BPM core is loaded, because
	 * it needs access to the global cache singleton.
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param bool $check | set false to disable *all* query error checking.
	 *
         * @return ?
         */

	public function setCache() {


		$all_cache_engines = array("apc", "memcached", "redis");
		
		if( array_key_exists('cache', $this->options) ){

			// Remove beginning and end padding, reduce groups of spaces to a
			// single space, remove commas, convert all characters to lower case

			$cache = trim($this->options['cache']);
			$cache = preg_replace('|\s+|', ' ', $cache);
			$cache = preg_replace('|,+|', '', $cache);			
			$cache = strtolower($cache);

			$this->cache_engine = explode(" ", $cache);

			// At this point, the array will contain keys for each cache 
			// engine the user listed

			if( array_search( 'all', $this->cache_engine ) !== false ){

				// Use all available cache engines	
				$this->cache_engine = $all_cache_engines;
			}
			elseif( array_search( 'off', $this->cache_engine ) !== false ){

				// Disable all cache engines					
				$this->cache_engine = false;
			}			

		}
		else {
			// Default to using all avaliable cache engines
			$this->cache_engine = $all_cache_engines;					
		}
		
		// Handle APC being disabled for *just* the cli
		// ====================================================
		
		$apc_cli_enabled = ini_get('apc.enable_cli');
		
		if(!$apc_cli_enabled ){			
			
			echo "WARN: APC not installed, or has been disabled for CLI scripts.\n";
			
			if( is_array($this->cache_engine) ){
				
				$apc_idx = array_search( 'apc', $this->cache_engine );
				
				if($apc_idx !== false){
					unset($this->cache_engine[$apc_idx]);	
				}
			}
						
		}		
				
	}
		
				
	/**
         * Sets the operating system platform the script runs on
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param bool $check | set false to disable *all* query error checking.
	 *
         * @return ?
         */

	public function runTests() {

		// Run the unit tests
		// ########################################################################################

		echo "\nTEST REPORT:\n";

		define('PHPUnit_MAIN_METHOD', false);

		$this->testplan->loadMockClasses();

		// Set test groups
		// ====================================================		
		
		if( array_key_exists('group', $this->options) ){

			// Remove beginning and end padding, reduce groups of spaces to a
			// single space, remove commas, convert all characters to lower case

			$groups = trim($this->options['groups']);
			$groups = preg_replace('|\s+|', ' ', $groups);
			$groups = preg_replace('|,+|', '', $groups);			
			$groups = strtolower($groups);

			$this->test_groups = explode(" ", $groups);

			// If the user passes 'all' as the test group name, null the array
			// so the dictionary class runs all available tests

			if( array_search( 'all', $this->test_groups ) !== false ){

				$this->test_groups = null;
			}

		}
		else {
			
			$this->test_groups = null;
		}
		
		// Handle APC being disabled for *just* the cli
		// ====================================================
		
		$apc_cli_enabled = ini_get('apc.enable_cli');
		
		if(!$apc_cli_enabled ){						
			
			if( is_array($this->test_groups) ){
				
				$apc_idx = array_search( 'apc_cache', $this->test_groups );
				
				if($apc_idx !== false){
					unset($this->test_groups[$apc_idx]);	
				}
			}
						
		}		

		$this->testplan->getTestCases($this->test_groups);

		$classes = bpmtest_get_all_test_cases();


		if( isset($opts['l']) ) {
			bpmtest_listall_testcases($classes);
		}
		else {
			do_action('test_start');

			// Hide warnings during testing, since that's the normal WP behaviour
			if ( !WP_DEBUG ) {
				error_reporting(E_ALL ^ E_NOTICE);
			}
			// Run the tests and print the results
			$result = bpmtest_run_tests($classes, @$opts['t']);

			return $result;

		}

	}


	/**
         * Sets the operating system platform the script runs on
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param bool $check | set false to disable *all* query error checking.
	 *
         * @return ?
         */

	public function logResults($test_result, $web_page=false) {


		if(!$web_page){

			bpmtest_print_result($test_result['printer'],$test_result['suite']);

		}
		else {
		    
		    $result = $test_result['suite'];

		    $pass = $result->passed();
		    $number_of_tests = $result->count();
		    $detected_failures = $result->failureCount();
		    $failures= $result->failures();
		    $incompleted_tests= $result->notImplemented();
		    $skipped = $result->skipped();
		    $number_skipped=$result->skippedCount();

		    $success = "unsuccessfully";
		    $entire_test = $result->wasSuccessful();
		    if($entire_test)
			    $success = "successfully";
		    //print_r($pass);
		    ?>
			    <h2>Test Results</h2>
			    <p>The test suite finished <?php echo $success ?>.<br/>
			    In <?php echo $number_of_tests?> tests there was
			    <?php echo $number_skipped?> skiped, <?php echo count($incompleted_tests);?> incomplete,
			    <?php echo $detected_failures?> failing and <?php echo count($pass) ?> passed!</p>


			    <table class="widefat fixed" cellspacing="0">
			    <thead>
				    <tr>
					    <th id="test" class="manage-column" scope="col">Test Case</th>
					    <th id="function" class="manage-column" scope="col">Test</th>
					    <th id="status" class="manage-column" scope="col">Status</th>
					    <th id="Message" class="manage-column" scope="col">Message</th>
				    </tr>
			    </thead>
			    <tfoot>
				    <tr>
					    <th id="test" class="manage-column" scope="col">Test Case</th>
					    <th id="function" class="manage-column" scope="col">Test</th>
					    <th id="status" class="manage-column" scope="col">Status</th>
					    <th id="Message" class="manage-column" scope="col">Message</th>
				    </tr>

			    </tfoot>
			    <tbody>
				    <?php
					     $passedKeys = array_keys($pass);
					     $skippedKeys = array_keys($skipped);
					     $incompletedKeys = array_keys($incompleted_tests);
					     self::arrange_results($skippedKeys,'skipped');
					     self::arrange_results($incompletedKeys,'incompleted');
					     self::arrange_results($failuresKeys,'failed');
					     self::arrange_results($passedKeys,'passed');

					     //handle failures
					     foreach ($failures as $failure)
					     {
						    if($failure instanceof PHPUnit_Framework_TestFailure)
						    {
							    $failure_msg = $failure->getExceptionAsString();

							    $failedTest = $failure->failedTest();
						    if ($failedTest instanceof PHPUnit_Framework_SelfDescribing)
							    $testName = $failedTest->toString();
						    else
							$testName = get_class($failedTest);


						    $var = explode('::',$testName);
							    $testname=$var[0];
							    $function=$var[1];
						    echo
							    '<tr class="alternate author-self status-publish iedit" valign="top">
							    <td class="test-title column-title">
							    <strong>'.$testname.'</strong>
							    </td>
							    <td class="function column-status">
							    <strong>'.$function.'</strong>
							    </td>
							    <td class="status column-status">
							    <strong>failed</strong>
							    </td>
						    <td class="message column-status">
							    <strong>'.$failure_msg.'</strong>
							    </td>';
						    }
					     }
				    ?>
			    </tbody>
			    </table>

		    <?php



		}


	}


	function arrange_results($array,$status){

		if(!empty($array))
		{
			foreach ($array as $test)
			{
				$var = explode('::',$test);

				$testname=$var[0];
				$function=$var[1];

				self::print_row($testname,$function,$status);
			}
		}
	}

	function print_row($test, $function,$status)
	{
		echo
		'<tr class="alternate author-self status-publish iedit" valign="top">
		<td class="test-title column-title">
		<strong>'.$test.'</strong>
		</td>
		<td class="function column-status">
		<strong>'.$function.'</strong>
		</td>
		<td class="status column-status">
		<strong>'.$status.'</strong>
		</td>
		<td class="message column-status">
		<strong></strong>
		</td>';

	}
	

}


?>