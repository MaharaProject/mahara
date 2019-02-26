<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'configusers/suspendedusers');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('suspendeduserstitle', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'suspendedusers');

$type = param_alpha('type', 'suspended');
$enc_type = json_encode($type);
$offset = param_integer('offset', 0);
$limit = param_integer('limit', 10);
$typeform = pieform(
    array(
        'name' => 'usertype',
        'class' => 'with-heading with-label-widthauto form-condensed',
        'elements' => array(
            'type' => array(
                'type' => 'select',
                'title' => get_string('show'),
                'options' => array(
                    'suspended' => get_string('suspendedusers', 'admin'),
                    'expired'   => get_string('expiredusers', 'admin'),
                ),
                'defaultvalue' => $type,
            ),
            'typesubmit' => array(
                'type' => 'submit',
                'class' => 'js-hidden',
                'value' => get_string('change'),
            ),
        ),
    )
);

// Filter for institutional admins:
$instsql = $USER->get('admin') ? '' : '
    AND ui.institution IN (' . join(',', array_map('db_quote', array_keys($USER->get('institutions')))) . ')';

$count = get_field_sql('
    SELECT COUNT(*)
    FROM (
        SELECT u.id
        FROM {usr} u
        LEFT OUTER JOIN {usr_institution} ui ON (ui.usr = u.id)
        WHERE ' . ($type == 'expired' ? 'u.expiry < current_timestamp' : 'suspendedcusr IS NOT NULL') . '
        AND deleted = 0 ' . $instsql . '
        GROUP BY u.id
    ) AS a');

$data = get_records_sql_assoc('
    SELECT
        u.id, u.firstname, u.lastname, u.studentid, u.suspendedctime, u.suspendedreason AS reason,
        ua.firstname AS cusrfirstname, ua.lastname AS cusrlastname, ' . db_format_tsfield('u.expiry', 'expiry') . '
    FROM {usr} u
    LEFT JOIN {usr} ua on (ua.id = u.suspendedcusr)
    LEFT OUTER JOIN {usr_institution} ui ON (ui.usr = u.id)
    WHERE ' . ($type == 'expired' ? 'u.expiry < current_timestamp' : 'u.suspendedcusr IS NOT NULL') . '
    AND u.deleted = 0 ' . $instsql . '
    GROUP BY
        u.id, u.firstname, u.lastname, u.studentid, u.suspendedctime, u.suspendedreason,
        ua.firstname, ua.lastname, u.expiry
    ORDER BY ' . ($type == 'expired' ? 'u.expiry' : 'u.suspendedctime') . ', u.id
    LIMIT ?
    OFFSET ?', array($limit, $offset));

if (!$data) {
    $data = array();
}
else {
    $institutions = get_records_sql_array('
        SELECT ui.usr, ui.studentid, i.displayname
        FROM {usr_institution} ui INNER JOIN {institution} i ON ui.institution = i.name
        WHERE ui.usr IN (' . join(',', array_keys($data)) . ')', array());
    if ($institutions) {
        foreach ($institutions as &$i) {
            $data[$i->usr]->institutions[] = $i->displayname;
            $data[$i->usr]->institutionids[] = $i->studentid;
        }
    }
    $data = array_values($data);
    foreach ($data as &$record) {
        $record->name      = full_name($record);
        $record->firstname = $record->cusrfirstname;
        $record->lastname  = $record->cusrlastname;
        $record->cusrname  = full_name($record);
        $record->expiry    = $record->expiry ? format_date($record->expiry, 'strftimew3cdate') : '-';
        unset($record->firstname, $record->lastname);
    }
}

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'admin/users/suspended.php?type=' . $type,
    'count' => $count,
    'limit' => $limit,
    'offset' => $offset,
    'setlimit' => true,
    'datatable' => 'suspendedlist',
    'jsonscript' => 'admin/users/suspended.json.php',
));

$smarty = smarty(array('paginator'));
setpageicon($smarty, 'icon-user-times');

$smarty->assign('typeform', $typeform);
$smarty->assign('data', $data);

$smarty->assign('INLINEJAVASCRIPT', <<<EOF

jQuery(function ($) {
    p = {$pagination['javascript']}
    $('#usertype_type').on('change', function (event) {
        var params = {
            'limit': $limit,
            'offset': 0,
            'sesskey': config['sesskey'],
            'setlimit': true,
            'type': $('#usertype_type option:selected').val(),
        };
        p.sendQuery(params);
        event.preventDefault();
        // Show the buttons relating to the 'type' selected
        show_buttons(params.type);
    });

    function show_buttons(type) {
        if (type == 'suspended') {
            $('#buttons_unsuspend').show();
            $('#buttons_unexpire').hide();
        }
        else if (type == 'expired') {
            $('#buttons_unsuspend').hide();
            $('#buttons_unexpire').show();
        }
    }

    $(window).on('pageupdated', {}, function(e, data) {
        // For when we are switching between suspended and expired
        var tmp = $('<div>').append(data.data.pagination);
        var paginationid = tmp.find('.pagination-wrapper').attr('id');
        if (paginationid !== $('.pagination-wrapper').attr('id')) {
            $('.pagination-wrapper').replaceWith(data.data.pagination);
            p = eval(data.data.pagination_js);
        }
    });

    show_buttons('$type');

    var wireselectall = function() {
        $("#selectall").on("click", function(e) {
            e.preventDefault();
            $("#suspendedlist :checkbox").prop("checked", true);
        });
    };

    var wireselectnone = function() {
        $("#selectnone").on("click", function(e) {
            e.preventDefault();
            $("#suspendedlist :checkbox").prop("checked", false);
        });
    };
    wireselectall();
    wireselectnone();
});
EOF
);

$form = pieform_instance(array(
    'name'      => 'buttons',
    'renderer'  => 'div',
    'autofocus' => false,
    'elements' => array(
        'buttons' => array(
            'type' => 'fieldset',
            'class' => 'btn-group float-right',
            'isformgroup' => false,
            'elements'  => array(
                'unsuspend' => array(
                    'class' => 'btn-secondary text-inline',
                    'type' => 'submit',
                    'isformgroup' => false,
                    'renderelementsonly' => true,
                    'name' => 'unsuspend',
                    'value' => get_string('unsuspendusers', 'admin')
                ),
                'unexpire' => array(
                    'class' => 'btn-secondary text-inline',
                    'type' => 'submit',
                    'isformgroup' => false,
                    'renderelementsonly' => true,
                    'name' => 'unexpire',
                    'value' => get_string('unexpireusers', 'admin')
                ),
                'delete' => array(
                    'class' => 'btn-secondary text-inline',
                    'type'    => 'submit',
                    'isformgroup' => false,
                    'renderelementsonly' => true,
                    'confirm' => get_string('confirmdeleteusers', 'admin'),
                    'name'    => 'delete',
                    'value'   => get_string('deleteusers', 'admin')
                )
            )
        )
    )
));
$html = $smarty->fetch('admin/users/suspendresults.tpl');
$smarty->assign('suspendhtml', $html);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('buttonformopen', $form->get_form_tag());
$smarty->assign('buttonform', $form->build(false));
$smarty->display('admin/users/suspended.tpl');

function buttons_submit_unsuspend(Pieform $form, $values) {
    global $SESSION;

    $ids = get_user_ids_from_post();
    foreach ($ids as $userid) {
        unsuspend_user($userid);
    }

    $SESSION->add_ok_msg(get_string('usersunsuspendedsuccessfully', 'admin'));
    redirect('/admin/users/suspended.php');
}

function buttons_submit_unexpire(Pieform $form, $values) {
    global $SESSION;

    $ids = get_user_ids_from_post();
    foreach ($ids as $userid) {
        unexpire_user($userid);
    }

    $SESSION->add_ok_msg(get_string('usersreactivated', 'admin'));
    redirect('/admin/users/suspended.php?type=expired');
}

function buttons_submit_delete(Pieform $form, $values) {
    global $SESSION;

    $ids = get_user_ids_from_post();
    foreach ($ids as $userid) {
        delete_user($userid);
    }

    $SESSION->add_ok_msg(get_string('usersdeletedsuccessfully', 'admin'));
    redirect('/admin/users/suspended.php');
}

function get_user_ids_from_post() {
    $ids = array();
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 4) == 'usr_') {
            $ids[] = intval(substr($key, 4));
        }
    }

    if (!$ids) {
        global $SESSION;
        $SESSION->add_info_msg(get_string('nousersselected', 'admin'));
        redirect('/admin/users/suspended.php');
    }

    return $ids;
}
