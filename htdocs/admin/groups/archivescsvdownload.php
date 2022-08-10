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
define('INSTITUTIONALADMIN', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

require_once('searchlib.php');

$search = (object) array(
    'query'          => '',
    'sortby'         => 'firstname',
    'sortdir'        => 'asc',
    'archivedsubmissions' => false,
    'currentsubmissions' => false,
);

if (param_exists('current')) {
    $search->currentsubmissions = true;
}
else {
    $search->archivedsubmissions = true;
}

$search->institution = param_alphanum('institution', null);
if (!empty($search->institution)) {
    if (!$USER->get('admin') && !$USER->is_institutional_admin($search->institution)) {
        throw new AccessDeniedException();
    }
}

$results = get_admin_user_search_results($search, 0, 0);
if (!empty($results['data'])) {
    foreach ($results['data'] as $key => $data) {
        $primaryemail = '';
        if (is_array($data['email'])) {
            foreach ($data['email'] as $email) {
                if (isset($email->primary)) {
                    $primaryemail = $email->title;
                }
            }
        }
        $results['data'][$key]['email'] = $primaryemail;

        // convert archivectime to human readable and sortable format
        if (!empty($results['data'][$key]['archivectime'])) {
            $results['data'][$key]['archivectime'] = date("Y-m-d H:i:s", $results['data'][$key]['archivectime']);
        }
    }
}

if (!empty($results['data'])) {
    if ($search->archivedsubmissions) {
        $csvfields = array('username', 'email', 'firstname', 'lastname', 'preferredname', 'submittedto', 'specialid', 'filetitle', 'filepath', 'filename', 'archivectime');
        $USER->set_download_file(generate_csv($results['data'], $csvfields), 'archivedsubmissions.csv', 'text/csv');
    }
    else {
        $csvfields = array('username', 'email', 'firstname', 'lastname', 'preferredname', 'submittedto', 'specialid', 'submittedtime');
        $USER->set_download_file(generate_csv($results['data'], $csvfields), 'currentsubmissions.csv', 'text/csv');
    }

    redirect(get_config('wwwroot') . 'download.php');
}
$SESSION->add_error_msg(get_string('nocsvresults', 'admin'));
redirect(get_config('wwwroot') . 'admin/groups/archives.php?institution=' . $search->institution);