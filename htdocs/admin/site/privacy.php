<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite/privacy');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'privacy');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('privacy', 'admin'));

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

$data = get_records_sql_assoc("
    SELECT  s.id, s.version, u.firstname, u.lastname, u.id AS userid, s.content, s.ctime
      FROM {site_content_version} s
 LEFT JOIN {usr} u ON s.author = u.id");

krsort($data);

$smarty = smarty();
setpageicon($smarty, 'icon-umbrella');

$smarty->assign('results', $data);
$smarty->display('admin/site/privacy.tpl');
