<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Cecilia Vela Gurovic
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('CLI', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require(get_config('libroot') . 'cli.php');
require(get_config('libroot') . 'upgrade.php');
require(get_config('docroot') . 'local/install.php');

$cli = get_cli();

$options = array();
$options['delete'] = (object) array(
  'shortoptions' => array('d'),
  'description' => get_string('cli_outcomes_delete_description', 'admin'),
  'required' => false,
  'defaultvalue' => false,
);

$options['typefile'] = (object) array(
  'shortoptions' => array('t'),
  'description' => get_string('cli_outcomes_typefile_description', 'admin'),
  'required' => false,
);

$options['subjectfile'] = (object) array(
  'shortoptions' => array('s'),
  'description' => get_string('cli_outcomes_subjectfile_description', 'admin'),
  'required' => false,
);

$options['institution'] = (object) array(
  'shortoptions' => array('i'),
  'description' => get_string('cli_outcomes_institution_description', 'admin'),
  'required' => false,
);

$settings = new stdClass();
$settings->options = $options;
$settings->info = get_string('cli_outcomes_info', 'admin');

$cli->setup($settings);

$delete      = $cli->get_cli_param_boolean('delete');
$typefile    = $cli->get_cli_param('typefile');
$subjectfile = $cli->get_cli_param('subjectfile');
$institution = $cli->get_cli_param('institution');

if ($typefile) {
  if (!file_exists($typefile)) {
    $cli->cli_exit(get_string('cli_portfolio_export_filenotfound', 'admin'));
  }
}

if ($subjectfile) {
  if (!file_exists($subjectfile)) {
    $cli->cli_exit(get_string('cli_portfolio_export_filenotfound', 'admin'));
  }
}

if ($institution) {
  if (!get_field('institution', 'id', 'name', $institution)) {
    $cli->cli_exit(get_string('cli_outcomes_institutionnotfound', 'admin', $institution));
  }
}

// Check whether Mahara is installed yet
if (!table_exists(new XMLDBTable('config'))) {
    $cli->cli_exit(get_string('maharanotinstalled', 'admin'), false);
}

if (!$delete && !$typefile && !$subjectfile) {
  $cli->cli_print_help();
  $cli->cli_exit();
}

if ($delete) {
    $wheresql = '';
    $where = array();
    if ($institution) {
        $wheresql .= " AND institution = ?";
        $where[] = $institution;
    }
    try {
        $cli->cli_print(get_string('cli_outcomes_deleteing', 'admin'));
        $deletablesubjectcategories = get_records_sql_array("
            SELECT os.id AS osid, osc.id AS oscid FROM {outcome_subject} os
            JOIN {outcome_subject_category} osc ON osc.id = os.outcome_subject_category
            WHERE os.id NOT IN (
                SELECT subject FROM {view_activity}
            ) " . $wheresql, $where);
        if ($deletablesubjectcategories) {
            $subjects = array();
            $subjectcategories = array();
            foreach($deletablesubjectcategories as $item) {
                $subjects[$item->osid] = true;
                $subjectcategories[$item->oscid] = true;
            }
            if (!empty($subjects)) {
                execute_sql("DELETE FROM {outcome_subject} WHERE id IN (" . implode(',', array_keys($subjects)) . ")");
                $cli->cli_print(get_string('cli_outcomes_deleted', 'admin', count($subjects), 'outcome_subject'));
            }
            if (!empty($subjectcategories)) {
                execute_sql("DELETE FROM {outcome_subject_category} WHERE id IN (" . implode(',', array_keys($subjectcategories)) . ") AND id NOT IN (SELECT outcome_subject_category FROM {outcome_subject})");
                $cli->cli_print(get_string('cli_outcomes_deleted', 'admin', count($subjectcategories), 'outcome_subject_category'));
            }
        }
        $deletabletypecategories = get_records_sql_array("
            SELECT ot.id AS otid, oc.id AS ocid FROM {outcome_type} ot
            JOIN {outcome_category} oc ON oc.id = ot.outcome_category
            WHERE ot.id NOT IN (
                SELECT outcome_type FROM {outcome}
            ) " . $wheresql, $where);
        if ($deletabletypecategories) {
            $types = array();
            $categories = array();
            foreach($deletabletypecategories as $item) {
                $types[$item->otid] = true;
                $categories[$item->ocid] = true;
            }
            if (!empty($types)) {
                execute_sql("DELETE FROM {outcome_type} WHERE id IN (" . implode(',', array_keys($types)) . ")");
                $cli->cli_print(get_string('cli_outcomes_deleted', 'admin', count($types), 'outcome_type'));
            }
            if (!empty($categories)) {
                execute_sql("DELETE FROM {outcome_category} WHERE id IN (" . implode(',', array_keys($categories)) . ") AND id NOT IN (SELECT outcome_category FROM {outcome_type})");
                $cli->cli_print(get_string('cli_outcomes_deleted', 'admin', count($categories), 'outcome_category'));
            }
        }
        if (empty($deletabletypecategories) && empty($deletablesubjectcategories)) {
            $cli->cli_print(get_string('cli_outcomes_nothing_deleted', 'admin'));
        }
    }
    catch (Exception $e) {
        $cli->cli_print($e->getMessage());
    }
}
else if ($typefile) {
    $dataadded = false;
    $csv = array_map('str_getcsv', file($typefile));
    array_walk($csv, function(&$a) use ($csv) {
        $a = array_combine($csv[0], $a);
    });
    $headers = array_shift($csv); // remove column header
    if ($headers && $csv) {
        if (!check_type_headers($headers)) {
            $cli->cli_exit(get_string('cli_outcomes_type_headers_error', 'admin'));
        }
        foreach ($csv as $k => $data) {
            // Check whether we have an institution with that name and with outcomes set up
            if (!record_exists('institution', 'name', $data['Institution'], 'outcomeportfolio', '1')) {
                $cli->cli_exit(get_string('cli_outcomes_bad_institution', 'admin', $data['Institution']), false);
            }
            $outcome_category_id =  ensure_type_category_exists($data['Outcome category'], $data['Institution']);
            $dataadded = ensure_type_exists($data['Outcome type'], $data['Outcome type abbreviation'], $data['CSS class'], $outcome_category_id);
        }
    }
    if ($dataadded) {
        $cli->cli_exit(get_string('cli_outcomes_type_added', 'admin'), false);
    }
    else {
        $cli->cli_exit(get_string('cli_outcomes_no_type_added', 'admin'), false);
    }
}
else if ($subjectfile) {
    $dataadded = false;
    $csv = array_map('str_getcsv', file($subjectfile));
    array_walk($csv, function(&$a) use ($csv) {
        $a = array_combine($csv[0], $a);
    });
    $headers = array_shift($csv); // remove column header
    if ($headers && $csv) {
        if (!check_subject_headers($headers)) {
            $cli->cli_exit('Check headers in your file, they should be: Outcome subject category | Institution | Subject | Subject abbreviation');
        }
        foreach ($csv as $k => $data) {
            // Check whether we have an institution with that name and with outcomes set up
            if (!record_exists('institution', 'name', $data['Institution'], 'outcomeportfolio', '1')) {
                $cli->cli_exit(get_string('cli_outcomes_bad_institution', 'admin', $data['Institution']), false);
            }
            $outcome_subject_category_id = ensure_subject_category_exists($data['Outcome subject category'], $data['Institution']);
            $dataadded = ensure_subject_exists($data['Subject'], $data['Subject abbreviation'], $outcome_subject_category_id);
        }
    }
    if ($dataadded) {
        $cli->cli_exit(get_string('cli_outcomes_subject_added', 'admin'), false);
    }
    else {
        $cli->cli_exit(get_string('cli_outcomes_no_subject_added', 'admin'), false);
    }
}

/**
 * Helper function to check types file has correct headers
 * @param array $headers list of headers from csv file
 * @return boolean
 */
function check_type_headers($headers) {
    $defaultheaders = array('Outcome category', 'Institution', 'Outcome type', 'Outcome type abbreviation', 'CSS class');
    return array_intersect($defaultheaders, $headers) === $defaultheaders;
}

/**
 * Helper function to check subjects file has correct headers
 * @param array $headers list of headers from csv file
 * @return boolean
 */
function check_subject_headers($headers) {
    $defaultheaders = array('Outcome subject category', 'Institution', 'Subject', 'Subject abbreviation');
    return array_intersect($defaultheaders, $headers) === $defaultheaders;
}

/**
 * Saves outcome_type_category data if not already on the DB
 * @return int outcome_type_category id from DB
 */
function ensure_type_category_exists($category_title, $institution_name) {
    $outcome_category_db = get_record('outcome_category', 'title', $category_title, 'institution', $institution_name);
    if (!$outcome_category_db) {
        $outcome_category = (object) array(
            'title'       => $category_title,
            'institution' => $institution_name
        );
        $outcome_category_id = insert_record('outcome_category', $outcome_category, false, true);
    }
    else {
        $outcome_category_id = $outcome_category_db->id;
    }
    return $outcome_category_id;
}

/**
 * Saves outcome_type data if not already on the DB
 * returns boolean to indicate if data has been added to the DB
 */
function ensure_type_exists($type_title, $type_abbreviation, $type_styleclass, $outcome_category_id){
    $outcome_type_db = get_record('outcome_type', 'title', $type_title, 'abbreviation', $type_abbreviation);
    if (!$outcome_type_db) {
        $outcome_type = (object) array(
            'title' => $type_title,
            'abbreviation' => $type_abbreviation,
            'styleclass' => $type_styleclass,
            'outcome_category' => $outcome_category_id,
        );
        insert_record('outcome_type', $outcome_type);
        return true;
    }
    return false;
}

/**
 * Saves outcome_subject_category data if not already on the DB
 * @return int outcome_subject_category id from DB
 */
function ensure_subject_category_exists($category_name, $institution_name) {
    $outcome_subject_category_db = get_record('outcome_subject_category', 'name', $category_name, 'institution', $institution_name);
    if (!$outcome_subject_category_db) {
        $outcome_subject_category = (object) array(
            'name'        => $category_name,
            'institution' => $institution_name
        );
        $outcome_subject_category_id = insert_record('outcome_subject_category', $outcome_subject_category, false, true);
    }
    else {
        $outcome_subject_category_id = $outcome_subject_category_db->id;
    }
    return $outcome_subject_category_id;
}

/**
 * Saves outcome_subject data if not already the DB
 * returns boolean to indicate if data has been added to the DB
 */
function ensure_subject_exists($subject_title, $subject_abbreviation, $outcome_subject_category_id) {
    $outcome_subject_db = get_record('outcome_subject', 'title', $subject_title, 'abbreviation', $subject_abbreviation);
    if (!$outcome_subject_db) {
        $outcome_subject = (object) array(
            'title'                    => $subject_title,
            'abbreviation'             => $subject_abbreviation,
            'outcome_subject_category' => $outcome_subject_category_id,
        );
        insert_record('outcome_subject', $outcome_subject);
        return true;
    }
    return false;
}