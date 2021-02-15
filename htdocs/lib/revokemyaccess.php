<?php

/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Fergus Whyte <fergusw@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
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
        $title = get_field('view', 'title', 'id', $viewid);
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
    $message = hsc($values['message']);
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
        if (!empty($viewids)) {
            // The code will never reach this point unless at least one view exists. So it is safe.
            $insql = implode(",", $viewids);
            delete_records_sql("DELETE FROM {view_access} WHERE view IN (" . $insql . ") AND usr = ?", array($USER->id));
        }
    }
    else {
        delete_records_select('view_access', 'view = ? AND usr = ?', array($values['viewid'], $USER->id));
    }
    if ($owner) {
        $data = new stdClass();
        $data->viewid  = $values['viewid'];
        if ($message) {
            $data->message = $message;
        }
        else {
            $data->message = false;
        }
        $data->fromid = $USER->get('id');
        $data->toid = $owner->get('id');
        activity_occurred('viewaccessrevoke', $data);
    }
    $eventtitle = hsc($viewobj->get('title'));
    $eventid = $viewobj->get('id');
    $eventfor = 'view';
    if ($viewobj->get('collection')) {
        $eventtitle = hsc($viewobj->get('collection')->get('name'));
        $eventid = $viewobj->get('collection')->get('id');
        $eventfor = 'collection';
    }
    handle_event('removeviewaccess', array(
        'id' => $eventid,
        'eventfor' => $eventfor,
        'reason'  => $message,
        'portfoliotitle' => $eventtitle,
        )
    );

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
        $goto =  get_config('wwwroot') . 'view/view.php?id=' . $form->get_element('viewid')['value'];
        $form->reply(PIEFORM_OK, array(
            'goto' => $goto,
            'revokationcanceled' => true,
        ));
    }
}
