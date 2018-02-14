<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('JSON', 1);
define('NOSESSKEY', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
$institution = param_alphanum('institution', null);


// Get the institution privacy statement.
$privacy = get_latest_privacy_versions(array($institution));

json_headers();
print json_encode($privacy);
