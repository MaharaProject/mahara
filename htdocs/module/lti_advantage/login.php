<?php
/**
 *
 * @package    mahara
 * @subpackage module.lti_advantage
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Script cannot be called directly.
 *
 * @var int
 */
define('INTERNAL', 1);

/**
 * Can be accessed while not logged in.
 */
define('PUBLIC', 1);

require_once('lib/lti-1-3-php-library/lti/lti.php');
require_once('database.php');
$lti_db = new LTI_Advantage_Database();

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');

// Check if we need to set the csp-ancestor-exemption.
if (!empty($_REQUEST['client_id']) && empty($SESSION->get('csp-ancestor-exemption'))) {
    $issuer = $lti_db->find_issuer_by_client_id($_REQUEST['client_id']);
    $parts = parse_url($issuer);
    if (!empty($parts['scheme']) && !empty($parts['host'])) {
        $cspurl = $parts['scheme'] . '://' . $parts['host'];
        // Update the headers but don't update the SESSION yet. Do that in the
        // call to home.php after authentication has been done.
        update_csp_headers($cspurl);
    }
}

use \IMSGlobal\LTI;

$lti_cache = new LTI\Cache();
$lti_cache->set_cache_dir($CFG->dataroot . '/temp');

LTI\LTI_OIDC_Login::new($lti_db, $lti_cache)
    ->do_oidc_login_redirect(TOOL_HOST . '/module/lti_advantage/home.php')
    ->do_redirect();
