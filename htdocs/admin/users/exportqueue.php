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
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'configusers/exportqueue');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('exportqueue', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'exportqueue');
require_once('searchlib.php');

// If an action button has been submitted
if ($action = param_alphanum('action', null)) {
    $rows = array();
    if (param_variable('exportrows', null) && $action == 'export') {
        $rows = param_variable('exportrows', null);
    }
    if (param_variable('deleterows', null) && $action == 'delete') {
        $rows = param_variable('deleterows', null);
    }
    $rowids = array_map('intval', $rows);

    // If only institutional admin make sure we can only action items that belong to users in our institution
    if (!$USER->get('admin') && $USER->is_institutional_admin()) {
        $institutions = $USER->get('admininstitutions');
        foreach ($rowids as $key => $rowid) {
            if (!get_field_sql('
                SELECT COUNT(*) FROM {export_queue} e
                JOIN {usr} u ON e.usr = u.id
                JOIN {usr_institution} ui ON ui.usr = u.id
                WHERE ui.institution IN (' . join(',', array_map('db_quote', $institutions)) . ')
                AND e.id = ?',
                array($rowid))) {
                    // not allowed to action this one.
                    unset($rowids[$key]);
            }
        }
    }
    // For deleting rows
    if ($action == 'delete' && !empty($rowids)) {
        foreach ($rowids as $rowid) {
            db_begin();
            // Need to relese any pending archiving
            if ($items = get_records_select_array('export_queue_items', 'exportqueueid = ?', array($rowid), 'id')) {
                $views = array();
                // To make sure we process the item with this id only once we keep a track of the $lastid
                // We don't know if the $item will be a collection or view (or artefact possibly in the future)
                // In the case of a user exporting to leap2a there can be a number of collections/views to deal
                // with so we want to deal with each collection or view only once.
                $lastid = '';
                $submitted = false;
                $what = false;
                foreach ($items as $key => $item) {
                    if (!empty($item->collection) && $lastid != 'collection_' . $item->collection) {
                        $what = 'collections';
                        $lastid = 'collection_' . $item->collection;
                        $views = array_merge($views, get_column('collection_view', 'view', 'collection', $item->collection));
                        $submitted = get_record('collection', 'id', $item->collection);
                    }
                    else if (empty($item->collection) && !empty($item->view) && $lastid != 'view_' . $item->view) {
                        $what = 'views';
                        $lastid = 'view_' . $item->view;
                        $views = array_merge($views, array($item->view));
                        $submitted = get_record('view', 'id', $item->view);
                    }
                }
                require_once(get_config('docroot') . 'lib/view.php');
                if ($submitted->submittedstatus == View::PENDING_RELEASE) {
                    // we need to release the submission
                    if ($what == 'collections') {
                        require_once(get_config('docroot') . 'lib/collection.php');
                        $id = substr($lastid, strlen('collection_'));
                        $collection = new Collection($id);
                        try {
                            $collection->release($USER->get('id'));
                        }
                        catch (SystemException $e) {
                            $errors[] = get_string('submissionreleasefailed', 'export');
                            log_warn($e->getMessage());
                        }
                    }
                    else if ($what == 'views') {
                        $id = substr($lastid, strlen('view_'));
                        $view = new View($id);
                        try {
                            $view->release($USER->get('id'));
                        }
                        catch (SystemException $e) {
                            $errors[] = get_string('submissionreleasefailed', 'export');
                            log_warn($e->getMessage());
                        }
                    }
                    else {
                        $errors[] = get_string('submissionreleasefailed', 'export');
                    }
                }

                if (!delete_records('export_queue_items', 'exportqueueid', $rowid)) {
                    log_warn('Unable to delete export queue items for ID: ' . $rowid);
                    db_rollback();
                }
            }
            if (!delete_records('export_queue', 'id', $rowid)) {
                log_warn('Unable to delete export queue row ID: ' . $rowid);
                db_rollback();
            }
            db_commit();
        }
        $SESSION->add_ok_msg(get_string('exportqueuedeleted', 'admin', count($rowids)));
    }
    else if ($action == 'export' && !empty($rowids)) {
        // Make failed rows change to pending rows so they can be picked up next cron run
        foreach ($rowids as $rowid) {
            execute_sql('UPDATE {export_queue} SET starttime = NULL, ctime = NOW() WHERE id = ?', array($rowid));
        }
        // And clear cron lock
        // @TODO - use the actual cron_free function when cron/lib.php actually contains a cron class
        delete_records('config', 'field', '_cron_lock_core_export_process_queue');
        $SESSION->add_ok_msg(get_string('exportqueuearchived', 'admin', count($rowids)));
    }
}

$search = (object) array(
    'query' => trim(param_variable('query', '')),
    'sortby' => param_alpha('sortby', 'firstname'),
    'sortdir' => param_alpha('sortdir', 'asc'),
);

$offset = param_integer('offset', 0);
$limit = param_integer('limit', 10);

if ($USER->get('admin')) {
    $institutions = get_records_array('institution', '', '', 'displayname');
    $search->institution = param_alphanum('institution', 'all');
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

list($html, $columns, $pagination, $search) = build_admin_export_queue_results($search, $offset, $limit);

$js = <<<EOF
jQuery(function($) {
    var p = {$pagination['javascript']}

    new ExportQueue(p);
})
EOF;

$smarty = smarty(array('adminexportqueue', 'paginator'), array(), array('ascending' => 'mahara', 'descending' => 'mahara'));
setpageicon($smarty, 'icon-user');
$smarty->assign('search', $search);
$smarty->assign('limit', $limit);
$smarty->assign('institutions', !empty($institutions) ? $institutions : array());
$smarty->assign('results', $html);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('columns', $columns);
$smarty->assign('searchurl', $search['url']);
$smarty->assign('sortby', $search['sortby']);
$smarty->assign('sortdir', $search['sortdir']);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('admin/users/exportqueue.tpl');
