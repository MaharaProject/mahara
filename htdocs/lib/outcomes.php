<?php

/**
 *  Setup functions for Outcomes portfolio
 *
 * @package    mahara
 * @subpackage core
 * @author     Cecilia Vela Gurovic ~ do.t
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die(); // This is called internally only.

/**
 * Saves outcome_type_category data if not already on the DB
 * @return int outcome_type_category id from DB
 */
function ensure_type_category_exists(string $category_title, string $institution_name): int {
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
function ensure_type_exists(string $type_title, string $type_abbreviation, string $type_styleclass, $outcome_category_id): bool {
  $outcome_type_db = get_record('outcome_type', 'title', $type_title, 'abbreviation', $type_abbreviation);
  if (!$outcome_type_db) {
    $outcome_type = (object) array(
      'title' => $type_title,
      'abbreviation' => $type_abbreviation,
      'styleclass' => $type_styleclass,
      'outcome_category' => intval($outcome_category_id),
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
function ensure_subject_category_exists(string $category_name, string $institution_name): int {
  $outcome_subject_category_db = get_record(
    'outcome_subject_category',
    'name',
    $category_name,
    'institution',
    $institution_name
  );
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
function ensure_subject_exists(string $subject_title, string $subject_abbreviation, int $outcome_subject_category_id): bool {
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


/**
 * Process type file for outcomes
 *
 * Creates outcome types
 *
 * @param string $typefile  The path to a readable CSV file
 * @throws Exception on unexpected file content
 */
function process_type_file(string $typefile): bool {
  $dataadded = false;
  $csv = array_map('str_getcsv', file($typefile));
  array_walk($csv, function (&$a) use ($csv) {
    $a = array_combine($csv[0], $a);
  });
  $headers = array_shift($csv); // remove column header
  if ($headers && $csv) {
    if (!check_type_headers($headers)) {
      throw new MaharaException(get_string('cli_outcomes_type_headers_error', 'admin'));
    }
    foreach ($csv as $k => $data) {
      // Check whether we have an institution with that name and with outcomes set up
      if (!record_exists('institution', 'name', $data['Institution'], 'outcomeportfolio', '1')) {
        throw new MaharaException(
          get_string('cli_outcomes_bad_institution', 'admin', $data['Institution']),
          false
        );
      }
      $outcome_category_id =  ensure_type_category_exists($data['Outcome category'], $data['Institution']);
      $typedataadded = ensure_type_exists(
        $data['Outcome type'],
        $data['Outcome type abbreviation'],
        $data['CSS class'],
        $outcome_category_id
      );
      if ($typedataadded) {
        $dataadded = true;
      }
    }
  }
  return $dataadded;
}

/**
 * Process subject file for outcomes
 *
 * Creates:
 * - outcome categories (for outcome subjects and outcome types)
 * - outcome subjects
 *
 * @param string $subjectfile  The path to a readable CSV file
 * @throws Exception on unexpected file content
 */
function process_subject_file(string $subjectfile): bool {
  $dataadded = false;
  $csv = array_map('str_getcsv', file($subjectfile));
  array_walk($csv, function (&$a) use ($csv) {
    $a = array_combine($csv[0], $a);
  });
  $headers = array_shift($csv); // remove column header
  if ($headers && $csv) {
    if (!check_subject_headers($headers)) {
      throw new MaharaException(get_string('cli_outcomes_subject_headers_error', 'admin'));
    }
    foreach ($csv as $k => $data) {
      // Check whether we have an institution with that name and with outcomes set up
      if (!record_exists('institution', 'name', $data['Institution'], 'outcomeportfolio', '1')) {
        throw new MaharaException(get_string('cli_outcomes_bad_institution', 'admin', $data['Institution']));
      }
      $outcome_subject_category_id = ensure_subject_category_exists(
        $data['Outcome subject category'],
        $data['Institution']
      );
      $subjectdataadded = ensure_subject_exists(
        $data['Subject'],
        $data['Subject abbreviation'],
        $outcome_subject_category_id
      );
      if ($subjectdataadded) {
        $dataadded = true;
      }
    }
  }
  return $dataadded;
}
