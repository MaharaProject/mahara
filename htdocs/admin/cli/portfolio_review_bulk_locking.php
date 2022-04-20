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
require_once(get_config('libroot') . 'cli.php');
require_once(get_config('docroot') . 'local/lib/cron.php');

$cli = get_cli();

$options = array();
$options['dryrun'] = (object) array(
        'shortoptions' => array('d'),
        'description' => get_string('cli_param_dryrun', 'admin'),
        'required' => false,
        'defaultvalue' => true,
);
$options['locking'] = (object) array(
        'shortoptions' => array('l'),
        'description' => get_string('cli_locking', 'admin'),
        'required' => true,
        'defaultvalue' => 'unlock',
);
$options['collections'] = (object) array(
        'shortoptions' => array('c'),
        'description' => get_string('cli_locking_collections', 'admin'),
        'required' => true,
        'defaultvalue' => 0,
        'multiple' => true,
);

$settings = (object) array(
        'info' => get_string('cli_locking_info', 'admin'),
        'options' => $options,
);
$cli->setup($settings);

$dryrun = $cli->get_cli_param_boolean('dryrun');
$collections = $cli->get_cli_param('collections');
$lockstate = $cli->get_cli_param('locking');
$verbose = $cli->get_cli_param('verbose');

$collectionids = array();
if (is_array($collections)) {
    foreach ($collections as $collection) {
        $collid = intval($collection);
        if ($collid > 0) {
            $collectionids[] = $collid;
        }
        else {
            $cli->cli_print(get_string('cli_locking_collection_bad_id', 'admin', $collection));
        }
    }
}
else if (intval($collections) > 0) {
    $collectionids[] = intval($collections);
}
else if (is_string($collections)) {
    if (is_readable($collections)) {
        // Have we been given a path to a CSV file we can access
        // Check that it is a CSV file and has a column called 'collection'
        $csv = array_map('str_getcsv', file($collections));
        array_walk($csv, function(&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        // Remove the column headers and check for 'collection'
        $headers = array_shift($csv);
        if (!in_array('collectionid', $headers)) {
            $cli->cli_exit(get_string('cli_locking_collection_header_error', 'admin') . $collections);
        }
        foreach ($csv as $k => $v) {
            $id = (int)$csv[$k]['collectionid'];
            if ($id > 0) {
                $collectionids[] = $id;
            }
            else {
                $cli->cli_print(get_string('cli_locking_collection_bad_id', 'admin', $csv[$k]['collectionid']));
            }
        }
    }
    else {
        $cli->cli_exit(get_string('cli_locking_file_path_error', 'admin') . $collections);
    }
}

$cli->cli_print(get_string('cli_locking_portfolio_number', 'admin', $lockstate, count($collectionids)));
if ($verbose) {
    $cli->cli_print(get_string('cli_locking_collection_ids', 'admin') . implode(', ', $collectionids));
}
if ($dryrun) {
    $cli->cli_exit(get_string('cli_locking_onlydryrun', 'admin'));
}
if ($lockstate != 'unlock' && $lockstate != 'lock') {
    $cli->cli_exit(get_string('cli_locking_lockstate_error', 'admin'));
}

$done = array();
foreach ($collectionids as $ck => $cid) {
    // As the collections are locked on verification we need to
    // find the progress page within the collection
    if ($vid = get_field_sql("
        SELECT cv.view FROM {collection_view} cv
        JOIN {view} v ON v.id = cv.view
        WHERE cv.collection = ?
        AND v.type = ?", array($cid, 'progress'))) {
        // then find the id of the verification block that isn't a 'fill in comment' one
        if ($blockids = get_column_sql("
            SELECT id FROM {block_instance}
            WHERE view = ?
            AND blocktype = ?", array($vid, 'verification'))) {
            foreach ($blockids as $blockid) {
                if ($lockstate == 'unlock') {
                    $bi = new BlockInstance($blockid);
                    $configdata = $bi->get('configdata');
                    if ($configdata['addcomment']) {
                        execute_sql("DELETE FROM {blocktype_verification_comment} WHERE instance = ?", array($blockid));
                    }
                    else {
                        unset($configdata['verified']);
                        unset($configdata['verifieddate']);
                        unset($configdata['verifierid']);
                        $bi->set('configdata', $configdata);
                        $bi->commit();
                    }
                    execute_sql("UPDATE {collection} SET lock = ? WHERE id = ?", array(0, $cid));
                    $done[$cid] = get_string('cli_locking_collection_id', 'admin') . $cid . get_string('cli_locking_unlocked', 'admin');
                }
                else if ($lockstate == 'lock') {
                    execute_sql("UPDATE {collection} SET lock = ? WHERE id = ?", array(1, $cid));
                    $done[$cid] = get_string('cli_locking_collection_id', 'admin') . $cid . get_string('cli_locking_locked', 'admin');
                }
            }
        }
        else {
            $cli->cli_print(get_string('cli_locking_collection_id', 'admin') . $cid . get_string('cli_locking_no_review_block', 'admin'));
        }
    }
    else {
        $cli->cli_print(get_string('cli_locking_collection_id', 'admin') . $cid . get_string('cli_locking_no_portfolio_completion', 'admin'));
    }
}

foreach ($done as $donestr) {
    $cli->cli_print($donestr);
}

$cli->cli_exit(get_string('done'));
