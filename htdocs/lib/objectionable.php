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
        'class'             => 'js-safe-hidden',
        'jsform'            => true,
        'autofocus'         => false,
        'elements'          => array(),
        'jssuccesscallback' => 'objectionSuccess',
    );

    $form['elements']['message'] = array(
        'type'  => 'textarea',
        'title' => get_string('complaint'),
        'rows'  => 5,
        'cols'  => 80,
        'rules' => array(
            'required' => true
        )
    );

    $form['elements']['submit'] = array(
        'type'    => 'submitcancel',
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

    // The objectionable access record ensures the view is visible
    // to admins, and also marks the view as objectionable.

    $accessrecord = (object) array(
        'view'            => $view->get('id'),
        'accesstype'      => 'objectionable',
        'allowcomments'   => 1,
        'approvecomments' => 0,
        'visible'         => 0,
        'ctime'           => db_format_timestamp(time()),
    );

    delete_records('view_access', 'view', $view->get('id'), 'accesstype', 'objectionable', 'visible', 0);
    insert_record('view_access', $accessrecord);

    $data = new StdClass();
    $data->view       = $view->get('id');
    $data->message    = $values['message'];
    $data->reporter   = $USER->get('id');
    $data->ctime      = time();
    if ($artefact) {
        $data->artefact = $artefact->get('id');
    }

    activity_occurred('objectionable', $data);

    db_commit();

    if ($artefact) {
        $goto = get_config('wwwroot') . 'view/artefact.php?artefact=' . $artefact->get('id') . '&view='.$view->get('id');
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
    global $USER, $view;
    $owner = $view->get('owner');
    if (!(($owner && ($USER->get('admin') || $USER->is_admin_for_user($owner)))
            || ($view->get('group') && $USER->get('admin')))) {
        return;
    }

    $access = View::user_access_records($view->get('id'), $USER->get('id'));

    if (empty($access)) {
        return;
    }

    $isrude = false;
    foreach ($access as $a) {
        // Nasty hack: If the objectionable access record has a stop date, it
        // means that one of the admins has already dealt with it, so we don't
        // mark the view as objectionable.
        if ($a->accesstype == 'objectionable' && empty($a->stopdate)) {
            $isrude = true;
            break;
        }
    }

    if (!$isrude) {
        return;
    }

    return array(
        'name'     => 'notrude_form',
        'method'   => 'post',
        'elements' => array(
            'text' => array(
                'type' => 'html',
                'value' => get_string('viewobjectionableunmark', 'view'),
            ),
            'submit' => array(
                'type' => 'submit',
                'value' => get_string('notobjectionable'),
            ),
        ),
    );
}

function notrude_form_submit(Pieform $form, $values) {
    global $view, $artefact, $USER;

    require_once('activity.php');

    db_begin();

    // Set exipiry date on view access record.
    $accessrecord = (object) array(
        'view'            => $view->get('id'),
        'accesstype'      => 'objectionable',
        'allowcomments'   => 1,
        'approvecomments' => 0,
        'visible'         => 0,
        'stopdate'        => db_format_timestamp(time() + 60*60*24*7),
        'ctime'           => db_format_timestamp(time()),
    );

    delete_records('view_access', 'view', $view->get('id'), 'accesstype', 'objectionable', 'visible', 0);
    insert_record('view_access', $accessrecord);

    // Send notification to other admins.
    $reportername = display_default_name($USER);

    if ($artefact) {
        $goto = get_config('wwwroot') . 'view/artefact.php?artefact=' . $artefact->get('id') . '&view=' . $view->get('id');
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
