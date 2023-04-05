<?php

/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'share/sharedviews');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'revokemyaccess.php');
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
    'tagsonly' => get_string('tagsonly1', 'view'),
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
                    'class' => 'btn-secondary input-group-append no-label button',
                    'value' => get_string('search'),
                )
            ),
        ),

        'advanced' => array(
            'type'        => 'fieldset',
            'elements'    => array(
                'share' => array(
                    'type'         => 'checkboxes',
                    'class'        => 'stacked',
                    'elements'     => $shareoptions,
                    'labelwidth'   => 0,
                    'hideselectorbuttons' => true,
                ),
            ),
        ),
    )
));

// Institions: find if any have progress compeletion enabled.
// If no institution, institution is actually "mahara" and this is NOT stored in the usr_instition.
$completionvisible = 0;
$institutions = $USER->get('institutions');
if (empty($USER->get('institutions'))) {
    $institution = new Institution('mahara');
    $completionvisible = $institution->progresscompletion;
}
else {
    foreach ($institutions as $key => $institution) {
        $institution = new Institution($institution->institution);
        if ($completionvisible = $institution->progresscompletion) {
            break;
        }
    }
}
$data = View::shared_to_user(
    $query,
    $tag,
    $limit,
    $offset,
    $sort,
    $sortdir,
    $share,
    $USER->get('id')
);

$canremoveownaccess = false;
foreach ($data->data as $key => $item) {
    if ($completionvisible) { // Do any of the institutions the user has access to have progresscompletion?
        $ownername = $item['institution'] ? get_field('institution', 'displayname', 'name', $item['institution']) : '';
        $ownername = $item['group'] ? get_field('group', 'name', 'id', $item['group']) : $ownername;
        $ownername = $item['owner'] ? display_name($item['owner']) : $ownername;
        $data->data[$key]['progresspercentage'] = '<span class="icon icon-minus" title="' . hsc(get_string('progressnotavailable', 'collection', $item['title'], $ownername)) . '"></span>';
        $data->data[$key]['verification'] = '<span class="icon icon-minus" title="' . hsc(get_string('verifiednotavailable', 'collection', $item['title'], $ownername)) . '"></span>';
        if ($item['collid'] != null) {
            $collection = new Collection($item['collid']);
            if ($collection->can_have_progresscompletion()) {
                $progresspercentage = $collection->get_signed_off_and_verified_percentage();
                if ($progresspercentage !== false) {
                    $data->data[$key]['progresspercentage'] = $progresspercentage[0] . '%';
                }
            }
            if ($item['owner'] && $progressid = $collection->has_progresscompletion()) {
                $blockinstances = get_column('block_instance', 'id', 'view', $progressid, 'blocktype', 'verification');
                if ($blockinstances) {
                    $data->data[$key]['verification'] = 0;
                    foreach ($blockinstances as $subkey => $blockid) {
                        $blockinstance = new BlockInstance($blockid);
                        $configdata = $blockinstance->get('configdata');
                        if (!empty($configdata['primary'])) {
                            if (!empty($configdata['availabilitydate']) && $configdata['availabilitydate'] > time()) {
                                $data->data[$key]['verification'] = '<span class="icon icon-minus" title="' . hsc(get_string('verifiednotavailabledate', 'collection', $item['title'], $ownername, format_date($configdata['availabilitydate']))) . '"></span>';
                                break;
                            }
                            if (!empty($configdata['verified'])) {
                                $data->data[$key]['verification'] = 1;
                                break;
                            }
                            if (!empty($configdata['addcomment'])) {
                                if (record_exists('blocktype_verification_comment', 'instance', $blockid)) {
                                    $data->data[$key]['verification'] = 1;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    if (get_field('view_access', 'id', 'view', $data->data[$key]['viewid'], 'usr', $USER->id)) {
        $data->data[$key]['accessrevokable'] = true;
        $canremoveownaccess = true;
    }
}

$pagination = build_pagination(array(
    'id' => 'sharedviews_pagination',
    'url' => get_config('wwwroot') . 'view/sharedviews.php' . (!$queryparams ? '' : ('?' . http_build_query($queryparams))),
    'jsonscript' => 'json/sharedviews.php',
    'datatable' => 'sharedviewlist',
    'count' => $data->count,
    'limit' => $limit,
    'offset' => $offset,
    'setlimit' => true,
    'jumplinks' => 8,
    'numbersincludeprevnext' => 2,
));
//Make sure the user knows what they are removing.
$confirmationstr = '"' . get_string('revokemyaccessconfirm', 'collection') . '"';

$removeformjs = <<<EOF
    $('#revokemyaccess-form').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var viewid = button.data('viewid'); // Extract info from data-* attributes
        var title = button.data('title');
        var confirmationstr = $confirmationstr + title;
        var modal = $(this);
        $('#revokemyaccess-title').text(title);
        $('#revokemyaccess_form_submit').attr('data-confirm', confirmationstr);
        modal.find('[name=viewid]').val(viewid);
    });
EOF;

$revokemyaccessform = pieform(revokemyaccess_form());
$inlinejs = "jQuery(function() {" . $pagination['javascript'] . "});jQuery(function() {" . $removeformjs . "});";
$smarty = smarty(array('paginator'));
$percentagehelpicon = get_help_icon('core', 'view', 'sharedviews', 'completionpercentage');
$verificationhelpicon = get_help_icon('core', 'view', 'sharedviews', 'verification');
setpageicon($smarty, 'icon-square-share-nodes');
$smarty->assign('views', $data->data);
$smarty->assign('searchform', $searchform);
$smarty->assign('completionpercentagehelp', $percentagehelpicon);
$smarty->assign('verificationhelp', $verificationhelpicon);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('completionvisible', $completionvisible);
$smarty->assign('canremoveownaccess', $canremoveownaccess);
$smarty->assign('revokemyaccessform', $revokemyaccessform);
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
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
