<?php
/**
 * Export users portfolios.
 *
 * @package    mahara
 * @subpackage core
 * @author     Heena Agheda <heenaagheda@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('CLI', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require(get_config('libroot') . 'cli.php');

$cli = get_cli();

$options = array();
$options['dryrun'] = (object) array(
        'shortoptions' => array('d'),
        'description' => get_string('cli_param_dryrun_export', 'admin'),
        'required' => false,
        'defaultvalue' => true,
);
$options['filepath'] = (object) array(
        'shortoptions' => array('f'),
        'description' => get_string('cli_portfolio_export_filepath', 'admin'),
        'required' => false,
);
$options['exportformat'] = (object) array(
        'shortoptions' => array('e'),
        'description' => get_string('cli_portfolio_export_format', 'admin'),
        'required' => false,
        'defaultvalue' => 'html',
);
$options['offset'] = (object) array(
        'shortoptions' => array('o'),
        'description' => get_string('cli_portfolio_export_offset', 'admin'),
        'required' => false,
        'defaultvalue' => '0',
);
$options['limit'] = (object) array(
        'shortoptions' => array('l'),
        'description' => get_string('cli_portfolio_export_limit', 'admin'),
        'required' => false,
        'defaultvalue' => '1000',
);
$options['filterkey'] = (object) array(
        'shortoptions' => array('fk'),
        'description' => get_string('cli_portfolio_export_filterkey', 'admin'),
        'required' => false,
        'defaultvalue' => '',
        'examplevalue' => 'lastname',
);
$options['filtervalue'] = (object) array(
        'shortoptions' => array('fv'),
        'description' => get_string('cli_portfolio_export_filtervalue', 'admin'),
        'required' => false,
        'defaultvalue' => '',
        'examplevalue' => 'a',
);


$settings = (object) array(
    'info' => get_string('cli_portfolio_export_info', 'admin'),
    'options' => $options,
);
$cli->setup($settings);
$dryrun = $cli->get_cli_param_boolean('dryrun');
// Retrieve & validate the filepath
$allusers = false;
$filepath = $cli->get_cli_param('filepath');
if ($filepath) {
    if (!file_exists($filepath)) {
        $cli->cli_exit(get_string('cli_portfolio_export_filenotfound', 'admin'));
    }
}
else {
    $allusers = true;
}
// Retrieve & validate the exportformat
$exportformat = $cli->get_cli_param('exportformat');
if (!$exportformat) {
    $exportformat = 'html';
}
$allowedformats = array('html', 'leap', 'pdf');
if (!in_array($exportformat, $allowedformats)) {
    $cli->cli_exit(get_string('cli_portfolio_export_invalidformat', 'admin'));
}

// Set offset
$offset = $cli->get_cli_param('offset');
if (!$offset || $offset < 0) {
    $offset = 0;
}
// Set limit
$haslimit = true;
$limit = $cli->get_cli_param('limit');
if (!$limit || $limit < 0) {
    $haslimit = false;
}

$filterkey = $cli->get_cli_param('filterkey');
$filterval = $cli->get_cli_param('filtervalue');
$hasfilter = false;
if ($filterkey && $filterval) {
    $hasfilter = true;
}
$allowedfilters = array('firstname', 'lastname', 'email', 'username');
if ($hasfilter && !in_array($filterkey, $allowedfilters)) {
    $cli->cli_exit(get_string('cli_portfolio_export_invalidfilter', 'admin'));
}

$usernames = array();
if ($allusers) {
    // We want to only deal with the people
    // - that have logged in (account not just auto-generated)
    // - have created at least one page
    $sql = "SELECT username
            FROM {usr} u
            WHERE u.deleted = 0
            AND u.active = 1
            AND u.lastlogin IS NOT NULL
            AND EXISTS (
                SELECT 1 FROM {view} v
                WHERE v.owner = u.id
                AND v.type NOT IN ('dashboard', 'profile')
            )";
    $whereparams = array();
    if ($hasfilter) {
        $sql .= ' AND u.' . $filterkey . ' ILIKE ? ';
        $whereparams[] = $filterval . '%';
    }
    $sql .= ' ORDER BY u.lastname ASC';

    if ($haslimit && $hasfilter) {
        $cli->cli_print(get_string('cli_portfolio_export_infolimitfilter', 'admin', $limit, $filterkey, $filterval));
    }
    else if ($haslimit && !$hasfilter) {
        $cli->cli_print(get_string('cli_portfolio_export_infolimit', 'admin', $limit));
    }
    else if ($hasfilter) {
        $cli->cli_print(get_string('cli_portfolio_export_infofilter', 'admin', $filterkey, $filterval));
    }
    else {
        $cli->cli_print(get_string('cli_portfolio_export_infoout', 'admin'));
    }

    $sqllimit = ($haslimit) ? $limit : null;
    $sqloffset = ($haslimit) ? $offset : null;
    $alluserstoexport = get_records_sql_array($sql, $whereparams, $sqloffset, $sqllimit);
    if (is_array($alluserstoexport) && count($alluserstoexport)) {
        foreach ($alluserstoexport as $row) {
            $usernames[] = $row->username;
        }
    }
}
else {
    if (($handle = fopen($filepath, "r")) !== false) {
        $cli->cli_print(get_string('cli_portfolio_export_fromcsv', 'admin'));
        while (($data = fgetcsv($handle, 0, ",")) !== false) {
            $usernames[] = $data[0];
        }
        fclose($handle);
    }
}

if (count($usernames)) {
    if ($dryrun) {
        $viewsql = 'SELECT count(*) FROM {view} v
                    JOIN {usr} u ON u.id = v.owner
                    WHERE u.active = 1 AND u.username IN (' . join(',', array_fill(0, count($usernames), '?')) . ')';
        $viewcount = count_records_sql($viewsql, $usernames);
        $cli->cli_exit(get_string('cli_portfolio_export_users', 'admin', count($usernames), $viewcount, $exportformat));
    }
    else {
        $starttime = microtime(true);
        $cli->cli_print("--- " . date('Y-m-d H:i:s', time()) . " ---");
        bulkexport($usernames, $exportformat, $allusers);
        $cli->cli_print("--- " . date('Y-m-d H:i:s', time()) . " ---");
        $endtime = microtime(true);
        $duration = round($endtime - $starttime, 2);
        $elapsed = sprintf('%dh %dm %ds', (round($duration)/3600), (round($duration)/60%60), $duration%60);
        $cli->cli_print(get_string('cli_time_elapsed', 'admin', $elapsed));
    }
}
else {
    $cli->cli_exit(get_string('cli_portfolio_export_nousers', 'admin'));
}

/**
 * Create a zip archive containing the exported data.
 *
 * @param array $values The list of usernames that were exported
 * @param string $exportformat html | leap | pdf
 * @param boolean $allusers true | false
 */
function bulkexport($values, $exportformat = 'html', $allusers = true) {
    global $cli;

    $usernames = array();

    // Read in the usernames explicitly specified
    foreach ($values as $username) {
        $username = trim($username);
        if (!empty($username)) {
            $usernames[] = $username;
        }
    }
    safe_require('export', $exportformat);

    $listing = array();
    $files = array();
    $exportcount = 0;
    $exporterrors = array();

    $num_users = count($usernames);

    foreach ($usernames as $username) {
        $cli->cli_print(get_string('cli_portfolio_export_username', 'admin', $username));

        $user = new User();
        try {
            $user->find_by_username($username);
        }
        catch (AuthUnknownUserException $e) {
            continue; // Skip non-existent users
        }
        if (!$allusers) {
            $artifactssql = 'SELECT count(*) FROM {view} v
                JOIN {view_artefact} va on v.id = va.view
                WHERE v.owner=?';
            $count = count_records_sql($artifactssql, array($user->id));
            if ($count == 0) {
                $cli->cli_print(get_string('cli_portfolio_export_username_skipped', 'admin', $username));
                continue;
            }
        }
        if ($exportformat == 'html') {
            $exporter = new PluginExportHtml($user, PluginExport::EXPORT_ALL_VIEWS_COLLECTIONS, PluginExport::EXPORT_ALL_ARTEFACTS);
        }
        else if ($exportformat == 'leap') {
            $exporter = new PluginExportLeap($user, PluginExport::EXPORT_ALL_VIEWS_COLLECTIONS, PluginExport::EXPORT_ALL_ARTEFACTS);
        }
        else if ($exportformat == 'pdf') {
            $exporter = new PluginExportPdf($user, PluginExport::EXPORT_ALL_VIEWS_COLLECTIONS, PluginExport::EXPORT_ALL_ARTEFACTS);
        }

        try {
            $zipfile = $exporter->export(true);
        }
        catch (Exception $e) {
            $cli->cli_print(get_string('cli_portfolio_export_userfile_failed', 'admin', $e->getMessage()));
            $exporterrors[] = $username;
            continue;
        }
        $listing[] = array($user->id, $user->firstname, $user->lastname, $username, $zipfile);
        $files[] = $exporter->get('exportdir') . $zipfile;
        $exportcount++;
    }

    $cli->cli_print(get_string('cli_portfolio_export_zip', 'admin'));
    if (count($listing) && count($files)) {
        if (!$zipfile = bulkexport_create_zipfile($listing, $files)) {
            $cli->cli_print(get_string('bulkexportempty', 'admin'));
        }

        $cli->cli_print(get_string('cli_portfolio_export_zipout', 'admin', $exportcount, $zipfile));
        if (!empty($exporterrors)) {
            $cli->cli_print(get_string('couldnotexportusers', 'admin', implode(', ', $exporterrors)));
        }
    }
}

/**
 * Create a zip archive containing the exported data.
 *
 * @param array $listing The list of usernames that were exported
 * @param array $files A list of archive files for each user
 */
function bulkexport_create_zipfile($listing, $files) {
    if (empty($listing) or empty($files)) {
        return false;
    }
    if (count($listing) != count($files)) {
        throw new MaharaException("Files and listing don't match.");
    }

    // create temporary directories for the export
    $exportdir = get_config('dataroot') . 'export/cli/' . time() .  '/';
    if (!check_dir_exists($exportdir)) {
        throw new SystemException("Couldn't create the temporary export directory $exportdir");
    }
    $usersdir = 'users/';
    if (!check_dir_exists($exportdir . $usersdir)) {
        throw new SystemException("Couldn't create the temporary export directory $usersdir");
    }

    // move user zipfiles into the export directory
    foreach ($files as $filename) {
        if (copy($filename, $exportdir . $usersdir . basename($filename))) {
            unlink($filename);
        }
        else {
            throw new SystemException("Couldn't move $filename to $usersdir");
        }
    }

    // write username listing to a file
    $listingfile = 'usernames.csv';
    if (!file_put_contents($exportdir . $listingfile, bulkexport_data_to_csv($listing))) {
        throw new SystemException("Couldn't write usernames to a file");
    }

    // zip everything up
    $filename = 'mahara-bulk-export-'.time().'.zip';
    try {
        create_zip_archive($exportdir, $filename, array($listingfile, $usersdir));
    }
    catch (SystemException $e) {
        throw new SystemException('Failed to zip the export file: ' . $e->getMessage());
    }

    return $exportdir . $filename;
}

/**
 * Convert a 2D array to a CSV file. This follows the basic rules from http://en.wikipedia.org/wiki/Comma-separated_values
 *
 * @param array $input 2D array of values: each line is an array of values
 */
function bulkexport_data_to_csv($input) {
    if (empty($input) or !is_array($input)) {
        return '';
    }

    $output = '';
    foreach ($input as $line) {
        $lineoutput = '';

        foreach ($line as $element) {
            $element = str_replace('"', '""', $element);
            if (!empty($lineoutput)) {
                $lineoutput .= ',';
            }
            $lineoutput .= "\"$element\"";
        }

        $output .= $lineoutput . "\r\n";
    }

    return $output;
}
?>
