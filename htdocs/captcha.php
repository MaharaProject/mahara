<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
require('init.php');

// Get 5 random letters.
$code    = get_random_key(5);
$angles  = array(40, 0, 340, 20, 310);
$lefts   = array(30, 50, 70, 95, 110);
$bottoms = array(24, 20, 28, 34, 33);

$file  = theme_get_path('images/captcha.png');
$img   = imagecreatefrompng($file);
$black = imagecolorallocate($img, 60, 60, 60);
$ttf   = theme_get_path('captcha.ttf');

$captcha = '';

for ($i = 0; $i < strlen($code); $i++) {
    imagettftext($img, 18, $angles[$i], $lefts[$i], $bottoms[$i], $black, $ttf, $code{$i});
    $captcha .= $code{$i};
}

$SESSION->set('captcha', $captcha);
header('Content-type: image/png');
imagepng($img);

?>
