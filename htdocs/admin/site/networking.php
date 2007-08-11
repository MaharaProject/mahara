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
 * @author     Donal McMullan <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite/networking');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
require_once('pieforms/pieform.php');
require_once('searchlib.php');
define('TITLE', get_string('networking', 'admin'));

$opensslext = extension_loaded('openssl');
$curlext    = extension_loaded('curl');
$xmlrpcext  = extension_loaded('xmlrpc');

if (!$opensslext || !$curlext || !$xmlrpcext) {
    $smarty = smarty();
    $missingextensions = array();
    !$opensslext && $missingextensions[] = 'openssl';
    !$curlext    && $missingextensions[] = 'curl';
    !$xmlrpcext  && $missingextensions[] = 'xmlrpc';
    $smarty->assign('missingextensions', $missingextensions);
    $smarty->display('admin/networking.tpl');
    exit;
}

$openssl = OpenSslRepo::singleton();

$yesno = array(true  => get_string('yes'),
               false => get_string('no'));

$networkingform = pieform(
    array(
        'name'     => 'networkingform',
        'jsform'   => true,
        'elements' => array(
            'wwwroot' => array(
                'type'         => 'html',
                'title'        => get_string('wwwroot','admin'),
                'value'        => get_config('wwwroot')
            ),
            'pubkey' => array(
                'type'         => 'html',
                'title'        => get_string('publickey','admin'),
                'description'  => get_string('publickeydescription', 'admin'),
                'value'        => '<pre style="font-size: 0.7em">'.$openssl->certificate.'</pre>'
            ),
            'expires' => array(
                'type'         => 'html',
                'title'        => get_string('publickeyexpires','admin'),
                'value'        => format_date($openssl->expires)
            ),
            'enablenetworking' => array(
                'type'         => 'select',
                'title'        => get_string('enablenetworking','admin'),
                'description'  => get_string('enablenetworkingdescription','admin'),
                'defaultvalue' => get_config('enablenetworking'),
                'options'      => $yesno,
            ),
            'promiscuousmode' => array(
                'type'         => 'select',
                'title'        => get_string('promiscuousmode','admin'),
                'description'  => get_string('promiscuousmodedescription','admin'),
                'defaultvalue' => get_config('promiscuousmode'),
                'options'      => $yesno,
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('savechanges','admin')
            )
        )
    )
);

function networkingform_fail(Pieform $form) {
    $form->json_reply(PIEFORM_ERR, get_string('enablenetworkingfailed','admin'));
}

function networkingform_submit(Pieform $form, $values) {

    $reply = '';

    if (get_config('enablenetworking') != $values['enablenetworking']) {
        if (!set_config('enablenetworking', $values['enablenetworking'])) {
            networkingform_fail($form);
        } else {
            if (empty($values['enablenetworking'])) {
                $reply .= get_string('networkingdisabled','admin');
            } else {
                $reply .= get_string('networkingenabled','admin');
            }
        }
    }

    if (get_config('promiscuousmode') != $values['promiscuousmode']) {
        if (!set_config('promiscuousmode', $values['promiscuousmode'])) {
            networkingform_fail($form);
        } else {
            if (empty($values['promiscuousmode'])) {
                $reply .= get_string('promiscuousmodedisabled','admin');
            } else {
                $reply .= get_string('promiscuousmodeenabled','admin');
            }
        }
    }

    $form->json_reply(PIEFORM_OK, ($reply == '') ? get_string('networkingunchanged','admin') : $reply);
}

$smarty = smarty();
$smarty->assign('NETWORKINGFORM',   $networkingform);

$smarty->display('admin/networking.tpl');
?>
