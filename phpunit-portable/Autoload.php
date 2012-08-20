<?php

/**
 * PHPUNIT-PORTABLE
 * A modified version of Sebastian Bergmann's PHPUnit that doesn't require PEAR, doesn't require
 * installation, and can be included in a project's version control system.
 *
 * @version 3.1
 * @since 0.1
 * @author adapted from http://www.phpunit.de/
 * @author by the BP-Media team http://code.google.com/p/buddypress-media/
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @package PHPUnit
 * @subpackage PHPUnit-Portable
 * @link http://code.google.com/p/wp-razor/
 *
 * ========================================================================================================
 */

define( 'PHPU_BASE_PATH', dirname( __FILE__ ) );

require_once ( PHPU_BASE_PATH . '/Util/Filesystem.php' );
require_once ( PHPU_BASE_PATH . '/PHP/CodeCoverage/Filter.php' );

if (!function_exists('phpunit_autoload')) {

    function phpunit_autoload($class)
    {
        if (strpos($class, 'PHPUnit_') === 0) {

            $file = str_replace('_', '/', $class) . '.php';
	    $file = str_replace('PHPUnit', PHPU_BASE_PATH, $file);
	    
            $file = PHPUnit_Util_Filesystem::fileExistsInIncludePath($file);
	    
            if ($file) {
                require_once $file;
            }
        }
    }

    spl_autoload_register('phpunit_autoload');

    $dir    = dirname(__FILE__);
    $filter = PHP_CodeCoverage_Filter::getInstance();

    $filter->addDirectoryToBlacklist(
      $dir . '/Extensions', '.php', '', 'PHPUNIT', FALSE
    );

    $filter->addDirectoryToBlacklist(
      $dir . '/Framework', '.php', '', 'PHPUNIT', FALSE
    );

    $filter->addDirectoryToBlacklist(
      $dir . '/Runner', '.php', '', 'PHPUNIT', FALSE
    );

    $filter->addDirectoryToBlacklist(
      $dir . '/TextUI', '.php', '', 'PHPUNIT', FALSE
    );

    $filter->addDirectoryToBlacklist(
      $dir . '/Util', '.php', '', 'PHPUNIT', FALSE
    );

    $filter->addFileToBlacklist(__FILE__, 'PHPUNIT', FALSE);

    unset($dir, $filter);
}
