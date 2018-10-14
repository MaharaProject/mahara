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
define('TITLE', get_string('legal'));

$sections = array();

//termsandconditions
$termstitle = get_string('termsandconditions');
$termscontent = get_field_sql("
    SELECT s.content
    FROM {site_content_version} s
    WHERE s.institution = ?
    AND s.type = 'termsandconditions'
    ORDER BY s.id DESC
    LIMIT 1", array('mahara'));

$sections[] = array(
    'title' => $termstitle,
    'content' => $termscontent,
);

// privacystatement
$privacytitle = get_string('privacystatement');
$privacycontent = get_field_sql("
    SELECT s.content
    FROM {site_content_version} s
    WHERE s.institution = ?
    AND s.type = 'privacy'
    ORDER BY s.id DESC
    LIMIT 1", array('mahara'));

$sections[] = array(
    'title' => $privacytitle,
    'content' => $privacycontent,
);

$smarty = smarty();
setpageicon($smarty, 'icon-umbrella');
$smarty->assign('sections', $sections);
$smarty->display('sitepagesections.tpl');
