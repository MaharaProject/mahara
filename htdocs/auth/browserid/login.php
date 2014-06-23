<?php
/**
 *
 * @package    mahara
 * @subpackage auth-browserid
 * @author     Francois Marier <francois@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
require('../../init.php');
safe_require('auth', 'browserid');

if (!session_id()) {
    session_start();
}

if (empty($_SESSION['browseridexpires']) || time() >= $_SESSION['browseridexpires']) {
    $assertion = param_variable('assertion', null);
    if (!$assertion) {
        throw new AuthInstanceException(get_string('missingassertion','auth.browserid'));
    }

    // Send the assertion to the verification service
    $request = array(
        CURLOPT_URL        => PluginAuthBrowserid::BROWSERID_VERIFIER_URL,
        CURLOPT_POST       => 1,
        CURLOPT_POSTFIELDS => 'assertion='.urlencode($assertion).'&audience='.get_audience(),
    );

    $response = mahara_http_request($request);

    if (empty($response->data)) {
        throw new AuthInstanceException(get_string('badverification','auth.browserid'));
    }
    $jsondata = json_decode($response->data);
    if (empty($jsondata)) {
        throw new AuthInstanceException(get_string('badverification','auth.browserid'));
    }

    if ($jsondata->status != 'okay') {
        throw new AuthInstanceException(get_string('badassertion','auth.browserid', htmlspecialchars($jsondata->reason)));
    }
    $_SESSION['browseridexpires'] = $jsondata->expires/1000;
    $_SESSION['browseridemail'] = $jsondata->email;
}

// Not using $USER->get('sesskey') for this because when we printed the browserid setup stuff
// in auth/browserid/lib.php, $USER isn't set up yet.
$sesskey = param_variable('sesskey', false);
if ($sesskey && $sesskey == $SESSION->get('browseridsesskey')) {
    $returnurl = param_variable('returnurl', '/');
    $SESSION->clear('browseridsesskey');
}
else {
    $returnurl = '/';
}

$USER = new BrowserIDUser();
$USER->login($_SESSION['browseridemail']);
unset($_SESSION['browseridexpires']);
unset($_SESSION['browseridemail']);
redirect($returnurl);

function get_audience() {
    $url = parse_url(get_config('wwwroot'));

    if (!isset($url['port']) and 'http' == $url['scheme']) {
        $port = 80;
    }
    else if (!isset($url['port']) and 'https' == $url['scheme']) {
        $port = 443;
    }
    else if (isset($url['port'])) {
        $port = $url['port'];
    }
    else {
        log_debug('Persona: cannot decipher the value of wwwroot');
        return '';
    }
    return $url['scheme'] . '://' .$url['host'] . ':' . $port;
}
