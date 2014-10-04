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
require_once('pieforms/pieform.php');
define('TITLE', get_string('sharedwithme', 'view'));

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
$shareoptions['loggedin'] = get_string('loggedin', 'view');
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
    'class'          => 'search',
    'elements' => array(
        'query' => array(
            'type' => 'text',
            'title' => get_string('Query') . ': ',
            'class' => 'inline',
            'defaultvalue' => $searchdefault,
        ),
        'search' => array(
            'type'         => 'submit',
            'class'        => 'inline',
            'value'        => get_string('go')
        ),
        'advanced' => array(
            'type'        => 'fieldset',
            'legend'      => get_string('moreoptions', 'view'),
            'class'       => 'advanced',
            'collapsible' => true,
            'collapsed'   => true,
            'elements'    => array(
                'type' => array(
                    'type'         => 'select',
                    'title'        => get_string('searchwithin') . ': ',
                    'options'      => $searchoptions,
                    'defaultvalue' => $searchtype,
                ),
                'sort' => array(
                    'type'         => 'select',
                    'title'        => get_string('sortresultsby') . ' ',
                    'options'      => $sortoptions,
                    'defaultvalue' => $sort,
                ),
                'share' => array(
                    'type'         => 'checkboxes',
                    'title'        => get_string('sharedwith', 'view') . ': ',
                    'elements'     => $shareoptions,
                    'labelwidth'   => 0,
                ),
            ),
        ),
    )
));

$data = View::shared_to_user($query, $tag, $limit, $offset, $sort, $sortdir, $share);

$pagination = build_pagination(array(
    'id' => 'sharedviews_pagination',
    'url' => get_config('wwwroot') . 'view/sharedviews.php' . (empty($queryparams) ? '' : ('?' . http_build_query($queryparams))),
    'jsonscript' => '/json/sharedviews.php',
    'datatable' => 'sharedviewlist',
    'count' => $data->count,
    'limit' => $limit,
    'offset' => $offset,
));

$smarty = smarty(array('paginator'));
$smarty->assign('views', $data->data);
$smarty->assign('searchform', $searchform);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('INLINEJAVASCRIPT', 'addLoadEvent(function() {' . $pagination['javascript'] . '});');
$smarty->assign('PAGEHEADING', TITLE);
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
