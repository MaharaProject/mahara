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
require('init.php');
define('TITLE', get_string('privacystatement'));

$privacycontent = get_field_sql("
    SELECT s.content
    FROM {site_content_version} s
    WHERE s.institution = ?
    AND s.type = 'privacy'
    ORDER BY s.version DESC
    LIMIT 1", array('mahara'));
$smarty = smarty();
$smarty->assign('page_content', $privacycontent);
$smarty->display('sitepage.tpl');
