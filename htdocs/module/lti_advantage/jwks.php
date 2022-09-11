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

define('INTERNAL', 1);
define('PUBLIC', 1);

require_once('lib/lti-1-3-php-library/lti/lti.php');
require_once('database.php');

use \IMSGlobal\LTI;

$keysetsdb = get_records_assoc('lti_advantage_key');
$keys = array();
foreach ($keysetsdb as $key) {
    $keys[$key->id] = $key->private_key;
}

LTI\JWKS_Endpoint::new($keys)->output_jwks();