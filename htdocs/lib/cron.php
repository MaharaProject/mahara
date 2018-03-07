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
define('PUBLIC', 1);
define('CRON', 1);
define('TITLE', '');

require(dirname(dirname(__FILE__)).'/init.php');
require_once(get_config('docroot') . 'artefact/lib.php');
require_once(get_config('docroot') . 'import/lib.php');
require_once(get_config('docroot') . 'export/lib.php');
require_once(get_config('docroot') . 'lib/activity.php');
require_once(get_config('docroot') . 'lib/file.php');
require_once(get_config('docroot') . 'webservice/lib.php');

// If we are running behat tests, we only run cron via the behat step:
// I trigger (the )?cron
if (defined('BEHAT_TEST')) {
    if (php_sapi_name() == 'cli') {
        die_info("Can not run cron from command line when behat environment is enabled");
    }
    $behattrigger = param_boolean('behattrigger', false);
    if (!$behattrigger) {
        die_info("Missing or disabled behattrigger. When behat environment is enabled, cron can only triggered using the step: I trigger (the )?cron");
    }
}

// Check if we have come via browser and have the right urlsecret
// Note: if your crontab hits this file via curl/http thenyou will need
// to add the urlsecret there for the cron to work.
if (php_sapi_name() != 'cli' && get_config('urlsecret') !== null) {
    $urlsecret = param_alphanumext('urlsecret', -1);
    if ($urlsecret !== get_config('urlsecret')) {
        die_info(get_string('accessdeniednourlsecret', 'error'));
    }
}
// This is here for debugging purposes, it allows us to fake the time to test
// cron behaviour
$realstart = time();
$fake = isset($argv[1]);
$start = $fake ? strtotime($argv[1]) : $realstart;

log_info('---------- cron running ' . date('r', $start) . ' ----------');

if (!is_writable(get_config('dataroot'))) {
    log_warn("Unable to write to dataroot directory.");
}

// cron jobs (callfunction as in 'cron' table)
// that need to drop the elasticsearch triggers
$jobsneeddroptriggers = array(
    'recalculate_quota',
    'cron_site_data_daily',
    'user_login_tries_to_zero',
    'interaction_forum_new_post',
);

// for each plugin type
foreach (plugin_types() as $plugintype) {

    $table = $plugintype . '_cron';

    // get list of cron jobs to run for this plugin type
    $now = $fake ? (time() - ($realstart - $start)) : time();
    $jobs = get_records_select_array(
        $table,
        'nextrun < ? OR nextrun IS NULL',
        array(db_format_timestamp($now)),
        '',
        'plugin,callfunction,minute,hour,day,month,dayofweek,' . db_format_tsfield('nextrun')
    );

    if ($jobs) {
        // for each cron entry
        foreach ($jobs as $job) {
            if (!cron_lock($job, $start, $plugintype)) {
                continue;
            }

            // If some other cron instance ran the job while we were messing around,
            // skip it.
            $nextrun = get_field_sql('
                SELECT ' . db_format_tsfield('nextrun') . '
                FROM {' . $table . '}
                WHERE plugin = ? AND callfunction = ?',
                array($job->plugin, $job->callfunction)
            );
            if ($nextrun != $job->nextrun) {
                log_info("Too late to run $plugintype $job->plugin $job->callfunction; skipping.");
                cron_free($job, $start, $plugintype);
                continue;
            }

            $classname = generate_class_name($plugintype, $job->plugin);

            log_info("Running $classname::" . $job->callfunction);

            safe_require($plugintype, $job->plugin, 'lib.php', 'require_once');

            $droptriggers = in_array($job->callfunction, $jobsneeddroptriggers);
            if ($droptriggers) {
                drop_elasticsearch_triggers();
            }

            try {
                call_static_method($classname, $job->callfunction);
            }
            catch (Exception $e) {
                log_message($e->getMessage(), LOG_LEVEL_WARN, true, true, $e->getFile(), $e->getLine(), $e->getTrace());
                $output = $e instanceof MaharaException ? $e->render_exception() : $e->getMessage();
                echo "$output\n";
                // Don't call handle_exception; try to update next run time and free the lock
            }

            if ($droptriggers) {
                create_elasticsearch_triggers();
            }

            $nextrun = cron_next_run_time($start, (array)$job);

            // update next run time
            set_field(
                $plugintype . '_cron',
                'nextrun',
                db_format_timestamp($nextrun),
                'plugin',
                $job->plugin,
                'callfunction',
                $job->callfunction
            );

            cron_free($job, $start, $plugintype);
            $now = $fake ? (time() - ($realstart - $start)) : time();
        }
    }
}

// and now the core ones (much simpler)
$now = $fake ? (time() - ($realstart - $start)) : time();
$jobs = get_records_select_array(
    'cron',
    'nextrun < ? OR nextrun IS NULL',
    array(db_format_timestamp($now)),
    '',
    'id,callfunction,minute,hour,day,month,dayofweek,' . db_format_tsfield('nextrun')
);
if ($jobs) {
    foreach ($jobs as $job) {
        if (!cron_lock($job, $start)) {
            continue;
        }

        // If some other cron instance ran the job while we were messing around,
        // skip it.
        $nextrun = get_field_sql('
            SELECT ' . db_format_tsfield('nextrun') . '
            FROM {cron}
            WHERE id = ?',
            array($job->id)
        );
        if ($nextrun != $job->nextrun) {
            log_info("Too late to run core $job->callfunction; skipping.");
            cron_free($job, $start);
            continue;
        }

        log_info("Running core cron " . $job->callfunction);

        $function = $job->callfunction;

        $droptriggers = in_array($job->callfunction, $jobsneeddroptriggers);
        if ($droptriggers) {
            drop_elasticsearch_triggers();
        }

        try {
            $function();
        }
        catch (Exception $e) {
            log_message($e->getMessage(), LOG_LEVEL_WARN, true, true, $e->getFile(), $e->getLine(), $e->getTrace());
            $output = $e instanceof MaharaException ? $e->render_exception() : $e->getMessage();
            echo "$output\n";
            // Don't call handle_exception; try to update next run time and free the lock
        }

        if ($droptriggers) {
            create_elasticsearch_triggers();
        }

        $nextrun = cron_next_run_time($start, (array)$job);

        // update next run time
        set_field('cron', 'nextrun', db_format_timestamp($nextrun), 'id', $job->id);

        cron_free($job, $start);
        $now = $fake ? (time() - ($realstart - $start)) : time();
    }
}

$finish = time();

//Time relative to fake cron time
if (isset($argv[1])) {
    $diff = $realstart - $start;
    $finish = $finish - $diff;
}
log_info('---------- cron finished ' . date('r', $finish) . ' ----------');

function cron_next_run_time($lastrun, $job) {
    $run_date = getdate($lastrun);

    // we don't care about seconds for cron
    $run_date['seconds'] = 0;

    // assert valid month
    if (!cron_valid_month($job, $run_date)) {
        cron_next_month($job, $run_date);

        cron_first_day($job, $run_date);
        cron_first_hour($job, $run_date);
        cron_first_minute($job, $run_date);

        return datearray_to_timestamp($run_date);
    }

    // assert valid day
    if (!cron_valid_day($job, $run_date)) {
        cron_next_day($job, $run_date);

        cron_first_hour($job, $run_date);
        cron_first_minute($job, $run_date);

        return datearray_to_timestamp($run_date);
    }

    // assert valid hour
    if (!cron_valid_hour($job, $run_date)) {
        cron_next_hour($job, $run_date);

        cron_first_minute($job, $run_date);

        return datearray_to_timestamp($run_date);
    }

    cron_next_minute($job, $run_date);

    return datearray_to_timestamp($run_date);

}

function datearray_to_timestamp($date_array) {
    return mktime(
        $date_array['hours'],
        $date_array['minutes'],
        $date_array['seconds'],
        $date_array['mon'],
        $date_array['mday'],
        $date_array['year']
    );
}

/**
  * Determine next value for a single cron field
  *
  * This function is designed to parse a cron field specification and then
  * given a current value of the field, determine the next value of that field.
  *
  * @param $fieldspec Cron field specification (e.g. "3,7,20-30,40-50/2")
  * @param $currentvalue Current value of this field
  * @param $ceiling Maximum value this field can take (e.g. for minutes this would be set to 60)
  * @param &$propagate Determines (a) if this value can remain at current value
  * or not, (b) returns true if this field wrapped to zero to find the next
  * value.
  * @param &$steps Returns the number of steps that were taken to get from currentvalue to the next value.
  * @param $allowzero Is this field allowed to be 0?
  * @param $ceil_zero_same If the fieldspec has a number equivalent of ceiling in it, is that the same as 0?
  *
  * @return The next value for this field
  */
function cron_next_field_value($fieldspec, $currentvalue, $ceiling, &$propagate, &$steps, $allowzero = true, $ceil_zero_same = false) {
    $timeslices = array_pad(Array(), $ceiling, false);

    foreach ( explode(',',$fieldspec) as $spec ) {
		if (preg_match("~^(\\*|([0-9]{1,2})(-([0-9]{1,2}))?)(/([0-9]{1,2}))?$~",$spec,$matches)) {
            if ($matches[1] == '*') {
                $from = 0;
                $to   = $ceiling - 1;
            }
            else {
                $from = $matches[2];
                if (isset($matches[4])) {
                    $to   = $matches[4];
                }
                else {
                    $to   = $from;
                }
            }
            if (isset($matches[6])) {
                $step = $matches[6];
            }
            else {
                $step = 1;
            }

            for ($i = $from; $i <= $to; $i += $step) {
                if ($ceil_zero_same && $i == $ceiling) {
                    $timeslices[0] = true;
                }
                else {
                    $timeslices[$i] = true;
                }
            }

        }
    }

    // the previous field wrapped, this one HAS to change
    if ($propagate) {
        $currentvalue++;
        $steps = 1;
    }
    else {
        $steps = 0;
    }

    for ($currentvalue; $currentvalue < $ceiling; $currentvalue++, $steps++) {
        if ($timeslices[$currentvalue]) {
            break;
        }
    }

    // if we found a value
    if ($currentvalue != $ceiling) {
        $propagate = 0;
        return $currentvalue;
    }

    for ($currentvalue= ($allowzero ? 0 : 1); $currentvalue < $ceiling; $currentvalue++, $steps++) {
        if ($timeslices[$currentvalue]) {
            break;
        }
    }

    $propagate = 1;
    return $currentvalue;
}

function cron_day_of_week($date_array) {
    return date('w', mktime(0, 0, 0, $date_array['mon'], $date_array['mday'], $date_array['year']));
}

// --------------------------------------------------------

function cron_valid_month($job, $run_date) {
    $propagate = 0;
    cron_next_field_value($job['month'], $run_date['mon'], 13, $propagate, $steps, false);

    if ($steps) {
        return false;
    }
    else {
        return true;
    }
}

function cron_valid_day($job, $run_date) {
    $propagate = 0;
    cron_next_field_value($job['day'], $run_date['mday'], 32, $propagate, $dayofmonth_steps, false);

    $propagate = 0;
    cron_next_field_value($job['dayofweek'], cron_day_of_week($run_date), 7, $propagate, $dayofweek_steps, true);

    if ($job['dayofweek'] == '*') {
        return ($dayofmonth_steps ? false : true);
    }
    else if ($job['day'] == '*') {
        return ($dayofweek_steps ? false : true);
    }
    else {
        if ($dayofmonth_steps && $dayofweek_steps) {
            return false;
        }
        else {
            return true;
        }
    }
}

function cron_valid_hour($job, $run_date) {
    $propagate = 0;
    cron_next_field_value($job['hour'], $run_date['hours'], 24, $propagate, $steps);

    if ($steps) {
        return false;
    }
    else {
        return true;
    }
}

function cron_valid_minute($job, $run_date) {
    $propagate = 0;
    cron_next_field_value($job['minute'], $run_date['minutes'], 60, $propagate, $steps);

    if ($steps) {
        return false;
    }
    else {
        return true;
    }
}

function cron_next_month($job, &$run_date) {
    $propagate = 1;
    $run_date['mon'] = cron_next_field_value($job['month'], $run_date['mon'], 13, $propagate, $steps, false);

    if ($propagate) {
        $run_date['year']++;
    }
}

function cron_next_day($job, &$run_date) {
    // work out which has less steps
    $propagate = 1;
    cron_next_field_value($job['day'], $run_date['mday'], 32, $propagate, $month_steps, false);
    $propagate = 1;
    cron_next_field_value($job['dayofweek'], cron_day_of_week($run_date), 7, $propagate, $week_steps, true, true);

    if ($job['dayofweek'] == '*') {
        $run_date['mday'] += $month_steps;
    }
    else if ($job['day'] == '*') {
        $run_date['mday'] += $week_steps;
    }
    else if ($month_steps < $week_steps) {
        $run_date['mday'] += $month_steps;
    }
    else {
        $run_date['mday'] += $week_steps;
    }

    // if the day is outside the range of this month, try again from 0
    if ($run_date['mday'] > date('t', mktime(0, 0, 0, $run_date['mon'], 1, $run_date['year']))) {
        cron_next_month($job, $run_date);

        cron_first_day($job, $run_date);
    }
}

function cron_next_hour($job, &$run_date) {
    $propagate = 1;
    $run_date['hours'] = cron_next_field_value($job['hour'], $run_date['hours'], 24, $propagate, $steps);

    if ($propagate) {
        cron_next_day($job, $run_date);
    }
}

function cron_next_minute($job, &$run_date) {
    $propagate = 1;
    $run_date['minutes'] = cron_next_field_value($job['minute'], $run_date['minutes'], 60, $propagate, $steps);

    if ($propagate) {
        cron_next_hour($job, $run_date);
    }
}

function cron_first_day($job, &$run_date) {
    $propagate = 0;
    cron_next_field_value($job['day'], 1, 32, $propagate, $month_steps, false);

    $propagate = 0;
    $run_date['mday'] = 1;
    cron_next_field_value($job['dayofweek'], cron_day_of_week($run_date), 7, $propagate, $week_steps, true, true);

    if ($job['dayofweek'] == '*') {
        $run_date['mday'] += $month_steps;
    }
    else if ($job['day'] == '*') {
        $run_date['mday'] += $week_steps;
    }
    else if ($month_steps < $week_steps) {
        $run_date['mday'] += $month_steps;
    }
    else {
        log_debug('using week_steps: ' . $week_steps);
        $run_date['mday'] += $week_steps;
    }

    log_debug('    setting mday to ' . $run_date['mday']);
}

function cron_first_hour($job, &$run_date) {
    $propagate = 0;
    $run_date['hours'] = cron_next_field_value($job['hour'], 0, 24, $propagate, $steps);
}

function cron_first_minute($job, &$run_date) {
    $propagate = 0;
    $run_date['minutes'] = cron_next_field_value($job['minute'], 0, 60, $propagate, $steps);
}

function cron_job_id($job, $plugintype) {
    return $plugintype . (!empty($job->plugin) ? "_$job->plugin" : '') . '_' . $job->callfunction;
}

function cron_lock($job, $start, $plugintype='core') {
    global $DB_IGNORE_SQL_EXCEPTIONS;

    $jobname = cron_job_id($job, $plugintype);
    $lockname = '_cron_lock_' . $jobname;

    // The rationale for catching the SQLException on this insert is to
    // ensure that if two crons run simultaneously, they may both fail the
    // get_field and thus both try the insert. We try the get_field first
    // to try and limit the number of exceptions that we catch and throw.
    if (!$started = get_field('config', 'value', 'field', $lockname)) {
        try {
            $DB_IGNORE_SQL_EXCEPTIONS = true;
            insert_record('config', (object) array('field' => $lockname, 'value' => $start));
            $DB_IGNORE_SQL_EXCEPTIONS = false;
            return true;
        }
        catch (SQLException $e) {
            $DB_IGNORE_SQL_EXCEPTIONS = false;
            $started = get_field('config', 'value', 'field', $lockname);
        }
    }

    $strstart = $started ? date('r', $started) : '';
    $msg = "long-running cron job $jobname ($strstart).";

    // If it's been going for more than 24 hours, remove the lock
    if ($started && $started < $start - 60*60*24) {
        log_info('Removing lock record for ' . $msg);
        cron_free($job, $started, $plugintype);
        return false;
    }

    log_info('Skipping ' . $msg);
    return false;
}

function cron_free($job, $start, $plugintype='core') {
    delete_records('config', 'field', '_cron_lock_' . cron_job_id($job, $plugintype), 'value', $start);
}
