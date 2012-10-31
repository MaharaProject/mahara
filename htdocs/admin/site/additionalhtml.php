<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2012 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage admin
 * @author     Ruslan Kabalin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2012 Lancaster University
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite/additionalhtml');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'additionalhtml');

require(dirname(dirname(dirname(__FILE__))).'/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('additionalhtml', 'admin'));
define('DEFAULTPAGE', 'additionalhtmlhead');

$additionalhtmlitemnames = site_content_additional_html_items();
$additionalhtmlitems = get_records_select_assoc(
    'site_content',
    'name IN (' . join(',', array_fill(0, count($additionalhtmlitemnames), '?')) . ')',
    $additionalhtmlitemnames
);

$additionalhtmloptions = array();
$additionalhtmlcontent = array();
foreach ($additionalhtmlitemnames as $itemname) {
    $additionalhtmloptions[$itemname] = get_string($additionalhtmlitems[$itemname]->name, 'admin');
    $additionalhtmlcontent[$itemname] = $additionalhtmlitems[$itemname]->content;
}

$form = pieform(array(
    'name' => 'editadditionalhtmlcontent',
    'jsform' => true,
    'jssuccesscallback' => 'contentSaved',
    'elements' => array(
        'contentname' => array(
            'type' => 'select',
            'title' => get_string('additionalhtmllocation', 'admin'),
            'defaultvalue' => DEFAULTPAGE,
            'options' => $additionalhtmloptions
        ),
        'contenthtml' => array(
            'name' => 'contenthtml',
            'type' => 'textarea',
            'rows' => 25,
            'cols' => 100,
            'title' => get_string('additionalhtmlcontent', 'admin'),
            'defaultvalue' => $additionalhtmlcontent[DEFAULTPAGE],
            'rules' => array(
                'maxlength' => 65536,
            )
        ),
        'submit' => array(
            'type'  => 'submit',
            'value' => get_string('savechanges', 'admin')
        ),
    )
));


function editadditionalhtmlcontent_submit(Pieform $form, $values) {
    global $USER;
    $data = new StdClass;
    $data->name    = $values['contentname'];
    $data->content = $values['contenthtml'];
    $data->mtime   = db_format_timestamp(time());
    $data->mauthor = $USER->get('id');
    try {
        update_record('site_content', $data, 'name');
    }
    catch (SQLException $e) {
        $form->reply(PIEFORM_ERR, get_string('savefailed', 'admin'));
    }
    $form->reply(PIEFORM_OK, get_string('additionalhtmlsaved', 'admin'));
}

$smarty = smarty(array('adminsitehtmlcontent'), array(), array('admin' => array('discardcontentedits')));
$smarty->assign('contenteditform', $form);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/site/additionalhtml.tpl');
