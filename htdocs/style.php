<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2011 Catalyst IT Ltd and others; see:
 *                    http://wiki.mahara.org/Contributors
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
 * @author     Richard Mansfield
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('NOCHECKREQUIREDFIELDS', 1);
require('init.php');

header('Content-type: text/css');
$maxage = 604800;
header('Expires: '. gmdate('D, d M Y H:i:s', time() + $maxage) .' GMT');
header('Cache-Control: max-age=' . $maxage);
header('Pragma: public');

if ($style = param_integer('id', null)) {
    echo get_field('style', 'css', 'id', $style);
}

perf_to_log();
