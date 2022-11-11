<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-verification
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeVerification extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.verification');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.verification');
    }

    public static function single_only() {
        return false;
    }

    public static function single_artefact_per_block() {
        return false;
    }

    public static function get_categories() {
        return array('general' => 16350);
    }

    public static function get_viewtypes() {
        return array('progress');
    }

    public static function get_css_icon($blocktypename) {
        return 'certificate';
    }

    public static function get_instance_config_javascript(BlockInstance $instance) {
        return array('js/configform.js');
    }

    public static function override_instance_title(BlockInstance $instance) {
        if (!$instance->get('inedit')) {
            return '';
        }
        // When we don't want to display the title in block header on display page but do on edit page
        // and we still want to be able to edit the title in config form
        if ($instance->get('ineditconfig')) {
            return '';
        }
        return $instance->get('title');
    }

    public static function title_mandatory(BlockInstance $instance) {
        return true;
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        global $USER;

        $collectionid = $instance->get_view()->get('collection');
        $title = !$instance->get('inedit') ? $instance->get('title') : false;
        $configdata = $instance->get('configdata');
        // When we have 'progress' page type we will need to check that it's part of a collection
        // as the page will only exist as part of a collection
        if (!$collectionid) {
            return get_string('blockneedscollection', 'blocktype.verification');
        }
        $viewid = $instance->get_view()->get('id');
        $dwoo = smarty_core();
        $dwoo->assign('title', $title);
        $groups = get_column_sql('SELECT "group" FROM {view_access} WHERE "group" IS NOT NULL AND view = ?', array($viewid));
        $canverify = self::can_verify($configdata, $viewid, $groups);
        $canunverify = self::can_unverify($configdata, $viewid, $groups);
        $istemplate = $instance->get_view()->get_original_template();
        $dwoo->assign('istemplate', $istemplate);
        $verified = false;
        list($commentdata, $commenttype) = self::get_verification_comment($instance);
        $resetnames = '';
        if (!empty($configdata['resetstatement'])) {
            $alltypes = self::get_roleoptions();
            $names = array();
            foreach ($configdata['resetstatement'] as $type) {
                $names[] = $alltypes[$type];
            }
            $resetnames = implode(', ', $names);
        }
        if (!empty($configdata['addcomment']) && ($editing || !$instance->get_view()->get('owner'))) {
            $dwoo->assign('commentlist', get_string('commentformplaceholder', 'blocktype.verification'));
        }
        else if (!empty($configdata['addcomment']) && $commenttype == 'published' && !$canunverify) {
            $smarty = smarty_core();
            $smarty->assign('comments', array($commentdata));
            if (!empty($configdata['displayverifiername'])) {
                 $smarty->assign('displayverifier', true);
            }
            $comment = $smarty->fetch('blocktype:verification:commentlist.tpl');
            $dwoo->assign('commentlist', $comment);
        }
        else if (!empty($configdata['addcomment'])) {
            if ($commentdata) {
                $elements['postid'] = array(
                    'type' => 'hidden',
                    'value' => $commentdata->postid,
                );
            }
            $elements['comment'] = array (
                'type' => 'wysiwyg',
                'title' => get_string('verifycomment', 'blocktype.verification'),
                'hiddenlabel' => true,
                'width' => '100%',
                'class' => 'form-group-no-border',
                'height' => '150px',
                'defaultvalue' => $commentdata ? $commentdata->text : '',
                'rules' => array('maxlength' => 1000000),
            );
            $lockingstr = $namestr = '';
            if (!empty($configdata['lockportfolio'])) {
                $lockingstr = 'locking';
            }
            if (!empty($configdata['resetstatement'])) {
                $namestr = 'names';
            }
            $confirmtext = get_string('addcommentchecklist' . $lockingstr . $namestr, 'blocktype.verification', $resetnames);
            $elements['savecomment'] = array(
                'type' => 'multisubmit',
                'options' => array('draft', 'publish', 'cancel'),
                'primarychoice' => 'publish',
                'title' => '',
                'classes' => array('btn-secondary draftsubmit', 'submit', 'submitcancel cancel'),
                'confirm' => array(1 => $confirmtext),
                'value' => array(get_string('savedraft', 'blocktype.verification'), get_string('publish', 'blocktype.verification'), get_string('cancel', 'blocktype.verification'))
            );
            $elements['description'] = array(
                'type' => 'html',
                'value' => get_string('addcommentdescriptionhtml', 'blocktype.verification'),
            );
            $form = pieform(array(
                'name'        => 'addcomment_' . $instance->get('id'),
                'method'      => 'post',
                'viewid'      => $viewid,
                'jsform'      => true,
                'validatecallback' => array('PluginBlocktypeVerification', 'comment_validate'),
                'successcallback' => array('PluginBlocktypeVerification', 'comment_submit'),
                'jssuccesscallback' => 'verification_comment_success_' . $instance->get('id'),
                'plugintype'  => 'blocktype',
                'pluginname'  => 'verification',
                'elements'    => $elements
            ));
            $form .= self::verification_comment_js($instance->get('id'), $commentdata ? $commentdata->text : '<p></p>');
            $dwoo->assign('commentform', $form);
        }
        $titlevisible = !$istemplate;
        $contentvisible = !$istemplate;
        if (!empty($configdata['availabilitydate']) && $configdata['availabilitydate'] > time()) {
            $titlevisible = false;
            $contentvisible = false;
            //Editible logic taken from  view/view.php
            $submittedgroup = (int)$instance->get_view()->get('submittedgroup');
            $can_edit = $USER->can_edit_view($instance->get_view()) && !$submittedgroup &&  !$instance->get_view()->is_submitted();
            if ($instance->get_view()->get_collection()) {
                $pageistemplate = $instance->get_view()->get_original_template();
                $can_edit = $can_edit && $USER->can_edit_collection($instance->get_view()->get_collection());
                //Logic taken from progresscompletion page.
                if (($instance->get_view()->get('owner') && !$pageistemplate) || !$instance->get_view()->get('owner')) {
                    $can_edit = $can_edit && true;
                }
                else {
                    $can_edit =  false;
                }
            }
            if ( $can_edit || $canverify) {
                $dwoo->assign('availabilitydatemessage', get_string('availabilitydatemessage', 'blocktype.verification', format_date($configdata['availabilitydate'], 'strftimedate')));
                $titlevisible = true;
                $contentvisible = true;
            }
            $canverify = false;
        }
        if (!empty($configdata['verified'])) {
            $verified = true;
            $userid = $configdata['verifierid'];
            if (!empty($configdata['displayverifiername'])) {
                $verifiedon = get_string('verifiedonby', 'blocktype.verification', profile_url($userid), display_name($userid), format_date($configdata['verifieddate']));
            }
            else {
                $verifiedon = get_string('verifiedon', 'blocktype.verification', format_date($configdata['verifieddate']));
            }
            $dwoo->assign('verifiedon', $verifiedon);
        }
        $commentverified = false;
        if (!empty($configdata['addcomment'])) {
            if ($commentdata && $commenttype == 'published') {
                $commentverified = true;
            }
        }
        $isowner = false;
        if (!empty($instance->get_view()->get('owner')) && $instance->get_view()->get('owner') == $USER->get('id')) {
            $isowner = true;
        }
        if (!$instance->get_view()->get('owner') || ($canverify && !($isowner && $istemplate)) || $verified || $commentverified) {
            $titlevisible = true;
            $contentvisible = true;
        }
        $inedit = $instance->get('inedit');
        if (!$instance->get_view()->get('owner')) {
            $inedit = true;
        }

        $dwoo->assign('resetnames', $resetnames);
        $dwoo->assign('inedit', $inedit);
        $dwoo->assign('canverify', $canverify);
        $dwoo->assign('canunverify', $canunverify);
        $dwoo->assign('isverified', $verified);
        $dwoo->assign('contentvisible', $contentvisible);
        $dwoo->assign('titlevisible', $titlevisible);
        $dwoo->assign('blockid', $instance->get('id'));
        $dwoo->assign('data', $configdata);
        return $dwoo->fetch('blocktype:verification:content.tpl');
    }

    public static function can_unverify($configdata, $viewid, $groups=false) {
        return self::allowed_access('resetstatement', $configdata, $viewid, $groups);
    }

    public static function can_verify($configdata, $viewid, $groups=false) {
        return self::allowed_access('availableto', $configdata, $viewid, $groups);
    }

    public static function allowed_access($state, $configdata, $viewid, $groups=false) {
        global $USER;

        if (!empty($configdata[$state]) && is_array($configdata[$state])) {
            foreach ($configdata[$state] as $type) {
                if ($type == 'siteadmin' && $USER->get('admin')) {
                    return true;
                }
                if ($type == 'sitestaff' && $USER->get('staff')) {
                    return true;
                }
                if ($type == 'institutionadmin' && $USER->is_institutional_admin()) {
                    return true;
                }
                if ($type == 'institutionstaff' && $USER->is_institutional_staff()) {
                    return true;
                }

                $accessroles = self::get_roleoptions('accessrole');
                if (isset($accessroles[$type]) && record_exists('view_access', 'usr', $USER->get('id'), 'role', $type, 'view', $viewid)) {
                    return true;
                }
                $userroles = self::get_roleoptions('userrole');
                if (isset($userroles[$type]) && record_exists('usr_roles', 'usr', $USER->get('id'), 'role', $type)) {
                    return true;
                }
                if (is_array($groups) && !empty($groups)) {
                    $grouproles = self::get_roleoptions('grouprole');
                    foreach ($groups as $groupid) {
                        if (isset($grouproles[$type]) && record_exists('group_member', 'group', $groupid, 'member', $USER->get('id'), 'role', preg_replace('/^group/', '', $type))) {
                           return true;
                        }
                    }
                }
            }
            return false;
        }
        else if ($state == 'availableto') {
            return true;
        }
        return false;
    }

    public static function get_verification_comment(BlockInstance $instance) {
        global $USER;
        $owner = $instance->get_view()->get('owner');
        $userid = (!empty($USER) ? $USER->get('id') : 0);
        $sql = 'SELECT bwp.id AS postid, bwp.instance, bwp.from, bwp.private, bwp.postdate, bwp.text,' . db_format_tsfield('postdate') . ',
                    u.id, u.id AS userid, u.username, u.firstname, u.lastname, u.preferredname
                FROM {blocktype_verification_comment} bwp
                JOIN {usr} u ON bwp.from = u.id
                WHERE bwp.instance = ? AND u.deleted = 0
                ' . (($owner != $userid)  ? '
                     AND (bwp.private = 0 OR bwp.from = ' . db_quote($userid) . ') ' : '' ) . '
                ORDER BY bwp.postdate DESC
        ';
        $params = array($instance->get('id'));
        if ($records = get_records_sql_array($sql, $params, 0, 1)) {
            $data = array_map(function($item) {
                $item->displayname = display_name($item);
                $item->text = clean_html($item->text);
                $item->profileurl = profile_url($item);
                return $item;
            }, $records);
            return array($data[0], $data[0]->private ? 'draft' : 'published');
        }
        return array(false, false);
    }

    public static function delete_instance(BlockInstance $instance) {
        delete_records('blocktype_verification_comment', 'instance', $instance->get('id'));
        delete_records('blocktype_verification_undo', 'block', $instance->get('id'));
        return true;
    }

    public static function comment_validate(Pieform $form, $values) {
        // add any needed validating
    }

    public static function comment_submit(Pieform $form, $values) {
        global $USER;

        $instanceid = explode('_', $form->get_property('name'))[1];
        if (empty($instanceid)) {
            throw new BlockInstanceNotFoundException(get_string('blockinstancenotfound', 'error', 0));
        }
        $instance = new BlockInstance($instanceid);
        $configdata = $instance->get('configdata');
        $record = (object)array(
            'instance' => $instanceid,
            'from'     => $USER->get('id'),
            'private'  => $values['savecomment'] == 'draft' ? 1 : 0,
            'postdate' => db_format_timestamp(time()),
            'text'     => clean_html($values['comment']),
        );
        $newid = false;
        $resetverification = false;
        if (empty(clean_html($values['comment'])) && !empty($values['postid'])) {
            // we have an empty comment record so delete it
            delete_records('blocktype_verification_comment', 'id', $values['postid']);
            $resetverification = true;
        }
        else if (!empty($values['postid'])) {
            $record->id = $values['postid'];
            $oldfrom = get_field('blocktype_verification_comment', 'from', 'id', $record->id);
            if ($oldfrom != $record->from && param_integer('undo', false)) {
                $record->from = $oldfrom;
            }
            update_record('blocktype_verification_comment', $record, 'id');
            $newid = $record->id;
            if ($record->private === 1) {
                $resetverification = true;
            }
        }
        else if (!empty(clean_html($values['comment']))) {
            $newid = insert_record('blocktype_verification_comment', $record, 'id', true);
        }

        if ($resetverification && $undo = get_record_sql("SELECT * FROM {blocktype_verification_undo} WHERE block = ? LIMIT 1", array($instanceid))) {
            $goto = get_config('wwwroot') . 'collection/progresscompletion.php?id=' . $instance->get_view()->get_collection()->get('id');
            $users = array($instance->get_view()->get('owner'), $undo->reporter);
            $message = (object) array(
                'users' => $users,
                'subject' => get_string('undonesubject', 'collection'),
                'message' => get_string('undonemessage', 'collection', display_name($USER), $instance->get('title'), $instance->get_view()->get_collection()->get('name')),
                'url'     => get_config('wwwroot') . 'collection/progresscompletion.php?id=' . $instance->get_view()->get_collection()->get('id'),
                'urltext' => $instance->get_view()->get_collection()->get('name'),
            );
            activity_occurred('maharamessage', $message);
            delete_records('blocktype_verification_undo', 'block', $instanceid);
        }

        $newtext = null;
        if ($newid) {
            require_once('embeddedimage.php');
            $newtext = EmbeddedImage::prepare_embedded_images($values['comment'], 'verification_comment', $newid);
            // If there is an embedded image, update the src so users can have visibility
            if ($values['comment'] != $newtext) {
                $updatedcomment = new stdClass();
                $updatedcomment->id = $newid;
                $updatedcomment->text = $newtext;
                update_record('blocktype_verification_comment', $updatedcomment, 'id');
            }
        }

        list($commentdata, $type) = self::get_verification_comment($instance);
        if (!empty($commentdata)) {
            $smarty = smarty_core();
            $smarty->assign('comments', array($commentdata));
            if (!empty($configdata['displayverifiername'])) {
                $smarty->assign('displayverifier', true);
            }
            $renderedcomments = $smarty->fetch('blocktype:verification:commentlist.tpl');
        }
        else {
            $renderedcomments = '';
        }

        $view = new View($form->get_property('viewid'));

        if ($record->private != 1 && !empty($configdata['lockportfolio'])) {
            $view->get_collection()->lock_collection();
        }

        if ($record->private != 1 && !empty($configdata['notification'])) {
            // send notification to page owner
            $owner = $view->get('owner');
            require_once('activity.php');

            if (empty($configdata['displayverifiername'])) {
                $verifiersubjectstring = 'verifymessagesubjectnoname';
                $verifiersubjectargs = array();
                $verifiermessagestring = 'verifymessagenoname';
                $verifiermessageargs = array(format_date(strtotime($record->postdate)), html2text($configdata['text']) . ' ' . html2text($newtext));
            }
            else {
                $verifiersubjectstring = 'verifymessagesubject';
                $verifiersubjectargs = array(display_name($USER));
                $verifiermessagestring = 'verifymessage';
                $verifiermessageargs = array(display_name($USER), format_date(strtotime($record->postdate)), html2text($configdata['text']) . ' ' . html2text($newtext));
            }

            $message = array(
                'users'   => array($owner),
                'subject' => '',
                'message' => '',
                'strings' => (object) array(
                    'subject' => (object) array(
                        'key'     => $verifiersubjectstring,
                        'section' => 'blocktype.verification',
                        'args' => $verifiersubjectargs
                    ),
                    'message' => (object) array(
                        'key'     => $verifiermessagestring,
                        'section' => 'blocktype.verification',
                        'args'    => $verifiermessageargs,
                    ),
                ),
                'url'     => get_config('wwwroot') . 'collection/progresscompletion.php?id=' . $view->get_collection()->get('id'),
                'urltext' => $view->get_collection()->get('name'),
            );

            activity_occurred('maharamessage', $message);

        }

        if ($record->private != 1) {
            $record->verified = 1;
            if (isset($configdata['primary'])) {
                $record->primary = (bool)$configdata['primary'];
            }
            handle_event('verifiedprogress', array(
                'id' => $instanceid,
                'eventfor' => 'block',
                'block'  => $record,
                'parenttype' => 'collection',
                'parentid' => $view->get_collection()->get('id'),
            ));
        }

        if (empty($newtext) || $record->private) {
            // check if is the last verified locking statement block
            if (PluginBlocktypeVerification::is_last_locking_block($instance)) {
                $view->get_collection()->unlock_collection();
            }
        }

        $form->reply(PIEFORM_OK, array(
            'message'  => get_string('addcommentsuccess' . ($record->private ? 'draft' : ''), 'blocktype.verification', $instance->get('title')),
            'comments' => $commentdata,
            'commentlist' => $renderedcomments,
            'type'     => $type,
            'block'    => $instanceid,
            'goto'     => $view->get_url(),
        ));
    }

    public static function verification_comment_js($id, $text) {
       $text =  str_replace(array("\r", "\n"), '', $text);
       $js = <<<EOF
function verification_comment_success_{$id}(form, data) {
    formSuccess(form, data);
    if (data.type == 'draft') {
        handle_cancel_{$id}({$id}, data.comments.text);
    }
    else {
        location.reload();
    }
    $(window).trigger('colresize');
}

function handle_cancel_{$id}(id, text) {
    $('#addcomment_' + id + ' .submitcancel').off('click');
    $('#addcomment_' + id + ' .submitcancel').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        tinyMCE.get('addcomment_' + id + '_comment').setContent(text);
        tinyMCE.triggerSave();
    });
}
jQuery(function() {
    handle_cancel_{$id}({$id}, '{$text}');
});
EOF;
        return "<script>$js</script>";
    }

    public static function has_instance_config(BlockInstance $instance) {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance, $template=0, $new=false) {
        $configdata = $instance->get('configdata');
        if (!$height = get_config('blockeditorheight')) {
            $cfheight = param_integer('cfheight', 0);
            $height = $cfheight ? $cfheight * 0.7 : 150;
        }
        $text = '';
        if (array_key_exists('text', $configdata)) {
            $text = $configdata['text'];
        }

        $elements['primary'] = array (
            'type' => 'switchbox',
            'title' => get_string('primarystatement', 'blocktype.verification'),
            'description' => get_string('primarystatementdescription', 'blocktype.verification'),
            'defaultvalue' => isset($configdata['primary']) && $configdata['primary'],
        );

        $elements['text'] = array (
            'type' => 'wysiwyg',
            'title' => get_string('blockcontent', 'blocktype.verification'),
            'description' => get_string('blockcontentdescription', 'blocktype.verification'),
            'width' => '100%',
            'height' => $height . 'px',
            'defaultvalue' => $text,
            'rules' => array('maxlength' => 1000000),
        );

        $elements['addcomment'] = array (
            'type' => 'switchbox',
            'title' => get_string('addcomment', 'blocktype.verification'),
            'description' => get_string('addcommentdescription', 'blocktype.verification'),
            'defaultvalue' => isset($configdata['addcomment']) && $configdata['addcomment'],
        );

        $elements['displayverifiername'] = array (
            'type' => 'switchbox',
            'title' => get_string('displayverifiername', 'blocktype.verification'),
            'description' => get_string('displayverifiernamedescription', 'blocktype.verification'),
            'defaultvalue' => isset($configdata['displayverifiername']) && $configdata['displayverifiername'],
        );

        $elements['availableto'] = array(
            'type' => 'select',
            'isSelect2' => true,
            'class' => 'js-select2',
            'title' => get_string('availableto', 'blocktype.verification'),
            'description' => get_string('availabletodescription', 'blocktype.verification'),
            'multiple' => true,
            'options' => self::get_roleoptions(),
            'defaultvalue' => isset($configdata['availableto']) ? $configdata['availableto'] : null,
        );

        require_once('pieforms/pieform/elements/calendar.php');
        $availabilitydate = !empty($configdata['availabilitydate']) ? $configdata['availabilitydate'] : false;
        $elements['availabilitydate'] = array(
            'type'       => 'calendar',
            'caloptions' => array(
                'showsTime'      => false,
            ),
            'defaultvalue' => $availabilitydate,
            'title' => get_string('availabilitydate', 'blocktype.verification'),
            'description' => get_string('availabilitydatedescription', 'blocktype.verification', pieform_element_calendar_human_readable_dateformat()),
            'rules' => array(
                'required' => false,
            ),
        );

        $elements['lockportfolio'] = array (
            'type' => 'switchbox',
            'title' => get_string('lockportfolio', 'blocktype.verification'),
            'description' => get_string('lockportfoliodescription', 'blocktype.verification'),
            'defaultvalue' => isset($configdata['lockportfolio']) && $configdata['lockportfolio'],
        );

        $elements['notification'] = array (
            'type' => 'switchbox',
            'title' => get_string('notification', 'blocktype.verification'),
            'description' => get_string('notificationdescription', 'blocktype.verification'),
            'defaultvalue' => isset($configdata['notification']) && $configdata['notification'],
        );

        $elements['resetstatement'] = array(
            'type' => 'select',
            'isSelect2' => true,
            'class' => 'js-select2',
            'title' => get_string('resetstatement', 'blocktype.verification'),
            'description' => get_string('resetstatementdescription', 'blocktype.verification'),
            'multiple' => true,
            'options' => self::get_roleoptions(),
            'defaultvalue' => isset($configdata['resetstatement']) ? $configdata['resetstatement'] : null,
        );

        return $elements;
    }

    public static function get_roleoptions($type=false) {
        $roletypeoptions = array();
        $roleoptions = array(
            'siteadmin' => get_string('siteadmin', 'admin'),
            'sitestaff' => get_string('sitestaff', 'admin'),
            'institutionadmin' => get_string('institutionadmin', 'admin'),
            'institutionstaff' => get_string('institutionstaff', 'admin')
        );
        if ($roles = get_records_sql_array("
                SELECT DISTINCT role, 'mahara' AS location, 'userrole' AS type FROM {usr_roles} WHERE active = ?
                UNION
                SELECT DISTINCT role, 'view' AS location, 'accessrole' AS type FROM {usr_access_roles}
                UNION
                SELECT DISTINCT CONCAT('group', role) AS role, 'blocktype.verification' AS location, 'grouprole' AS type FROM {grouptype_roles}", array(1))) {
            foreach ($roles as $k => $role) {
                if ($role->type == $type) {
                    $roletypeoptions[$role->role] = get_string($role->role, $role->location);
                }
                $roleoptions[$role->role] = get_string($role->role, $role->location);
            }
        }
        if ($type) {
            // only return a list of this type
            return $roletypeoptions;
        }
        asort($roleoptions);
        return $roleoptions;
    }

    public static function instance_config_save($values, $instance) {
        require_once('embeddedimage.php');
        $newtext = EmbeddedImage::prepare_embedded_images($values['text'], 'text', $instance->get('id'));
        $values['text'] = $newtext;
        if (!empty($values['primary'])) {
            // If this block is being set as primary we need to turn the primary off on the others for this page
            // As the info is hidden in configdata we need to loop through them all
            if ($blockids = get_column_sql("SELECT b.id FROM {block_instance} b
                                            WHERE b.blocktype = ?
                                            AND b.view = ?
                                            AND b.id != ?
                                            AND b.configdata LIKE ?",
                    array('verification', $instance->get_view()->get('id'), $instance->get('id'), '%primary%'))) {
                $redraw = array();
                foreach ($blockids as $blockid) {
                    $bi = new BlockInstance($blockid);
                    $config = $bi->get('configdata');
                    $config['primary'] = 0;
                    $bi->set('configdata', $config);
                    $bi->commit();
                    $redraw[] = $blockid;
                }
                // Pass back a list of any other blocks that need to be rendered
                // due to this change.
                $values['_redrawblocks'] = $redraw;
            }
        }
        // Keep the state of the verification
        $configdata = $instance->get('configdata');
        if (!empty($configdata['verified'])) {
            $values['verified'] = $configdata['verified'];
        }
        if (!empty($configdata['verifieddate'])) {
            $values['verifieddate'] = $configdata['verifieddate'];
        }
        if (!empty($configdata['verifierid'])) {
            $values['verifierid'] = $configdata['verifierid'];
        }
        return $values;
    }

    public static function default_copy_type(BlockInstance $instance, View $view) {
        return 'fullinclself';
    }

    public static function rewrite_blockinstance_config(View $view, $configdata) {
        // Reset the verifying
        unset($configdata['verified']);
        unset($configdata['verifieddate']);
        unset($configdata['verifierid']);
        return $configdata;
    }

    /**
     * Content copying tasks.
     *
     * Copy any verification comments on the original block to the new block.
     *
     * @see PluginBlocktype::rewrite_blockinstance_extra_config()
     * @param View $view The View the block is on.
     * @param BlockInstance $block The new block instance.
     * @param array $configdata
     * @param array $artefactcopies
     * @param View $originalView The original View the block is from.
     * @param BlockInstance $originalBlock The original block instance.
     * @param boolean $copyissubmission True if the copy is a submission.
     *
     * @return array The new configdata.
     */
    public static function rewrite_blockinstance_extra_config(View $view, BlockInstance $block, $configdata, $artefactcopies, View $originalView, BlockInstance $originalBlock, $copyissubmission) {
        $regexp = array();
        $replacetext = array();
        $new_blockid = $block->get('id');
        if (isset($configdata['draft']) && $configdata['draft']) {
            $configdata['text'] = '';
            $configdata['draft'] = false;
        }
        // Prepare embedded images to reference their new files
        foreach ($artefactcopies as $copyobj) {
            $regexp[] = '#<img([^>]+)src="' . get_config('wwwroot') . 'artefact/file/download.php\?file=' . $copyobj->oldid . '([^0-9])#';
            $replacetext[] = '<img$1src="' . get_config('wwwroot') . 'artefact/file/download.php?file=' . $copyobj->newid . '$2';
        }
        // Prepare embedded images to reference their new blocks
        require_once('embeddedimage.php');
        $configdata['text'] = preg_replace($regexp, $replacetext, $configdata['text']);
        $configdata['text'] = EmbeddedImage::prepare_embedded_images($configdata['text'], 'text', $new_blockid);

        // Fetch any verification comments on the original block.
        $comments = get_records_array('blocktype_verification_comment', 'instance', $originalBlock->get('id'));
        if ($comments) {
            // Copy the comments to the new block.
            foreach ($comments as $comment) {
                // Update the block instance this comment is on.
                $comment->instance = $block->get('id');
                // Rewrite the embedded images in the comment.
                $comment->text = EmbeddedImage::prepare_embedded_images($comment->text, 'text', $block->get('id'));
                // Unset the id so that a new record is created.
                unset($comment->id);
                insert_record('blocktype_verification_comment', $comment);
            }
        }
        return $configdata;
    }

    /**
     * Shouldn't be linked to any artefacts via the view_artefacts table.
     *
     * @param BlockInstance $instance
     * @return array
     */
    public static function get_artefacts(BlockInstance $instance) {
        return array();
    }

    /**
     * Checks if is the last verified locking statement block
     */
    public static function is_last_locking_block(BlockInstance $instance) {
        $viewid = $instance->get_view()->get('id');

        $sql = "SELECT bi.id, bi.configdata, bvc.text, bvc.private FROM {block_instance} bi
        LEFT JOIN {blocktype_verification_comment} bvc
        ON bi.id = bvc.instance
        WHERE bi.blocktype = 'verification'
        AND bi.view = ? AND bi.id != ?";
        $verblocks = get_records_sql_assoc($sql, array($viewid, $instance->get('id')));
        $unlockcollection = true;
        if ($verblocks) {
            foreach ($verblocks as $b) {
                $verblock = new BlockInstance($b->id);
                $config = $verblock->get('configdata');
                if ($config['lockportfolio'] == 1 && empty($config['addcomment']) && isset($config['verified']) && $config['verified']) {
                    $unlockcollection = false;
                    break;
                }
                if ($config['lockportfolio'] == 1 && $config['addcomment'] == 1) {
                    if ($b->text && empty($b->private)) {
                        $unlockcollection = false;
                    }
                    break;
                }
            }
        }
        return $unlockcollection;
    }
}
