<?php
/**
 *
 * @package    mahara
 * @subpackage module.lti_advantage
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);

require_once('lib/lti-1-3-php-library/lti/lti.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('database.php');

use \IMSGlobal\LTI;

LTI\LTI_OIDC_Login::new(new LTI_Advantage_Database())
    ->do_oidc_login_redirect(TOOL_HOST . '/module/lti_advantage/home.php')
    ->do_redirect();
