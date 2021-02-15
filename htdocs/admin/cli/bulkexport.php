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
$options['filepath'] = (object) array(
        'shortoptions' => array('f'),
        'description' => 'Username csv File path',
        'required' => false,
        'examplevalue' => '/siteroot/users_bulkexport.csv',
);
$options['exportformat'] = (object) array(
        'shortoptions' => array('e'),
        'description' => 'Export format',
        'required' => false,
        'defaultvalue' => 'html',
        'examplevalue' => 'html|leap|pdf',
);
$options['offset'] = (object) array(
        'shortoptions' => array('o'),
        'description' => 'Offset',
        'required' => false,
        'defaultvalue' => '0',
        'examplevalue' => '0',
);
$options['limit'] = (object) array(
        'shortoptions' => array('l'),
        'description' => 'Limit',
        'required' => false,
        'defaultvalue' => '0',
        'examplevalue' => '100',
);
$options['filterkey'] = (object) array(
        'shortoptions' => array('fk'),
        'description' => 'Key to filter - users filter(firstname | lastname | email | username)',
        'required' => false,
        'defaultvalue' => '',
        'examplevalue' => 'lastname',
);
$options['filtervalue'] = (object) array(
        'shortoptions' => array('fv'),
        'description' => 'Value for key filter - start with',
        'required' => false,
        'defaultvalue' => '',
        'examplevalue' => 'a',
);


$settings = (object) array(
    'info' => 'This command-line PHP script allows you to export all users portfolio in html or leap format.',
    'options' => $options,
);
$cli->setup($settings);

// Retrieve & validate the filepath
$allusers = false;
$filepath = $cli->get_cli_param('filepath');
if ($filepath) {
    if (!file_exists($filepath)) {
        echo 'File not found';
    }
} else {
    $allusers = true;
}
// Retrieve & validate the exportformat
$exportformat = $cli->get_cli_param('exportformat');
if (!$exportformat) {
    $exportformat = 'html';
}
$allowedformats = array('html', 'leap', 'pdf');
if(!in_array($exportformat, $allowedformats)) {
    cli::cli_exit('Invalid export format', true);
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
if($filterkey && $filterval) {
    $hasfilter = true;
}
$allowedfilters = array('firstname', 'lastname', 'email', 'username');
if(!in_array($filterkey, $allowedfilters)) {
    cli::cli_exit('Invalid filter key', true);
}

$usernames = array();
if($allusers) {
    $sql = 'SELECT username
            FROM {usr} u
            WHERE u.deleted = 0
            AND u.active = 1 AND EXISTS ( select 1 from {view} v
            JOIN {view_artefact} va on v.id = va.view
            WHERE v.owner = u.id)';
    if($hasfilter) {
        $sql .= ' AND u.'.$filterkey.' ILIKE \''.$filterval.'%\'';
    }

    $sql .= ' ORDER BY u.lastname ASC';

    if ($haslimit) {
        $sql .= ' LIMIT '.$limit.' OFFSET '.$offset;
        echo PHP_EOL.'Exporting portfolios for '.$limit.' users.'.PHP_EOL;
    } else if ($hasfilter){
        echo PHP_EOL.'Exporting portfolios for users based on filter key and filter value.'.PHP_EOL;
    } else {
        echo PHP_EOL.'Exporting portfolios for all users.'.PHP_EOL;
    }

    $alluserstoexport = get_records_sql_array($sql);
    if (is_array($alluserstoexport) && count($alluserstoexport)) {
        foreach ($alluserstoexport as $row) {
            $usernames[] = $row->username;
        }
    }
} else {
    if (($handle = fopen($filepath, "r")) !== false) {
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $usernames[] = $data[0];
        }
        fclose($handle);
    }
}
if(count($usernames)) {
    $starttime = microtime(true);
    echo PHP_EOL.'Export started at '.date("Y-m-d H:i:s", time()).PHP_EOL;
    bulkexport($usernames, $exportformat, $allusers);
    echo PHP_EOL.'Export ended at '.date("Y-m-d H:i:s", time()).PHP_EOL;
    $endtime = microtime(true);
    echo PHP_EOL.'Time taken: '.($endtime - $starttime).' Seconds'.PHP_EOL.PHP_EOL;
} else {
    echo PHP_EOL.'No users to export portfolio.'.PHP_EOL;
}
function bulkexport($values, $exportformat = 'html', $allusers = true) {
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
        echo PHP_EOL.'Export started for username: '. $username;

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
                echo ' ... Skipped - No Portfolios '.PHP_EOL;
                continue;
            }
        }
        echo ' (id: '.$user->id.', Name: '. $user->firstname .' '. $user->lastname . ') ';
        if($exportformat == 'html'){
            $exporter = new PluginExportHtml($user, PluginExport::EXPORT_ALL_VIEWS_COLLECTIONS, PluginExport::EXPORT_ALL_ARTEFACTS);
        } else {
            $exporter = new PluginExportLeap($user, PluginExport::EXPORT_ALL_VIEWS_COLLECTIONS, PluginExport::EXPORT_ALL_ARTEFACTS);
        }
        try {
            $zipfile = $exporter->export();
        }
        catch (Exception $e) {
            echo 'Failed to export : '.$e->getMessage().PHP_EOL;
            $exporterrors[] = $username;
            continue;
        }
        $listing[] = array($user->id, $user->firstname, $user->lastname, $username, $zipfile);
        $files[] = $exporter->get('exportdir') . $zipfile;
        $exportcount++;
        echo ' ... done'.PHP_EOL.PHP_EOL;
    }

    echo 'Please wait, creating bundle zip...'.PHP_EOL;
    if (count($listing) && count($files)) {
        if (!$zipfile = bulkexport_create_zipfile($listing, $files)) {
            echo get_string('bulkexportempty', 'admin');
        }
    
        echo 'Exported '.$exportcount.' users to '.$zipfile.PHP_EOL.PHP_EOL;
        if (!empty($exporterrors)) {
            echo get_string('couldnotexportusers', 'admin', implode(', ', $exporterrors)).PHP_EOL.PHP_EOL;
        }

        // Store the filename in the session, and redirect the iframe to it to trigger
        // the download. Here it would be nice to trigger the download for everyone,
        // but alas this is not possible for people without javascript.
        echo 'Exported filepath: '.$zipfile.PHP_EOL;
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
