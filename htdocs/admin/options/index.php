<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage admin
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL',1);
define('ADMIN',1);
define('MENUITEM','siteoptions');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('form.php');

$langoptions = get_languages();
$themeoptions = get_themes();
$yesno = array(true  => get_string('yes'),
               false => get_string('no'));

$siteoptionform = form(array(
    'name'     => 'siteoptions',
    'method'   => 'post',
    'action'   => '',
    'ajaxpost' => true,
    'elements' => array(
        'sitename' => array(
            'type'         => 'text',
            'title'        => get_string('sitename'),
            'description'  => get_string('sitenamedescription'),
            'defaultvalue' => get_config('sitename'),
        ),
        'language' => array(
            'type'         => 'select',
            'title'        => get_string('language'),
            'description'  => get_string('sitelanguagedescription'),
            'defaultvalue' => get_config('language'),
            'options'      => $langoptions,
        ),
        'theme' => array(
            'type'         => 'select',
            'title'        => get_string('theme'),
            'description'  => get_string('sitethemedescription'),
            'defaultvalue' => get_config('theme'),
            'options'      => $themeoptions,
        ),
        'viruschecking' => array(
            'type'         => 'checkbox',
            'title'        => get_string('viruschecking'),
            'description'  => get_string('viruscheckingdescription'),
            'defaultvalue' => get_config('viruschecking'),
        ),
        'pathtoclam' => array(
            'type'         => 'text',
            'title'        => get_string('pathtoclam'),
            'description'  => get_string('pathtoclamdescription'),
            'defaultvalue' => get_config('pathtoclam'),
        ),
        'sessionlifetime' => array(
            'type'         => 'text',
            'size'         => 4,
            'title'        => get_string('sessionlifetime'),
            'description'  => get_string('sessionlifetimedescription'),
            'defaultvalue' => get_config('session_timeout') / 60,
        ),
        'allowpublicviews' => array(
            'type'         => 'select',
            'title'        => get_string('allowpublicviews'),
            'description'  => get_string('allowpublicviewsdescription'),
            'defaultvalue' => get_config('allowpublicviews'),
            'options'      => $yesno,
        ),
        'artefactviewinactivitytime' => array(
            'type'         => 'expiry',
            'title'        => get_string('artefactviewinactivitytime'),
            'description'  => get_string('artefactviewinactivitytimedescription'),
            'defaultvalue' => get_config('artefactviewinactivitytime'),
        ),
        'contactaddress' => array(
            'type'         => 'text',
            'title'        => get_string('contactaddress'),
            'description'  => get_string('contactaddressdescription'),
            'defaultvalue' => get_config('contactaddress'),
            'rules'        => array(
                'email' => true
            )
        ),
        'submit' => array(
            'type'  => 'submit',
            'value' => get_string('updatesiteoptions')
        ),
    )
));

function siteoptions_fail($field) {
    json_reply('local', get_string('setsiteoptionsfailed', get_string($field)));
}

function siteoptions_submit($values) {
    $fields = array('sitename','language','theme','pathtoclam',
                    'allowpublicviews','artefactviewinactivitytime',
                    'contactaddress');
    foreach ($fields as $field) {
        if (!set_config($field, $values[$field])) {
            siteoptions_fail($field);
        }
    }
    // submitted sessionlifetime is in minutes; db entry session_timeout is in seconds
    if (!set_config('session_timeout', $values['sessionlifetime'] * 60)) {
        siteoptions_fail('sessionlifetime');
    }
    // Submitted value is on/off; database entry should be 1/0
    if (!set_config('viruschecking', (int) ($values['viruschecking'] == 'on'))) {
        siteoptions_fail('viruschecking');
    }
    json_reply(false, get_string('siteoptionsset'));
}

$smarty = smarty(array(),array(),array('siteoptionsset','setsiteoptionsfailed'));
$smarty->assign('SITEOPTIONFORM',$siteoptionform);
$smarty->display('admin/options/index.tpl');

?>
