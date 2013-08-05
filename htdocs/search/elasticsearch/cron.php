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
 * @subpackage search-elasticsearch
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2013 Catalyst IT Ltd http://catalyst.net.nz
 *
 * A standalone script to run the elasticsearch cron job, primarily for testing purposes.
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('CRON', 1);
define('TITLE', '');

require(dirname(dirname(dirname(__FILE__))).'/init.php');
require_once(get_config('docroot') . 'artefact/lib.php');
require_once(get_config('docroot') . 'import/lib.php');
require_once(get_config('docroot') . 'export/lib.php');
require_once(get_config('docroot') . 'lib/activity.php');
require_once(get_config('docroot') . 'lib/file.php');
require_once(get_config('docroot') . 'search/lib.php');
require_once(get_config('docroot') . 'search/elasticsearch/lib.php');

raise_memory_limit('256M');
echo "BEGIN\n";
PluginSearchElasticsearch::cron();
echo "END\n";