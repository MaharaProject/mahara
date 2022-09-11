<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

json_headers();

$setting = param_alphanum('setting');
// Unset various statistic report page filters
// Don't pass in the session option directly for safety
if (isset($_SESSION['usersforstats']) && $setting == 'removeuserfilter') {
    $SESSION->set('usersforstats', null);
}
if (isset($_SESSION['portfoliofilter']) && $setting == 'removeportfoliofilter') {
    $SESSION->set('portfoliofilter', null);
}

json_reply(false, array('data' => 'success'));
