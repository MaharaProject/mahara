<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2012 Catalyst IT
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
 * @subpackage auth-browserid
 * @author     Francois Marier <francois@catalyst.net.nz>
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

$USER = new BrowserIDUser();
$USER->login($_SESSION['browseridemail']);
unset($_SESSION['browseridexpires']);
unset($_SESSION['browseridemail']);
redirect();

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
