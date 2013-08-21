<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2013 Catalyst IT Ltd and others; see:
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
 * @subpackage blocktype-pdf
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2013 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

/**
 * This displays a pdf in an <iframe>
 *
 */
define('INTERNAL', 1);
define('PUBLIC', 1);
require(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/init.php');

$fileid = param_integer('file');
$viewid = param_integer('view');

if (!artefact_in_view($fileid, $viewid)) {
    throw new AccessDeniedException('');
}

if (!can_view_view($viewid)) {
    throw new AccessDeniedException('');
}

$file = artefact_instance_from_id($fileid);
if (!($file instanceof ArtefactTypeFile)) {
    throw new NotFoundException();
}

$smarty = smarty();
$smarty->assign('url', get_config('wwwroot') . 'artefact/file/download.php?file=' . $fileid);
$smarty->assign('title', $file->get('title'));
$smarty->display('blocktype:pdf:pdf.tpl');
