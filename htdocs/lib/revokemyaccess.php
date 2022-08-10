<?php

/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Fergus Whyte <fergusw@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

function revokemyaccess_form($viewid = null) {
    $form = array(
        'name'              => 'revokemyaccess_form',
        'method'            => 'post',
        'autofocus'         => false,
        'elements'          => array()
    );
    $form['elements']['message'] = array(
        'type'  => 'textarea',
        'class' => 'under-label',
        'title' => get_string('revokemyaccessreason','collection'),
        'rows'  => 5,
        'cols'  => 80,
        'rules' => array(
            'required' => false
        )
    );
    if ($viewid) {
        require_once('view.php');
        $view = new View($viewid);
        if ($view->get_collection()) {
            $title = $view->get_collection()->get('name');
        }
        else {
            $title = $view->get('title');
        }
        $form['elements']['viewid'] = array(
            'type' => 'hidden',
            'value' => $viewid,
            'dynamic' => false,
            'rules' => array(
                'required' => true,
                'integer' => true
            )
        );
        $form['elements']['submit'] = array(
            'type'    => 'submitcancel',
            'subclass'   => array('btn-secondary'),
            'value'   => array(get_string('removemyaccesssubmit', 'collection'), get_string('cancel')),
            'confirm' => array(get_string('revokemyaccessconfirm', 'collection') . $title),
        );
    }
    else {
        $form['elements']['viewid'] = array(
            'type' => 'hidden',
            'value' => '',
            'dynamic' => true,
            'rules' => array(
                'required' => true,
                'integer' => true
            )
        );
        $form['elements']['submit'] = array(
            'type'    => 'submitcancel',
            'subclass'   => array('btn-secondary'),
            'value'   => array(get_string('removemyaccesssubmit', 'collection'), get_string('cancel')),
            'confirm' => array(get_string('revokemyaccessconfirm', 'collection')),
        );
    }

    return $form;
}

function revokemyaccess_form_submit(Pieform $form, $values) {
    global $USER;
    $message = $values['message'];
    require_once('activity.php');
    require_once('view.php');
    if (!$USER->is_logged_in()) {
        throw new AccessDeniedException();
    }
    $viewid = $values['viewid'];
    $goto = get_config('wwwroot');
    if ($form->get_element_option('viewid', 'dynamic')) {
        $goto .= 'view/sharedviews.php';
    }

    // You can only remove things shared to you.
    $view_access =  get_records_select_array('view_access', "view = ? AND usr = ?", array($viewid, $USER->id));
    if ($view_access === false) {
        throw new AccessDeniedException();
    }
    $viewobj = new View($values['viewid']);
    if (!$viewobj) {
        throw new ViewNotFoundException(get_string('viewnotfound', 'error', $values['viewid']));
    }
    $collection = $viewobj->get_collection();
    $owner = $viewobj->get_owner_object();
    if ($collection && $viewids = get_column('collection_view', 'view', 'collection', $collection->get('id'))) {
        if ($viewids) {
            // The code will never reach this point unless at least one view exists. So it is safe.
            $insql = implode(",", $viewids);
            delete_records_sql("DELETE FROM {view_access} WHERE view IN (" . $insql . ") AND usr = ?", array($USER->id));
        }
    }
    else {
        delete_records_select('view_access', 'view = ? AND usr = ?', array($values['viewid'], $USER->id));
    }
    if ($owner) {
        revokemyaccess_activity_occurred_handler($values['viewid'], $USER->get('id'), $owner->get('id'), $message);
    }
    revokemyaccess_event_handler($viewid, $message);

    $form->reply(
        PIEFORM_OK,
        array(
            'message' => get_string('revokemessagesent', 'collection'),
            'goto' => $goto,
        )
    );
}

function revokemyaccess_form_cancel_submit(Pieform $form) {
    if ($form->get_element_option('viewid', 'dynamic')) {
        $form->reply(PIEFORM_OK, array(
            'goto' => get_config('wwwroot') . 'view/sharedviews.php',
            'revokationcanceled' => true,
        ));
    }
    else {
        $viewid = $form->get_element('viewid')['value'];
        require_once('view.php');
        $view = new View($viewid);
        $goto = get_config('wwwroot') . 'view/view.php?id=' . $viewid;
        if ($view->get_collection()) {
            $pid = $view->get_collection()->has_progresscompletion();
            if ($pid && $pid == $viewid) {
                $goto = get_config('wwwroot') . 'collection/progresscompletion.php?id=' . $view->get_collection()->get('id');
            }
        }

        $form->reply(PIEFORM_OK, array(
            'goto' => $goto,
            'revokationcanceled' => true,
        ));
    }
}

/**
 * Log revocation event for event subscription
 */
function revokemyaccess_event_handler($viewid, $message='') {
    global $USER;
    $viewobj = new View($viewid);
    if (!$viewobj) {
        throw new ViewNotFoundException(get_string('viewnotfound', 'error', $viewid));
    }
    $portfolioid = $viewobj->get('collection') ? $viewobj->get('collection')->get('id') : $viewobj->get('id');
    $eventfor = $viewobj->get('collection') ? 'collection' : 'view';
    $portfoliotitle = $viewobj->get('collection') ? $viewobj->get('collection')->get('name') : $viewobj->get('title');
    $removertype = $USER->id === $viewobj->get('owner') ? 'owner' : 'accessor';
    $removerid = $USER->id;
    handle_event('removeviewaccess', array(
        'id' => $portfolioid,
        'eventfor' => $eventfor,
        'reason'  => hsc($message),
        'portfoliotitle' => hsc($portfoliotitle),
        'removedby' => $removertype,
        'removedid' => $removerid,
    ));
}

/**
 * Send email/notification of event to appropriate user on revocation
 */
function revokemyaccess_activity_occurred_handler($viewid, $fromusrid, $tousrid, $message=null) {
    $data = new stdClass();
    $data->viewid  = $viewid;
    if ($message) {
        $data->message = $message;
    }
    else {
        $data->message = false;
    }
    $data->fromid = $fromusrid;
    $data->toid = $tousrid;
    activity_occurred('viewaccessrevoke', $data);
}
