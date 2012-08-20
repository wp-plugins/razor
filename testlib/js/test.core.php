<?php

/**
 * RAZOR JS UNIT TEST CORE
 * Handles core functionality for the BP-Media JS Unit Test Platform
 *
 * @version 3.1
 * @since 0.1
 * @package Razor
 * @subpackage JavaScript
 * @license GPL v2.0
 * @link http://code.google.com/p/wp-razor/
 *
 * ========================================================================================================
 */

class RAZ_test_js_core {
    


	function RAZ_test_js_core(){

		require_once(BPM_PATH_TEST . '/testcase/js/' . '/dictionary.php');

	}

	
	/**
         * Loads and enqueues the test platform JS libraries and mandatory mock classes
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
         * @return bool $result | False on failure. True on success.
         */

	public function loadTestPlatform() {

	}


	/**
         * Returns data for all available test groups
         *
         * @version 0.1.9
         * @since 0.1.9
         *
         * @return bool/array $result | False on failure. Array of test group data on success.
         */

	public function listTestGroups(&$error=null) {


		$cls = new BPM_test_js_dictionary();
		$groups = $cls->groups;

		$result = array();

		foreach( $groups as $group_slug => $group ){

			$tests = array();

			foreach( $group["tests"] as $test_slug => $test ){

				if($test["enable"] == true){

					$tests[] = array(
							"name"=>$test["name"],
							"slug"=>$test_slug,
							"desc"=>$test["desc"],
							"file"=>$test["file"]
					);
				}
			}
			unset($test_slug, $test);

			if( count($tests) > 0 ){

				$result[] = array(
						"name"=>$group["name"],
						"slug"=>$group_slug,
						"desc"=>$group["desc"],
						"tests"=>$tests
				);
			}

			unset($tests);
			
		}
		unset($group_slug, $group);

		return $result;

	}


}

?>