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
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'skin.php');

if (!get_config('skins')) {
     json_reply('local', get_string('accessdenied', 'error'));
}

$fontpreview  = !is_null($SESSION->get('fontpreview')) ? $SESSION->get('fontpreview') : 21;
$fontsize     = !is_null($SESSION->get('fontsize')) ? $SESSION->get('fontsize') : 28;
$fonttype     = !is_null($SESSION->get('fonttype')) ? $SESSION->get('fonttype') : 'all'; // possible values: all, site, google
$setlimit = param_boolean('setlimit', false);
$limit   = param_integer('limit', 10);
$offset  = param_integer('offset', 0);
$query   = param_integer('query', null);

$data = Skin::get_sitefonts_data($limit, $offset, $fonttype);
$sitefonts = '';
$googlefonts = '';
foreach ($data->data as $font) {
    if ($font['fonttype'] == 'site') {
        $sitefonts .= $font['title'] . '|';
    }
    if ($font['fonttype'] == 'google') {
        $googlefonts .= urlencode($font['title']) . '|';
    }
}
$sitefonts = rtrim($sitefonts, '|');
$googlefonts = rtrim($googlefonts, '|');

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'admin/site/fonts.php',
    'count' => $data->count,
    'limit' => $limit,
    'offset' => $offset,
    'setlimit' => true,
    'datatable' => 'fontlist',
    'jsonscript' => 'admin/site/fonts.json.php',
    'resultcounttextsingular' => get_string('font', 'skin'),
    'resultcounttextplural' => get_string('fonts', 'skin')
));

$smarty = smarty_core();
$smarty->assign('query', $query);
$smarty->assign('sitefonts', $data->data);
$smarty->assign('preview', $fontpreview); // Transfer $SESSION value into template
$smarty->assign('size', $fontsize);       // Transfer $SESSION value into template
$html = $smarty->fetch('skin/sitefontresults.tpl');

json_reply(false, array(
    'message' => null,
    'data' => array(
        'tablerows' => $html,
        'pagination' => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
        'count' => $data->count,
        'results' => $data->count . ' ' . ($data->count == 1 ? get_string('result') : get_string('results')),
        'offset' => $offset,
        'setlimit' => $setlimit,
    )
));