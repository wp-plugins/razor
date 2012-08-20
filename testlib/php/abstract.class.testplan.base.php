<?php
/**
 * RAZOR TESTPLAN BASE CLASS
 *
 * Ensures scripts are run in a specific order, and allows scripts to be easily enabled and
 * disabled without having to move them between directories (which would cause huge SVN problems
 * if developers were editing and moving around scripts at the same time).
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

abstract class RAZ_testPlan_base {

    
	var $panels;		    // The plan's test panels array
	var $cases;		    // The plan's test cases array    

	// ============================================================================================================ //
		
	
	/**
         * Loads mock classes used by the unit test runners in this dictionary
         *
         * @version 0.1.9
         * @since 0.1.9
         */ 
    
	public function getMockClasses() {}
	
	
	/**
         * Fetches the requested database images and loads their remapper classes
         *
         * @version 0.1.9
         * @since 0.1.9
         *
	 * @param string/null $load_panel | Single panel name as string, or NULL
         * @return bool $result | False on failure. True on success.
         */

	public function getTestPanels($load_panel, &$error=null) {
	   	
		
		global $razor;
	    
		if( count($this->panels) < 1 ){

			$error = array(
				'numeric'=>1,
				'text'=>"Test panels array is empty",
				'data'=>$this->panels,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			);
			
			return false;
		}
		
		if($load_panel){
		    
			// If a panel name is specified, load it
		    
			if( $this->panels[$load_panel]['enable'] == true ){

				require_once( $razor->path_testplan . $this->panels[$load_panel]['file'] );
				
				return true;
				
			}
			elseif( $this->panels[$load_panel]['enable'] === false ){

				$error = array(
					'numeric'=>2,
					'text'=>"Requested panel is not enabled",
					'data'=>$load_panel,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				);
				
				return false;
			}			
			else {	
				
				$error = array(
					'numeric'=>3,
					'text'=>"Requested panel doesn't exist",
					'data'=>$load_panel,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				);	
				
				return false;
			}
										
		}		
		else {
			// Otherwise, load the first enabled panel
		    
			foreach( $this->panels as $panel){
			    
				if( $panel['enable'] == true ){

					require_once( $razor->path_testplan . $panel['file'] );
					
					return true;
				}
			}
			unset($panel);
			
			$error = array(
				'numeric'=>4,
				'text'=>"Requested panel doesn't exist",
				'data'=>$load_panel,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			);
			
			return false;
		    
		}				
		
	}
			
	
	/**
         * Loads requested test cases so the test core can run them
         *
         * @version 0.1.9
         * @since 0.1.9
         *
	 * @param string/array $load_groups | Single group of cases as string. Multiple groups as array of string.
         * @return bool/array $result | False on failure. Array of test names on success.
         */

	public function getTestCases($load_groups, &$error=null) {

		global $razor;
	    
		if( count($this->cases) < 1 ){

			$error = array(
				'numeric'=>5,
				'text'=>"Test cases array is empty",
				'data'=>$this->cases,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			);
			
			return false;
		}
		
		$case_count = 0;
		
		// Single case group passed as string
		// =================================================
		if( is_string($load_groups) ){

			foreach( $this->cases[$load_groups] as $case ){

				if( $case['enable'] == true ){

					require_once( $razor->path_testplan . $case['file'] );
					$case_count++;
				}

			}
			unset($case);				
		}

		// Multiple groups passed as array
		// =================================================
		elseif( is_array($load_groups) ){

			foreach( $load_groups as $group_name ){

				foreach( $this->cases[$group_name] as $case ){

					if( $case['enable'] == true ){

						require_once( $razor->path_testplan . $case['file'] );
						$case_count++;
					}

				}
				unset($case);

			}
			unset($group_name);			
		}

		// Load all case groups
		// =================================================
		else {

			foreach( $this->cases as $group ){

				foreach( $group as $case ){

					if( $case['enable'] == true ){

						require_once( $razor->path_testplan . $case['file'] );
					}

				}
				unset($case);

			}
			unset($group);			
		}
		
		if($case_count > 0){
		    
			return true;
		}
		else {
			$error = array(
				'numeric'=>6,
				'text'=>"All tests for requested test groups were disabled",
				'data'=>$load_groups,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			);
		}

	}

}

?>