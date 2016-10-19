<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Richard Mansfield
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configextensions/cleanurls');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('cleanurls', 'admin'));

$regenerateform = pieform(array(
    'name'       => 'regenerateurls',
    'autofocus'  => false,
    'class'      => 'delete',
    'elements'   => array(
        'regenerate' => array(
            'type'         => 'submit',
            'title'        => get_string('regenerateurls', 'admin'),
            'class'        => 'btn-primary',
            'description'  => get_string('regenerateurlsdescription', 'admin'),
            'confirm'      => get_string('regenerateurlsconfirm', 'admin'),
            'value'        => get_string('submit'),
        ),
    ),
));

$cleanurlconfigkeys = array(
    'cleanurluserdefault', 'cleanurlgroupdefault', 'cleanurlviewdefault',
    'cleanurlcharset', 'cleanurlinvalidcharacters', 'cleanurlvalidate',
);

foreach ($cleanurlconfigkeys as $k) {
    $cleanurlconfig[$k] = get_config($k);
}

$smarty = smarty();
setpageicon($smarty, 'icon-puzzle-piece');

$smarty->assign('cleanurls', get_config('cleanurls'));
$smarty->assign('cleanurlconfig', $cleanurlconfig);
$smarty->assign('regenerateform', $regenerateform);
$smarty->display('admin/extensions/cleanurls.tpl');

// Regenerates urlids for users, groups, and portfolio pages.
function regenerateurls_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    require_once('upgrade.php');
    log_debug("Regenerating clean urls...");
    db_begin();

    // Checking duplicates one by one is too slow, so drop the index,
    // generate the urlids in big chunks, remove duplicates in one hit,
    // recreate the index.

    // Users: set urlid based on username.
    $table = new XMLDBTable('usr');
    $index = new XMLDBIndex('urliduk');
    $index->setAttributes(XMLDB_INDEX_UNIQUE, array('urlid'));
    drop_index($table, $index);

    execute_sql('UPDATE {usr} SET urlid = NULL WHERE NOT urlid IS NULL');
    $usrcount = count_records_select('usr', 'deleted = 0 AND id > 0');
    if (!get_config('nousernames')) {
        $sql = 'SELECT id, username FROM {usr} WHERE id > ? AND deleted = 0 ORDER BY id';
    }
    else {
        $sql = 'SELECT id, firstname, lastname, preferredname FROM {usr} WHERE id > ? AND deleted = 0 ORDER BY id';
    }

    $done = 0;
    $lastid = 0;
    $limit = 1000;
    while ($records = get_records_sql_array($sql, array($lastid), 0, $limit)) {
        $firstid = $lastid;
        $values = array();
        foreach ($records as $r) {
            $r->urlid = generate_urlid(get_raw_user_urlid($r), get_config('cleanurluserdefault'), 3, 30);
            array_push($values, $r->id, $r->urlid);
            $lastid = $r->id;
        }

        $updatesql = "UPDATE {usr} SET urlid = CASE id\n ";
        $updatesql .= join("\n ", array_fill(0, count($records), 'WHEN ? THEN ?'));
        $updatesql .= "\n ELSE NULL\n END WHERE id > ? AND id <= ? AND deleted = 0";
        array_push($values, $firstid, $lastid);
        execute_sql($updatesql, $values);

        $done += count($records);
        log_debug("Generating user urls: $done/$usrcount");
    }

    // Fix any duplicates created above
    $dupurls = get_records_sql_array('
        SELECT id, urlid
        FROM {usr}
        WHERE urlid IN (
            SELECT urlid FROM {usr} WHERE id > 0 AND deleted = 0 GROUP BY urlid HAVING COUNT(id) > 1
        )
        ORDER BY urlid, id',
        array()
    );

    $last = null;
    if ($dupurls) {
        log_debug('Fixing ' . count($dupurls) . ' duplicate user urls');
        $ids = array();
        $values = array();
        for ($i = 0; $i < count($dupurls); $i++) {
            if ($dupurls[$i]->urlid != $last) {
                // The first user with this name can keep it, but get all the taken urlids that are similar
                // so we can check against them when appending digits below.
                $taken = get_column_sql(
                    "SELECT urlid FROM {usr} WHERE urlid LIKE ?",
                    array(substr($dupurls[$i]->urlid, 0, 24) . '%')
                );
            }
            else {
                // Subsequent users need digits appended, while keeping the max length at 30
                $suffix = 1;
                $try = substr($dupurls[$i]->urlid, 0, 28) . '-1';
                while (in_array($try, $taken)) {
                    $suffix++;
                    $try = substr($dupurls[$i]->urlid, 0, 29 - strlen($suffix)) . '-' . $suffix;
                }
                $taken[] = $try;
                $ids[] = $dupurls[$i]->id;
                array_push($values, $dupurls[$i]->id, $try);
            }
            $last = $dupurls[$i]->urlid;
        }

        $updatesql = "UPDATE {usr} SET urlid = CASE id\n ";
        $updatesql .= join("\n ", array_fill(0, count($values) / 2, 'WHEN ? THEN ?'));
        $updatesql .= "\n ELSE urlid\n END\n WHERE id IN (";
        $updatesql .= join(',', array_fill(0, count($ids), '?'));
        $updatesql .= ')';
        $values = array_merge($values, $ids);
        execute_sql($updatesql, $values);
    }

    $table = new XMLDBTable('usr');
    $index = new XMLDBIndex('urliduk');
    $index->setAttributes(XMLDB_INDEX_UNIQUE, array('urlid'));
    add_index($table, $index);

    // Groups: set urlid based on group name
    execute_sql('UPDATE {group} SET urlid = NULL');

    $table = new XMLDBTable('group');
    $index = new XMLDBIndex('urliduk');
    $index->setAttributes(XMLDB_INDEX_UNIQUE, array('urlid'));
    drop_index($table, $index);

    // Transliteration using iconv is bad if locale is set to C, so set it based on
    // the site language.
    $lang = $sitelang = get_config('lang');
    set_locale_for_language($lang);

    $groupcount = count_records('group', 'deleted', 0);
    $sql = 'SELECT id, name FROM {group} WHERE deleted = 0 AND id > ? ORDER BY id';

    $done = 0;
    $lastid = 0;
    $limit = 1000;
    while ($records = get_records_sql_array($sql, array($lastid), 0, $limit)) {
        $firstid = $lastid;
        $values = array();
        foreach ($records as $r) {
            $r->urlid = generate_urlid($r->name, get_config('cleanurlgroupdefault'), 3, 30);
            array_push($values, $r->id, $r->urlid);
            $lastid = $r->id;
        }

        $updatesql = "UPDATE {group} SET urlid = CASE id\n ";
        $updatesql .= join("\n ", array_fill(0, count($records), 'WHEN ? THEN ?'));
        $updatesql .= "\n ELSE NULL\n END WHERE id > ? AND id <= ? AND deleted = 0";
        array_push($values, $firstid, $lastid);
        execute_sql($updatesql, $values);

        $done += count($records);
        log_debug("Generating group urls: $done/$groupcount");
    }

    // Fix duplicates...
    $dupurls = get_records_sql_array('
        SELECT id, urlid
        FROM {group}
        WHERE urlid IN (
            SELECT urlid FROM {group} WHERE id > 0 AND deleted = 0 GROUP BY urlid HAVING COUNT(id) > 1
        )
        ORDER BY urlid, id',
        array()
    );

    $last = null;
    if ($dupurls) {
        log_debug('Fixing ' . count($dupurls) . ' duplicate group urls');
        $ids = array();
        $values = array();
        for ($i = 0; $i < count($dupurls); $i++) {
            if ($dupurls[$i]->urlid != $last) {
                // The first group with this name can keep it, get similar group urls
                $taken = get_column_sql(
                    "SELECT urlid FROM {group} WHERE urlid LIKE ?",
                    array(substr($dupurls[$i]->urlid, 0, 24) . '%')
                );
            }
            else {
                // Append digits while keeping the max length at 30
                $suffix = 1;
                $try = substr($dupurls[$i]->urlid, 0, 28) . '-1';
                while (in_array($try, $taken)) {
                    $suffix++;
                    $try = substr($dupurls[$i]->urlid, 0, 29 - strlen($suffix)) . '-' . $suffix;
                }
                $taken[] = $try;
                $ids[] = $dupurls[$i]->id;
                array_push($values, $dupurls[$i]->id, $try);
            }
            $last = $dupurls[$i]->urlid;
        }

        $updatesql = "UPDATE {group} SET urlid = CASE id\n ";
        $updatesql .= join("\n ", array_fill(0, count($values) / 2, 'WHEN ? THEN ?'));
        $updatesql .= "\n ELSE urlid\n END\n WHERE id IN (";
        $updatesql .= join(',', array_fill(0, count($ids), '?'));
        $updatesql .= ')';
        $values = array_merge($values, $ids);
        execute_sql($updatesql, $values);
    }

    $table = new XMLDBTable('group');
    $index = new XMLDBIndex('urliduk');
    $index->setAttributes(XMLDB_INDEX_UNIQUE, array('urlid'));
    add_index($table, $index);

    // Views: set urlid based on view title.  Only portfolio views need urlids, and they
    // only need to be unique when they're owned by the same entity.
    // The iconv utf8 conversion gives better results if we set the locale based on the
    // user's language preference, so these are pulled from the db when appropriate.
    execute_sql('UPDATE {view} SET urlid = NULL');

    $table = new XMLDBTable('view');
    $index = new XMLDBIndex('urliduk');
    $index->setAttributes(XMLDB_INDEX_UNIQUE, array('urlid', 'owner', 'group', 'institution'));
    drop_index($table, $index);

    $viewcount = count_records('view', 'type', 'portfolio');
    $sql = "
        SELECT v.id, v.title, ap.value AS lang
        FROM {view} v LEFT JOIN {usr_account_preference} ap ON ap.usr = v.owner AND ap.field = 'lang'
        WHERE v.id > ? AND v.type = 'portfolio'
        ORDER BY v.id";

    $done = 0;
    $lastid = 0;
    $limit = 1000;
    while ($records = get_records_sql_array($sql, array($lastid), 0, $limit)) {
        $firstid = $lastid;
        $values = array();
        foreach ($records as $r) {
            if (empty($r->lang) || $r->lang == 'default') {
                $r->lang = $sitelang;
            }
            if ($lang != $r->lang) {
                set_locale_for_language($r->lang);
                $lang = $r->lang;
            }

            $r->urlid = generate_urlid($r->title, get_config('cleanurlviewdefault'), 3, 100);
            array_push($values, $r->id, $r->urlid);
            $lastid = $r->id;
        }

        $updatesql = "UPDATE {view} SET urlid = CASE id\n ";
        $updatesql .= join("\n ", array_fill(0, count($records), 'WHEN ? THEN ?'));
        $updatesql .= "\n ELSE NULL\n END WHERE id > ? AND id <= ?";
        array_push($values, $firstid, $lastid);
        execute_sql($updatesql, $values);

        $done += count($records);
        log_debug("Generating page urls: $done/$viewcount");
    }

    // Reset locale
    set_locale_for_language($sitelang);

    // Fix duplicates with the same owner, group, or institution
    $dupurls = get_records_sql_array("
        SELECT
            v.id, dv.urlid, dv.owner, dv.group, dv.institution
        FROM
            {view} v,
            (SELECT d.urlid, d.owner, d.group, d.institution
             FROM {view} d
             WHERE d.type = 'portfolio'
             GROUP BY d.urlid, d.owner, d.group, d.institution
             HAVING COUNT(d.id) > 1) dv
        WHERE
            v.type = 'portfolio'
            AND v.urlid = dv.urlid
            AND (v.owner = dv.owner OR (v.owner IS NULL AND dv.owner IS NULL))
            AND (v.group = dv.group OR (v.group IS NULL AND dv.group IS NULL))
            AND (v.institution = dv.institution OR (v.institution IS NULL AND dv.institution IS NULL))
        ORDER BY
            dv.urlid, dv.owner, dv.group, dv.institution, v.id",
        array()
    );

    $last = array('urlid' => null, 'owner' => null, 'group' => null, 'institution' => null);
    if ($dupurls) {
        log_debug('Fixing ' . count($dupurls) . ' duplicate page urls');
        $ids = array();
        $values = array();
        for ($i = 0; $i < count($dupurls); $i++) {
            $hasdupes = clone $dupurls[$i];
            unset($hasdupes->id);
            if ($hasdupes != $last) {
                // The first view with this name can keep it
                // Get similar view names to check uniqueness when appending digits
                if (!is_null($hasdupes->owner)) {
                    $ownersql = 'owner = ?';
                    $ownervalue = $hasdupes->owner;
                }
                else if (!is_null($hasdupes->group)) {
                    $ownersql = 'group = ?';
                    $ownervalue = $hasdupes->group;
                }
                else if (!is_null($hasdupes->institution)) {
                    $ownersql = 'institution = ?';
                    $ownervalue = $hasdupes->institution;
                }
                $taken = get_column_sql(
                    'SELECT urlid FROM {view} v WHERE urlid LIKE ? AND v.' . $ownersql,
                    array(substr($dupurls[$i]->urlid, 0, 94), $ownervalue)
                );
            }
            else {
                // Subsequent views with this name need digits appended, keeping max length at 100
                $suffix = 1;
                $try = substr($dupurls[$i]->urlid, 0, 98) . '-1';
                while (in_array($try, $taken)) {
                    $suffix++;
                    $try = substr($dupurls[$i]->urlid, 0, 99 - strlen($suffix)) . '-' . $suffix;
                }
                $taken[] = $try;
                $ids[] = $dupurls[$i]->id;
                array_push($values, $dupurls[$i]->id, $try);
            }
            $last = $hasdupes;
        }

        $updatesql = "UPDATE {view} SET urlid = CASE id\n ";
        $updatesql .= join("\n ", array_fill(0, count($values) / 2, 'WHEN ? THEN ?'));
        $updatesql .= "\n ELSE urlid\n END\n WHERE id IN (";
        $updatesql .= join(',', array_fill(0, count($ids), '?'));
        $updatesql .= ')';
        $values = array_merge($values, $ids);
        execute_sql($updatesql, $values);
    }

    $table = new XMLDBTable('view');
    $index = new XMLDBIndex('urliduk');
    $index->setAttributes(XMLDB_INDEX_UNIQUE, array('urlid', 'owner', 'group', 'institution'));
    add_index($table, $index);

    // Reset in the session for this user - currently logged-in users may end up wiping theirs
    $USER->urlid = get_field('usr', 'urlid', 'id', $USER->get('id'));
    $USER->commit();

    db_commit();

    $SESSION->add_ok_msg(get_string('generateduserurls', 'admin', $usrcount));
    $SESSION->add_ok_msg(get_string('generatedgroupurls', 'admin', $groupcount));
    $SESSION->add_ok_msg(get_string('generatedviewurls', 'admin', $viewcount));

    redirect('/admin/extensions/cleanurls.php');
}
