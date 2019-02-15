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

define('OBJECTIONABLE_REVIEW', 1); // Admin needs to review
define('OBJECTIONABLE_CHANGE', 2); // User needs to make changes

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
        'class'   => 'btn-secondary',
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
    $objection->status = OBJECTIONABLE_REVIEW;

    insert_record('objectionable', $objection);

    $data = new stdClass();
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
            'objection_cancelled' => true,
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
    $reviewmessage = !empty($isrude->reviewedby) ? '<br><br>' . get_string('objectionreviewsent') : '';
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
                'value' => get_string('viewobjectionableunmark', 'view') . $reviewmessage,
            ),
            'submitbuttons' => array(
                'type' => 'fieldset',
                'class' => 'btn-group last',
                'elements' => array (
                    'review' => array(
                        'class' => 'btn-secondary text-inline',
                        'type' => 'html',
                        'value' => '<button type="button" class="btn btn-secondary" data-toggle="modal-docked" data-target="#objection-review">
                                        <span class="icon icon-lg icon-check text-danger left" role="presentation" aria-hidden="true"></span>' .
                                        get_string('stillobjectionable') .
                                    '</button>'
                    ),
                    'submit' => array(
                        'class' => 'btn-secondary text-inline',
                        'name' => 'submit', // must be called submit so we can access it's value
                        'type'  => 'button',
                        'usebuttontag' => true,
                        'content' => '<span class="icon icon-lg icon-times text-danger left" role="presentation" aria-hidden="true"></span> '.get_string('notobjectionable'),
                        'value' => 'submit'
                    )
                )
            )
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
    $objection->suspended = 0;
    $objection->status = 0;

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

/**
 * Returns a form for user to review objectionable content,
 * if the user is allowed to do that.
 *
 * @returns array Form elements.
 */
function stillrude_form() {
    global $USER, $view, $artefact;
    $owner = $view->get('owner');
    if (!
        (
         ($owner && ($USER->get('admin') || $USER->is_admin_for_user($owner)))
            || ($view->get('group') && $USER->get('admin'))
            || ($view->get('group') && group_user_access($view->get('group'), $USER->get('id')) == 'admin')
         )
        ) {
        return false;
    }

    if ($artefact) {
        $params = array('artefact', $artefact->get('id'));
    }
    else {
        $params = array('view', $view->get('id'));
    }
    $isrude = get_record_select('objectionable', 'objecttype = ? AND objectid = ? AND resolvedby IS NULL LIMIT 1', $params);

    if (!$isrude) {
        return false;
    }

    return array(
        'name'     => 'stillrude_form',
        'method'   => 'post',
        'elements' => array(
            'objection' => array(
                'type' => 'hidden',
                'value' => $isrude->id,
            ),
            'report' => array(
                'type' => 'html',
                'title' => get_string('complaint'),
                'value' => $isrude->report,
            ),
            'adminreply' => array(
                'type' => 'textarea',
                'title' => get_string('reviewcomplaint'),
                'description' => get_string('reviewcomplaintdesc'),
                'defaultvalue' => $isrude->review,
                'rows'  => 5,
                'cols'  => 80
            ),
            'removeaccess' => array(
                'type' => 'switchbox',
                'title' => get_string('removeaccess'),
                'description' => get_string('removeaccessdesc'),
                'defaultvalue' => $isrude->suspended,
            ),
            'submitcancel' => array(
                'type' => 'submitcancel',
                'class' => 'btn-primary',
                'value' => array(get_string('submit'), get_string('cancel')),
                'goto' => $view->get_url(),
            ),
        ),
    );
}

function stillrude_form_submit(Pieform $form, $values) {
    global $view, $artefact, $USER;

    require_once('activity.php');

    db_begin();
    $review = !empty($values['adminreply']) ? $values['adminreply'] : $values['report'];
    $objection = new stdClass();
    $objection->reviewedby = $USER->get('id');
    $objection->review = $review;
    $objection->reviewedtime = db_format_timestamp(time());
    $objection->suspended = !empty($values['removeaccess']) ? 1 : 0;
    $objection->status = OBJECTIONABLE_CHANGE;

    update_record('objectionable', $objection, array('id' => $values['objection']));

    // Send notification to the owner.
    if ($artefact) {
        $goto = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $artefact->get('id') . '&view=' . $view->get('id');
    }
    else {
        $goto = $view->get_url();
    }

    $message = get_string('stillobjectionablecontent', 'activity', $review);
    if ($objection->suspended) {
        $message .= "\n\n" . get_string('stillobjectionablecontentsuspended', 'activity');
    }
    $data = new stdClass();
    $data->view       = $view->get('id');
    $data->message    = $message;
    $data->reporter   = $USER->get('id');
    $data->fromuser   = $USER->get('id');
    $data->touser     = $view->get('owner');
    $data->ctime      = time();
    if ($artefact) {
        $data->artefact = $artefact->get('id');
    }

    activity_occurred('objectionable', $data);

    db_commit();

    $form->reply(PIEFORM_OK, array(
        'message' => get_string('messagesent'),
        'goto' => $goto,
        )
    );
}

/**
 * Returns a form to review objectionable material.
 *
 * @returns array Form elements.
 */
function review_form($viewid = null) {
    $form = array(
        'name'              => 'review_form',
        'method'            => 'post',
        // 'class'             => 'js-safe-hidden',
        'jsform'            => true,
        'autofocus'         => false,
        'elements'          => array(),
        'jssuccesscallback' => 'reviewSuccess',
    );
    if ($viewid && $viewmessage = get_record_sql("SELECT id, review FROM {objectionable}
                                             WHERE objecttype = 'view' AND objectid = ?
                                             ORDER BY reportedtime LIMIT 1", array($viewid))) {
        if ($viewmessage->review) {
            $message = '<strong>' . get_string('lastobjection', 'mahara') . '</strong><br>' . hsc($viewmessage->review);
        }
        else {
            $message = '<strong>' . get_string('objectionnotreviewed', 'mahara') . '</strong>';
        }
        $form['elements']['reportedmessage'] = array(
            'type' => 'html',
            'value' => $message
        );
        $form['elements']['reportid'] = array(
            'type' => 'hidden',
            'value' => $viewmessage->id
        );
    }
    else if ($viewid && $artefactmessage = get_record_sql("SELECT o.id, o.review, a.title FROM {objectionable} o
                                             JOIN {view_artefact} va ON va.artefact = o.objectid
                                             JOIN {artefact} a ON a.id = va.artefact
                                             WHERE o.objecttype = 'artefact' AND va.view = ?
                                             ORDER BY reportedtime LIMIT 1", array($viewid))) {
        if ($artefactmessage->review) {
            $message = '<strong>' . get_string('lastobjection', 'mahara') . '</strong><br>' . hsc($artefactmessage->review);
        }
        else {
            $message = '<strong>' . get_string('objectionnotreviewed', 'mahara') . '</strong>';
        }
        $form['elements']['reportedmessage'] = array(
            'type' => 'html',
            'value' => '<strong>' . get_string('lastobjectionartefact', 'mahara', hsc($artefactmessage->title)) . '</strong><br>' . hsc($artefactmessage->review)
        );
        $form['elements']['reportid'] = array(
            'type' => 'hidden',
            'value' => $artefactmessage->id
        );
    }

    $form['elements']['message'] = array(
        'type'  => 'textarea',
        'class' => 'under-label',
        'title' => get_string('message'),
        'rows'  => 5,
        'cols'  => 80,
        'rules' => array(
            'required' => true
        )
    );

    $form['elements']['submit'] = array(
        'type'    => 'submitcancel',
        'class'   => 'btn-secondary',
        'value'   => array(get_string('notifyadministrator'), get_string('cancel')),
        'confirm' => array(get_string('notifyadministratorreview')),
    );
    return $form;
}

function review_form_submit(Pieform $form, $values) {
    global $USER, $view;

    if (!$USER->is_logged_in() &&
        $USER->get('id') === $view->get_owner_object()->get('owner')) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }

    require_once('activity.php');

    db_begin();

    $objection = new stdClass();
    $objection->status = OBJECTIONABLE_REVIEW;
    $objection->id = $values['reportid'];
    update_record('objectionable', $objection);

    $objection = get_record('objectionable', 'id', $values['reportid']);
    if ($objection->review) {
        $reviewmessage = get_string('replyingtoobjection', 'mahara', hsc($objection->review), hsc($values['message']));
    }
    else {
        $reviewmessage = get_string('objectionnotreviewedreply', 'mahara', hsc($values['message']));
    }
    $artefact = ($objection->objecttype == 'artefact') ? $objection->objectid : null;

    $data = new stdClass();
    $data->view       = $view->get('id');
    $data->message    = $reviewmessage;
    $data->reporter   = $USER->get('id');
    $data->fromuser   = $USER->get('id');
    $data->ctime      = time();
    $data->review     = true;
    if ($artefact) {
        $data->artefact = $artefact;
    }

    activity_occurred('objectionable', $data);

    db_commit();

    if ($artefact) {
        $goto = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $artefact . '&view='.$view->get('id');
    }
    else {
        $goto = $view->get_url();
    }

    $form->reply(PIEFORM_OK, array(
            'message' => get_string('reviewrequestsent'),
            'goto' => $goto,
        )
    );
}

function review_form_cancel_submit(Pieform $form) {
    global $view;
    $form->reply(PIEFORM_OK, array(
            'goto' => $view->get_url(),
        )
    );
}
