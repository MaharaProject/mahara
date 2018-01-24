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
define('ADMIN', 1);
define('MENUITEM', 'configsite/privacy');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'privacy');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('legal', 'admin'));
define('SUBSECTIONHEADING', get_string('privacy', 'admin'));

$versionid = param_integer('id', null);

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

$data = get_records_sql_assoc("
    SELECT  s.id, s.version, u.firstname, u.lastname, u.id AS userid, s.content, s.ctime
    FROM {site_content_version} s
    LEFT JOIN {usr} u ON s.author = u.id
    WHERE s.institution = ?
    ORDER BY s.id DESC", array('mahara'));

if ($versionid) {
    if ($pageoptions = get_record('site_content_version', 'id', $versionid, 'institution', 'mahara')) {
        $form = pieform(array(
            'name'              => 'editsitepage',
            'jsform'            => false,
            'jssuccesscallback' => 'contentSaved',
            'elements'          => array(
                'version' => array(
                    'type'         => 'text',
                    'title'        => get_string('version', 'admin'),
                    'description'  => get_string('lastversion', 'admin', $pageoptions->version),
                    'defaultvalue' => '',
                    'rules' => array(
                        'required'    => true,
                        'maxlength' => 15
                    )
                ),
                'pageinstitution' => array('type' => 'hidden', 'value' => 'mahara'),
                'pagetext' => array(
                    'name'        => 'pagetext',
                    'type'        => 'wysiwyg',
                    'rows'        => 25,
                    'cols'        => 100,
                    'title'       => get_string('pagetext', 'admin'),
                    'defaultvalue' => $pageoptions->content,
                    'rules'       => array(
                        'maxlength' => 65536,
                        'required' => true
                    )
                ),
                'submit' => array(
                    'class' => 'btn-primary',
                    'type'  => 'submitcancel',
                    'value' => array(get_string('savechanges', 'admin'), get_string('cancel')),
                    'goto'  => get_config('wwwroot') . 'admin/site/privacy.php',
                ),
            )
        ));
    }
    else {
        throw new ViewNotFoundException(get_string('siteprivacystatementnotfound', 'error', $versionid));
    }
}

function editsitepage_validate(Pieform $form, $values) {
    // Check if the version entered by the user already exists.
    if (record_exists('site_content_version', 'institution', $values['pageinstitution'], 'version', $values['version'])) {
        $form->set_error('version', get_string('versionalreadyexist', 'admin', $values['version']));
    }
}

function editsitepage_submit(Pieform $form, $values) {
    global $USER, $SESSION;

    $id = get_field('site_content_version', 'id', 'version', $values['version']);
    require_once('embeddedimage.php');
    // Update the pagetext with any embedded image info
    $pagetext = EmbeddedImage::prepare_embedded_images($values['pagetext'], 'staticpages', $id);

    $data = new StdClass;
    $data->content = $pagetext;
    $data->author = $USER->get('id');
    $data->institution = $values['pageinstitution'];
    $data->ctime = db_format_timestamp(time());
    $data->version = $values['version'];
    $data->type = 'privacy';

    try {
        insert_record('site_content_version', $data);
        $SESSION->add_ok_msg(get_string('pagesaved', 'admin'));
    }
    catch (SQLException $e) {
        $SESSION->add_ok_msg(get_string('savefailed', 'admin'));
    }
    redirect(get_config('wwwroot').'admin/site/privacy.php');
}

if ($versionid && $pageoptions) {
    $smarty = smarty(array('adminsitepages'), array(), array('admin' => array('discardpageedits')));
    $smarty->assign('pageeditform', $form);
    $smarty->assign('content', $pageoptions->content);
    $smarty->assign('version', $pageoptions->version);
}
else {
    $smarty = smarty();
}
setpageicon($smarty, 'icon-umbrella');
$smarty->assign('results', $data);
$smarty->assign('latestversion', reset($data)->version);
$smarty->assign('versionid', $versionid);
$smarty->assign('latestprivacyid', reset($data)->id);
$smarty->display('admin/site/privacy.tpl');
