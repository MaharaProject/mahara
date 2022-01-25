<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configextensions/embddedurls');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('embeddedurlstitle', 'admin'));

$checkurlraw = param_variable('checkurl', null);

$siteurl = get_config('wwwroot');
$sitepath = '%' . $siteurl . '%';
if ($checkurlraw === null) {
    $checkpath = '%/artefact/%';  // basic check to see if any potential wrong URLs
}
else {
    $checkurlraw = $checkurlraw . ((substr($checkurlraw, -1) != '/') ? '/' : '');
    $checkpath = '%' . $checkurlraw . '%';
}
// Check to see if there are potential embedded URLs to update
$results = get_records_sql_array("SELECT COUNT(*) AS count, 'section_view_instructions' AS type, 'view' AS t, 'instructions' as f FROM {view}
                                  WHERE instructions LIKE ? AND instructions NOT LIKE ?
                                  UNION
                                  SELECT COUNT(*) AS count, 'section_view_description' AS type, 'view' AS t, 'description' AS f FROM {view}
                                  WHERE description LIKE ? AND description NOT LIKE ?
                                  UNION
                                  SELECT COUNT(*) AS count, 'section_group' AS type, 'group' AS t, 'description' AS f FROM {group}
                                  WHERE description LIKE ? AND description NOT LIKE ?
                                  UNION
                                  SELECT COUNT(*) AS count, 'section_artefact' As type, 'artefact' AS t, 'description' AS f FROM {artefact}
                                  WHERE description LIKE ? AND description NOT LIKE ?
                                  UNION
                                  SELECT COUNT(*) AS count, 'section_interactionpost' AS type, 'interaction_forum_post' AS t, 'body' AS f FROM {interaction_forum_post}
                                  WHERE body LIKE ? AND body NOT LIKE ?
                                  UNION
                                  SELECT COUNT(*) AS count, 'section_interaction' AS type, 'interaction_instance' AS t, 'description' AS f FROM {interaction_instance}
                                  WHERE description LIKE ? AND description NOT LIKE ?
                                  UNION
                                  SELECT COUNT(*) AS count, 'section_block' AS type, 'block_instance' AS t, 'configdata' AS f FROM {block_instance}
                                  WHERE configdata LIKE ? AND configdata NOT LIKE ?",
                                 array($checkpath, $sitepath,
                                       $checkpath, $sitepath,
                                       $checkpath, $sitepath,
                                       $checkpath, $sitepath,
                                       $checkpath, $sitepath,
                                       $checkpath, $sitepath,
                                       $checkpath, $sitepath));
$grandtotal = array_sum(array_column($results, 'count'));

$checkform = pieform(array(
    'name'       => 'checkurl',
    'autofocus'  => false,
    'class'      => 'delete',
    'elements'   => array(
        'fromurl' => array(
            'type'         => 'text',
            'title'        => get_string('oldurl', 'admin'),
            'description'  => get_string('oldurldescription', 'admin'),
            'defaultvalue' => $checkurlraw ? $checkurlraw : '',
        ),
        'checksubmit' => array(
            'type'         => 'submit',
            'title'        => get_string('checkurl', 'admin'),
            'class'        => 'btn-primary',
            'value'        => get_string('checkurl', 'admin'),
            'hiddenlabel'  => true,
        ),
    ),
));

$migrateform = pieform(array(
    'name'       => 'migrateurls',
    'autofocus'  => false,
    'class'      => 'delete',
    'elements'   => array(
        'migrate' => array(
            'type'         => 'submit',
            'title'        => get_string('migrateurls', 'admin'),
            'class'        => 'btn-primary',
            'description'  => get_string('migrateurlsdescription', 'admin', $checkurlraw, get_config('wwwroot')),
            'confirm'      => get_string('migrateurlsconfirm', 'admin'),
            'value'        => get_string('domigrateurls', 'admin'),
            'hiddenlabel'    => true,
        ),
        'fromurl' => array(
            'type'         => 'hidden',
            'value' => $checkurlraw ? $checkurlraw : '',
        ),
    ),
));

$smarty = smarty();
setpageicon($smarty, 'icon-cog');

$smarty->assign('potentialembeddedurls', $results);
$smarty->assign('checkform', $checkform);
$smarty->assign('checkurlraw', $checkurlraw);
$smarty->assign('migrateform', $migrateform);
$smarty->assign('grandtotal', $grandtotal);
$smarty->assign('checkurl', $checkurlraw);
$smarty->display('admin/extensions/embeddedurls.tpl');

// Check url
function checkurl_submit(Pieform $form, $values) {
    if (empty($values['fromurl'])) {
        $form->set_error('fromurl', get_string('urlneeded'));
    }
    if (!preg_match('/^https?\:\/\//', $values['fromurl'])) {
        $form->set_error('fromurl', get_string('urlneedshttp'));
    }
    redirect('/admin/extensions/embeddedurls.php?checkurl=' . $values['fromurl']);
}

// Migrate embedded urls
function migrateurls_validate(Pieform $form, $values) {
    if (empty($values['fromurl'])) {
        $form->set_error('fromurl', get_string('urlneeded'));
    }
    if (!preg_match('/^https?\:\/\//', $values['fromurl'])) {
        $form->set_error('fromurl', get_string('urlneedshttp'));
    }
}

function migrateurls_submit(Pieform $form, $values) {
    global $SESSION, $results;
    if (is_array($results)) {
        $basiccount = 0;
        $blockcount = 0;
        $fromurl = $values['fromurl'];
        $fromurl .= (substr($fromurl, -1) != '/') ? '/' : '';
        foreach ($results as $result) {
            // Update the places we need to
            if ($result->count > 0) {
                if ($result->type == 'section_block') {
                    $blockcount = $result->count;
                    // need to handle special so first find the blocks
                    $sql = "SELECT id FROM {block_instance} WHERE configdata LIKE ?";
                    $records = get_records_sql_array($sql, array('%' . $fromurl . '%'));
                    if ($records) {
                        $count = 0;
                        $limit = 1000;
                        $total = count($records);
                        require_once(get_config('docroot').'blocktype/lib.php');
                        foreach ($records as $record) {
                            $bi = new BlockInstance($record->id);
                            $configdata = $bi->get('configdata');
                            foreach ($configdata as $ck => $cv) {
                                $newvalue = preg_replace("@" . $fromurl . "@", get_config('wwwroot'), $cv);
                                if ($newvalue != $cv) {
                                    // we have changed data in the configdata option
                                    $configdata[$ck] = $newvalue;
                                    $bi->set('configdata', $configdata);
                                    $bi->commit();
                                }
                            }
                            $count++;
                            if (($count % $limit) == 0 || $count == $total) {
                                set_time_limit(30);
                            }
                        }
                    }
                }
                else {
                    execute_sql("UPDATE " . db_table_name($result->t) . " SET " . db_quote_identifier($result->f) . " = REPLACE(" . db_quote_identifier($result->f) . ", ?, ?) WHERE " . db_quote_identifier($result->f) . " LIKE ?", array($fromurl, get_config('wwwroot'), '%' . $fromurl . '%'));
                    $basiccount += $result->count;
                }
            }
        }
    }

    $SESSION->add_ok_msg(get_string('migratedbasicurls', 'admin', $basiccount));
    $SESSION->add_ok_msg(get_string('migratedblockurls', 'admin', $blockcount));

    redirect('/admin/extensions/embeddedurls.php');
}
