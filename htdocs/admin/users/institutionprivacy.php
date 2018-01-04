<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Stacey Walker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('privacy', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'institutionprivacy');
define('MENUITEM', 'manageinstitutions/privacy');
require_once('institution.php');
$versionid = param_integer('id', null);

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

$institutionelement = get_institution_selector(false);

if (empty($institutionelement)) {
    $smarty = smarty();
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

$institution = param_alphanum('institution', null);
if (!$institution || !$USER->can_edit_institution($institution)) {
    $institution = empty($institutionelement['value']) ? $institutionelement['defaultvalue'] : $institutionelement['value'];
}
else if (!empty($institution)) {
    $institutionelement['defaultvalue'] = $institution;
}
$institutionselector = pieform(array(
    'name' => 'usertypeselect',
    'class' => 'form-inline',
    'elements' => array(
        'institution' => $institutionelement,
    )
));
$wwwroot = get_config('wwwroot');

// The "Add one" link displayed when an institution has no privay statement of its own.
$href = $wwwroot . 'admin/users/institutionprivacy.php?institution=' . $institution . '&id=0';

$privacies = get_records_sql_assoc("
    SELECT  s.id, s.version, u.firstname, u.lastname, u.id AS userid, s.content, s.ctime
    FROM {site_content_version} s
    LEFT JOIN {usr} u ON s.author = u.id
    WHERE s.institution = ?
    ORDER BY s.id DESC", array($institution));

$form = false;
if ($versionid !== null) {
    $pageoptions = get_record('site_content_version', 'id', $versionid, 'institution', $institution);
    if ($versionid === 0 || $pageoptions) {
        $form = pieform(array(
            'name'              => 'editsitepage',
            'jsform'            => false,
            'jssuccesscallback' => 'contentSaved',
            'elements'          => array(
                'version' => array(
                    'type'         => 'text',
                    'title'        => get_string('version', 'admin'),
                    'description'  => $pageoptions ? get_string('lastversion', 'admin', $pageoptions->version) : '',
                    'defaultvalue' => '',
                    'rules' => array(
                        'required'    => true,
                        'maxlength' => 15
                    )
                ),
                'pageinstitution' => array('type' => 'hidden', 'value' => $institution),
                'pagetext' => array(
                    'name'        => 'pagetext',
                    'type'        => 'wysiwyg',
                    'rows'        => 25,
                    'cols'        => 100,
                    'title'       => get_string('pagetext', 'admin'),
                    'defaultvalue' => $pageoptions ? $pageoptions->content : '',
                    'rules'       => array(
                        'maxlength' => 65536,
                        'required' => true
                    )
                ),
                'submit' => array(
                    'class' => 'btn-primary',
                    'type'  => 'submit',
                    'value' => get_string('savechanges', 'admin')
                ),
            )
        ));
    }
    else {
        throw new ViewNotFoundException(get_string('institutionprivacystatementnotfound', 'error', $institutionelement['options'][$institution], $versionid));
    }
}

function editsitepage_validate(Pieform $form, $values) {
    // Check if the version entered by the user already exists
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
    redirect(get_config('wwwroot').'admin/users/institutionprivacy.php?institution=' . $values['pageinstitution']);
}

// Site privacy to display in an expandable panel
$siteprivacycontent = get_record_sql("
    SELECT s.content, s.ctime
    FROM {site_content_version} s
    WHERE s.institution = ?
    ORDER BY s.id DESC
    LIMIT 1", array('mahara'));

$js = <<< EOF
jQuery(function($) {
  function reloadUsers() {
      window.location.href = '{$wwwroot}admin/users/institutionprivacy.php?institution=' + $('#usertypeselect_institution').val();
  }

  $('#usertypeselect_institution').on('change', reloadUsers);
});
EOF;

$smarty = smarty();
setpageicon($smarty, 'icon-umbrella');

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('href', $href);
$smarty->assign('siteprivacycontent', $siteprivacycontent);
$smarty->assign('lastupdated', get_string('lastupdatedon', 'blocktype.externalfeed', format_date(strtotime($siteprivacycontent->ctime))));
$smarty->assign('versionid', $versionid);
$smarty->assign('privacies', $privacies);
$smarty->assign('pageeditform', $form);
$smarty->assign('institution', $institution);
$smarty->assign('latestversion', $privacies ? reset($privacies)->version : 0);
$smarty->assign('latestprivacyid', $privacies ? reset($privacies)->id : 0);
$smarty->assign('version', $versionid && $pageoptions ? $pageoptions->version : '');
$smarty->assign('institutionselector', $institutionselector);
$smarty->display('admin/users/institutionprivacy.tpl');
