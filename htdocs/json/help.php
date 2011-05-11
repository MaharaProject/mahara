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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
define('PUBLIC', 1);
define('NOSESSKEY', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

json_headers();

$plugintype = param_alpha('plugintype');
$pluginname = param_alpha('pluginname');
$page       = param_alphanumext('page', null);
$section    = param_alphanumext('section', null);
$form       = param_alphanumext('form', null);
$element    = param_alphanumext('element', null);

$data = get_helpfile($plugintype, $pluginname, $form, $element, $page, $section);

if (empty($data)) {
    json_reply('local', get_string('nohelpfound'));
}

$json = array('error' => false, 'content' => $data);
json_reply(false, $json);
