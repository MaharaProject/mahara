<?php
/**
 *
 * @package    mahara
 * @subpackage skin
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2010-2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite/siteskins');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'siteskins');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'skin.php');
define('TITLE', get_string('siteskins', 'skin'));

if (!get_config('skins')) {
    throw new FeatureNotEnabledException();
}

$limit   = param_integer('limit', 6); // For 2x3 grid, showing thumbnails of view skins (2 rows with 3 thumbs each).
$offset  = param_integer('offset', 0);

$data = Skin::get_myskins_data($limit, $offset, 'site');

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'admin/site/skins.php',
    'count' => $data->count,
    'limit' => $limit,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('skin', 'skin'),
    'resultcounttextplural' => get_string('skins', 'skin')
));

$smarty = smarty();
$smarty->assign('skins', $data->data);
$smarty->assign('siteskins', true);
$smarty->assign('pagination', $pagination['html']);
$smarty->display('skin/index.tpl');
