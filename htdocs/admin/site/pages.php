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
define('MENUITEM', 'configsite/sitepages');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'sitepages');

require(dirname(dirname(dirname(__FILE__))).'/init.php');
define('TITLE', get_string('staticpages', 'admin'));
define('DEFAULTPAGE', 'home');

$sitepages = array();
$corepagenames = site_content_pages();
$localpagenames = function_exists('local_site_content_pages') ? local_site_content_pages() : array();
if ($pagenames = array_merge($corepagenames, $localpagenames)) {
    $sitepages = get_records_select_array(
        'site_content', 'name IN (' . join(',', array_fill(0, count($pagenames), '?')) . ')', $pagenames
    );
}

$pageoptions = array();
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
        'pageinstitution' => array('type' => 'hidden', 'value' => 'mahara'),
        'pagename'    => array(
            'type'    => 'select',
            'title'   => get_string('pagename', 'admin'),
            'defaultvalue' => DEFAULTPAGE,
            'options' => $pageoptions
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
            'class' => 'btn-primary',
            'type'  => 'submit',
            'value' => get_string('savechanges', 'admin')
        ),
    )
));


function editsitepage_submit(Pieform $form, $values) {
    global $USER;
    $data = new StdClass;
    $data->name    = $values['pagename'];
    $data->content = $values['pagetext'];
    $data->mtime   = db_format_timestamp(time());
    $data->mauthor = $USER->get('id');
    $data->institution = 'mahara';
    try {
        update_record('site_content', $data, array('name', 'institution'));
    }
    catch (SQLException $e) {
        $form->reply(PIEFORM_ERR, get_string('savefailed', 'admin'));
    }
    $form->reply(PIEFORM_OK, get_string('pagesaved', 'admin'));
}

$smarty = smarty(array('adminsitepages'), array(), array('admin' => array('discardpageedits')));
setpageicon($smarty, 'icon-pencil');

$smarty->assign('pageeditform', $form);
$smarty->display('admin/site/pages.tpl');
