<?php
/**
 * BP-MEDIA UNIT TEST SCRIPT
 * Runs a user-configurable panel of unit tests against a BP-Media installation
 *
 * @version 0.1.9
 * @since 0.1.9
 * @author based on code from http://svn.automattic.com/wordpress-tests/
 * @package BP-Media
 * @subpackage Unit Test
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */
 
 /**
 * Plugin Name: Razor
 * Plugin URI:  https://code.google.com/p/buddypress-media/people/list/
 * Description: State of the art cross-platform unit testing for WordPress plugins
 * Author:      BP-Media Team
 * Author URI:  https://code.google.com/p/wp-razor/
 * Version:     3.1
 */


    // Prevent hackers from remotely launching the unit test script
    // via the web server
    // ============================================================

    if( !defined( 'ABSPATH' ) && !(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) ){

	    exit;
    }
    else {
	
	    // CASE 1: We're at the command prompt
	    // ==============================================================	
	    if( !defined( 'ABSPATH' ) ){

		    require_once( dirname(__FILE__) . '/testlib/php/class.core.php');

		    global $razor;
		    $razor = new RAZ_test_core();
		  
		    $razor->init();
		    $razor->parseOptions();
		    $razor->displayHelp();

		    $razor->setDatabase();
		    $razor->setPlatform();
		    $razor->setModeWP();
		    $razor->setPathWP();
		    $razor->setErrorMode();  

		    $razor->loadTestPlan();
		    $razor->loadTestPlatform();
		    $razor->loadTestPanel();    
		    $razor->setupGlobals(); 
		    $razor->setupDB(); 


		    // Launch the WordPress core
		    // ########################################################################################

		    // When PHP include() is run inside a function, functions defined inside the files that
		    // are loaded are placed in the global scope, but variables inside the files are placed in
		    // the scope of the function that called include(). Since WordPress has a lot of legacy code
		    // that assumes it's running in the global scope, we have to load wp's files as a direct
		    // include in the main script file.

			    echo "Loading WordPress install...";

			    // This file actually loads the wp core
			    require_once(ABSPATH.'wp-settings.php');

			    // Allow tests to override wp_die
			    add_filter( 'wp_die_handler', '_wp_die_handler_filter' );

			    // Override the default PHP mail function
			    $GLOBALS['phpmailer'] = new MockPHPMailer();

			    // Upgrade the WordPress installation if necessary. This is used to test if BP-Media will
			    // survive a WP version upgrade
			    require_once(ABSPATH.'wp-admin/includes/upgrade.php');

			    // If the user has told the script to test against a "blog network" installation
			    // set up the correct WP globals

			    if( $cls->mode_wp == 'network' ){

				    $GLOBALS['blog_id'] = 1;
				    $GLOBALS['wpdb']->blogid = 1;
				    $GLOBALS['current_blog'] = $GLOBALS['wpdb']->get_results('SELECT * from wp_blogs where blog_id=1');
			    }

			    global $original_wpdb;	    
			    $original_wpdb = $GLOBALS['wpdb'];      // Back-up the $wpdb global in case our tests damage it

			    echo " OK\n";

		    // ########################################################################################
		    // At this point the WP core and all plugins are loaded   

		    $razor->setCache();  		
		    $result = $razor->runTests();
		    $razor->logResults($result, $web_page=false);
	    
	    }
	    // CASE 2: WordPress is trying to load us as a plugin
	    // ==============================================================		    
	    else {
		
		
		
	    }
	    
    
    }


?>