<?php
/**
 * OAuth v1 Identity Provider component
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @author     Arjan Scherpenisse <arjan@scherpenisse.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * @author Arjan Scherpenisse <arjan@scherpenisse.net>
 *
 * The MIT License
 *
 * Copyright (c) 2007-2008 Mediamatic Lab
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('XMLRPC', 1);
define('TITLE', '');

global $SESSION, $USER;

// Catch anything that goes wrong in init.php
ob_start();
    require(dirname(dirname(__FILE__)) . '/init.php');
    $errors = trim(ob_get_contents());
ob_end_clean();

require_once(dirname(__FILE__) . '/lib.php');

if (!webservice_protocol_is_enabled('oauth')) {
    header("HTTP/1.0 404 Not Found");
    die;
}

// you must use HTTPS as token based auth is a hazzard without it
if (!is_https() && get_config('productionmode')) {
    header("HTTP/1.0 403 Forbidden - HTTPS must be used");
    die;
}

/*
 * Always announce XRDS OAuth discovery
 */
header('X-XRDS-Location: ' . get_config('wwwroot') . 'webservice/oauthv1/services.xrds');

/*
 * Initialize OAuth store
 */
require_once(get_config('docroot') . 'webservice/libs/oauth-php/OAuthServer.php');
require_once(get_config('docroot') . 'webservice/libs/oauth-php/OAuthStore.php');
OAuthStore::instance('Mahara');
global $server;
$server = new OAuthServer();

!isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] = null;

// Now - what kind of OAuth interaction are we handling?
if ($_SERVER['PATH_INFO'] == '/request_token') {
        $server->requestToken();
        exit;
}
else if ($_SERVER['PATH_INFO'] == '/access_token') {
        $server->accessToken();
        exit;
}
else if ($_SERVER['PATH_INFO'] == '/authorize') {
        # logon
        if (!$USER->is_logged_in()) {
            $form = pieform_instance(auth_get_login_form());
            auth_draw_login_page(null, $form);
            exit;

        }
        $rs = null;
        try {
            $rs = $server->authorizeVerify();
        }
        catch (OAuthException2 $e) {
            header('HTTP/1.1 400 Bad Request');
            header('Content-Type: text/plain');

            echo "Failed OAuth Request: " . $e->getMessage();
            exit();
        }
        // XXX user must be logged in
        // display what is accessing and ask the user to confirm
        $form = array(
            'renderer' => 'div',
            'type' => 'div',
            'id' => 'maintable',
            'name' => 'authorise',
            'jsform' => false,
            'successcallback' => 'oauth_authorise_submit',
            'elements' => array(
                                'application_uri' => array(
                                    'title'        => get_string('application_title', 'auth.webservice'),
                                    'value'        =>  '<a href="' . $rs['application_uri'] . '">' . $rs['application_title'] . '</a>',
                                    'type'         => 'html',
                                ),
                                'application_access' => array(
                                    'value'        =>  get_string('oauth_access', 'auth.webservice'),
                                    'type'         => 'html',
                                ),
                                'instructions' => array(
                                    'value'        =>  get_string('oauth_instructions', 'auth.webservice') . "<br/><br/>",
                                    'type'         => 'html',
                                ),
                                'submit' => array(
                                    'type'  => 'submitcancel',
                                    'value' => array(get_string('authorise', 'auth.webservice'), get_string('cancel')),
                                    'goto'  => get_config('wwwroot'),
                                ),
            ),
        );

        $form = pieform($form);
        $smarty = smarty(array(), array('<link rel="stylesheet" type="text/css" href="' . $THEME->get_url('style/webservice.css', false, 'auth/webservice') . '">',));
        $smarty->assign('form', $form);
        $smarty->assign('PAGEHEADING', get_string('authorise', 'auth.webservice'));
        $smarty->display('form.tpl');
        exit;
}
else if ($_SERVER['PATH_INFO'] == '/oob') {
        // display the verifier token
        $verifier = $SESSION->get('oauh_verifier');
        $SESSION->set('oauh_verifier', null);
        $form = array(
            'renderer' => 'div',
            'type' => 'div',
            'id' => 'maintable',
            'name' => 'authorise',
            'jsform' => false,
            'successcallback' => 'oauth_authorise_submit',
            'elements' => array(
                                'instructions' => array(
                                    'title'        => get_string('instructions', 'auth.webservice'),
                                    'value'        =>  get_string('oobinfo', 'auth.webservice'),
                                    'type'         => 'html',
                                ),
                                'verifier' => array(
                                    'title'        => get_string('verifier', 'auth.webservice'),
                                    'value'        =>  '<div id="verifier">' . $verifier . '</div>',
                                    'type'         => 'html',
                                ),
            ),
        );
        $form = pieform($form);
        $smarty = smarty(array(), array('<link rel="stylesheet" type="text/css" href="' . $THEME->get_url('style/webservice.css', false, 'auth/webservice') . '">',));
        $smarty->assign('form', $form);
        $smarty->assign('PAGEHEADING', get_string('oob', 'auth.webservice'));
        $smarty->display('form.tpl');
        exit;
}
else {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: text/plain');
    echo "Unknown request";
}


function oauth_authorise_submit(Pieform $form, $values) {
    global $server, $USER, $SESSION;
    try {
        $server->authorizeVerify();
        $verifier = $server->authorizeFinish(true, $USER->get('id'));
        $SESSION->set('oauh_verifier', $verifier);
        redirect('/webservice/oauthv1.php/oob');
    }
    catch (OAuthException2 $e) {
        header('HTTP/1.1 400 Bad Request');
        header('Content-Type: text/plain');

        echo "Failed OAuth Request: " . $e->getMessage();
    }
    exit;
}
