<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage export
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/export');
require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('export', 'export'));
require_once('file.php');

$form = pieform(array(
    'name' => 'export',
    'elements' => array(
        'format' => array(
            'type' => 'select',
            'title' => 'Export format',
            'options' => array(
                'leap' => 'LEAP2A',
                'html' => 'HTML',
            ),
            'defaultvalue' => 'html',
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => 'Get export',
        ),
    ),
));


function export_submit(Pieform $form, $values) {
    global $USER;
    safe_require('export', $values['format']);

    $user = new User();
    $user->find_by_id($USER->get('id'));

    $class = 'PluginExport' . ucfirst($values['format']);
    $exporter = new $class($user, EXPORT_ALL_VIEWS, EXPORT_ALL_ARTEFACTS);

    $zipfile = $exporter->export();
    serve_file($exporter->get('exportdir') . $zipfile, $zipfile, 'application/x-zip', array('lifetime' => 0));
    exit;
}

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->display('export/index.tpl');

?>
