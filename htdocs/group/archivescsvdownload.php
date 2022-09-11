<?php
/**
 * Manage the archiving of submitted portfolios from groups.
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
define('GROUP', param_integer('group'));

require_once('searchlib.php');

$group = group_current_group();
$role = group_user_access($group->id);
if (!group_role_can_access_archives($group, $role)) {
    throw new AccessDeniedException();
}

$search = (object) array(
    'query'          => '',
    'sortby'         => 'firstname',
    'sortdir'        => 'asc',
    'group'          => param_integer('group'),
    'archivedsubmissions' => true,
);

$results = get_group_archived_submissions_results($search, 0, 0);
if (!empty($results['data'])) {
    foreach ($results['data'] as $key => $data) {
        $primaryemail = $data['email'];
        if (is_array($data['email'])) {
            foreach ($data['email'] as $email) {
                if (isset($email->primary)) {
                    $primaryemail = $email->title;
                }
            }
        }
        $results['data'][$key]['email'] = $primaryemail;
    }
}

if (!empty($results['data'])) {
    $csvfields = array('username', 'email', 'firstname', 'lastname', 'preferredname', 'submittedto', 'specialid', 'filetitle', 'filepath', 'filename', 'archivectime');

    $USER->set_download_file(generate_csv($results['data'], $csvfields), 'archivedsubmissions_' . $search->group . '.csv', 'text/csv');
    redirect(get_config('wwwroot') . 'download.php');
}
$SESSION->add_error_msg(get_string('nocsvresults', 'admin'));
redirect(get_config('wwwroot') . 'group/archives.php?group=' . $search->group);