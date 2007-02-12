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
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
define('PUBLIC', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

json_headers();

$plugintype = param_alpha('plugintype');
$pluginname = param_alpha('pluginname');
$page       = param_variable('page', null);
$section    = param_variable('section', null);
$form       = param_alphanum('form', null);
$element    = param_alphanum('element', null);

$location = get_config('docroot') ;
$file = 'help/';

if ($plugintype != 'core') {
    $location .= $plugintype . '/' . $pluginname . '/lang/';
}
else {
    $location .= 'lang/';
}
if ($page) {
    $page = str_replace('-', '/', $page);
    $file .= 'pages/' . $page . '.html';
}
else if ($section) {
    $file .= 'sections/' . $section . '.html';
}
else if (!empty($form) && !empty($element)) {
    $file .= 'forms/' . $form . '.' . $element . '.html';
}
else if (!empty($form) && empty($element)) {
    $file .= 'forms/' . $form . '.html';
}
else {
    if ($page) {
        json_reply(true, get_string('nohelpfoundpage'));
    }
    json_reply(true, get_string('nohelpfound'));
}

// now we have to try and locate the help file
$lang = current_language();
if ($lang == 'en.utf8') {
    $trieden = true;
}
else {
    $trieden = false;
}

// try the current language
$langfile = $location . $lang . '/' . $file;
if (is_readable($langfile)) {
    $data = file_get_contents($langfile);
}

// if it's not found, try the parent language if there is one...
if (empty($data) && empty($trieden)) {
    $langfile = $location . $lang . '/langconfig.php';
    if ($parentlang = get_string_from_file('parentlanguage', $langfile)) {
        if ($parentlang == 'en.utf8') {
            $trieden = true;
        }
        $langfile = $location . $parentlang . '/' . $file;
        if (is_readable($langfile)) {
            $data = file_get_contents($langfile);
        }
    }
}

// if it's STILL not found, and we haven't already tried english ...
if (empty($data) && empty($trieden)) {
    $langfile = $location .  'en.utf8/' . $file;
    if (is_readable($langfile)) {
        $data = file_get_contents($langfile);
    }
}

if (empty($data)) {
    json_reply(true, get_string('nohelpfound'));
}

$json = array('error' => false, 'content' => $data);
echo json_encode($json);
exit;


?>
