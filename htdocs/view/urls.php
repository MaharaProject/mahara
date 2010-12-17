<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'urls');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');

$view = new View(param_integer('id'));
$collection = $view->get_collection();
$title = $collection ? $collection->get('name') : $view->get('title');

define('TITLE', get_string('secreturls', 'view') . ': ' . $title);

$group = $view->get('group');
$institution = $view->get('institution');
View::set_nav($group, $institution, true);

if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}

$newform = array(
    'name'     => 'newurl',
    'elements' => array(
        'submit' => array(
            'title'       => get_string('newsecreturl', 'view'),
            'description' => get_string('generatesecreturl', 'view', hsc($title)),
            'type'        => 'submit',
            'value'       => get_string('add'),
        ),
    ),
);

$editurls = array();

$allowcomments = $view->get('allowcomments');

$records = get_records_select_array(
    'view_access',
    'view = ? AND visible = 1 AND NOT token IS NULL',
    array($view->get('id')),
    'token'
);

if (!$records) {
    $records = array();
}

$tokens = array();

for ($i = 0; $i < count($records); $i++) {
    $r =& $records[$i];
    $tokens[$r->token] = $r->token;
    $elements = array(
        'token'     => array(
            'type'         => 'hidden',
            'value'        => $r->token,
        ),
        'startdate' => array(
            'type'         => 'calendar',
            'title'        => get_string('From') . ':',
            'defaultvalue' => !empty($r->startdate) ? strtotime($r->startdate) : null,
            'caloptions'   => array(
                'showsTime'      => true,
                'ifFormat'       => get_string('strftimedatetimeshort'),
            ),
        ),
        'stopdate'  => array(
            'type'         => 'calendar',
            'title'        => get_string('To') . ':',
            'defaultvalue' => !empty($r->stopdate) ? strtotime($r->stopdate) : null,
            'caloptions'   => array(
                'showsTime'      => true,
                'ifFormat'       => get_string('strftimedatetimeshort'),
            ),
        ),
    );
    if (!$allowcomments) {
        $elements['allowcomments'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('allowcomments', 'artefact.comment'),
            'defaultvalue' => $r->allowcomments,
        );
        $elements['approvecomments'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('moderatecomments', 'artefact.comment'),
            'defaultvalue' => $r->approvecomments,
        );
    }
    $elements['submit'] = array(
        'type'  => 'submit',
        'value' => get_string('save'),
    );
    $editurls[$i] = array(
        'id'         => $i,
        'url'        => get_config('wwwroot') . 'view/view.php?t=' . $r->token,
        'editform'   => pieform(array(
            'name'             => 'editurl_' . $i,
            'successcallback'  => 'editurl_submit',
            'validatecallback' => 'editurl_validate',
            'jsform'           => true,
            'elements'         => $elements,
        )),
        'deleteform' => pieform(array(
            'name'             => 'deleteurl_' . $i,
            'successcallback'  => 'deleteurl_submit',
            'renderer'         => 'oneline',
            'elements'         => array(
                'token'  => array(
                    'type'         => 'hidden',
                    'value'        => $r->token,
                ),
                'submit' => array(
                    'type'         => 'image',
                    'src'          => $THEME->get_url('images/icon_close.gif'),
                    'elementtitle' => get_string('delete'),
                    'confirm'      => get_string('reallydeletesecreturl', 'view'),
                ),
            ),
        )),
    );
}

function editurl_validate(Pieform $form, $values) {
    if (empty($values['startdate'])) {
        $values['startdate'] = null;
    }
    if (empty($values['stopdate'])) {
        $values['stopdate'] = null;
    }
    if ($values['stopdate'] && time() > $values['stopdate']) {
        $form->set_error('stopdate', get_string('stopdatecannotbeinpast', 'view'));
    }
    if ($values['startdate'] && $values['stopdate'] && $values['startdate'] > $values['stopdate']) {
        $form->set_error('startdate', get_string('startdatemustbebeforestopdate', 'view'));
    }
}

function editurl_submit(Pieform $form, $values) {
    global $tokens, $view, $collection, $SESSION;

    $viewid = $view->get('id');

    if ($collection) {
        $viewids = get_column('collection_view', 'view', 'collection', $collection->get('id'));
    }
    else {
        $viewids = array($viewid);
    }

    $access = (object) array(
        'token'           => $values['token'],
        'startdate'       => db_format_timestamp($values['startdate']),
        'stopdate'        => db_format_timestamp($values['stopdate']),
    );
    if (!$view->get('allowcomments')) {
        if ($access->allowcomments = (int) $values['allowcomments']) {
            $access->approvecomments = (int) $values['approvecomments'];
        }
    }

    $whereobject = (object) array('token' => $values['token']);

    if (isset($tokens[$values['token']])) {
        foreach ($viewids as $id) {
            $access->view = $id;
            $whereobject->view = $id;
            update_record('view_access', $access, $whereobject);
        }
        $message = get_string('secreturlupdated', 'view');
        $form->reply(PIEFORM_OK, $message);
    }

    $form->reply(PIEFORM_ERR, get_string('formerror'));
}

function deleteurl_submit(Pieform $form, $values) {
    global $tokens, $view, $collection, $SESSION;

    $viewid = $view->get('id');

    if ($collection) {
        $viewids = get_column('collection_view', 'view', 'collection', $collection->get('id'));
    }
    else {
        $viewids = array($viewid);
    }

    if (isset($tokens[$values['token']])) {
        $select = 'token = ? AND view IN (' . join(',', $viewids) . ')';
        delete_records_select('view_access', $select, array($values['token']));
        $SESSION->add_ok_msg(get_string('secreturldeleted', 'view'));
    }

    redirect('/view/urls.php?id=' . $viewid);
}

function newurl_submit(Pieform $form, $values) {
    global $view, $collection;

    $viewid = $view->get('id');

    if ($collection) {
        $viewids = get_column('collection_view', 'view', 'collection', $collection->get('id'));
    }
    else {
        $viewids = array($viewid);
    }

    $access = View::new_token($viewids[0]);
    for ($i = 1; $i < count($viewids); $i++) {
        $access->view = $viewids[$i];
        insert_record('view_access', $access);
    }

    redirect('/view/urls.php?id=' . $viewid);
}

$newform = pieform($newform);

$js = <<<EOF
\$j(function() {
    \$j('.url-open-editform').click(function(e) {
        e.preventDefault();
        \$j('#' + this.id + '-form').toggleClass('js-hidden');
    });
});
EOF;

$smarty = smarty(
    array('jquery'),
    array(),
    array(),
    array('sidebars' => false)
);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('editurls', $editurls);
$smarty->assign('newform', $newform);
$smarty->display('view/urls.tpl');
