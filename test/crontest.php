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
 * @subpackage tests
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL',1);
define('PUBLIC',1);

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

require(dirname(dirname(__FILE__)).'/htdocs/init.php');

log_dbg('********** RUNNING CRON TEST **********');

$cronscript = get_config('libroot') . 'cron.php';

test_cron_start('Job to run every minute ... ');
test_cron_set('get_event_subscriptions', '*', '*', '*', '*', '*', null);
test_cron_set('menu_items', '*', '*', '*', '*', '*', '2006-06-01 10:03');
test_cron_run('2006-06-01 10:06');
test_cron_assert('get_event_subscriptions', '2006-06-01 10:07');
test_cron_assert('menu_items', '2006-06-01 10:07');
test_cron_finish();

test_cron_start('Job to run every n minutes ... ');
test_cron_set('get_event_subscriptions', '*/5', '*', '*', '*', '*', null);
test_cron_set('menu_items', '*/12', '*', '*', '*', '*', null);
test_cron_run('2006-06-01 10:06');
test_cron_assert('get_event_subscriptions', '2006-06-01 10:10');
test_cron_assert('menu_items', '2006-06-01 10:12');
test_cron_finish();

test_cron_start('Job to run on nth day of the month ... ');
test_cron_set('get_event_subscriptions', '21', '21', '31', '*', '*', null);
test_cron_set('menu_items', '21', '21', '15', '*', '*', null);
test_cron_run('2006-06-01 10:06');
test_cron_assert('get_event_subscriptions', '2006-07-31 21:21');
test_cron_assert('menu_items', '2006-06-15 21:21');
test_cron_finish();

test_cron_start('Job to run in a month that\'s already been this year ... ');
test_cron_set('get_event_subscriptions', '1', '12', '30', '4', '*', null);
test_cron_set('menu_items', '12', '13', '10', '5', '*', null);
test_cron_run('2006-06-01 10:06');
test_cron_assert('get_event_subscriptions', '2007-04-30 12:01');
test_cron_assert('menu_items', '2007-05-10 13:12');
test_cron_finish();

test_cron_start('Job to run on a range ... ');
test_cron_set('get_event_subscriptions', '30-40', '*', '*', '*', '*', null);
test_cron_set('menu_items', '10-30/3', '*', '*', '*', '*', null);
test_cron_run('2006-06-01 10:18');
test_cron_assert('get_event_subscriptions', '2006-06-01 10:30');
test_cron_assert('menu_items', '2006-06-01 10:19');
test_cron_finish();

test_cron_start('Job to run on on sundays (using 0 and 7) ... ');
test_cron_set('get_event_subscriptions', '*', '*', '*', '*', '0', null);
test_cron_set('menu_items', '*', '*', '*', '*', '7', null);
test_cron_run('2006-06-01 10:18');
test_cron_assert('get_event_subscriptions', '2006-06-04 00:00');
test_cron_assert('menu_items', '2006-06-04 00:00');
test_cron_finish();

test_cron_start('Job to run on a day of the week ... ');
test_cron_set('get_event_subscriptions', '1', '12', '4', '8', '3', null);
test_cron_set('menu_items', '12', '13', '4', '7', '3', null);
test_cron_run('2006-06-01 10:06');
test_cron_assert('get_event_subscriptions', '2006-08-02 12:01');
test_cron_assert('menu_items', '2006-07-04 13:12');
test_cron_finish();


test_cron_start('Pretend to do nigel\'s squash run... ');
test_cron_set('get_event_subscriptions', '0', '12', '*', '*', '3', null);
test_cron_run('2006-10-30 16:56');
test_cron_assert('get_event_subscriptions', '2006-11-01 12:00');
test_cron_finish();


test_cron_start('Update mochikit documentation... ');
test_cron_set('get_event_subscriptions', '0', '7', '*', '*', '*', null);
test_cron_run('2006-10-30 16:56');
test_cron_assert('get_event_subscriptions', '2006-10-31 7:00');
test_cron_finish();


test_cron_start('Run moodle cron.php every 5 minutes... ');
test_cron_set('get_event_subscriptions', '*/5', '*', '*', '*', '*', null);
test_cron_run('2006-10-30 16:56');
test_cron_assert('get_event_subscriptions', '2006-10-30 17:00');
test_cron_finish();


test_cron_start('Test yearly boundary (run on 1st jan)... ');
test_cron_set('get_event_subscriptions', '*', '*', '1', '1', '*', null);
test_cron_run('2006-10-30 16:56');
test_cron_assert('get_event_subscriptions', '2007-01-01 0:00');
test_cron_finish();

function test_cron_set($callfunction, $minute, $hour, $day, $month, $dayofweek, $nextrun = null) {
    delete_records('artefact_cron', 'plugin', 'internal', 'callfunction', $callfunction);

    insert_record(
        'artefact_cron',
        (object) array (
            'plugin'          => 'internal',
            'callfunction'    => $callfunction,
            'nextrun'         => $nextrun,
            'minute'          => $minute,
            'hour'            => $hour,
            'day'             => $day,
            'month'           => $month,
            'dayofweek'       => $dayofweek
        )
    );
}

function test_cron_assert($callfunction, $nextrun) {
    global $errors;

    $record = get_record_select(
        'artefact_cron',
        'callfunction = ?',
        array($callfunction),
        'nextrun'
    );

    $nextrun = strtotime($nextrun);
    $realrun = strtotime($record->nextrun);

    if ($nextrun != $realrun) {
        echo("\n\tAssertion failed in '$callfunction': expected " . test_cron_prettydate($nextrun) . " != actual " . test_cron_prettydate($realrun) . "\n");
        $errors++;
    }
}

function test_cron_start($text) {
    global $errors;

    echo $text;

    $errors = 0;
}

function test_cron_run($stamp) {
    global $cronscript;

    exec("php $cronscript " . escapeshellarg($stamp));
}

function test_cron_finish() {
    global $errors;

    if ($errors) {
        echo("FAILED\n");
    }
    else {
        echo("OK\n");
    }
}

function test_cron_prettydate($timestamp) {
    return db_format_timestamp($timestamp);
}
