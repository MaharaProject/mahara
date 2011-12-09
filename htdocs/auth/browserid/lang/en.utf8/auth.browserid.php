<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2012 Catalyst IT
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
 * @subpackage auth-browserid
 * @author     Francois Marier <francois@catalyst.net.nz>
 */

defined('INTERNAL') || die();

$string['browserid'] = 'BrowserID';
$string['title'] = 'BrowserID';
$string['description'] = 'Authenticate using a BrowserID';

$string['badassertion'] = 'The given BrowserID assertion is not valid: %s';
$string['badverification'] = 'Mahara did not receive valid JSON output from the BrowserID verifier.';
$string['login'] = 'BrowserID Login';
$string['missingassertion'] = 'BrowserID did not return an alpha-numeric assertion.';
