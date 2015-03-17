<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
        return array();
    }

    public static function get_plugin_name() {
        return 'plans';
    }

    public static function is_active() {
        return get_field('artefact_installed', 'active', 'name', 'plans');
    }

    public static function menu_items() {
        return array(
            'content/plans' => array(
                'path' => 'content/plans',
                'url'  => 'artefact/plans/index.php',
                'title' => get_string('Plans', 'artefact.plans'),
                'weight' => 60,
            ),
        );
    }

    public static function get_artefact_type_content_types() {
        return array(
            'task' => array('text'),
        );
    }

    public static function progressbar_link($artefacttype) {
        return 'artefact/plans/index.php';
    }
}

class ArtefactTypePlan extends ArtefactType {

    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);
        if (empty($this->id)) {
            $this->container = 1;
        }
    }

    public static function get_links($id) {
        return array(
            '_default' => get_config('wwwroot') . 'artefact/plans/plan.php?id=' . $id,
        );
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
        global $THEME;
        return $THEME->get_image_url('plan', 'artefact/plans');
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
                                        ORDER BY title ASC", array($USER->get('id')), $offset, $limit))
                                        || ($plans = array());
        foreach ($plans as &$plan) {
            if (!isset($plan->tags)) {
                $plan->tags = ArtefactType::artefact_get_tags($plan->id);
            }
            $plan->description = '<p>' . preg_replace('/\n\n/','</p><p>', $plan->description) . '</p>';
        }
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

    public static function validate(Pieform $form, $values) {
        global $USER;
        if (!empty($values['plan'])) {
            $id = (int) $values['plan'];
            $artefact = new ArtefactTypePlan($id);
            if (!$USER->can_edit_artefact($artefact)) {
                $form->set_error('submit', get_string('canteditdontownplan', 'artefact.plans'));
            }
        }
    }

    public static function submit(Pieform $form, $values) {
        global $USER, $SESSION;

        $new = false;

        if (!empty($values['plan'])) {
            $id = (int) $values['plan'];
            $artefact = new ArtefactTypePlan($id);
        }
        else {
            $artefact = new ArtefactTypePlan();
            $artefact->set('owner', $USER->get('id'));
            $new = true;
        }

        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        if (get_config('licensemetadata')) {
            $artefact->set('license', $values['license']);
            $artefact->set('licensor', $values['licensor']);
            $artefact->set('licensorurl', $values['licensorurl']);
        }
        $artefact->set('tags', $values['tags']);
        $artefact->commit();

        $SESSION->add_ok_msg(get_string('plansavedsuccessfully', 'artefact.plans'));

        if ($new) {
            redirect('/artefact/plans/plan.php?id='.$artefact->get('id'));
        }
        else {
            redirect('/artefact/plans/index.php');
        }
    }

    /**
    * Gets the new/edit plans pieform
    *
    */
    public static function get_form($plan=null) {
        require_once(get_config('libroot') . 'pieforms/pieform.php');
        require_once('license.php');
        $elements = call_static_method(generate_artefact_class_name('plan'), 'get_planform_elements', $plan);
        $elements['submit'] = array(
            'type' => 'submitcancel',
            'value' => array(get_string('saveplan','artefact.plans'), get_string('cancel')),
            'goto' => get_config('wwwroot') . 'artefact/plans/index.php',
        );
        $planform = array(
            'name' => empty($plan) ? 'addplan' : 'editplan',
            'plugintype' => 'artefact',
            'pluginname' => 'task',
            'validatecallback' => array(generate_artefact_class_name('plan'),'validate'),
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
            'tags'        => array(
                'type'        => 'tags',
                'title'       => get_string('tags'),
                'description' => get_string('tagsdescprofile'),
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

        if (get_config('licensemetadata')) {
            $elements['license'] = license_form_el_basic($plan);
            $elements['license_advanced'] = license_form_el_advanced($plan);
        }

        return $elements;
    }

    public function render_self($options) {
        $limit = !isset($options['limit']) ? 10 : (int) $options['limit'];
        $offset = isset($options['offset']) ? intval($options['offset']) : 0;

        $tasks = ArtefactTypeTask::get_tasks($this->id, $offset, $limit);

        $template = 'artefact:plans:taskrows.tpl';

        $baseurl = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $this->id;
        if (!empty($options['viewid'])) {
            $baseurl .= '&view=' . $options['viewid'];
        }

        $pagination = array(
            'baseurl' => $baseurl,
            'id' => 'task_pagination',
            'datatable' => 'tasktable',
            'jsonscript' => 'artefact/plans/viewtasks.json.php',
        );

        ArtefactTypeTask::render_tasks($tasks, $template, $options, $pagination);

        $smarty = smarty_core();
        $smarty->assign_by_ref('tasks', $tasks);
        if (isset($options['viewid'])) {
            $smarty->assign('artefacttitle', '<a href="' . $baseurl . '">' . hsc($this->get('title')) . '</a>');
        }
        else {
            $smarty->assign('artefacttitle', hsc($this->get('title')));
        }
        $smarty->assign('plan', $this);

        if (!empty($options['details']) and get_config('licensemetadata')) {
            $smarty->assign('license', render_license($this));
        }
        else {
            $smarty->assign('license', false);
        }
        $smarty->assign('owner', $this->get('owner'));
        $smarty->assign('tags', $this->get('tags'));

        return array('html' => $smarty->fetch('artefact:plans:viewplan.tpl'), 'javascript' => '');
    }

    public static function is_countable_progressbar() {
        return true;
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
            if ($pdata = get_record('artefact_plans_task', 'artefact', $this->id, null, null, null, null, '*, ' . db_format_tsfield('completiondate'))) {
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
        return array(
            '_default' => get_config('wwwroot') . 'artefact/plans/edit/task.php?id=' . $id,
        );
    }

    public static function get_icon($options=null) {
        global $THEME;
        return $THEME->get_image_url('plantask', 'artefact/plans');
    }

    public static function is_singular() {
        return false;
    }

    /**
     * This method extends ArtefactType::commit() by adding additional data
     * into the artefact_plans_task table.
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
            'artefact'  => $this->get('id'),
            'completed' => $this->get('completed'),
            'completiondate' => $date,
        );

        if ($new) {
            $success = insert_record('artefact_plans_task', $data);
        }
        else {
            $success = update_record('artefact_plans_task', $data, 'artefact');
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
        delete_records('artefact_plans_task', 'artefact', $this->id);

        parent::delete();
        db_commit();
    }

    public static function bulk_delete($artefactids) {
        if (empty($artefactids)) {
            return;
        }

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();
        delete_records_select('artefact_plans_task', 'artefact IN (' . $idstr . ')');
        parent::bulk_delete($artefactids);
        db_commit();
    }


    /**
    * Gets the new/edit tasks pieform
    *
    */
    public static function get_form($parent, $task=null) {
        require_once(get_config('libroot') . 'pieforms/pieform.php');
        require_once('license.php');
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
            'validatecallback' => array(generate_artefact_class_name('task'),'validate'),
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
                'description' => get_string('titledesc','artefact.plans'),
                'size' => 30,
                'rules' => array(
                    'required' => true,
                ),
            ),
            'completiondate' => array(
                'type'       => 'calendar',
                'caloptions' => array(
                    'showsTime'      => false,
                    'ifFormat'       => '%Y/%m/%d',
                    'dateFormat'     => 'yy/mm/dd',
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
            'tags'        => array(
                'type'        => 'tags',
                'title'       => get_string('tags'),
                'description' => get_string('tagsdescprofile'),
            ),
            'completed' => array(
                'type' => 'switchbox',
                'switchtext' => 'yesno',
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
        if (get_config('licensemetadata')) {
            $elements['license'] = license_form_el_basic($task);
            $elements['license_advanced'] = license_form_el_advanced($task);
        }

        $elements['parent'] = array(
            'type' => 'hidden',
            'value' => $parent,
        );

        return $elements;
    }

    public static function validate(Pieform $form, $values) {
        global $USER;
        if (!empty($values['task'])) {
            $id = (int) $values['task'];
            $artefact = new ArtefactTypeTask($id);
            if (!$USER->can_edit_artefact($artefact)) {
                $form->set_error('submit', get_string('canteditdontowntask', 'artefact.plans'));
            }
        }
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
        if (get_config('licensemetadata')) {
            $artefact->set('license', $values['license']);
            $artefact->set('licensor', $values['licensor']);
            $artefact->set('licensorurl', $values['licensorurl']);
        }
        $artefact->set('tags', $values['tags']);
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
        $datenow = time(); // time now to use for formatting tasks by completion

        ($results = get_records_sql_array("
            SELECT a.id, at.artefact AS task, at.completed, ".db_format_tsfield('completiondate').",
                a.title, a.description, a.parent, a.owner
                FROM {artefact} a
            JOIN {artefact_plans_task} at ON at.artefact = a.id
            WHERE a.artefacttype = 'task' AND a.parent = ?
            ORDER BY at.completiondate ASC, a.id", array($plan), $offset, $limit))
            || ($results = array());

        // format the date and setup completed for display if task is incomplete
        if (!empty($results)) {
            foreach ($results as $result) {
                if (!empty($result->completiondate)) {
                    // if record hasn't been completed and completiondate has passed mark as such for display
                    if ($result->completiondate < $datenow && !$result->completed) {
                        $result->completed = -1;
                    }
                    $result->completiondate = format_date($result->completiondate, 'strftimedate');
                }
                $result->description = '<p>' . preg_replace('/\n\n/','</p><p>', $result->description) . '</p>';
                $result->tags = ArtefactType::artefact_get_tags($result->id);
            }
        }

        $result = array(
            'count'  => count_records('artefact', 'artefacttype', 'task', 'parent', $plan),
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

    /**
     * Function to append the rendered html to the $tasks data object
     *
     * @param   array   $tasks      The tasks array containing task objects + pagination count data
     * @param   string  $template   The name of the template to use for rendering
     * @param   array   $options    The block instance options
     * @param   array   $pagination The pagination data
     *
     * @return  array   $tasks      The tasks array updated with rendered table html
     */
    public function render_tasks(&$tasks, $template, $options, $pagination) {

        $smarty = smarty_core();
        $smarty->assign_by_ref('tasks', $tasks);
        $smarty->assign_by_ref('options', $options);
        $tasks['tablerows'] = $smarty->fetch($template);

        if ($tasks['limit'] && $pagination) {
            $pagination = build_pagination(array(
                'id' => $pagination['id'],
                'class' => 'center',
                'datatable' => $pagination['datatable'],
                'url' => $pagination['baseurl'],
                'jsonscript' => $pagination['jsonscript'],
                'count' => $tasks['count'],
                'limit' => $tasks['limit'],
                'offset' => $tasks['offset'],
                'numbersincludefirstlast' => false,
                'resultcounttextsingular' => get_string('task', 'artefact.plans'),
                'resultcounttextplural' => get_string('tasks', 'artefact.plans'),
            ));
            $tasks['pagination'] = $pagination['html'];
            $tasks['pagination_js'] = $pagination['javascript'];
        }
    }

    public static function is_countable_progressbar() {
        return true;
    }
}
