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
    case 'profileiconbyid':
    case 'profileicon':
        $id = param_integer('id');
        $size = param_variable('size', '');

        if ($type == 'profileicon') {
            // Convert ID of user to the ID of a profileicon
            $id = get_field('usr', 'profileicon', 'id', $id);
        }

        if ($id) {
            if ($size && !preg_match('/\d+x\d+/', $size)) {
                throw new UserException('Invalid size for image specified');
            }

            if ($size) {
                list($width, $height) = explode('x', $size);
                if ($width > 300 || $height > 300) {
                    throw new UserException('Requested image size is too big');
                }
                if ($width % 5 != 0 || $height % 5 != 0) {
                    throw new UserException('Requested image size must be in multiples of 5 for width and height');
                }
            }

            if ($path = get_dataroot_image_path('artefact/internal/profileicons', $id, $size)) {
                $type = get_mime_type($path);
                if ($type) {
                    header('Content-type: ' . $type);
                    readfile($path);
                    exit;
                }
            }
        }

        header('Content-type: ' . 'image/gif');
        if ($path = theme_get_path('images/no_userphoto' . $size . '.gif')) {
            readfile($path);
            exit;
        }
        readfile(theme_get_path('images/no_userphoto40x40.gif'));
        break;
}

?>
