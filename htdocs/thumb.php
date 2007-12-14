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
        $size = get_imagesize_parameters();

        if ($type == 'profileicon') {
            // Convert ID of user to the ID of a profileicon
            $id = get_field('usr', 'profileicon', 'id', $id);
        }

        if ($id) {
            if ($path = get_dataroot_image_path('artefact/internal/profileicons', $id, $size)) {
                $type = get_mime_type($path);
                if ($type) {
                    header('Content-type: ' . $type);
                    readfile($path);
                    exit;
                }
            }
        }

        // We couldn't find an image for this user. Attempt to use the 'no user 
        // photo' image for the current theme
        //
        // NOTE: the institutional admin branch allows the theme to be locked 
        // down. This means that $USER->get('theme') should be used here 
        // instead, when that branch is merged. And don't forget to change it 
        // below at the other get_config('theme') call!
        if ($path = get_dataroot_image_path('artefact/internal/profileicons/no_userphoto/' . get_config('theme'), 0, $size)) {
            header('Content-type: ' . 'image/png');
            readfile($path);
            exit;
        }

        // If we couldn't find the no user photo picture, we put it into 
        // dataroot if we can
        $nouserphotopic = theme_get_path('images/no_userphoto.png');
        if ($nouserphotopic) {
            // Move the file into the correct place.
            $directory = get_config('dataroot') . 'artefact/internal/profileicons/no_userphoto/' . get_config('theme') . '/originals/0/';
            check_dir_exists($directory);
            copy($nouserphotopic, $directory . '0');
            header('Content-type: ' . 'image/png');
            readfile($directory . '0');
            exit;
        }


        // Emergency fallback
        header('Content-type: ' . 'image/png');
        readfile(theme_get_path('images/no_userphoto.png'));
        exit;
        break;

    case 'blocktype':
        $bt = param_alpha('bt'); // blocktype
        $ap = param_alpha('ap', null); // artefact plugin (optional)
        
        $basepath = 'blocktype/' . $bt;
        if (!empty($ap)) {
            $basepath = 'artefact/' . $ap . '/' . $basepath;
        }
        header('Content-type: image/png');
        $path = get_config('docroot') . $basepath . '/thumb.png';
        if (is_readable($path)) {
            readfile($path);
            exit;
        }
        readfile(theme_get_path('images/no_thumbnail.png'));
        break;
    case 'viewlayout':
        header('Content-type: image/png');
        $vl = param_integer('vl');
        if ($widths = get_field('view_layout', 'widths', 'id', $vl)) {
            if ($path = theme_get_path('images/vl-' . str_replace(',', '-', $widths) . '.png')) {
                readfile($path);
                exit;
            }
        }
        readfile(theme_get_path('images/no_thumbnail.png'));
        break;
}

?>
