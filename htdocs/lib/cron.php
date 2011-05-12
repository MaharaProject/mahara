<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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

// This is here for debugging purposes, it allows us to fake the time to test
// cron behaviour
$realstart = time();
$fake = isset($argv[1]);
$start = $fake ? strtotime($argv[1]) : $realstart;

log_debug('---------- cron running ' . date('r', $start) . ' ----------');
raise_memory_limit('128M');

if (!is_writable(get_config('dataroot'))) {
    log_debug("Warning - unable to write to dataroot directory.");
}

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
                log_debug("Too late to run $plugintype $job->plugin $job->callfunction; skipping.");
                cron_free($job, $start, $plugintype);
                continue;
            }

            $classname = generate_class_name($plugintype, $job->plugin);

            log_debug("Running $classname::" . $job->callfunction);

            safe_require($plugintype, $job->plugin, 'lib.php', 'require_once');
            call_static_method(
                $classname,
                $job->callfunction
            );

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
            log_debug("Too late to run core $job->callfunction; skipping.");
            cron_free($job, $start);
            continue;
        }

        log_debug("Running core cron " . $job->callfunction);

        $function = $job->callfunction;
        $function();
        
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
log_debug('---------- cron finished ' . date('r', $finish) . ' ----------');

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

    // If it's been going for more than 24 hours, start another one anyway
    if ($started && $started < $start - 60*60*24) {
        delete_records('config', 'field', $lockname);
        insert_record('config', (object) array('field' => $lockname, 'value' => $start));
        log_debug('Restarting ' . $msg);
        return true;
    }

    log_debug('Skipping ' . $msg);
    return false;
}

function cron_free($job, $start, $plugintype='core') {
    delete_records('config', 'field', '_cron_lock_' . cron_job_id($job, $plugintype), 'value', $start);
}
