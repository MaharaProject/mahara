<?php
/**
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
define('MENUITEM', 'development/updateurls');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('embeddedurlstitle1', 'admin'));

$checkurlraw = param_variable('checkurl', null);

$siteurl = get_config('wwwroot');
$sitepath = '%' . $siteurl . '%';
if ($checkurlraw === null) {
    $checkpath = '%/artefact/%';  // basic check to see if any potential wrong URLs
}
else {
    // Pull the domain from the URL so we don't need to care about the protocol
    $checkurl = parse_url($checkurlraw);
    $checkpath = '%' . $checkurl['host'] . '%';
}
// Check to see if there are potential embedded URLs to update
// These queries are of the form:
//    count: the number of records that match the query
//    type: the type of records. Use as the string key and a selector when processing
//    t: the table name the records live in
//    f: the field the content lives in.
// @see migrateurls_submit() for processing of the records
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
                                  WHERE configdata LIKE ? AND configdata NOT LIKE ?
                                  UNION
                                  SELECT COUNT(*) AS count, 'section_wall_post' AS type, 'blocktype_wall_post' AS t, 'text' AS f FROM {blocktype_wall_post}
                                  WHERE text LIKE ? AND text NOT LIKE ?
                                  UNION
                                  SELECT COUNT(*) AS count, 'section_verification_comment' AS type, 'blocktype_verification_comment' AS t, 'text' AS f FROM {blocktype_verification_comment}
                                  WHERE text LIKE ? AND text NOT LIKE ?
                                  UNION
                                  SELECT COUNT(*) AS count, 'section_artefact_resume_membership' AS type, 'artefact_resume_membership' AS t, 'description' AS f FROM {artefact_resume_membership}
                                  WHERE description LIKE ? AND description NOT LIKE ?
                                  UNION
                                  SELECT COUNT(*) AS count, 'section_artefact_resume_employmenthistory' AS type, 'artefact_resume_employmenthistory' AS t, 'positiondescription' AS f FROM {artefact_resume_employmenthistory}
                                  WHERE positiondescription LIKE ? AND positiondescription NOT LIKE ?
                                  UNION
                                  SELECT COUNT(*) AS count, 'section_artefact_resume_educationhistory' AS type, 'artefact_resume_educationhistory' AS t, 'qualdescription' AS f FROM {artefact_resume_educationhistory}
                                  WHERE qualdescription LIKE ? AND qualdescription NOT LIKE ?
                                  UNION
                                  SELECT COUNT(*) AS count, 'section_artefact_resume_certification' AS type, 'artefact_resume_certification' AS t, 'description' AS f FROM {artefact_resume_certification}
                                  WHERE description LIKE ? AND description NOT LIKE ?
                                  UNION
                                  SELECT COUNT(*) AS count, 'section_artefact_resume_book' AS type, 'artefact_resume_book' AS t, 'description' AS f FROM {artefact_resume_book}
                                  WHERE description LIKE ? AND description NOT LIKE ?
                                  UNION
                                  SELECT COUNT(*) AS count, 'section_static_pages' AS type, 'site_content' AS t, 'content' AS f FROM {site_content}
                                  WHERE content LIKE ? AND content NOT LIKE ?",
                                 array($checkpath, $sitepath,
                                       $checkpath, $sitepath,
                                       $checkpath, $sitepath,
                                       $checkpath, $sitepath,
                                       $checkpath, $sitepath,
                                       $checkpath, $sitepath,
                                       $checkpath, $sitepath,
                                       $checkpath, $sitepath,
                                       $checkpath, $sitepath,
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
            'confirm'      => get_string('migrateurlsconfirm1', 'admin'),
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
setpageicon($smarty, 'icon-link');

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

    $basiccount = 0;
    $blockcount = 0;
    if (is_array($results)) {
        // Parse the URL.
        $parsedurl = parse_url($values['fromurl']);
        $fromhost = $parsedurl['host'];
        $protocols = ['https://', 'http://', '//'];
        foreach ($protocols as $protocol) {
            // It is possible to end up with a mixture of 'http://',
            // 'https://',and sometimes a protocolless '//' as the protocols.
            // We will do the replacement on all of these options.
            $fromurl = $protocol . $fromhost . '/';
            foreach ($results as $result) {
                // Update the URL in the places it needs updating.
                if ($result->count > 0) {
                    // Currently we have 2 types of data that we are
                    // processing.
                    // If a field contains serialized data, we need to
                    // unserialize it, update the data, and then reserialize
                    // it before saving it again. Currently "section_block" is
                    // the only type that contains serialized data.
                    // If a field is just plain text, we can just do a simple
                    // string replace. This is the "else" part of the condition.
                    if ($result->type == 'section_block') {
                        // This processes the configdata field in the
                        // block_instance table.
                        // If other types added that contain serialized data,
                        // this section will need to be made more generic or an
                        // "else if" added for the new type.
                        // Also note that this only works with key/value pairs.
                        // If the serialized data contains nested arrays, this
                        // will not work.
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
                        // If the field just contains raw html/text then we can
                        // just do a simple update.
                        execute_sql("UPDATE " . db_table_name($result->t) . " SET " . db_quote_identifier($result->f) . " = REPLACE(" . db_quote_identifier($result->f) . ", ?, ?) WHERE " . db_quote_identifier($result->f) . " LIKE ?", array($fromurl, get_config('wwwroot'), '%' . $fromurl . '%'));
                        $basiccount += $result->count;
                    }
                }
            }
        }
    }

    $SESSION->add_ok_msg(get_string('migratedbasicurls', 'admin', $basiccount));
    $SESSION->add_ok_msg(get_string('migratedblockurls', 'admin', $blockcount));

    redirect('/admin/extensions/embeddedurls.php');
}
