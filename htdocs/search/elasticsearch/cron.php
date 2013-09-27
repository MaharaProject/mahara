<?php
/**
 *
 * @package    mahara
 * @subpackage search-elasticsearch
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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