<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2012 Catalyst IT Ltd and others; see:
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
 * @subpackage api
 * @author     Alan McNatty <alan@catalyst.net.nz>, Catalyst IT Ltd
 *
 */

defined('INTERNAL') || die();

function mobile_api_json_reply( $arr ) {
    header('Content-type: application/json');
    header('Pragma: no-cache');

    echo json_encode($arr);
    perf_to_log();
    exit;
}
