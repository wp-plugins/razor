=== Plugin Name ===
Contributors: foxly, fanquake, 2inov8, boonebgorges
Tags: buddypress, unit-test
Requires at least: 3.4
Tested up to: 3.4.1
Stable tag: 3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

State of the art cross-platform automated unit testing for WordPress plugins on Linux, Windows, and Mac OS ...from the BP-Media Team.

== Description ==

Razor is a powerful cross-platform unit test system that verifies the PHP and JavaScript code used in WordPress plugins works properly. It's the unit test system behind large plugins like <a href="http://wordpress.org/extend/plugins/buddypress/">BuddyPress</a> and <a href="http://code.google.com/p/buddypress-media/">BP-Media</a>.

To learn how to use Razor, please visit our <a href="https://code.google.com/p/wp-razor/">Developer Support Site</a>

<h4>It Just Works</h4>
Razor works on <u>any</u> modern desktop operating system, with <u>zero</u> user intervention. Our battle-hardened core runs reliably on even the most misconfigured of servers, automatically handling common problems. In the rare cases where Razor can't run, it provides useful debugging info.

<h4>Multi Platform</h4>
Razor runs on Linux, Windows, and Mac OS, letting you effortlessly move from your Windows desktop to your Mac notebook to your Linux production server, confident that your unit tests will run correctly. Razor's multi-platform support dramatically simplifies coordinating large development teams, because everyone on the team can use the same test platform and the same unit tests.

<h4>Zero Dependencies</h4>
Razor uses a specially modified fork of PHPUnit that don't need PEAR. This eliminates PHPUnit's legendary installation problems for Mac and Windows users. Zero dependencies also means you can check Razor into your team's version control system, confident that it will run on any machine.

<h4>Scalable</h4>
Razor meets the needs of both single developers and large development teams. Casual users can run tests in the WordPress backend, viewing results in their browser. Enterprise users can run tests in the terminal window, and can also use Razor as part of large-scale distributed test systems like <a href="http://travis-ci.org/">Travis-CI</a>.

<h4>Extremely Powerful</h4>
Razor lets developers script tests that were previously impossible to automate. Our powerful test core can install and activate plugins, load the entire database from an image file, and even check the code coverage of testcases. Razor's powerful command-line interface makes it easy to integrate with large automated test systems.


== Screenshots ==

1. **Terminal Window** - Razor running in the terminal window on Windows 7
2. **Debug Dump** - Using RAZ_debug::dump() to dump a variable to the browser 
2. **Debug Diff** - Using RAZ_debug::diff() to compare two arrays in the browser