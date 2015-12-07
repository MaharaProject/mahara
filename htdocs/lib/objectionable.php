<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Returns a form to report objectionable material.
 *
 * @returns array Form elements.
 */
function objection_form() {
    $form = array(
        'name'              => 'objection_form',
        'method'            => 'post',
        // 'class'             => 'js-safe-hidden',
        'jsform'            => true,
        'autofocus'         => false,
        'elements'          => array(),
        'jssuccesscallback' => 'objectionSuccess',
    );

    $form['elements']['message'] = array(
        'type'  => 'textarea',
        'class' => 'under-label',
        'title' => get_string('complaint'),
        'rows'  => 5,
        'cols'  => 80,
        'rules' => array(
            'required' => true
        )
    );

    $form['elements']['submit'] = array(
        'type'    => 'submitcancel',
        'class'   => 'btn-default',
        'value'   => array(get_string('notifyadministrator'), get_string('cancel')),
        'confirm' => array(get_string('notifyadministratorconfirm')),
    );
    return $form;
}

function objection_form_submit(Pieform $form, $values) {
    global $USER, $view, $artefact;

    if (!$USER->is_logged_in()) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }

    require_once('activity.php');

    db_begin();

    $objection = new stdClass();
    if ($artefact) {
        $objection->objecttype = 'artefact';
        $objection->objectid   = $artefact->get('id');
    }
    else {
        $objection->objecttype = 'view';
        $objection->objectid   = $view->get('id');
    }
    $objection->reportedby = $USER->get('id');
    $objection->report = $values['message'];
    $objection->reportedtime = db_format_timestamp(time());

    insert_record('objectionable', $objection);

    $data = new StdClass();
    $data->view       = $view->get('id');
    $data->message    = $values['message'];
    $data->reporter   = $USER->get('id');
    $data->fromuser   = $USER->get('id');
    $data->ctime      = time();
    if ($artefact) {
        $data->artefact = $artefact->get('id');
    }

    activity_occurred('objectionable', $data);

    db_commit();

    if ($artefact) {
        $goto = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $artefact->get('id') . '&view='.$view->get('id');
    }
    else {
        $goto = $view->get_url();
    }

    $form->reply(PIEFORM_OK, array(
            'message' => get_string('reportsent'),
            'goto' => $goto,
        )
    );
}

function objection_form_cancel_submit(Pieform $form) {
    global $view;
    $form->reply(PIEFORM_OK, array(
            'goto' => $view->get_url(),
        )
    );
}

/**
 * Returns a form to mark a view as unobjectionable,
 * if the user is allowed to do that.
 *
 * @returns array Form elements.
 */
function notrude_form() {
    global $USER, $view, $artefact;
    $owner = $view->get('owner');
    if (!
        (
         ($owner && ($USER->get('admin') || $USER->is_admin_for_user($owner)))
            || ($view->get('group') && $USER->get('admin'))
            || ($view->get('group') && group_user_access($view->get('group'), $USER->get('id')) == 'admin')
         )
        ) {
        return;
    }

    if ($artefact) {
        $params = array('artefact', $artefact->get('id'));
    }
    else {
        $params = array('view', $view->get('id'));
    }
    $isrude = get_record_select('objectionable', 'objecttype = ? AND objectid = ? AND resolvedby IS NULL LIMIT 1', $params);

    if (!$isrude) {
        return;
    }

    return array(
        'name'     => 'notrude_form',
        'method'   => 'post',
        'elements' => array(
            'objection' => array(
                'type' => 'hidden',
                'value' => $isrude->id,
            ),
            'text' => array(
                'type' => 'html',
                'class' => 'objectionable-message',
                'value' => get_string('viewobjectionableunmark', 'view'),
            ),
            'submit' => array(
                'type' => 'button',
                'usebuttontag' => true,
                'class' => 'btn-default',
                'value' => '<span class="icon icon-lg icon-times text-danger left" role="presentation" aria-hidden="true"></span> '.get_string('notobjectionable'),
            ),
        ),
    );
}

function notrude_form_submit(Pieform $form, $values) {
    global $view, $artefact, $USER;

    require_once('activity.php');

    db_begin();

    $objection = new stdClass();
    if ($artefact) {
        $objection->objecttype = 'artefact';
        $objection->objectid   = $artefact->get('id');
    }
    else {
        $objection->objecttype = 'view';
        $objection->objectid   = $view->get('id');
    }
    $objection->resolvedby = $USER->get('id');
    $objection->resolvedtime = db_format_timestamp(time());

    update_record('objectionable', $objection, array('id' => $values['objection']));

    // Send notification to other admins.
    $reportername = display_default_name($USER);

    if ($artefact) {
        $goto = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $artefact->get('id') . '&view=' . $view->get('id');
    }
    else {
        $goto = $view->get_url();
    }

    $data = (object) array(
        'view'      => $view->get('id'),
        'reporter'  => $USER->get('id'),
        'subject'   => false,
        'message'   => false,
        'strings'   => (object) array(
            'subject' => (object) array(
                'key'     => 'viewunobjectionablesubject',
                'section' => 'view',
                'args'    => array($view->get('title'), $reportername),
            ),
            'message' => (object) array(
                'key'     => 'viewunobjectionablebody',
                'section' => 'view',
                'args'    => array($reportername, $view->get('title'), $view->formatted_owner()),
            ),
        ),
    );

    activity_occurred('objectionable', $data);

    db_commit();

    $form->reply(PIEFORM_OK, array(
        'message' => get_string('messagesent'),
        'goto' => $goto,
        )
    );
}
