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
define('MENUITEM', 'myportfolio/sharedviews');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
define('TITLE', get_string('sharedwithme', 'view'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'sharedviews');

$query  = param_variable('query', null);
$tag    = param_variable('tag', null);
$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$queryparams = array();

$searchoptions = array(
    'titleanddescriptionanduser' => get_string('titleanddescriptionandtagsandowner', 'view'),
    'tagsonly' => get_string('tagsonly', 'view'),
);
if (!empty($tag)) {
    $searchtype = 'tagsonly';
    $searchdefault = $tag;
    $queryparams['tag'] = $tag;
    $query = null;
}
else {
    $searchtype = 'titleanddescriptionanduser';
    $searchdefault = $query;
    if ($query != '') {
        $queryparams['query'] = $query;
    }
}

$sortoptions = array(
    'lastchanged' => get_string('lastupdateorcomment'),
    'mtime'       => get_string('lastupdate'),
    'ownername'   => get_string('Owner', 'view'),
    'title'       => get_string('Title'),
);

if (!in_array($sort = param_alpha('sort', 'lastchanged'), array_keys($sortoptions))) {
    $sort = 'lastchanged';
}
if ($sort !== 'lastchanged') {
    $queryparams['sort'] = $sort;
}
$sortdir = ($sort == 'lastchanged' || $sort == 'mtime') ? 'desc' : 'asc';

$share = $queryparams['share'] = $sharedefault = array('user', 'friend', 'group');

$shareoptions = array(
    'user'        => get_string('Me', 'view'),
    'friend'      => get_string('friends', 'view'),
    'group'       => get_string('mygroups'),
);
if ($USER->get('institutions')) {
    $shareoptions['institution'] = get_string('myinstitutions', 'group');
}
$shareoptions['loggedin'] = get_string('registeredusers', 'view');
if (get_config('allowpublicviews')) {
    $shareoptions['public'] = get_string('public', 'view');
}

foreach ($shareoptions as $k => &$v) {
    $v = array('title' => $v, 'value' => $k, 'defaultvalue' => in_array($k, $sharedefault));
}

$searchform = pieform(array(
    'name' => 'search',
    'checkdirtychange' => false,
    'dieaftersubmit' => false,
    'renderer'       => 'div',
    'class'          => 'search with-heading form-inline admin-user-search',
    'elements' => array(
        'searchwithin' => array(
            'type' => 'fieldset',
            'class' => 'dropdown-group js-dropdown-group',
            'elements' => array(
                'query' => array(
                    'title' => get_string('search') . ': ',
                    'hiddenlabel' => false,
                    'type'  => 'text',
                    'class' => 'with-dropdown js-with-dropdown'
                ),
                'type' => array(
                    'class' => 'dropdown-connect js-dropdown-connect',
                    'type'         => 'select',
                    'title'        => get_string('searchwithin') . ': ',
                    'options'      => $searchoptions,
                    'defaultvalue' => $searchtype,
                )
            )
        ),

        'inputgroupsort' => array(
            'type'  => 'fieldset',
            'title' => get_string('Query') . ': ',
            'class' => 'input-group',
            'elements'     => array(
                'sort' => array(
                    'class' => 'input-small',
                    'type'         => 'select',
                    'title'        => get_string('sortresultsby') . ' ',
                    'options'      => $sortoptions,
                    'defaultvalue' => $sort,
                ),
               'submit' => array(
                    'type'  => 'button',
                    'usebuttontag' => true,
                    'class' => 'btn-primary input-group-btn no-label button',
                    'value' => get_string('search'),
                )
            ),
        ),

        'advanced' => array(
            'type'        => 'fieldset',
            'legend'      => get_string('moreoptions', 'view'),
            'class'       => 'advanced last as-link link-expand-right',
            'collapsible' => true,
            'collapsed'   => true,
            'elements'    => array(
                'share' => array(
                    'class' => 'fullwidth',
                    'type'         => 'checkboxes',
                    'class'        => 'stacked',
                    'title'        => get_string('sharedwith', 'view') . ': ',
                    'elements'     => $shareoptions,
                    'labelwidth'   => 0,
                ),
            ),
        ),
    )
));

$data = View::shared_to_user($query, $tag, $limit, $offset, $sort, $sortdir,
                             $share, $USER->get('id'));

$pagination = build_pagination(array(
    'id' => 'sharedviews_pagination',
    'url' => get_config('wwwroot') . 'view/sharedviews.php' . (empty($queryparams) ? '' : ('?' . http_build_query($queryparams))),
    'jsonscript' => 'json/sharedviews.php',
    'datatable' => 'sharedviewlist',
    'count' => $data->count,
    'limit' => $limit,
    'offset' => $offset,
    'setlimit' => true,
    'jumplinks' => 8,
    'numbersincludeprevnext' => 2,
));

$smarty = smarty(array('paginator'));
$smarty->assign('views', $data->data);
$smarty->assign('searchform', $searchform);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('INLINEJAVASCRIPT', 'addLoadEvent(function() {' . $pagination['javascript'] . '});');
$smarty->display('view/sharedviews.tpl');
exit;

function search_submit(Pieform $form, $values) {
    // Convert (query,type) parameters from form to (query,tag)
    global $queryparams, $tag, $query, $share;

    if (isset($queryparams['query'])) {
        unset($queryparams['query']);
        $query = null;
    }

    if (isset($queryparams['tag'])) {
        unset($queryparams['tag']);
        $tag = null;
    }

    if ((isset($values['query']) && ($values['query'] != ''))) {
        if ($values['type'] == 'tagsonly') {
            $queryparams['tag'] = $tag = $values['query'];
        }
        else {
            $queryparams['query'] = $query = $values['query'];
        }
    }

    $share = $queryparams['share'] = param_variable('share', array());
}
