<?php
/**
 * RAZOR TEST PANEL BASE CLASS
 * 
 * Sets the host's filesystem, database, and WordPress install into a specific state prior
 * to running unit tests, and reverts the host to a default state after tests have been completed
 *
 * @version 3.1
 * @since 0.1
 * @package Razor
 * @subpackage Core
 * @license GPL v2.0
 * @link http://code.google.com/p/wp-razor/
 *
 * ========================================================================================================
 */

abstract class RAZ_testPanel_base{

    
	var $name;		    // The panel's human-readable name
	var $slug;		    // The panel's machine-readable slug 

	// ============================================================================================================ //
	
	
	/**
	 * Set up global variables and constants (typically used to set WordPress global defines)
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */
	function setupGlobals() {

	}
	
	
	/**
	 * Move assets (such as plugin files) into place before the test runner spins-up
	 * the WordPress installation
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */
	function setupAssets() {

	}
	
	
	/**
	 * Set up db image and remapping options
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */
	function setupDB() {		
		
	}
	
	
	/**
	 * Perform any setup tasks that have to be completed after the DB image has been loaded
	 * but before the test groups are run
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */	
	function setupState() {
	    
	}
	
	
	/**
	 * Cleanup assets after completion of the test panel
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 */
	function tearDownAssets() {

	}	
		
}

?>