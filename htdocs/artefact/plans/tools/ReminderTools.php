<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

namespace artefact\plans\tools;


use User;
use AuthUnknownUserException;

class ReminderTools {

    /**
     * @param array $reminders
     * @return bool
     */
    public static function sendReminderNotificationToUser(array $reminders) {
        require_once(get_config('libroot') . 'activity.php');

        $userToRemind = new User();
        try {
            $userToRemind->find_by_id($reminders[0]->userid);
        }
        catch(AuthUnknownUserException $e) {
            log_warn($e->getMessage());
            return false;
        }

        $firstName = $userToRemind->get('firstname');
        $lang = (empty($userToRemind->lang) || $userToRemind->lang == 'default') ? get_config('lang') : $userToRemind->lang;
        $siteName = get_config('sitename');

        $reminderTableRowsText = '';
        foreach ($reminders as $reminder) {
            $taskTitle = $reminder->title;
            $url = get_config('wwwroot') . 'artefact/plans/task/edit.php?id=' . $reminder->taskid;
            $completionDate = format_date(strtotime($reminder->completiondate), 'strftimedaydate');
            $daysLeft = $reminder->daysleft;
            if ($daysLeft >= 0) {
                $reminderInfo = get_string_from_language($lang, 'reminderinfonearlydue', 'artefact.plans', abs($daysLeft) + 1);
            }
            else {
                $reminderInfo = get_string_from_language($lang, 'reminderinfodue', 'artefact.plans', abs($daysLeft));
            }

            $reminderTableRowsText .= "\n" . get_string_from_language($lang, 'Task', 'artefact.plans') . ': ' . $taskTitle . "\n" .
                get_string_from_language($lang, 'reminderinfo', 'artefact.plans') . ': ' . $reminderInfo . "\n" .
                get_string_from_language($lang, 'URL', 'artefact.plans') . ': ' . $url . "\n" .
                get_string_from_language($lang, 'completiondate', 'artefact.plans') . ': ' . $completionDate . "\n";
        }

        activity_occurred('maharamessage',
                          array(
                              'users' => array($userToRemind->get('id')),
                              'subject' => get_string_from_language($lang,'remindersubject', 'artefact.plans'),
                              'message' => get_string_from_language($lang,'youhaveremindertasksmessagetext', 'artefact.plans', $firstName, $reminderTableRowsText, $siteName),
                              'url' => 'artefact/plans/index.php',
                              'urltext' => get_string_from_language($lang,'visitplans', 'artefact.plans'),
                          )
        );

        return true;
    }

    /**
     * @return bool
     * @throws \SQLException
     */
    public static function check_user_reminders() {
        if (is_postgres()) {
            $sql = 'SELECT u.id AS userid, a.id AS taskid, a.title, t.completiondate, EXTRACT(DAY FROM((t.completiondate - NOW()) || \' days\')::interval) AS daysleft FROM {artefact_plans_task} t ' .
                'INNER JOIN {artefact} a ON a.id = t.artefact ' .
                'INNER JOIN {usr} u ON u.id = a.owner ' .
                'WHERE u.active = 1 AND t.completed = 0 AND t.remindermailsent = 0 AND t.reminder IS NOT NULL ' .
                'AND t.completiondate - (t.reminder || \' seconds\')::interval <= NOW() ' .
                'ORDER BY userid, t.completiondate';
        }
        else {
            $sql = 'SELECT u.id AS userid, a.id AS taskid, a.title, t.completiondate, timestampdiff(DAY, NOW(), t.completiondate) AS daysleft FROM {artefact_plans_task} t ' .
                'INNER JOIN {artefact} a ON a.id = t.artefact ' .
                'INNER JOIN {usr} u ON u.id = a.owner ' .
                'WHERE u.active = 1 AND t.completed = 0 AND t.remindermailsent = 0 AND t.reminder IS NOT NULL ' .
                'AND timestampdiff(SECOND, t.completiondate, NOW()) < t.reminder ' .
                'ORDER BY userid, t.completiondate';
        }
        $results = get_records_sql_array($sql);

        if (!$results) {
            return true;
        }

        $currentProcessedUserId = $results[0]->userid;
        $currentProcessedUserReminders = [];
        foreach ($results as $reminder) {
            // if it's the same user as in the last cycle, collect his reminders
            if ($reminder->userid === $currentProcessedUserId) {
                $currentProcessedUserReminders[] = $reminder;
                continue;
            }
            // Now we have got all reminders for this user, send mail to him and on success mark tasks as reminded
            if (self::sendReminderNotificationToUser($currentProcessedUserReminders)) {
                self::markTasksAsReminded($currentProcessedUserReminders);
            }

            // initiate a new current processed user
            $currentProcessedUserId = $reminder->userid;
            $currentProcessedUserReminders = [];
            $currentProcessedUserReminders[] = $reminder;
        }

        // send mail to last processed user
        if (self::sendReminderNotificationToUser($currentProcessedUserReminders)) {
            self::markTasksAsReminded($currentProcessedUserReminders);
        }

        return true;
    }

    public static function check_group_reminders() {
        // ToDo: Select only Grouptasks which are no RootGroupTasks/SelectionTasks
        // ToDo: Background: Cause they are created as user tasks when chosen and so processed in function check_user_reminders)

        //$sql = 'SELECT g.id AS groupid, a.id AS taskid, a.title, t.completiondate, t.reminder FROM {artefact_plans_task} t ' .
        //    'INNER JOIN {artefact} a ON a.id = t.artefact ' .
        //    'INNER JOIN {group} g ON g.id = a.group ' .
        //    'WHERE t.completed = 0 AND t.remindermailsent = 0 AND t.reminder AND NOW()-t.reminder < 0 ' .
        //    'ORDER BY groupid, t.completiondate';

        $sql = 'SELECT g.id AS groupid, a.id AS taskid, a.title, t.completiondate, t.reminder FROM {artefact_plans_task} t ' .
            'INNER JOIN {artefact} a ON a.id = t.artefact ' .
            'INNER JOIN {group} g ON g.id = a.group ' .
            'WHERE t.completed = 0 AND t.remindermailsent = 0 AND t.reminder AND NOW()-t.reminder < 0 ' .
            'ORDER BY groupid, t.completiondate';

        $results = get_records_sql_array($sql);

    }

    /**
     * @param array $remindedTasks
     * @return bool
     * @throws \SQLException
     */
    private static function markTasksAsReminded(array $remindedTasks) {
        $remindedTaskIds = [];
        foreach ($remindedTasks as $remindedTask) {
            $remindedTaskIds[] = $remindedTask->taskid;
        }

        $sql = "UPDATE {artefact_plans_task} SET remindermailsent = 1
                WHERE artefact IN (" . implode(', ', array_map('db_quote', $remindedTaskIds)) . ")";
        return execute_sql($sql);
    }
}