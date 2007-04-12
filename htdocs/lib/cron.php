<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage core
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('CRON', 1);

/**
  * This defines (in seconds) how far PAST the next run time of a cron job
  * we're allowed to get and still go back and run it.
  *
  * For both these examples we will assume a value of 300 (seconds)
  *
  * example 1: If we have a job that was meant to run at 10:45am, and it's now
  * 10:48am we know that the job was meant to run 3 minutes (180 seconds) ago.
  * This is within the threshold MAXRUNAGE and so we will run the job, and
  * update the next run time.
  *
  * example 2: If we have a job that was meant to run at 9:34am, and it's now
  * 9:40am we know the job _should_ have been run, but it's too late now. We
  * DON'T run the job, but do update it's next run time.
 */
define('MAXRUNAGE', 300);


require(dirname(dirname(__FILE__)).'/init.php');
require('artefact.php');

// This is here for debugging purposes, it allows us to fake the time to test
// cron behaviour
if(isset($argv[1])) {
    $now = strtotime($argv[1]);
}
else {
    $now = time();
}

log_debug('---------- cron running ' . date('r', $now) . ' ----------');

// for each plugin type
foreach (plugin_types() as $plugintype) {

    // get list of cron jobs to run for this plugin type
    $jobs = get_records_select_array(
        $plugintype . '_cron',
        'nextrun >= ? AND nextrun < ?',
        array(db_format_timestamp($now - MAXRUNAGE), db_format_timestamp($now)),
        '',
        'plugin,callfunction,minute,hour,day,month,dayofweek,' . db_format_tsfield('nextrun')
    );

    if ($jobs) {
        // for each cron entry
        foreach ($jobs as $job) {
            $classname = generate_class_name($plugintype, $job->plugin);

            log_debug("Running $classname::" . $job->callfunction);

            safe_require($plugintype, $job->plugin, 'lib.php', 'require_once');
            call_static_method(
                $classname,
                $job->callfunction
            );

            $nextrun = cron_next_run_time($now, (array)$job);

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
        }
    }

    // get a list of cron jobs that should have, but didn't get run
    $jobs = get_records_select_array(
        $plugintype . '_cron',
        'nextrun < ? OR nextrun IS NULL',
        array(db_format_timestamp($now - MAXRUNAGE)),
        '',
        'plugin,callfunction,minute,hour,day,month,dayofweek,nextrun'
    );

    if ($jobs) {
        // for each cron entry
        foreach ($jobs as $job) {
            if ($job->nextrun) {
                log_warn('cronjob "' . $job->plugin . '.' . $job->callfunction . '" didn\'t get run because the nextrun time was too old');
            }
            
            $nextrun = cron_next_run_time($now, (array)$job);

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
        }
    }
}

// and now the core ones (much simpler)
if ($jobs = get_records_select_array('cron', 'nextrun >= ? AND nextrun < ?',
    array(db_format_timestamp($now - MAXRUNAGE), db_format_timestamp($now)))) {
    foreach ($jobs as $job) {
        log_debug("Running core cron " . $job->callfunction);

        $function = $job->callfunction;
        $function();
        
        $nextrun = cron_next_run_time($now, (array)$job);
        
        // update next run time
        set_field('cron', 'nextrun', db_format_timestamp($nextrun), 'id', $job->id);
    }
}

// and missed ones...
if ($jobs = get_records_select_array('cron', 'nextrun < ? OR nextrun IS NULL',
    array(db_format_timestamp($now - MAXRUNAGE)))) {
    foreach ($jobs as $job) {
      if ($job->nextrun) {
          log_warn('core cronjob "' . $job->callfunction 
              . '" didn\'t get run because the nextrun time was too old');
      }
      
      $nextrun = cron_next_run_time($now, (array)$job);
      
      // update next run time
      set_field('cron', 'nextrun', db_format_timestamp($nextrun), 'id', $job->id);
    }
}


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

?>
