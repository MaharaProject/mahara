<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('CLI', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . 'auth/lib.php');
require(get_config('libroot') . 'cli.php');

$cli = get_cli();

$options = array();

$settings = (object) array(
        'info' => get_string('cli_fast_index', 'admin'),
        'options' => $options,
);
$cli->setup($settings);

// First check that there isn't an elasticsearch cron indexing the site
if (get_record('config', 'field', '_cron_lock_search_elasticsearch_cron')) {
    $cli->cli_exit(get_string('indexingrunning', 'search.elasticsearch'), true);
}

// Set the elasticsearch cron nextrun to null
if (!update_record('search_cron', array('nextrun' => NULL), array('plugin' => 'elasticsearch', 'callfunction' => 'cron'))) {
    $cli->cli_exit(get_string('cli_unabletoupdatecron', 'admin'), true);
}

if ($total = count_records_sql("SELECT COUNT(*) FROM {search_elasticsearch_queue} WHERE status != ?", array(2))) {
    $chunk = get_config_plugin('search', 'elasticsearch', 'cronlimit');
    $runs = ceil($total/$chunk);
    $cli->cli_print('count: ' . $total, true);
    $cli->cli_print('limit: ' . $chunk, true);
    $cli->cli_print('runs: ' . $runs, true);
    if ($runs > 1) {
        // only go fast if it's worth it - because we've reset the nextrun it
        // will finish reindexing within 1 minute anyway.
        $path = 'php ' . get_config('docroot') . 'lib/cron.php';
        $cli->cli_print('path: ' . $path);
        while ($runs > 0) {
            passthru($path, $ret);
            if ($ret !==0) {
                $cli->cli_exit(get_string('cli_problemindexing', 'admin'), true);
            }
            $runs--;
            update_record('search_cron', array('nextrun' => NULL), array('plugin' => 'elasticsearch', 'callfunction' => 'cron'));
            $cli->cli_print('runs: ' . $runs, true);
        }
    }
}
$cli->cli_exit(get_string('cli_done', 'admin'), true);
