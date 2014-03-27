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
define('MENUITEM', 'manageinstitutions/sitepages');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'institutionstaticpages');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('institutionstaticpages', 'admin'));
require_once('pieforms/pieform.php');
require_once('license.php');
define('DEFAULTPAGE', 'home');
require_once('institution.php');

$sitepages = array();
$corepagenames = site_content_pages();
$localpagenames = function_exists('local_site_content_pages') ? local_site_content_pages() : array();
if ($pagenames = array_merge($corepagenames, $localpagenames)) {
    $sitepages = get_records_select_array(
        'site_content', 'name IN (' . join(',', array_fill(0, count($pagenames), '?')) . ')', $pagenames
    );
}
$pageoptions = array();

$institutionselector = get_institution_selector(false);
if (!empty($institutionselector['options']) && sizeof($institutionselector['options']) > 1) {
    $institutionselector['defaultvalue'] = key($institutionselector['options']);
}
else if (!empty($institutionselector['options']) && sizeof($institutionselector['options']) == 1) {
    // Institutional admins with only 1 institution do not get institution dropdown
    // Same with admins and only one institution exists
    $institutionselector = array('type' => 'hidden',
                                 'value' => key($institutionselector['options']),
                                 'defaultvalue' => key($institutionselector['options']),
                                );
}
else if (empty($institutionselector['options'])) {
    // Only the 'no institution' institution exists so we need to display this fact
    $smarty = smarty(array(), array(), array());
    $smarty->assign('noinstitutionsadmin', (($USER->admin) ? get_string('noinstitutionstaticpagesadmin', 'admin', get_config('wwwroot') . 'admin/site/pages.php') : false));
    $smarty->assign('noinstitutions', get_string('noinstitutionstaticpages', 'admin'));
    $smarty->assign('PAGEHEADING', TITLE);
    $smarty->display('admin/site/pages.tpl');
    exit;
}


foreach ($sitepages as $page) {
    $section = in_array($page->name, $localpagenames) ? 'local' : 'admin';
    $pageoptions[$page->name] = get_string($page->name, $section);
    $pagecontents[$page->name] = $page->content;
}
asort($pageoptions);

$getstring = array('discardpageedits' => json_encode(get_string('discardpageedits', 'admin')));

$form = pieform(array(
    'name'              => 'editsitepage',
    'jsform'            => true,
    'jssuccesscallback' => 'contentSaved',
    'elements'          => array(
        'pageinstitution' => $institutionselector,
        'pagename'    => array(
            'type'    => 'select',
            'title'   => get_string('pagename', 'admin'),
            'defaultvalue' => DEFAULTPAGE,
            'options' => $pageoptions
        ),
        'pageusedefault' => array(
            'type'    => 'checkbox',
            'title'   => get_string('usedefault', 'admin'),
            'description'  => get_string('usedefaultdescription1', 'admin'),
            'defaultvalue' => (get_config_institution($institutionselector['defaultvalue'], 'sitepages_' . DEFAULTPAGE) == 'mahara' ? 1 : 0),
        ),
        'pagetext' => array(
            'name'        => 'pagetext',
            'type'        => 'wysiwyg',
            'rows'        => 25,
            'cols'        => 100,
            'title'       => get_string('pagetext', 'admin'),
            'defaultvalue' => $pagecontents[DEFAULTPAGE],
            'rules'       => array(
                'maxlength' => 65536,
                'required' => true
            )
        ),
        'submit' => array(
            'type'  => 'submit',
            'value' => get_string('savechanges', 'admin')
        ),
    )
));

function editsitepage_validate(Pieform $form, $values) {
    $allowedinstitutions = get_institution_selector(false);
    if (array_search($values['pageinstitution'], array_flip($allowedinstitutions['options'])) === false) {
        $form->set_error(null, get_string('staticpageinstitutionbad', 'admin', $values['pageinstitution']));
    }
}

function editsitepage_submit(Pieform $form, $values) {
    global $USER;
    $data = new StdClass;
    $data->name    = $values['pagename'];
    if (empty($values['pageusedefault'])) {
        $data->content = $values['pagetext'];
    }
    $data->mtime   = db_format_timestamp(time());
    $data->mauthor = $USER->get('id');
    $data->institution = $values['pageinstitution'];
    // update the institution config if needed
    if (isset($values['pageusedefault'])) {
        $configdata = new StdClass;
        $configdata->institution = $data->institution;
        $configdata->field = 'sitepages_' . $data->name;
        $whereobj = clone $configdata;
        $configdata->value = !empty($values['pageusedefault']) ? 'mahara' : $data->institution;
        ensure_record_exists('institution_config', $whereobj, $configdata);
    }
    if (get_record('site_content', 'name', $data->name, 'institution', $data->institution)) {
        try {
            update_record('site_content', $data, array('name', 'institution'));
        }
        catch (SQLException $e) {
            $form->reply(PIEFORM_ERR, get_string('savefailed', 'admin'));
        }
    }
    else {
        // local site page doesn't exist for this institution so we shall add it
        $data->ctime = db_format_timestamp(time());
        try {
            insert_record('site_content', $data);
        }
        catch (SQLException $e) {
            $form->reply(PIEFORM_ERR, get_string('savefailed', 'admin'));
        }
    }
    $form->reply(PIEFORM_OK, get_string('pagesaved', 'admin'));
}

$smarty = smarty(array('adminsitepages'), array(), array('admin' => array('discardpageedits')));
$smarty->assign('noinstitutionsadmin', (($USER->admin) ? get_string('noinstitutionstaticpagesadmin', 'admin', get_config('wwwroot') . 'admin/site/pages.php') : false));
$smarty->assign('pageeditform', $form);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/site/pages.tpl');
