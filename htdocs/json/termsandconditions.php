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

$institution = param_alphanum('institution', 'mahara');
if (empty($institution)) { // if '0' is passed via the form we display the default terms and conditions
    $institution = 'mahara';
}
$useterms = false;
if ($institution != 'mahara') {
    // try to fetch institution's terms and conditions
    $useterms = get_record_sql("SELECT sc.content FROM {site_content} sc
                                LEFT JOIN {institution_config} ic ON ic.value = sc.institution
                                WHERE ic.field = ?
                                AND sc.name = ?
                                AND ic.institution = ?",
                               array('sitepages_termsandconditions', 'termsandconditions', $institution));
}
if (empty($useterms)) {
    // get the default terms and conditions
    $useterms = get_record_sql("SELECT content FROM {site_content}
                               WHERE institution = ?
                               AND name = ?", array($institution, 'termsandconditions'));
}
json_headers();
print json_encode($useterms);
