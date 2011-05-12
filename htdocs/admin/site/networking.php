<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
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
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite/networking');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'networking');

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
    $smarty->display('admin/site/networking.tpl');
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
                'description'  => get_string('wwwrootdescription', 'admin'),
                'value'        => get_config('wwwroot')
            ),
            'pubkey' => array(
                'type'         => 'html',
                'title'        => get_string('publickey','admin'),
                'description'  => get_string('publickeydescription2', 'admin', 365),
                'value'        => '<pre style="font-size: 0.7em; white-space: pre;">'.$openssl->certificate.'</pre>'
            ),
            'sha1fingerprint' => array(
                'type'         => 'html',
                'title'        => 'SHA1 Fingerprint',
                'value'        => $openssl->sha1_fingerprint
            ),
            'md5fingerprint' => array(
                'type'         => 'html',
                'title'        => 'MD5 Fingerprint',
                'value'        => $openssl->md5_fingerprint
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
    $form->reply(PIEFORM_ERR, array(
        'message' => get_string('enablenetworkingfailed','admin'),
        'goto'    => '/admin/site/networking.php',
    ));
}

function networkingform_submit(Pieform $form, $values) {
    $reply = '';

    if (get_config('enablenetworking') != $values['enablenetworking']) {
        if (!set_config('enablenetworking', $values['enablenetworking'])) {
            networkingform_fail($form);
        }
        else {
            if (empty($values['enablenetworking'])) {
                $reply .= get_string('networkingdisabled','admin');
            }
            else {
                $reply .= get_string('networkingenabled','admin');
            }
        }
    }

    if (get_config('promiscuousmode') != $values['promiscuousmode']) {
        if (!set_config('promiscuousmode', $values['promiscuousmode'])) {
            networkingform_fail($form);
        }
        else {
            if (empty($values['promiscuousmode'])) {
                $reply .= get_string('promiscuousmodedisabled','admin');
            }
            else {
                $reply .= get_string('promiscuousmodeenabled','admin');
            }
        }
    }

    $form->reply(PIEFORM_OK, array(
        'message' => ($reply == '') ? get_string('networkingunchanged','admin') : $reply,
        'goto'    => '/admin/site/networking.php',
    ));
}

$smarty = smarty();
$smarty->assign('networkingform', $networkingform);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/site/networking.tpl');
