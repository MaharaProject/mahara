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
define('MENUITEM', 'myportfolio/skins');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'skin');
define('SECTION_PAGE', 'index');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('skin.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('myskins', 'skin'));

if (!can_use_skins()) {
    throw new FeatureNotEnabledException();
}

$filter = param_alpha('filter', 'all');
$limit  = param_integer('limit', 6); // For 2x3 grid, showing thumbnails of view skins (2 rows with 3 thumbs each).
$offset = param_integer('offset', 0);
$metadata = param_integer('metadata', null);
$id = param_integer('id', null);

$data = Skin::get_myskins_data($limit, $offset, $filter);

$form = pieform(array(
    'name'   => 'filter',
    'method' => 'post',
    'renderer' => 'oneline',
    'elements' => array(
        'options' => array(
            'title' => get_string('filter'),
            'hiddenlabel' => true,
            'type' => 'select',
            'options' => array(
                'all'     => get_string('allskins', 'skin'),
                'site'     => get_string('siteskins', 'skin'),
                'user'  => get_string('userskins', 'skin'),
                'public'  => get_string('publicskins', 'skin'),
            ),
            'defaultvalue' => $filter
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('filter')
        )
    ),
));

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'skin/index.php?filter=' . $filter,
    'count' => $data->count,
    'limit' => $limit,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('skin', 'skin'),
    'resultcounttextplural' => get_string('skins', 'skin'),
));

$css = array(
    '<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/raw/static/style/skin.css">',
);

$inlinejs = <<<EOF
    function toggle_metadata(el) {
        jQuery('#overlay').remove();
        var meta = el.closest('.skin-controls').find('.skin-metadata');
        if (meta.hasClass('hidden')) {
            // need to display 'popup' box
            meta.removeClass('hidden');
            meta.addClass('skin_metadata_overlay');
            meta.addClass('metadata_block');
            getViewport = function() {
                var viewport = jQuery(window);
                return {
                    l: viewport.scrollLeft(),
                    t: viewport.scrollTop(),
                    w: viewport.width(),
                    h: viewport.height()
                }
            }
            var scrolltop = (((getViewport().h / 2) - 100) > 0) ? (getViewport().h / 2) - 100 : 0;
            meta.css('left', '30%');
            meta.css('top', (getViewport().t + scrolltop));
            jQuery(document.body).append('<div id="overlay"></div>');
            meta.find('.metadataclose').focus();
        }
        else {
            // need to hide 'popup' box
            meta.addClass('hidden');
            el.focus();
        }
    }

    jQuery(function() {
        // wire up the buttons to toggle the popup information on/off
        jQuery('a.btn-big-info').each(function() {
            jQuery(this).click(function(i) {
                toggle_metadata(jQuery(this));
                return false;
            });
        });
        jQuery('.metadataclose').click(function(e) {
            toggle_metadata(jQuery(this).closest('.skin-controls').find('.btn-big-info'));
            return false;
        });
    });
EOF;
$smarty = smarty(array(), $css, array(), array());
$smarty->assign('skins', $data->data);
$smarty->assign('user', $USER->get('id'));
$smarty->assign('form', $form);
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('id', $id);
$smarty->assign('metadata', $metadata);
$smarty->assign('filter', $filter);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('skin/index.tpl');

function filter_submit(Pieform $form, $values) {
    redirect('/skin/index.php?filter=' . $values['options']);
}
