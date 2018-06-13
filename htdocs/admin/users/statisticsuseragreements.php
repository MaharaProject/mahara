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
define('ADMIN', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'legal');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$userid = param_integer('userid', null);
$versionid = param_integer('versionid', null);
$activetab = param_alpha('activetab', null);

$user = get_record('usr', 'id', $userid);
define('SUBSECTIONHEADING', get_string('legalconsent', 'admin'));
define('TITLE', display_name($user, null, true));

$usercontent = array();
if ($userid) {
    $usercontent = get_records_sql_array("
        SELECT s.version, ua.ctime AS agreeddate, s.type, s.content, s.institution, s.id, i.displayname, s2.current, ua.agreed
        FROM {usr_agreement} ua
        JOIN {site_content_version} s ON s.id = ua.sitecontentid
        JOIN {institution} i ON i.name = s.institution
        LEFT JOIN (
            SELECT MAX(id) AS current, type, institution
            FROM {site_content_version}
            GROUP BY type, institution) s2 ON s.type = s2.type AND s.id = s2.current AND s.institution = s2.institution
        WHERE ua.usr = ?
        ORDER BY ua.id DESC", array($userid));
}
$link = get_config('wwwroot') . 'admin/users/statisticsuseragreements.php?userid=' . $userid;

$js = <<< EOF
$(function() {
    checkActiveTab('$activetab');
})
EOF;

$smarty = smarty(array('privacy'));
$smarty->assign('id', $userid);
$smarty->assign('usercontent', $usercontent);
$smarty->assign('versionid', $versionid);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('link', $link);
$smarty->assign('activetab', $activetab);
$smarty->display('admin/users/statisticsuseragreements.tpl');
