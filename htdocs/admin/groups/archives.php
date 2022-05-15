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
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'managegroups/archives');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'archives');

$tabs = (object) [
    'currentclass' => '',
    'archivedclass' => '',
];

define('TITLE', get_string('submissions', 'admin'));
if (param_exists('current')) {
    $tabs->currentclass = 'active';
}
else {
    $tabs->archivedclass = 'active';
}

if ($USER->get('admin') && param_exists('releaseids')) {
    $releaseids = param_variable('releaseids');
    $releaseaction = param_variable('action');
    $returntouser = false;
    // Release the locked items.
    foreach ($releaseids as $releaseid => $releasetype) {
        $view = $collection = false;
        if ($releasetype === 'collection') {
            $collection = new Collection($releaseid);
            $releasecollection = true;
        }
        else {
            $view = new View($releaseid);
            $releasecollection = false;
        }

        if (is_plugin_active('submissions', 'module')) {
            /** @var \Submissions\Models\Submission $submission */
            /** @var \Submissions\Models\Evaluation $evaluation */
            list($submission, $evaluation) = \Submissions\Repository\SubmissionRepository::findCurrentSubmissionAndAssignedEvaluationByPortfolioElement(($releasecollection ? $collection : $view));
            // This value (1) is based on the option in the release form at
            // releaseview_submit(). A value of 1 means 'noresult'.
            if ($submission && $evaluation->get('success') != 1) {
                $evaluation->set('success', 1);
                $evaluation->commit();
            }
        }

        // If we're a collection, release that.
        if ($collection) {
            try {
                // Override the strings for the message keys.
                $releasemessageoverrides = [
                    'host' => [
                        'subjectkey' => 'portfolioreleasedsubject',
                        'messagekey' => 'currentarchivereleasedsubmittedhostmessage',
                    ],
                ];
                $collection->release($USER, $releasemessageoverrides);
                $msg = get_string('portfolioreleasedsuccesswithname', 'group', $collection->get('name'));
            }
            catch (SystemException $e) {
                $msg = get_string('portfolioreleasefailed', 'group', $collection->get('name'));
            }
            $SESSION->add_ok_msg($msg);
        }
        else {
            try {
                // Override the strings for the message keys.
                $releasemessageoverrides = [
                    'host' => [
                        'subjectkey' => 'portfolioreleasedsubject',
                        'messagekey' => 'currentarchivereleasedsubmittedhostmessage',
                    ],
                ];
                $view->release($USER, $releasemessageoverrides);
                $msg = get_string('portfolioreleasedsuccesswithname', 'group', $view->get('title'));
            }
            catch (SystemException $e) {
                $msg = get_string('portfolioreleasefailed', 'group', $view->get('title'));
            }
            $SESSION->add_ok_msg($msg);
        }
    }
    redirect('/admin/groups/archives.php?current=1');
}

require_once('searchlib.php');

$search_params = [
    'type' => param_exists('current') ? 'current' : 'archived',
    'query' => trim(param_variable('query', '')),
    'sortby' => param_alpha('sortby', 'firstname'),
    'sortdir' => param_alpha('sortdir', 'asc'),
];

$offset = param_integer('offset', 0);
$limit = param_integer('limit', 10);

if ($USER->get('admin')) {
    $institutions = get_records_array('institution', '', '', 'displayname');
    $search_params['institution'] = trim(param_alphanum('institution', 'all'));
}
else {
    $institutionnames = array_keys($USER->get('admininstitutions'));
    $institutions = get_records_select_array(
        'institution',
        'suspended = 0 AND name IN (' . join(',', array_fill(0, count($institutionnames), '?')) . ')',
        $institutionnames,
        'displayname'
    );
}
list($html, $columns, $pagination, $search) = build_admin_archived_submissions_results($search_params, $offset, $limit);

$searchtypecurrent = false;
$searchtypearchived = false;
if (param_exists('current')) {
    $search['type'] = 'current';
    $searchtypecurrent = true;
}
else {
    $search['type'] = 'archived';
    $searchtypearchived = true;
}

$js = <<<EOF
jQuery(function() {
    var p = {$pagination['javascript']}

    new CurrentSubmissionsRelease(p);
})
EOF;

$jsscripts = [
    'adminreleasesubmissions',
    'paginator',
];

$smarty = smarty($jsscripts, array(), array('ascending' => 'mahara', 'descending' => 'mahara'));
setpageicon($smarty, 'icon-archive');
$smarty->assign('tabs', $tabs);
$smarty->assign('search', $search);
$smarty->assign('searchtypecurrent', $searchtypecurrent);
$smarty->assign('searchtypearchived', $searchtypearchived);
$smarty->assign('query', trim(param_variable('query', '')));
$smarty->assign('limit', $limit);
$smarty->assign('institutions', !empty($institutions) ? $institutions : array());
$smarty->assign('results', $html);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('columns', $columns);
$smarty->assign('searchurl', $search['urlshort']);
$smarty->assign('sortby', $search['sortby']);
$smarty->assign('sortdir', $search['sortdir']);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('admin/groups/archives.tpl');
