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
require_once(get_config('libroot') . 'file.php');

$cli = get_cli();

$options = array();
$options['langpacks'] = (object) array(
        'shortoptions' => array('l'),
        'description' => get_string('cli_langpack', 'admin'),
        'required' => true,
        'examplevalue' => 'de',
);
define('CLI_LANGPACKS_BACKUP_DEFAULT', -1);
$options['keepbackups'] = (object) array(
        'shortoptions' => array('b'),
        'description' => get_string('cli_langpack_backup', 'admin'),
        'required' => false,
        'defaultvalue' => CLI_LANGPACKS_BACKUP_DEFAULT,
);
define('CLI_LANGPACKS_REVERT_DEFAULT', -1);
$options['revertbackups'] = (object) array(
        'shortoptions' => array('r'),
        'description' => get_string('cli_langpack_revert', 'admin'),
        'required' => false,
        'defaultvalue' => CLI_LANGPACKS_REVERT_DEFAULT,
);

$settings = (object) array(
        'info' => get_string('cli_langpack_info', 'admin'),
        'options' => $options,
);
$cli->setup($settings);

// Get which language packs we are going to update
$langpacks = $cli->get_cli_param('langpacks');
$langpacks = explode(',', $langpacks);

// No need to update the English lang pack as English lang strings are default in Mahara
$key = array_search('en', $langpacks);
if ($key !== false) {
    unset($langpacks[$key]);
    $cli->cli_print(get_string('cli_langpack_en', 'admin'));
}
if (empty($langpacks)) {
    $cli->cli_exit(get_string('cli_langpack_missing', 'admin'));
}

// Get installed languages
$languages = get_languages();
$series = get_config('series');
if (preg_match('/dev$/', get_config('release'))) {
    $series = 'master';
}
$seriesfilesuffix = $series . '.tar.gz';

$cli->cli_print(get_string('cli_lang_branch', 'admin', $series));

$tmpdir = get_config('dataroot') . 'temp';
if (!check_dir_exists($tmpdir) || !is_writable($tmpdir)) {
    $cli->cli_exit(get_string('cli_tmpdir_notwritable', 'admin', $tmpdir));
}
$langdir = get_config('dataroot') . 'langpacks';
if (!check_dir_exists($langdir) || !is_writable($langdir)) {
    $cli->cli_exit(get_string('cli_langdir_notwritable', 'admin', $langdir));
}
$backupdir = $langdir . '_backup';
if (!check_dir_exists($backupdir) || !is_writable($backupdir)) {
    $cli->cli_exit(get_string('cli_backupdir_notwritable', 'admin', $backupdir));
}
$langdirownership = posix_getpwuid(fileowner($langdir));
if ($cli->get_cli_param('revertbackups') === CLI_LANGPACKS_REVERT_DEFAULT) {
    $rollback = false;
}
else {
    $rollback = $cli->get_cli_param_boolean('revertbackups');
}

foreach ($langpacks as $lang) {
    $cli->cli_print('================== ' . $lang . ' ==================');
    if ($rollback) {
        if (is_dir($backupdir . '/' . $lang . '.utf8')) {
            // To revert lang from langpacks_backup back to langpacks
            if (!copyr($backupdir . '/' . $lang . '.utf8', $langdir . '/' . $lang . '.utf8')) {
                $cli->cli_print(get_string('cli_restore_warning', 'admin', $lang));
            }
            $cli->cli_print(get_string('cli_restore_done', 'admin', $lang));
        }
        else {
            $cli->cli_print(get_string('cli_restore_warning', 'admin', $lang));
            continue;
        }
    }
    else {
        // Install/update lang from langpacks.mahara.org to langpacks
        // First: work out what we should be doing
        $dobackup = false;
        $langexists = (array_key_exists($lang . '.utf8', $languages)) ? true : false;
        $cli->cli_print(get_string('cli_language_status', 'admin', $lang, ($langexists ? 'true' : 'false')));
        if ($cli->get_cli_param('keepbackups') === CLI_LANGPACKS_BACKUP_DEFAULT) {
            // no backup specified so do if lang already exists
            if ($langexists) {
                $dobackup = true;
            }
        }
        else {
            $dobackup = (!$langexists) ? false : $cli->get_cli_param_boolean('keepbackups');
        }
        $cli->cli_print(get_string('cli_language_make_backup', 'admin', ($dobackup ? 'true' : 'false')));

        // fetch the lang packs we need and save them to tmp
        $langpackurl = 'https://langpacks.mahara.org/';
        $filename = $lang . '-' . $seriesfilesuffix;
        $langurl = $langpackurl . $filename;
        $cli->cli_print(get_string('cli_langpack_url', 'admin', $langurl));
        $checklang = mahara_http_request(
            array(
                CURLOPT_URL => $langurl,
                CURLOPT_HEADER => false,
            ),
            true
        );
        if ($checklang->info['http_code'] != '200') {
            $cli->cli_print(get_string('cli_langpack_url_failed', 'admin', $lang, $checklang->info['http_code']));
            continue;
        }
        $file = $checklang->data;
        file_put_contents($tmpdir . '/' . $filename, $file);
        $cli->cli_print(get_string('cli_langpack_upload', 'admin', $filename));
        // if we need to make a backup - do it now
        if ($dobackup) {
            if (!copyr($langdir . '/' . $lang . '.utf8', $backupdir . '/' . $lang . '.utf8')) {
                $cli->cli_print(get_string('cli_langpack_backup_failed', 'admin', $lang));
            }
            $cli->cli_print(get_string('cli_langpack_backup_done', 'admin', $lang));
        }

        // We can't use dots in our filename as it confuses PharData as to what bit is an extension
        $filenameclean = substr($filename, 0, stripos($filename, '.tar.gz'));
        $filenameclean = str_replace('.', '_', $filenameclean) . '.tar.gz';

        rename($tmpdir . '/' . $filename, $tmpdir . '/' . $filenameclean);
        $filename = $filenameclean;

        // extract contents of langpack into langpacks dir
        $filenametmp = substr($filename, 0, stripos($filename, '.gz'));
        // Try to decompress the langpack file
        try {
            // Need to remove old decompressed file
            if (file_exists($tmpdir . '/' . $filenametmp)) {
                unlink($tmpdir . '/' . $filenametmp);
            }
            $phargz = new PharData($tmpdir . '/' . $filename);
            $phargz->decompress();
        }
        catch (Exception $e) {
            $cli->cli_print(get_string('cli_langpack_extract_failed', 'admin', $filename));
        }
        // Extract to langpacks dir
        try {
            $phar = new PharData($tmpdir . '/' . $filenametmp);
            $phar->extractTo($langdir, null, true); // extract all files, and overwrite
            $cli->cli_print(get_string('cli_langpack_extract_done', 'admin', $lang));
        }
        catch (Exception $e) {
            // handle errors
            $cli->cli_print(get_string('cli_langpack_extract_failed', 'admin', $filenametmp, $e->getMessage()));
        }
    }
    $cli->cli_print('------------------------------------------');
}
$cli->cli_exit(get_string('done'));
