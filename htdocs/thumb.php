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
define('PUBLIC', 1);

require('init.php');
require_once('file.php');

$type = param_alpha('type');

switch ($type) {
    case 'template':
        require_once('template.php');
        $name = param_alpha('name');
        $template = template_locate($name);
        if (isset($template['thumbnail'])) {
            header("Content-type: " . $template['thumbnailcontenttype']);
            readfile($template['thumbnail']);
            exit;
        }

        header('Content-type: ' . 'image/gif');
        readfile(theme_get_image_path('images/no_thumbnail.gif'));
        exit;
        break;

    case 'profileicon':
        $id = param_integer('id');
        $size = param_variable('size', '');
        if ($size && !preg_match('/\d+x\d+/', $size)) {
            throw new UserException('Invalid size for image specified');
        }

        log_debug('looking for image for user, id = ' . $id . ' and size = ' . $size);

        if ($path = get_dataroot_image_path('artefact/internal/profileicons', $id, $size)) {
            $type = get_mime_type($path);
            if ($type) {
                header('Content-type: ' . $type);
                readfile($path);
                exit;
            }
        }
        header('Content-type: ' . 'image/gif');
        readfile(theme_get_image_path('images/no_userphoto40x40.gif'));
        break;
}

?>
