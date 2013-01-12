<?php
/**
 * RAZOR - WORDPRESS PLUGIN CONTROL CLASS
 * Allows razor to activate, deactivate, and set transient info for WordPress plugins 
 *
 * @version 3.2
 * @since 0.1
 * @package Razor
 * @subpackage Utils
 * @license GPL v2.0
 * @link http://code.google.com/p/wp-razor/
 *
 * ========================================================================================================
 */

class RAZ_pluginManager {
        
    
	var $db_object;				    // The class' database object
	
	
	// ============================================================================================================ //
	
	
	public function __construct() {
	    
		global $razor;
		$this->db_object = $razor->db_object;
	}
    
	
	/**
         * Activates a plugin on a single-site install, or the root blog on a multi-blog network if
	 * $blog_id is not set. If $blog_id is set, activates a plugin on the supplied $blog_id on
	 * a multi-blog network. Note that this function does NOT fire the WP plugin activation actions
	 * because its designed to be run *before* the WP core loads. 
         *
	 * @version 3.2
	 * @since 0.1
	 * @param string $folder | name of the plugin's folder within the site plugins folder
	 * @param string $loader | name of the plugin's loader file	
	 * @param int $blog_id | blog_id to use if activating plugin on a multiblog network	  
         * @return bool | Exception on failure. True on success
         */
    
	public function activatePlugin($folder, $loader, $blog_id=null) {
		    
		global $razor;
		
		$db_prefix = $razor->db_prefix;		
		$blog_prefix = '';
		
		if($blog_id !== null){
		    
			$blog_prefix = $blog_id . '_';
		}

		// STAGE 1 - Active Plugins
		// =================================================================
		
		$sql = "SELECT option_value FROM " . $db_prefix . $blog_prefix . "options WHERE option_name = 'active_plugins'";
		$result = $this->db_object->get_var($sql);

		if($result){

			$active_plugins = unserialize($result);		
			$key = array_search($folder . "/" . $loader, $active_plugins);

			if($key === false){			   
			    
				$active_plugins[] = $folder . "/" . $loader;				
				$active_plugins = serialize($active_plugins);

				$sql = "UPDATE " . $db_prefix . $blog_prefix . "options SET option_value = '$active_plugins' WHERE option_name = 'active_plugins'";
				$rows_changed = $this->db_object->query($sql);

				if(!$rows_changed){				    
				    
					$error = array(
						'numeric'=>1,
						'text'=>"Couldn't write updated row to database",
						'data'=>array('folder'=>$folder, 'loader'=>$loader, 'sql'=>$sql),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					);
					
					throw new RAZ_exception($error);
				}				
			}									

		}
		else {
			$error = array(
				'numeric'=>2,
				'text'=>"'active_plugins' key doesn't exist in the database. Has WP changed their data model?",
				'data'=>array('folder'=>$folder, 'loader'=>$loader, 'sql'=>$sql),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			);
			
			throw new RAZ_exception($error);
		}
		
		
		// STAGE 2 - Transient Plugin Slugs
		// =================================================================
		
		$sql = "SELECT option_value FROM " . $db_prefix . $blog_prefix . "options WHERE option_name = '_transient_plugin_slugs'";
		$result = $this->db_object->get_var($sql);

		if($result){

			$transient_plugin_slugs = unserialize($result);		
			$key = array_search($folder . "/" . $loader, $transient_plugin_slugs);

			if($key === false){			   
			    
				$transient_plugin_slugs[] = $folder . "/" . $loader;				
				$transient_plugin_slugs = serialize($transient_plugin_slugs);

				$sql = "UPDATE " . $db_prefix . $blog_prefix . "options SET option_value = '$transient_plugin_slugs' WHERE option_name = '_transient_plugin_slugs'";
				$rows_changed = $this->db_object->query($sql);

				if(!$rows_changed){				    
				    
					$error = array(
						'numeric'=>3,
						'text'=>"Couldn't write updated row to database",
						'data'=>array('folder'=>$folder, 'loader'=>$loader, 'sql'=>$sql),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					);
					
					throw new RAZ_exception($error);
				}				
			}									

		}
		else {
			$error = array(
				'numeric'=>4,
				'text'=>"'_transient_plugin_slugs' key doesn't exist in the database. Has WP changed their data model?",
				'data'=>array('folder'=>$folder, 'loader'=>$loader, 'sql'=>$sql),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			);
			
			throw new RAZ_exception($error);
		}
		
		
		// STAGE 3 - Site Transient Update Plugins
		// =================================================================		
		// Note this is a *sitewide* option so its only ever on the root blog
		
		if( !defined('MULTISITE') || (MULTISITE == false) ) {
		    
			$sql = "SELECT option_value FROM " . $db_prefix . "options WHERE option_name = '_site_transient_update_plugins'";
			$result = $this->db_object->get_var($sql);

			if($result){

				$st_update_plugins = unserialize($result);
				$checked = $st_update_plugins->checked;

				$key = array_search($folder . "/" . $loader, $checked);

				if($key === false){

					$checked[] = $folder . "/" . $loader;
					$st_update_plugins->checked = $checked;
					$st_update_plugins = serialize($st_update_plugins);

					$sql = "UPDATE " . $db_prefix . "options SET option_value = '$st_update_plugins' WHERE option_name = '_site_transient_update_plugins'";
					$rows_changed = $this->db_object->query($sql);

					if(!$rows_changed){

						$error = array(
							'numeric'=>5,
							'text'=>"Couldn't write updated row to database",
							'data'=>array('folder'=>$folder, 'loader'=>$loader, 'sql'=>$sql),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>null
						);
						
						throw new RAZ_exception($error);
					}

				}

			}
			else {
				$error = array(
					'numeric'=>6,
					'text'=>"'_site_transient_update_plugins' key doesn't exist in the database. Has WP changed their data model?",
					'data'=>array('folder'=>$folder, 'loader'=>$loader, 'sql'=>$sql),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				);
				
				throw new RAZ_exception($error);
			}
		
		}
		
		
		return true;		
				
	}
	
		
	/**
         * Deactivates a plugin on a single-site install, or the root blog on a multi-blog network if
	 * $blog_id is not set. If $blog_id is set, activates a plugin on the supplied $blog_id on
	 * a multi-blog network. Note that this function does NOT fire the WP plugin deactivation actions
	 * because its designed to be run *before* the WP core loads. 
         *
	 * @version 3.2
	 * @since 0.1
	 * @param string $folder | name of the plugin's folder within the site plugins folder
	 * @param string $loader | name of the plugin's loader file
	 * @param int $blog_id | blog_id to use if activating plugin on a multiblog network		 	 
         * @return bool | Exception on failure. True on success
         */
	
	public function deactivatePlugin($folder, $loader, $blog_id=null) {
	
		global $razor;
		
		$db_prefix = $razor->db_prefix;		
		$blog_prefix = '';
		
		if($blog_id !== null){
		    
			$blog_prefix = $blog_id . '_';
		}

		// STAGE 1 - Active Plugins
		// =================================================================
		
		$sql = "SELECT option_value FROM " . $db_prefix . $blog_prefix . "options WHERE option_name = 'active_plugins'";
		$result = $this->db_object->get_var($sql);

		if($result){

			$active_plugins = unserialize($result);		
			$key = array_search($folder . "/" . $loader, $active_plugins);

			if($key !== false){			   
			    
				unset($active_plugins[$key]);				
				$active_plugins = serialize($active_plugins);

				$sql = "UPDATE " . $db_prefix . $blog_prefix . "options SET option_value = '$active_plugins' WHERE option_name = 'active_plugins'";
				$rows_changed = $this->db_object->query($sql);

				if(!$rows_changed){				    
				    
					$error = array(
						'numeric'=>1,
						'text'=>"Couldn't write updated row to database",
						'data'=>array('folder'=>$folder, 'loader'=>$loader, 'sql'=>$sql),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					);
					
					throw new RAZ_exception($error);
				}				
			}									

		}
		else {
			$error = array(
				'numeric'=>2,
				'text'=>"'active_plugins' key doesn't exist in the database. Has WP changed their data model?",
				'data'=>array('folder'=>$folder, 'loader'=>$loader, 'sql'=>$sql),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			);
			
			throw new RAZ_exception($error);
		}
		
		
		// STAGE 2 - Transient Plugin Slugs
		// =================================================================
		
		$sql = "SELECT option_value FROM " . $db_prefix . $blog_prefix . "options WHERE option_name = '_transient_plugin_slugs'";
		$result = $this->db_object->get_var($sql);

		if($result){

			$transient_plugin_slugs = unserialize($result);		
			$key = array_search($folder . "/" . $loader, $transient_plugin_slugs);

			if($key !== false){			   
			    
				unset($transient_plugin_slugs[$key]);				
				$transient_plugin_slugs = serialize($transient_plugin_slugs);

				$sql = "UPDATE " . $db_prefix . $blog_prefix . "options SET option_value = '$transient_plugin_slugs' WHERE option_name = '_transient_plugin_slugs'";
				$rows_changed = $this->db_object->query($sql);

				if(!$rows_changed){				    
				    
					$error = array(
						'numeric'=>3,
						'text'=>"Couldn't write updated row to database",
						'data'=>array('folder'=>$folder, 'loader'=>$loader, 'sql'=>$sql),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					);
					
					throw new RAZ_exception($error);
				}				
			}									

		}
		else {
			$error = array(
				'numeric'=>4,
				'text'=>"'_transient_plugin_slugs' key doesn't exist in the database. Has WP changed their data model?",
				'data'=>array('folder'=>$folder, 'loader'=>$loader, 'sql'=>$sql),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			);
			
			throw new RAZ_exception($error);
		}
		
		
		// STAGE 3 - Site Transient Update Plugins
		// =================================================================
		// Note this is a *sitewide* option so its only ever on the root blog
		
		if( !defined('MULTISITE') || (MULTISITE == false) ) {
		    
			// Note this is a *sitewide* option so its only ever ont he root blog
			$sql = "SELECT option_value FROM " . $db_prefix . "options WHERE option_name = '_site_transient_update_plugins'";
			$result = $this->db_object->get_var($sql);

			if($result){

				$st_update_plugins = unserialize($result);
				$checked = $st_update_plugins->checked;

				$key = array_search($folder . "/" . $loader, $checked);

				if($key === false){

					$checked[] = $folder . "/" . $loader;
					$st_update_plugins->checked = $checked;
					$st_update_plugins = serialize($st_update_plugins);

					$sql = "UPDATE " . $db_prefix . "options SET option_value = '$st_update_plugins' WHERE option_name = '_site_transient_update_plugins'";
					$rows_changed = $this->db_object->query($sql);

					if(!$rows_changed){

						$error = array(
							'numeric'=>5,
							'text'=>"Couldn't write updated row to database",
							'data'=>array('folder'=>$folder, 'loader'=>$loader, 'sql'=>$sql),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>null
						);
						
						throw new RAZ_exception($error);
					}

				}

			}
			else {
				$error = array(
					'numeric'=>6,
					'text'=>"'_site_transient_update_plugins' key doesn't exist in the database. Has WP changed their data model?",
					'data'=>array('folder'=>$folder, 'loader'=>$loader, 'sql'=>$sql),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				);
				
				throw new RAZ_exception($error);
			}
		
		}
				
		return true;			
				
	}
			
	
	/**
         * Activates a plugin sitewide on a multi-blog network but does not activate it on individual
	 * blogs. You'll typically need to call activatePlugin on the root blog during a test run. Note 
	 * that this function does NOT fire the WP plugin activation actions because its designed to be 
	 * run *before* the WP core loads. 
         *
	 * @version 3.2
	 * @since 0.1
	 * @param string $folder | name of the plugin's folder within the site plugins folder
	 * @param string $loader | name of the plugin's loader file	 
         * @return bool | Exception on failure. True on success
         */
	
	public function siteWide_activatePlugin($folder, $loader) {
	
		global $razor;
		
		$db_prefix = $razor->db_prefix;	
		
		$sql = "SELECT option_value FROM " . $db_prefix . "options WHERE option_name = 'active_sitewide_plugins'";
		$result = $this->db_object->get_var($sql);

		if($result){

			$active_plugins = unserialize($result);		
			$key = array_search($folder . "/" . $loader, $active_plugins);

			if($key !== false){
			    
				// If the plugin's key exists, its already activated
				return true;
			}
			else {
			    
				$active_plugins[] = $folder . "/" . $loader;				
				$active_plugins = serialize($active_plugins);

				$sql = "UPDATE " . $db_prefix . "options SET option_value = '$active_plugins' WHERE option_name = 'active_sitewide_plugins'";
				$rows_changed = $this->db_object->query($sql);

				if($rows_changed){
				    
					return true;
				}
				else {
				    
					$error = array(
						'numeric'=>1,
						'text'=>"Couldn't write updated row to database",
						'data'=>array('folder'=>$folder, 'loader'=>$loader, 'rows_changed'=>$rows_changed),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					);
					
					throw new RAZ_exception($error);
				}				
			}									

		}
		else {

			// Sometimes the active_sitewide_plugins option isn't set 
			// on new sites. Add it.
			// ======================================================
		    
			$active_plugins = array();

			$active_plugins[] = $folder . "/" . $loader;				
			$active_plugins = serialize($active_plugins);

			$sql = "INSERT INTO " . $db_prefix . "options (option_name, option_value) VALUES ('active_sitewide_plugins', '" . $active_plugins . "')";
			$rows_changed = $this->db_object->query($sql);

			if($rows_changed){

				return true;
			}
			else {

				$error = array(
					'numeric'=>2,
					'text'=>"Couldn't write updated row to database",
					'data'=>array('folder'=>$folder, 'loader'=>$loader, 'rows_changed'=>$rows_changed),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				);
				
				throw new RAZ_exception($error);
			}		    
		    
		}
		
				
	}
		
	
	/**
         * Deactivates a plugin sitewide on a multi-blog network but does not deactivate it on individual
	 * blogs. Note that this function does NOT fire the WP plugin activation actions because its 
	 * designed to be run *before* the WP core loads. 
         *
	 * @version 3.2
	 * @since 0.1
	 * @param string $folder | name of the plugin's folder within the site plugins folder
	 * @param string $loader | name of the plugin's loader file	 
         * @return bool | Exception on failure. True on success
         */
	
	public function siteWide_deactivatePlugin($folder, $loader) {
	
		global $razor;
		
		$db_prefix = $razor->db_prefix;	
		
		$sql = "SELECT option_value FROM " . $db_prefix . "options WHERE option_name = 'active_sitewide_plugins'";
		$result = $this->db_object->get_var($sql);

		if($result){

			$active_plugins = unserialize($result);		
			$key = array_search($folder . "/" . $loader, $active_plugins);

			if($key == false){
			    
				// If the plugin's key doesn't exist, its not activated
				return true;
			}
			else {
			    
				unset( $active_plugins[$key] );				
				$active_plugins = serialize($active_plugins);

				$sql = "UPDATE " . $db_prefix . "options SET option_value = '$active_plugins' WHERE option_name = 'active_sitewide_plugins'";
				$rows_changed = $this->db_object->query($sql);

				if($rows_changed){
				    
					return true;
				}
				else {
				    
					$error = array(
						'numeric'=>1,
						'text'=>"Couldn't write updated row to database",
						'data'=>array('folder'=>$folder, 'loader'=>$loader, 'rows_changed'=>$rows_changed),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					);
					
					throw new RAZ_exception($error);
				}				
			}									

		}
		else {
			$error = array(
				'numeric'=>2,
				'text'=>"'active_sitewide_plugins' key doesn't exist in the database. Has WP changed their data model?",
				'data'=>array('folder'=>$folder, 'loader'=>$loader, 'sql'=>$sql),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			);
			
			throw new RAZ_exception($error);
		}
		
				
	}
	
    
}


?>