<?php
/**
 * Language packs administration page
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'development/langpacks');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'languagepacks');

require(dirname(dirname(dirname(__FILE__))).'/init.php');

define('TITLE', get_string('languagepacks_title', 'langpacks'));

$series = get_config('series');
if (preg_match('/dev$/', get_config('release'))) {
    $series = 'main';
}
$langpackurl = 'https://langpacks.mahara.org/';
$seriesfilesuffix = $series . '.tar.gz';

$languages = get_installed_languages();
$langstoupdate = get_languages_need_updating($languages);

$tmpdir = get_config('dataroot') . 'temp';
if (!check_dir_exists($tmpdir) || !is_writable($tmpdir)) {
    $errormessage = get_string('cli_tmpdir_notwritable', 'admin', $tmpdir);
}
$langdir = get_config('dataroot') . 'langpacks';
if (!check_dir_exists($langdir) || !is_writable($langdir)) {
    $errormessage = get_string('cli_langdir_notwritable', 'admin', $langdir);
}
$backupdir = $langdir . '_backup';
if (!check_dir_exists($backupdir) || !is_writable($backupdir)) {
    $errormessage = get_string('cli_backupdir_notwritable', 'admin', $backupdir);
}

$headers = get_table_headers();
$syncform = get_syncform();
$addform = get_add_lang_form();

$smarty = smarty();
setpageicon($smarty, 'icon-language');
$smarty->assign('INLINEJAVASCRIPT', <<<EOF
jQuery(function ($) {
    var wireselectall = function() {
        $("#selectall").on("click", function(e) {
            e.preventDefault();
            $("#searchresults :checkbox").prop("checked", true);
        });
    };

    var wireselectnone = function() {
        $("#selectnone").on("click", function(e) {
            e.preventDefault();
            $("#searchresults :checkbox").prop("checked", false);
        });
    };
    wireselectall();
    wireselectnone();
});
EOF
);

$smarty->assign('syncformopen', $syncform->get_form_tag());
$smarty->assign('syncform', $syncform->build());
$smarty->assign('results', $langstoupdate);
$smarty->assign('columns', $headers);
$smarty->assign('ncols', count($headers));
$smarty->assign('addform', $addform);
$smarty->assign('installedlanguages', $languages);
$smarty->display('admin/site/langpacks.tpl');

function get_table_headers() {
    return array(
        'select' =>  array(
            'mergefirst' => true,
            'headhtml' => '<div class="btn-group" role="group"><a class="btn btn-sm btn-secondary" href="" id="selectall">' . get_string('All') . '</a><a class="btn active btn-sm btn-secondary" href="" id="selectnone">' . get_string('none') . '</a></div>',
            'template' => 'admin/site/langselectcolumn.tpl',
            'class'    => 'nojs-hidden with-checkbox',
            'accessible' => get_string('bulkselect', 'langpacks'),
        ),
        'name' => array(
            'name'     => get_string('name'),
        ),
        'code' => array(
            'name'     => get_string('code', 'langpacks'),
        ),
        'lastupdated' => array(
            'name'     => get_string('lastupdated', 'admin'),
        ),
        'file' => array(
            'name'     => get_string('fileorigin', 'langpacks'),
            'template' => 'admin/site/langfilecolumn.tpl',
        ),
    );
}

/**
 * Check to see if a newer version of the file exists
 * and update only those that have changed
 *
 * @param array $languages Array of language arrays
 * @return array<string,array<string,mixed>>
 */
function get_languages_need_updating($languages) {
    global $langpackurl, $seriesfilesuffix;

    $toupdate = array();
    foreach ($languages as $lang) {
        $filename = $lang['code'] . '-' . $seriesfilesuffix;
        $etag = fetch_etag($lang['code']);

        $needupdate = true;
        $localetagstr = get_config('lang_' . $lang['code'] . '_etag');
        $localversiontime = 0;
        if (!$localetagstr) {
            // We don't have a value in config table for this language so lets see if we can work it out based on the files
            if (file_exists(get_config('dataroot') . 'temp/' . $filename)) {
                $localetagstr = 'oldzip_' . filemtime(get_config('dataroot') . 'temp/' . $filename);
            }
            else if (file_exists(get_config('dataroot') . 'langpacks/' . $lang['code'] . '.utf8' . '/lang/' . $lang['code'] . '.utf8' . '/langconfig.php')) {
                $localetagstr = 'oldconf_' . filemtime(get_config('dataroot') . 'langpacks/' . $lang['code'] . '.utf8' . '/lang/' . $lang['code'] . '.utf8' . '/langconfig.php');
            }
        }

        if ($localetagstr) {
            list($localetag, $localversiontime) = explode('_', $localetagstr);
            if ($localetag === $etag) {
                $needupdate = false;
            }
        }
        $langurl = '<a href="' . $langpackurl . $filename . '" target="_blank">' . $langurl = $lang['code'] . '-' . $seriesfilesuffix . '</a>';
        $toupdate[$lang['code']] = array(
            'code' => $lang['code'],
            'name'=> $lang['name'],
            'file' => $langurl,
            'lastupdated' => format_date($localversiontime, 'strftimedatetimesuffix'),
            'active' => !$needupdate,
        );
    }
    return $toupdate;
}

/**
 * Fetch the etag headers information about language file
 *
 * See: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag
 *
 * @param string $langcode
 * @return string the etag
 */
function fetch_etag($langcode) {
    global $langpackurl, $seriesfilesuffix;
    // fetch the lang packs etag
    $filename = $langcode . '-' . $seriesfilesuffix;
    $langurl = $langpackurl . $filename;
    $langheaders = get_headers($langurl, 1);
    $httpcode = substr($langheaders[0], 9, 3);
    $etag = '';
    if ($httpcode == '200' && !empty($langheaders['ETag'])) {
        $etag = str_replace('"', '', $langheaders['ETag']);
    }
    return $etag;
}

/**
 * Fetch a list of the languages installed
 *
 * @return array
 */
function get_installed_languages() {
    $languages = array();
    foreach(get_languages() as $lang => $name) {
        if (preg_match('/\.utf8$/', $lang)) {
            $l = substr($lang, 0, -5);
            // no need to update English language pack
            if ($l != 'en') {
                $languages[] = array(
                    'code' => substr($lang, 0, -5),
                    'name' => $name,
                );
            }
        }
    }
    return $languages;
}

/**
 * Generate the sync languages form
 *
 * return Pieform instance
 */
function get_syncform() {
    $form = pieform_instance(array(
        'name'      => 'synclanguage',
        'renderer'  => 'div',
        'autofocus' => false,
        'elements' => array(
            'sync' => array(
                'class' => 'btn-secondary btn',
                'type'    => 'submit',
                'isformgroup' => false,
                'renderelementsonly' => true,
                'confirm' => get_string('confirmsync', 'langpacks'),
                'name'    => 'sync',
                'value'   => get_string('updatelangpacks', 'langpacks')
            )
        )
    ));
    return $form;
}

/**
 * Submit function for synclanguage form to update langpacks
 *
 * @param Pieform $form
 * @param array $values
 */
function synclanguage_submit(Pieform $form, $values) {
    global $SESSION;

    $codes = param_array('lang_code', array());
    if (!$codes) {
        $SESSION->add_error_msg(get_string('nolanguageselected', 'langpacks'));
        redirect('/admin/site/langpacks.php');
    }

    // update langpacks
    update_langpacks($codes);

    $SESSION->add_ok_msg(get_string('languagesyncsuccessfully', 'langpacks'));
    redirect('/admin/site/langpacks.php');
}

/**
 * Update language packs in Mahara
 *
 * @param array $codes  list of language codes
 * @param boolean $dobackup  Whether to back up the existing language if overwriting with new instance
 * @param string|null $new   Pass in language code to indicate we want to install it
 */
function update_langpacks($codes, $dobackup=true, $new=null) {
    global $langdir, $backupdir, $SESSION, $langstoupdate, $langpackurl, $seriesfilesuffix;

    $options = $dobackup ? ' -b' : '';
    foreach($codes as $code) {
        if (isset($langstoupdate[$code]) || $code == $new) {
            $etag = fetch_etag($code);
            // do a backup, always if on admin section
            $result = shell_exec('php ' . get_config('docroot') . 'admin/cli/sync_langpacks.php -l=' . escapeshellarg($code) . $options);
            if ($result) {
                set_config('lang_' . $code . '_etag', $etag . '_' . time());
            }
            else {
               $SESSION->add_error_msg(get_string('languagesyncunsuccessful', 'langpacks', $code));
            }
        }
    }
}

/**
 * Fetch available loanguage packs metadata
 *
 * @return array
 */
function fetch_available_languages() {
    global $langpackurl, $series;
    $availablefile = file_get_contents($langpackurl . 'langpacks.json');
    $allavailable = json_decode($availablefile);
    $available = array_keys((array)$allavailable->{$series});
    return $available;
}

/**
 * Form for adding in a new language to the site
 *
 * @return Pieform
 */
function get_add_lang_form() {
    global $langpackurl, $series, $langstoupdate;

    // Fetch all the available langpacks and then filter them down
    // to the ones not installed, focusing on the packs for this version
    $available = fetch_available_languages();
    $used = array_keys($langstoupdate);
    $available = array_values(array_diff($available, $used));
    $options = array();
    $lang = current_language();
    $lang = preg_replace('/\.utf8$/', '', $lang);
    foreach ($available as $v) {
        $options[$v] = locale_get_display_name($v, $lang);
    }
    if (isset($options['mi'])) {
        // We need to get the Te Reo version of the string as it has the macron
        $options['mi'] = locale_get_display_name('mi', 'mi');
    }
    asort($options);
    $form = array(
        'name'      => 'add_language',
        'renderer'  => 'div',
        'class'     => 'form-inline',
        'autofocus' => false,
        'elements' => array(
            'addlang' => array(
                'type' => 'select',
                'options' => $options,
                'class' => 'last',
                'rules' => array(
                    'required' => true,
                ),
            ),
            'addlangbtn' => array(
                'type'    => 'submit',
                'class'   => 'submit btn btn-primary last',
                'value'   => get_string('addlangpack', 'langpacks')
            ),
            'addlangdesc' => array(
                'type' => 'html',
                'value' => get_string('addlangpackdescription', 'langpacks'),
                'class' => 'description'
            )
        )
    );
    return pieform($form);
}

/**
 * Add language validation
 *
 * @param Pieform $form
 * @param array $values
 */
function add_language_validate(Pieform $form, $values) {
    $rawlang = $values['addlang'];
    $available = fetch_available_languages();
    if (!in_array($rawlang, $available)) {
        throw new ParamOutOfRangeException();
    }
    // make sure we have the utf8 part
    $lang = rtrim($rawlang, '.utf8') . '.utf8';
    $langfile = get_language_root($lang) . 'lang/' . $lang . '/langconfig.php';
    $etag = fetch_etag($rawlang);
    if (is_readable($langfile)) {
        $langname = get_string_from_file('thislanguage', $langfile);
        $form->set_error('addlang', get_string('langalreadyinstalled', 'langpacks', $langname));
    }
    else if (empty($etag)) {
        $form->set_error('addlang', get_string('notvalidlangpack', 'langpacks', $rawlang));
    }
}

/**
 * Add language submission
 *
 * @param Pieform $form
 * @param array $values
 */
function add_language_submit(Pieform $form, $values) {
    global $langstoupdate, $SESSION;

    $rawlang = $values['addlang'];
    $lang = rtrim($rawlang, '.utf8') . '.utf8';
    update_langpacks(array($rawlang), false, $rawlang);

    $langfile = get_language_root($lang) . 'lang/' . $lang . '/langconfig.php';

    if (is_readable($langfile)) {
        $langname = get_string_from_file('thislanguage', $langfile);
        $SESSION->add_ok_msg(get_string('langpackadded', 'langpacks', $langname));
    }
    redirect('/admin/site/langpacks.php');
}
