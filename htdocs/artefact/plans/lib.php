<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage artefact-plans
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginArtefactPlans extends PluginArtefact {

    public static function get_artefact_types() {
        return array(
            'task',
            'plan',
        );
    }

    public static function get_block_types() {
        return array('plans');
    }

    public static function get_plugin_name() {
        return 'plans';
    }

    public static function menu_items() {
        return array(
            array(
                'path' => 'profile/plans',
                'url'  => 'artefact/plans/',
                'title' => get_string('myplans', 'artefact.plans'),
                'weight' => 40,
            ),
        );
    }

}

class ArtefactTypePlan extends ArtefactType {

    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);
    }

    public static function get_links($id) {
        return array();
    }

    public function delete() {
        if (empty($this->id)) {
            return;
        }

        db_begin();
        parent::delete();
        db_commit();
    }

    public static function get_icon($options=null) {
    }

    public static function is_singular() {
        return false;
    }


    /**
     * This function returns a list of the given user's plans.
     *
     * @param limit how many plans to display per page
     * @param offset current page to display
     * @return array (count: integer, data: array)
     */
    public static function get_plans($offset=0, $limit=10) {
        global $USER;

        ($plans = get_records_sql_array("SELECT * FROM {artefact}
                                        WHERE owner = ? AND artefacttype = 'plan'
                                        ORDER BY id
                                        OFFSET ? LIMIT ?",array($USER->get('id'), $offset, $limit)))
                                        || ($plans = array());
        $result = array(
            'count'  => count_records('artefact', 'owner', $USER->get('id'), 'artefacttype', 'plan'),
            'data'   => $plans,
            'offset' => $offset,
            'limit'  => $limit,
        );

        return $result;
    }

    /**
     * Builds the plans list table
     *
     * @param plans (reference)
     */
    public static function build_plans_list_html(&$plans) {
        $smarty = smarty_core();
        $smarty->assign_by_ref('plans', $plans);
        $plans['tablerows'] = $smarty->fetch('artefact:plans:planslist.tpl');
        $pagination = build_pagination(array(
            'id' => 'planlist_pagination',
            'class' => 'center',
            'url' => get_config('wwwroot') . 'artefact/plans/index.php',
            'jsonscript' => 'artefact/plans/plans.json.php',
            'datatable' => 'planslist',
            'count' => $plans['count'],
            'limit' => $plans['limit'],
            'offset' => $plans['offset'],
            'firsttext' => '',
            'previoustext' => '',
            'nexttext' => '',
            'lasttext' => '',
            'numbersincludefirstlast' => false,
            'resultcounttextsingular' => get_string('plan', 'artefact.plans'),
            'resultcounttextplural' => get_string('plans', 'artefact.plans'),
        ));
        $plans['pagination'] = $pagination['html'];
        $plans['pagination_js'] = $pagination['javascript'];
    }

    public static function submit(Pieform $form, $values) {
        global $USER, $SESSION;

        $new = false;

        if (!empty($values['plan'])) {
            $id = (int) $values['plan'];
            $artefact = new ArtefactTypePlan($id);
            $USER->can_edit_artefact($id);
        }
        else {
            $artefact = new ArtefactTypePlan();
            $artefact->set('owner', $USER->get('id'));
            $new = true;
        }

        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->commit();

        $SESSION->add_ok_msg(get_string('plansavedsuccessfully', 'artefact.plans'));

        if ($new) {
            redirect('/artefact/plans/plan.php?id='.$artefact->get('id'));
        }
        else {
            redirect('/artefact/plans/');
        }
    }

    /**
    * Gets the new/edit plans pieform
    *
    */
    public static function get_form($plan=null) {
        require_once(get_config('libroot') . 'pieforms/pieform.php');
        $elements = call_static_method(generate_artefact_class_name('plan'), 'get_planform_elements', $plan);
        $elements['submit'] = array(
            'type' => 'submitcancel',
            'value' => array(get_string('saveplan','artefact.plans'), get_string('cancel')),
            'goto' => get_config('wwwroot') . 'artefact/plans/',
        );
        $planform = array(
            'name' => empty($plan) ? 'addplan' : 'editplan',
            'plugintype' => 'artefact',
            'pluginname' => 'task',
            'successcallback' => array(generate_artefact_class_name('plan'),'submit'),
            'elements' => $elements,
        );

        return pieform($planform);
    }

    /**
    * Gets the new/edit fields for the plan pieform
    *
    */
    public static function get_planform_elements($plan) {
        $elements = array(
            'title' => array(
                'type' => 'text',
                'defaultvalue' => null,
                'title' => get_string('title', 'artefact.plans'),
                'size' => 30,
                'rules' => array(
                    'required' => true,
                ),
            ),
            'description' => array(
                'type'  => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'defaultvalue' => null,
                'title' => get_string('description', 'artefact.plans'),
            ),
        );

        if (!empty($plan)) {
            foreach ($elements as $k => $element) {
                $elements[$k]['defaultvalue'] = $plan->get($k);
            }
            $elements['plan'] = array(
                'type' => 'hidden',
                'value' => $plan->id,
            );
        }

        return $elements;
    }

}

class ArtefactTypeTask extends ArtefactType {

    protected $completed = 0;
    protected $completiondate;

    /**
     * We override the constructor to fetch the extra data.
     *
     * @param integer
     * @param object
     */
    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if ($this->id) {
            if ($pdata = get_record('artefact_task', 'task', $this->id, null, null, null, null, '*, ' . db_format_tsfield('completiondate'))) {
                foreach($pdata as $name => $value) {
                    if (property_exists($this, $name)) {
                        $this->$name = $value;
                    }
                }
            }
            else {
                // This should never happen unless the user is playing around with task IDs in the location bar or similar
                throw new ArtefactNotFoundException(get_string('taskdoesnotexist', 'artefact.plans'));
            }
        }
    }

    public static function get_links($id) {
        return array();
    }

    public static function get_icon($options=null) {
    }

    public static function is_singular() {
        return false;
    }

    /**
     * This method extends ArtefactType::commit() by adding additional data
     * into the artefact_task table.
     *
     */
    public function commit() {
        if (empty($this->dirty)) {
            return;
        }

        // Return whether or not the commit worked
        $success = false;

        db_begin();
        $new = empty($this->id);

        parent::commit();

        $this->dirty = true;

        $completiondate = $this->get('completiondate');
        if (!empty($completiondate)) {
            $date = db_format_timestamp($completiondate);
        }
        $data = (object)array(
            'task'  => $this->get('id'),
            'completed' => $this->get('completed'),
            'completiondate' => $date,
        );

        if ($new) {
            $success = insert_record('artefact_task', $data);
        }
        else {
            $success = update_record('artefact_task', $data, 'task');
        }

        db_commit();

        $this->dirty = $success ? false : true;

        return $success;
    }

    /**
     * This function extends ArtefactType::delete() by also deleting anything
     * that's in task.
     */
    public function delete() {
        if (empty($this->id)) {
            return;
        }

        db_begin();
        delete_records('artefact_task', 'task', $this->id);

        parent::delete();
        db_commit();
    }

    /**
    * Gets the new/edit tasks pieform
    *
    */
    public static function get_form($parent, $task=null) {
        require_once(get_config('libroot') . 'pieforms/pieform.php');
        $elements = call_static_method(generate_artefact_class_name('task'), 'get_taskform_elements', $parent, $task);
        $elements['submit'] = array(
            'type' => 'submitcancel',
            'value' => array(get_string('savetask','artefact.plans'), get_string('cancel')),
            'goto' => get_config('wwwroot') . 'artefact/plans/plan.php?id=' . $parent,
        );
        $taskform = array(
            'name' => empty($task) ? 'addtasks' : 'edittask',
            'plugintype' => 'artefact',
            'pluginname' => 'task',
            'successcallback' => array(generate_artefact_class_name('task'),'submit'),
            'elements' => $elements,
        );

        return pieform($taskform);
    }

    /**
    * Gets the new/edit fields for the tasks pieform
    *
    */
    public static function get_taskform_elements($parent, $task=null) {
        $elements = array(
            'title' => array(
                'type' => 'text',
                'defaultvalue' => null,
                'title' => get_string('title', 'artefact.plans'),
                'size' => 30,
                'rules' => array(
                    'required' => true,
                ),
            ),
            'completiondate' => array(
                'type'       => 'calendar',
                'caloptions' => array(
                    'showsTime'      => false,
                    'ifFormat'       => '%Y/%m/%d'
                    ),
                'defaultvalue' => null,
                'title' => get_string('completiondate', 'artefact.plans'),
                'description' => get_string('dateformatguide'),
                'rules' => array(
                    'required' => true,
                ),
            ),
            'description' => array(
                'type'  => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'defaultvalue' => null,
                'title' => get_string('description', 'artefact.plans'),
            ),
            'completed' => array(
                'type' => 'checkbox',
                'defaultvalue' => null,
                'title' => get_string('completed', 'artefact.plans'),
                'description' => get_string('completeddesc', 'artefact.plans'),
            ),
        );

        if (!empty($task)) {
            foreach ($elements as $k => $element) {
                $elements[$k]['defaultvalue'] = $task->get($k);
            }
            $elements['task'] = array(
                'type' => 'hidden',
                'value' => $task->id,
            );
        }

        $elements['parent'] = array(
            'type' => 'hidden',
            'value' => $parent,
        );

        return $elements;
    }

    public static function submit(Pieform $form, $values) {
        global $USER, $SESSION;

        if (!empty($values['task'])) {
            $id = (int) $values['task'];
            $artefact = new ArtefactTypeTask($id);
        }
        else {
            $artefact = new ArtefactTypeTask();
            $artefact->set('owner', $USER->get('id'));
            $artefact->set('parent', $values['parent']);
        }

        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->set('completed', $values['completed'] ? 1 : 0);
        $artefact->set('completiondate', $values['completiondate']);
        $artefact->commit();

        $SESSION->add_ok_msg(get_string('plansavedsuccessfully', 'artefact.plans'));

        redirect('/artefact/plans/plan.php?id='.$values['parent']);
    }

    /**
     * This function returns a list of the current plans tasks.
     *
     * @param limit how many tasks to display per page
     * @param offset current page to display
     * @return array (count: integer, data: array)
     */
    public static function get_tasks($plan, $offset=0, $limit=10) {
        global $USER;
        $datenow = time(); // time now to use for formatting tasks by completion

        ($results = get_records_sql_array("
            SELECT ap.task, ap.completed, ".db_format_tsfield('completiondate').", a.title, a.description, a.parent
                FROM {artefact} a
            JOIN {artefact_task} ap ON ap.task = a.id
            WHERE a.owner = ? AND a.artefacttype = 'task'
            AND a.parent = ?
            ORDER BY ap.completiondate DESC
            LIMIT ? OFFSET ?", array($USER->get('id'), $plan, $limit, $offset)))
            || ($results = array());

        // format the date and setup completed for display if task is incomplete
        if (!empty($results)) {
            foreach ($results as $result) {
                if (!empty($result->completiondate)) {
                    // if record hasn't been completed and completiondate has passed mark as such for display
                    if ($result->completiondate < $datenow && !$result->completed) {
                        $result->completed = -1;
                    }
                    $result->completiondate = strftime(get_string('strftimedate'), $result->completiondate);
                }
            }
        }

        $result = array(
            'count'  => count_records('artefact', 'owner', $USER->get('id'), 'artefacttype', 'task', 'parent', $plan),
            'data'   => $results,
            'offset' => $offset,
            'limit'  => $limit,
            'id'     => $plan,
        );

        return $result;
    }

    /**
     * Builds the tasks list table for current plan
     *
     * @param tasks (reference)
     */
    public function build_tasks_list_html(&$tasks) {
        $smarty = smarty_core();
        $smarty->assign_by_ref('tasks', $tasks);
        $tasks['tablerows'] = $smarty->fetch('artefact:plans:taskslist.tpl');
        $pagination = build_pagination(array(
            'id' => 'tasklist_pagination',
            'class' => 'center',
            'url' => get_config('wwwroot') . 'artefact/plans/plan.php?id='.$tasks['id'],
            'jsonscript' => 'artefact/plans/tasks.json.php',
            'datatable' => 'taskslist',
            'count' => $tasks['count'],
            'limit' => $tasks['limit'],
            'offset' => $tasks['offset'],
            'firsttext' => '',
            'previoustext' => '',
            'nexttext' => '',
            'lasttext' => '',
            'numbersincludefirstlast' => false,
            'resultcounttextsingular' => get_string('task', 'artefact.plans'),
            'resultcounttextplural' => get_string('tasks', 'artefact.plans'),
        ));
        $tasks['pagination'] = $pagination['html'];
        $tasks['pagination_js'] = $pagination['javascript'];
    }
}

?>
