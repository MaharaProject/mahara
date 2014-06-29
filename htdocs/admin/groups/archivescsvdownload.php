<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
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
    'archivedsubmissions' => true,
);
$search->institution = param_alphanum('institution', null);
if (!empty($search->institution)) {
    if (!$USER->get('admin') && !$USER->is_institutional_admin($search->institution)) {
        throw new AccessDeniedException();
    }
}

$results = get_admin_user_search_results($search, 0, false);

if (!empty($results['data'])) {
    $csvfields = array('username', 'email', 'firstname', 'lastname', 'preferredname', 'submittedto', 'specialid', 'filetitle', 'filepath', 'filename', 'archivectime');

    $USER->set_download_file(generate_csv($results['data'], $csvfields), 'archivedsubmissions.csv', 'text/csv');
    redirect(get_config('wwwroot') . 'download.php');
}
$SESSION->add_error_msg(get_string('nocsvresults', 'admin'));
redirect(get_config('wwwroot') . 'admin/groups/archives.php?institution=' . $search->institution);