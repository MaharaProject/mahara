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
define('MENUITEM', 'configsite/networking');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'networking');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
require_once('searchlib.php');
define('TITLE', get_string('networking', 'admin'));

$opensslext = extension_loaded('openssl');
$curlext    = extension_loaded('curl');
$xmlrpcext  = extension_loaded('xmlrpc');


if (!$opensslext || !$curlext || !$xmlrpcext) {
    $smarty = smarty();
    setpageicon($smarty, 'icon-exchange');

    $missingextensions = array();
    !$opensslext && $missingextensions[] = 'openssl';
    !$curlext    && $missingextensions[] = 'curl';
    !$xmlrpcext  && $missingextensions[] = 'xmlrpc';
    $smarty->assign('missingextensions', $missingextensions);
    $smarty->display('admin/site/networking.tpl');
    exit;
}

$openssl = OpenSslRepo::singleton();

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
                'type'         => 'switchbox',
                'title'        => get_string('enablenetworking','admin'),
                'description'  => get_string('enablenetworkingdescription','admin'),
                'defaultvalue' => get_config('enablenetworking'),
            ),
            'promiscuousmode' => array(
                'type'         => 'switchbox',
                'class'        => 'last',
                'title'        => get_string('promiscuousmode','admin'),
                'description'  => get_string('promiscuousmodedescription','admin'),
                'defaultvalue' => get_config('promiscuousmode'),
            ),
            'submitbuttons' => array(
                'type' => 'fieldset',
                'class' => 'btn-group last',
                'elements' => array (
                    'submit' => array(
                        'class' => 'btn-primary text-inline',
                        'name' => 'submit',
                        'type'  => 'button',
                        'usebuttontag' => true,
                        'content' => get_string('savechanges','admin'),
                        'value' => 'submit'
                    ),
                    'deletesubmit' => array(
                        'class' => 'btn-secondary text-inline',
                        'name' => 'submit', // must be called submit so we can access it's value
                        'type'  => 'button',
                        'usebuttontag' => true,
                        'content' => '<span class="icon icon-refresh icon-lg left text-danger" role="presentation" aria-hidden="true"></span> '. get_string('deletekey', 'admin'),
                        'value' => 'deletekey'
                    )
                )
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

    if ($form->get_submitvalue() === 'deletekey') {
        global $SESSION;
        $openssl = OpenSslRepo::singleton();
        $openssl->get_keypair(true);
        $SESSION->add_info_msg(get_string('keydeleted', 'admin'));
        // Using cancel here as a hack to get it to redirect so it shows the new keys
        $form->reply(PIEFORM_CANCEL, array(
            'location'    => get_config('wwwroot') . 'admin/site/networking.php'
        ));
    }

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
        'goto'    => get_config('wwwroot') . 'admin/site/networking.php',
    ));
}

$smarty = smarty();
setpageicon($smarty, 'icon-exchange');
$smarty->assign('networkingform', $networkingform);
$smarty->display('admin/site/networking.tpl');
