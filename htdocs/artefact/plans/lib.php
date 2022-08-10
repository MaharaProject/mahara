<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

use artefact\plans\tools\PlansTools;
use artefact\plans\tools\ReminderTools;

defined('INTERNAL') || die();

class PluginArtefactPlans extends PluginArtefact {


    public static function get_artefact_types() {
        return [
            'task',
            'plan',
        ];
    }

    public static function get_block_types() {
        return [
            'plans',
        ];
    }

    public static function get_plugin_name() {
        return 'plans';
    }

    /**
     * Fetch the human readable name for the plugin
     *
     * @return string
     */
    public static function get_plugin_display_name() {
        return get_string('pluginname', 'artefact.plans');
    }

    public static function is_active() {
        return get_field('artefact_installed', 'active', 'name', 'plans');
    }

    public static function menu_items() {
        return [
            'create/plans' => [
                'path' => 'create/plans',
                'url'  => 'artefact/plans/index.php',
                'title' => get_string('Plans', 'artefact.plans'),
                'weight' => 50,
            ],
        ];
    }

    public static function group_tabs($groupid, $role) {
        if ($role) {
            return [
                'groupplans' => [
                    'path' => 'groups/groupplans',
                    'url' => 'artefact/plans/index.php?group='.$groupid,
                    'title' => get_string('groupplans', 'artefact.plans'),
                    'weight' => 80,
                ],
            ];
        }
        return [];
    }

    public static function get_artefact_type_content_types() {
        return [
            'plan' => ['plan'],
            'task' => ['task'],
        ];
    }

    public static function progressbar_link($artefacttype) {
        return 'artefact/plans/index.php';
    }

    public static function get_cron() {
        return [
            (object) [
                'callfunction' => 'cron_check_reminders',
                'hour' => '4',
                'minute' => '00',
            ]
        ];
    }

    /**
     * @throws SQLException
     */
    public static function cron_check_reminders() {
        require_once('tools/ReminderTools.php');

        ReminderTools::check_user_reminders();
        //ReminderTools::check_group_reminders();
    }

    /**
     * @return array
     */
    public static function get_event_subscriptions() {
        $subscriptions = [];

        $subscription = new stdClass();
        $subscription->plugin = 'plans';

        $subscription->event = 'deleteview';
        $subscription->callfunction = 'delete_view_event';
        $subscriptions[] = clone $subscription;

        $subscription->event = 'createcollection';
        $subscription->callfunction = 'create_collection_event';
        $subscriptions[] = clone $subscription;

        $subscription->event = 'deletecollection';
        $subscription->callfunction = 'delete_collection_event';
        $subscriptions[] = clone $subscription;

        return $subscriptions;
    }

    /**
     * @param string $event
     * @param array $data
     * @throws CollectionNotFoundException
     * @throws MaharaException
     * @throws SQLException
     */
    public static function create_collection_event($event, $data) {
        $collectionId = $data['id'];
        if (!empty($collectionId)) {
            require_once(get_config('libroot').'collection.php');
            require_once('tools/PlansTools.php');

            $collection = new \Collection($collectionId);
            list($ownerType, $ownerId) = PlansTools::getOwnerTypeAndOwnerIdFromMaharaObject($collection);

            $collection->set('name', PlansTools::createUniqueStringForDBField(
                'collection',
                'name',
                $collection->get('name'),
                $ownerType,
                $ownerId,
                null,
                null,
                'id',
                $collection->get('id')
            ));

            $collection->commit();
        }
    }

    /**
     * @param string $event
     * @param array $data
     * @throws SQLException
     */
    public static function delete_collection_event($event, $data) {
        // Remove task assignments to collection
        if (!empty($data['id'])) {
            $sql = 'UPDATE {artefact_plans_task} SET outcome = NULL, outcometype = NULL ' .
                'WHERE outcometype = \'collection\' AND outcome = ?';

            execute_sql($sql, [$data['id']]);
        }
    }

    /**
     * @param string $event
     * @param array $data
     * @throws SQLException
     */
    public static function delete_view_event($event, $data) {
        // Remove task assignments to view
        if (!empty($data['id'])) {
            $sql = 'UPDATE {artefact_plans_task} SET taskview = NULL WHERE taskview = ?';

            execute_sql($sql, [$data['id']]);

            $sql = 'UPDATE {artefact_plans_task} SET outcome = NULL, outcometype = NULL ' .
                'WHERE outcometype = \'view\' AND outcome = ?';

            execute_sql($sql, [$data['id']]);
        }
    }
}

class ArtefactTypePlan extends ArtefactType {

    protected $template;
    // $roottemplate === null   => Plan is not created from template
    // $roottemplate > 0        => Plan is created from template and root template plan still exists
    // $roottemplate === 0      => Plan is created from template but root template plan was deleted in the meantime
    protected $roottemplate;
    protected $rootgroupplan;
    protected $selectionplan;

    /**
     * ArtefactTypePlan constructor.
     * @param int $id
     * @param array|null $data
     * @throws ArtefactNotFoundException
     * @throws SQLException
     * @throws SystemException
     */
    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if ($this->id) {
            if (defined('INSTALLER') && !db_table_exists('artefact_plans_plan')) {
                // Upgrading from older site and table does not yet exist
            }
            else if ($pdata = get_record('artefact_plans_plan', 'artefact', $this->id)) {
                foreach($pdata as $name => $value) {
                    if (property_exists($this, $name)) {
                        $this->$name = $value;
                    }
                }
            }
            else {
                // This should never happen unless the user is playing around with task IDs in the location bar or similar
                throw new ArtefactNotFoundException(get_string('plandoesnotexist', 'artefact.plans'));
            }
        }
        else {
            $this->container = 1;
        }
    }

    /**
     * @param int $id
     * @return array
     */
    public static function get_links($id) {
        return [
            '_default' => get_config('wwwroot') . 'artefact/plans/plan/view.php?id=' . $id,
        ];
    }

    /**
     * This method extends ArtefactType::commit() by adding additional data
     * into the artefact_plans_task table.
     *
     * @return bool|int|void
     * @throws SQLException
     */
    public function commit() {
        if (empty($this->dirty)) {
            return;
        }

        // Return whether or not the commit worked
        $success = false;

        db_begin();
        $new = empty($this->id);

        if ($new) {
            require_once('tools/PlansTools.php');

            // Adjust title if owner has another plan with the same title
            if (!empty($this->get('group'))) {
                $ownerType = 'group';
                $ownerId = $this->get('group');
            }
            else {
                $ownerType = 'owner';
                $ownerId = $this->get('owner');
            }
            $this->set('title',
                       PlansTools::createUniqueStringForDBField(
                           'artefact',
                           'title',
                           $this->get('title'),
                           $ownerType,
                           $ownerId,
                           'artefacttype',
                           'plan'
                       )
            );
        }

        parent::commit();

        $this->dirty = true;

        $data = (object)[
            'artefact'  => $this->get('id'),
            'template' => $this->get('template'),
            'roottemplate' => $this->get('roottemplate'),
            'rootgroupplan' => $this->get('rootgroupplan'),
            'selectionplan' => $this->get('selectionplan'),
        ];

        if ($new) {
            $success = insert_record('artefact_plans_plan', $data);
        }
        else {
            $success = update_record('artefact_plans_plan', $data, 'artefact');
        }

        db_commit();

        $this->dirty = $success ? false : true;

        return $success;
    }

    /**
     * @throws SQLException
     */
    public function delete() {
        if (empty($this->id)) {
            return;
        }
        require_once('tools/PlansTools.php');

        db_begin();
        $planId = $this->id;

        delete_records('artefact_plans_plan', 'artefact', $planId);
        parent::delete();

        PlansTools::removePlanAssignmentsDependingOnRootGroupPlanByRootGroupPlanId($planId);
        PlansTools::removePlanAssignmentsDependingOnRootTemplatePlanByRootTemplatePlanId($planId);

        db_commit();
    }

    /**
     * @param array $artefactids
     * @param bool $log
     * @throws SQLException
     */
    public static function bulk_delete($artefactids, $log=false) {
        if (empty($artefactids)) {
            return;
        }

        require_once('tools/PlansTools.php');

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();

        delete_records_select('artefact_plans_plan', 'artefact IN (' . $idstr . ')');
        parent::bulk_delete($artefactids);

        foreach ($artefactids as $artefactid) {
            PlansTools::removePlanAssignmentsDependingOnRootGroupPlanByRootGroupPlanId($artefactid);
            PlansTools::removePlanAssignmentsDependingOnRootTemplatePlanByRootTemplatePlanId($artefactid);
        }

        db_commit();
    }

    public static function get_icon($options=null) {
        global $THEME;
        return false;
    }

    /**
     * @return bool
     */
    public static function is_singular() {
        return false;
    }

    /**
     * @return bool
     */
    public function is_template() {
        return !empty($this->template);
    }

    /**
     * @return bool
     */
    public function is_created_from_template() {
        return !is_null($this->roottemplate);
    }

    /**
     * @return bool
     */
    public function is_groupplan() {
        return !is_null($this->group);
    }

    /**
     * @return bool
     */
    public function is_selection_plan() {
        return !empty($this->selectionplan);
    }

    /**
     * @return bool
     */
    public function is_root_groupplan() {
        return !is_null($this->rootgroupplan);
    }

    /**
     * @param stdClass $group
     * @return bool
     */
    public static function user_can_edit_groupplan(stdClass $group) {
        global $USER;

        $role = group_user_access($group->id);
        return ($role and $role !== 'member' or $USER->get('admin'));
    }

    /**
     * @param stdClass $group
     * @return bool
     */
    public static function user_can_view_groupplans(stdClass $group) {
        global $USER;

        $role = group_user_access($group->id);
        return ($role or $USER->get('admin'));
    }

    /**
     * @param int $planId
     * @return bool
     * @throws SQLException
     */
    public static function planHasTimeCriticalTasks($planId) {
        $sql = 'SELECT * FROM {artefact} a INNER JOIN {artefact_plans_task} t ON t.artefact = a.id ' .
                'WHERE a.parent= ? AND t.completed = 0 AND t.reminder IS NOT NULL AND t.completiondate IS NOT NULL AND t.completiondate > NOW()';
        if (is_postgres()) {
            $sql .= " AND t.completiondate - (t.reminder * INTERVAL '1 SECOND') < NOW()";
        }
        else {
            $sql .= " AND DATE_ADD(t.completiondate, INTERVAL - t.reminder SECOND) < NOW()";
        }

        $result = record_exists_sql($sql, [$planId]);
        return $result;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param stdClass|null $group
     * @return array
     * @throws SQLException
     */
    public static function get_plans($offset = 0, $limit = 10, stdClass $group = null) {
        global $USER;

        if (is_null($group)) {
            $groupId = null;
            $ownerDBField = 'owner';
            $ownerId = $USER->get('id');
        }
        else {
            $groupId = $group->id;
            $ownerDBField = 'group';
            $ownerId = $groupId;
        }

        ($plans = get_records_sql_array("SELECT * FROM {artefact}
                                        INNER JOIN {artefact_plans_plan} ON artefact = id
                                        WHERE " . db_quote_identifier($ownerDBField) . " = ? AND artefacttype = 'plan'
                                        ORDER BY title ASC", [$ownerId], $offset, $limit))
        || ($plans = []);

        foreach ($plans as &$plan) {
            if (!isset($plan->tags)) {
                $plan->tags = ArtefactType::artefact_get_tags($plan->id);
            }
            $plan->hastimecriticaltasks = self::planHasTimeCriticalTasks($plan->id);
            $plan->description = '<p>' . preg_replace('/\n\n/', '</p><p>', $plan->description) . '</p>';
        }

        $result = [
            'count' => count_records('artefact', $ownerDBField, $ownerId, 'artefacttype', 'plan'),
            'data' => $plans,
            'offset' => $offset,
            'limit' => $limit,
            'group' => $groupId
        ];

        return $result;
    }

    /**
     * @param array $plans
     * @param bool $canEdit
     * @throws ParameterException
     */
    public static function build_plans_list_html(&$plans, $canEdit) {
        $smarty = smarty_core();
        $smarty->assign('plans', $plans);

        $urlQuery = '';
        if ($plans['group']) {
            $urlQuery = '?group=' . $plans['group'];
        }

        $smarty->assign('canedit', $canEdit);
        $smarty->assign('templatetext', get_string('template', 'artefact.plans'));

        $plans['tablerows'] = $smarty->fetch('artefact:plans:indexplans.tpl');
        $pagination = build_pagination([
                                           'id' => 'planlist_pagination',
                                           'url' => get_config('wwwroot') . 'artefact/plans/index.php' . $urlQuery,
                                           'jsonscript' => 'artefact/plans/plans.json.php',
                                           'datatable' => 'planslist',
                                           'count' => $plans['count'],
                                           'limit' => $plans['limit'],
                                           'offset' => $plans['offset'],
                                           'setlimit' => true,
                                           'jumplinks' => 6,
                                           'numbersincludeprevnext' => 2,
                                           'resultcounttext' => get_string('nplans', 'artefact.plans', $plans['count']),
                                       ]);
        $plans['pagination'] = $pagination['html'];
        $plans['pagination_js'] = $pagination['javascript'];
    }

    /**
     * @param Pieform $form
     * @param array $values
     * @throws ArtefactNotFoundException
     * @throws PieformException
     * @throws SQLException
     * @throws SystemException
     */
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

    /**
     * @param Pieform $form
     * @param array $values
     * @throws ArtefactNotFoundException
     * @throws CollectionNotFoundException
     * @throws SQLException
     * @throws SystemException
     */
    public static function submit(Pieform $form, array $values) {
        global $USER, $SESSION, $view;

        $new = false;

        if (!empty($values['plan'])) {
            $planId = (int) $values['plan'];
            $plan = new ArtefactTypePlan($planId);
        }
        else {
            $plan = new ArtefactTypePlan();

            if (!empty($values['groupid'])) {
                $plan->set('group', $values['groupid']);
            }
            else {
                $plan->set('owner', $USER->get('id'));
            }
            $new = true;
        }

        $plan->set('title', $values['title']);
        $plan->set('description', $values['description']);

        $plan->set('template', $values['template'] ? 1 : 0);
        // if it's a new groupplan based on a Userplan-template the userPlanTemplateId is submitted and we can set it as root template
        $plan->set('roottemplate', $values['createfromuserplantemplate'] ? $values['createfromuserplantemplate'] : $values['roottemplate']);
        $plan->set('rootgroupplan', $values['rootgroupplan']);
        $plan->set('selectionplan', $values['selectionplan'] ? 1 : 0);

        if (get_config('licensemetadata')) {
            $plan->set('license', $values['license']);
            $plan->set('licensor', $values['licensor']);
            $plan->set('licensorurl', $values['licensorurl']);
        }
        $plan->set('tags', $values['tags']);
        $plan->commit();

        // if it's a new groupplan based on a Userplan-template the userPlanTemplateId is submitted
        if ($values['createfromuserplantemplate']) {
            PlansTools::createGroupPlanTasksFromUserPlanTemplate($plan, $values['createfromuserplantemplate']);
        }

        $SESSION->add_ok_msg(get_string('plansavedsuccessfully', 'artefact.plans'));

        if ($view && $USER->can_edit_view($view)) {
            redirect(get_config('wwwroot') . 'view/blocks.php?id=' . $view->get('id'));
        }

        $urlVars = [];
        if ($plan->is_groupplan()) {
            $urlVars = ['group' => $plan->get('group')];
        }

        if ($new) {
            $urlVars['id'] = $plan->get('id');
            redirect('/artefact/plans/plan/view.php?' . http_build_query($urlVars));
        }
        else {
            $urlQuery = '';
            if ($urlVars) {
                $urlQuery = '?' . http_build_query($urlVars);
            }
            redirect('/artefact/plans/index.php' . $urlQuery);
        }
    }

    /**
     * Gets the new/edit plans pieform
     *
     * @param stdClass|null $group
     * @param ArtefactTypePlan|null $plan
     * @return string
     */
    public static function get_form(stdClass $group = null, ArtefactTypePlan $plan = null) {
        global $USER, $view;

        if ($view && $USER->can_edit_view($view)) {
            $returnurl = get_config('wwwroot') . 'view/blocks.php?id=' . $view->get('id');
        }
        else {
            $urlQuery = ($group ? '?group=' . $group->id : '');
            $returnurl = get_config('wwwroot') . 'artefact/plans/index.php' . $urlQuery;
        }

        require_once('license.php');

        $elements = self::get_planform_elements($plan);
        if ($group) {
            $elements['groupid'] = ['type' => 'hidden', 'value' => $group->id]; // if set, it is a groupplan/-task
            $elements['template'] = ['type' => 'hidden', 'value' => false]; // Groupplans can not be templates
            $elements['selectionplan']['readonly'] = ($group->editroles === 'all'); // Groupplans can be set to selection plans if normal members can't edit group artefacts
        }
        else if (is_null($plan)) {
            $elements['selectionplan']['readonly'] = false; // New non group plans can also be potentially set to selection plans
        }
        $elements['createfromuserplantemplate'] = ['type' => 'hidden', 'dynamic' => true, 'value' => null];

        $elements['submit'] = [
            'type' => 'submitcancel',
            'subclass' => ['btn-primary'],
            'value' => [get_string('saveplan','artefact.plans'), get_string('cancel')],
            'goto' => $returnurl,
        ];
        $planform = [
            'name' => empty($plan) ? 'addplan' : 'editplan',
            'plugintype' => 'artefact',
            'pluginname' => 'task',
            'validatecallback' => ['ArtefactTypePlan','validate'],
            'successcallback' => ['ArtefactTypePlan','submit'],
            'elements' => $elements,
        ];

        return pieform($planform);
    }

    /**
     * Gets the new/edit fields for the plan pieform
     *
     * @param ArtefactTypePlan|null $plan
     * @return array
     */
    public static function get_planform_elements(ArtefactTypePlan $plan=null) {
        $elements = [
            'title' => [
                'type' => 'text',
                'defaultvalue' => null,
                'title' => get_string('title', 'artefact.plans'),
                'size' => 30,
                'rules' => [
                    'required' => true,
                ],
            ],
            'description' => [
                'type'  => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'defaultvalue' => null,
                'title' => get_string('description', 'artefact.plans'),
            ],
            'template' => [
                'type'  => 'switchbox',
                'description' => get_string('templatedescription', 'artefact.plans'),
                'defaultvalue' => false,
                'title' => get_string('template', 'artefact.plans'),
            ],
            'roottemplate' => [
                'type' => 'hidden',
                'defaultvalue' => null,
                'value' => null,
            ],
            'selectionplan' => [
                'type'  => 'switchbox',
                'description' => get_string('selectionplandescription', 'artefact.plans'),
                'defaultvalue' => false,
                'title' => get_string('selectionplan', 'artefact.plans'),
                'readonly' => true,
            ],
            'rootgroupplan' => [
                'type' => 'hidden',
                'defaultvalue' => null,
                'value' => null,
            ],
            'tags'        => [
                'type'        => 'tags',
                'title'       => get_string('tags'),
                'description' => get_string('tagsdescprofile'),
                'help'        => true,
            ],
        ];

        if (!empty($plan)) {
            foreach ($elements as $k => $element) {
                $elements[$k]['defaultvalue'] = $plan->get($k);
            }
            $elements['plan'] = [
                'type' => 'hidden',
                'value' => $plan->id,
            ];
            // Only at creation time a Plan can be set as template to simplify usage and coding
            $elements['template']['readonly'] = true;

            // Only on a template plan and a group plan (handled later) the status selectionplan can be edited
            if ($plan->get('template')) {
                $elements['selectionplan']['readonly'] = false;
            }
        }

        if (get_config('licensemetadata')) {
            $elements['license'] = license_form_el_basic($plan);
            $elements['license_advanced'] = license_form_el_advanced($plan);
        }

        return $elements;
    }

    /**
     * @param array $options
     * @return array
     * @throws MaharaException
     * @throws ParameterException
     * @throws SQLException
     */
    public function render_self($options) {
        $limit = !isset($options['limit']) ? 10 : (int) $options['limit'];
        $offset = isset($options['offset']) ? intval($options['offset']) : 0;

        $tasks = ArtefactTypeTask::get_tasks($this, $offset, $limit);

        $template = 'artefact:plans:view/plantasks.tpl';

        $baseurl = ''; //will set in the jsonscript
        $pagination = [
            'baseurl' => $baseurl,
            'id' => 'task_pagination',
            'datatable' => 'tasklist',
            'jsonscript' => 'artefact/plans/block/tasks.json.php',
        ];

        ArtefactTypeTask::render_tasks($tasks, $template, $options, $pagination);

        $smarty = smarty_core();
        $smarty->assign('tasks', $tasks);
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
        $smarty->assign('view', (!empty($options['viewid']) ? $options['viewid'] : null));
        $smarty->assign('owner', $this->get('owner'));
        $smarty->assign('tags', $this->get('tags'));

        return ['html' => $smarty->fetch('artefact:plans:view/plan.tpl'), 'javascript' => ''];
    }

    public static function is_countable_progressbar() {
        return true;
    }
}

class ArtefactTypeTask extends ArtefactType {

    protected $completed = 0;
    protected $completiondate;

    protected $startdate;
    protected $reminder;
    protected $taskview;
    protected $outcome;
    protected $outcometype;
    protected $template;
    // $roottemplatetask === null   => Task is not created from template
    // $roottemplatetask > 0        => Task is created from template and root template task still exists
    // $roottemplatetask === 0      => Task is created from template but root template task was deleted in the meantime
    protected $roottemplatetask;
    protected $rootgrouptask;

    /**
     * ArtefactTypeTask constructor.
     * @param int $id
     * @param null $data
     * @throws ArtefactNotFoundException
     * @throws SQLException
     * @throws SystemException
     */
    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if ($this->id) {
            if ($pdata = get_record('artefact_plans_task', 'artefact', $this->id, null, null, null, null, '*, ' .
                         db_format_tsfield('startdate') . ', ' .
                         db_format_tsfield('completiondate'))) {
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
        return [
            '_default' => get_config('wwwroot') . 'artefact/plans/task/edit.php?id=' . $id,
        ];
    }

    public static function get_icon($options=null) {
        global $THEME;
        return false;
    }

    public static function is_singular() {
        return false;
    }

    public function is_grouptask() {
        return !is_null($this->group);
    }

    public function is_created_from_template() {
        return !is_null($this->roottemplatetask);
    }

    public function is_chosen_grouptask() {
        return !is_null($this->rootgrouptask);
    }

    public function has_taskview() {
        return !is_null($this->taskview);
    }

    public function has_outcome() {
        return !is_null($this->outcome);
    }

    /**
     * * This method extends ArtefactType::commit() by adding additional data
     * into the artefact_plans_task table.
     *
     * @return bool|int|void
     * @throws SQLException
     */
    public function commit() {
        if (empty($this->dirty)) {
            return;
        }

        // Return whether or not the commit worked
        $success = false;

        db_begin();
        $new = empty($this->id);

        if ($new) {
            require_once('tools/PlansTools.php');
            // Adjust title if parent plan has another task with the same title
            if (!empty($this->get('group'))) {
                $ownerType = 'group';
                $ownerId = $this->get('group');
            }
            else {
                $ownerType = 'owner';
                $ownerId = $this->get('owner');
            }
            if ($this->get('parent')) {
                $this->set('title',
                       PlansTools::createUniqueStringForDBField(
                           'artefact',
                           'title',
                           $this->get('title'),
                           $ownerType,
                           $ownerId,
                           'parent',
                           $this->get('parent')
                       )
                );
            }
            else {
                $this->set('title',
                       PlansTools::createUniqueStringForDBField(
                           'artefact',
                           'title',
                           $this->get('title'),
                           $ownerType,
                           $ownerId
                       )
                );
            }
        }

        parent::commit();

        $this->dirty = true;

        $startdate = $this->get('startdate');
        $completiondate = $this->get('completiondate');
        $reminder = (int)$this->get('reminder');
        $taskview = (int)$this->get('taskview');
        $outcome = (int)$this->get('outcome');
        $outcometype = $this->get('outcometype');

        $data = (object)[
            'artefact'  => $this->get('id'),
            'completed' => $this->get('completed'),
            'completiondate' => (!is_null($completiondate) ? db_format_timestamp($completiondate) : null),
            'startdate' => (!is_null($startdate) ? db_format_timestamp($startdate) : null),
            'reminder' => (empty($reminder) ? null : $reminder),
            'taskview' => (empty($taskview) ? null : $taskview),
            'outcome' => (empty($outcome) ? null : $outcome),
            'outcometype' => (empty($outcometype) ? null : $outcometype),
            'template' => $this->get('template'),
            'roottemplatetask' => $this->get('roottemplatetask'),
            'rootgrouptask' => $this->get('rootgrouptask'),
        ];

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
     *
     * @throws ArtefactNotFoundException
     * @throws CollectionNotFoundException
     * @throws SQLException
     * @throws SystemException
     * @throws ViewNotFoundException
     */
    public function delete() {
        global $USER;

        if (empty($this->id)) {
            return;
        }

        require_once('tools/PlansTools.php');
        $taskId = $this->id;

        db_begin();

        // Check if we can delete automatically generated assigned views and collections if they are unmodified
        // (For group plan tasks from private template plans (taskview and outcome portfolio)
        // and private selection tasks from group selection plans(outcome portfolio only))
        if ($this->has_taskview() && $this->is_created_from_template()) {
            require_once(get_config('libroot').'view.php');

            $taskView = new \View($this->taskview);

            if (PlansTools::viewIsAutomaticallyDeletable($taskView)) {
                require_once(get_config('libroot').'collection.php');

                $collectionViewRecord = get_record('collection_view', 'view', $taskView->get('id'));

                if ($collectionViewRecord) {
                    $taskViewCollection = new \Collection($collectionViewRecord->collection);

                    // If it's the last view in the collection, delete also the parent collection
                    if (count($taskViewCollection->get_viewids()) === 1) {
                        $taskViewCollection->delete();
                    }
                }

                PlansTools::deleteViewAndAssignedArtefacts($taskView);
            }
        }

        if ($this->has_outcome() && ($this->is_chosen_grouptask() || $this->is_created_from_template())) {
            switch ($this->outcometype) {
                case 'view':
                    require_once(get_config('libroot').'view.php');

                    $outcomeView = new \View($this->outcome);
                    if (PlansTools::viewIsAutomaticallyDeletable($outcomeView)) {
                        PlansTools::deleteViewAndAssignedArtefacts($outcomeView);
                    }
                    break;
                case 'collection':
                    require_once(get_config('libroot').'collection.php');

                    $outcomeCollection = new \Collection($this->outcome);

                    if ($USER->can_edit_collection($outcomeCollection) && !$outcomeCollection->is_submitted()) {
                        // We have to count the deleted views cause we can't use get_viewids
                        // due to the db_begin/-end cache to check if the collection is empty
                        $deletedViews = 0;
                        foreach ($outcomeCollection->get_viewids() as $collectionViewId) {
                            $collectionView = new \View($collectionViewId);
                            // To check if the view is submitted should not be necessary, only to be absolutely sure,
                            // that the view really can be deleted
                            if (PlansTools::viewIsAutomaticallyDeletable($collectionView)) {
                                PlansTools::deleteViewAndAssignedArtefacts($collectionView);
                                $deletedViews += 1;
                            }
                        }

                        if (count($outcomeCollection->get_viewids()) === $deletedViews) {
                            $outcomeCollection->delete();
                        }
                    }
                    break;
            }
        }

        delete_records('artefact_plans_task', 'artefact', $taskId);
        parent::delete();

        // If this is a Rootgrouptask remove all references from other tasks
        PlansTools::removeTaskAssignmentsDependingOnRootGroupTaskByRootGroupTaskId($taskId);
        // If this is a template task remove all references from other tasks
        PlansTools::removeTaskAssignmentsDependingOnRootTemplateTaskByRootTemplateTaskId($taskId);

        db_commit();
    }

    /**
     * @param array $artefactids
     * @param bool $log
     * @throws SQLException
     */
    public static function bulk_delete($artefactids, $log=false) {
        if (empty($artefactids)) {
            return;
        }

        require_once('tools/PlansTools.php');

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();

        delete_records_select('artefact_plans_task', 'artefact IN (' . $idstr . ')');
        parent::bulk_delete($artefactids);
        foreach ($artefactids as $artefactid) {
            PlansTools::removeTaskAssignmentsDependingOnRootGroupTaskByRootGroupTaskId($artefactid);
            PlansTools::removeTaskAssignmentsDependingOnRootTemplateTaskByRootTemplateTaskId($artefactid);
        }

        db_commit();
    }


    /**
     * @param int $parentPlanId
     * @param stdClass|null $group
     * @param ArtefactTypeTask|null $task
     * @return string
     * @throws ArtefactNotFoundException
     * @throws ParameterException
     * @throws SQLException
     * @throws SystemException
     * @throws ViewNotFoundException
     */
    public static function get_form($parentPlanId, stdClass $group = null, ArtefactTypeTask $task = null) {
        global $USER, $view;

        if ($view && $USER->can_edit_view($view)) {
            $returnurl = get_config('wwwroot') . 'view/blocks.php?id=' . $view->get('id');
        }
        else if ($group) {
            $returnurl = get_config('wwwroot') . 'artefact/plans/plan/view.php?group=' . $group->id . '&id=' . $parentPlanId;
        }
        else {
            $returnurl = get_config('wwwroot') . 'artefact/plans/plan/view.php?id=' . $parentPlanId;
        }

        require_once('license.php');
        $elements = self::get_taskform_elements($parentPlanId, $group, $task);
        $elements['submit'] = [
            'type' => 'submitcancel',
            'subclass' => ['btn-primary'],
            'value' => [get_string('savetask','artefact.plans'), get_string('cancel')],
            'goto' => $returnurl,
        ];

        $taskform = [
            'name' => 'edittask',
            'plugintype' => 'artefact',
            'pluginname' => 'task',
            'validatecallback' => ['ArtefactTypetask','validate'],
            'successcallback' => ['ArtefactTypetask','submit'],
            'elements' => $elements,
        ];

        return pieform($taskform);
    }

    /**
     * Gets the new/edit fields for the tasks pieform
     *
     * @param $parentPlanId
     * @param stdClass|null $group
     * @param ArtefactTypeTask|null $task
     * @return array
     * @throws ArtefactNotFoundException
     * @throws SQLException
     * @throws SystemException
     * @throws ViewNotFoundException
     */
    public static function get_taskform_elements($parentPlanId, stdClass $group = null, ArtefactTypeTask $task = null) {
        require_once('pieforms/pieform/elements/calendar.php');
        require_once('tools/PlansTools.php');

        $parentPlan = new \ArtefactTypePlan($parentPlanId);

        $elements = [
            'title' => [
                'type' => 'text',
                'defaultvalue' => null,
                'title' => get_string('title', 'artefact.plans'),
                'description' => get_string('titledesc','artefact.plans'),
                'size' => 30,
                'rules' => [
                    'required' => true,
                ],
            ],
            'startdate' => [
                'type'       => 'calendar',
                'caloptions' => [
                    'showsTime'      => false,
                ],
                'defaultvalue' => null,
                'title' => get_string('startdate', 'artefact.plans'),
                'description' => get_string('startdatedescription', 'artefact.plans', pieform_element_calendar_human_readable_dateformat()),
                'rules' => [
                    'required' => false,
                ],
            ],
            'completiondate' => [
                'type' => 'calendar',
                'caloptions' => [
                    'showsTime' => false,
                ],
                'defaultvalue' => null,
                'title' => get_string('completiondate', 'artefact.plans'),
                'description' => get_string('completiondatedescription', 'artefact.plans', pieform_element_calendar_human_readable_dateformat()),
                'rules' => [
                    'required' => false,
                ],
            ],
            'reminder' => [
                'type'  => 'expiry',
                'title' => get_string('reminder', 'artefact.plans'),
                'description' => get_string('reminderdescription', 'artefact.plans'),
                'defaultvalue' => null,
                'class' => 'double'
            ],

            'description' => [
                'type'  => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'defaultvalue' => null,
                'title' => get_string('description', 'artefact.plans'),
            ],
            'view' => [
                'type' => 'select',
                'title' => get_string('taskview', 'artefact.plans'),
                'description' => get_string('taskviewdescription', 'artefact.plans'),
                'options' => [
                    null => get_string('none', 'artefact.plans')
                ],
                'collapseifoneoption' => false,
                'defaultvalue' => null,
            ],
            'outcome' => [
                'type' => 'select',
                'title' => get_string('outcome', 'artefact.plans'),
                'description' => get_string('outcomedescription', 'artefact.plans'),
                'collapseifoneoption' => false,
                'optgroups' => [
                    'none' => [
                        'label' => get_string('default'),
                        'options' => [
                            null => get_string('none', 'artefact.plans')
                        ],
                    ]
                ],
                'defaultvalue' => null
            ],
            'rootgrouptask' => [
                'type' => 'hidden',
                'value' => null,
            ],
            'template' => [
                'type' => 'hidden',
                'value' => null,
            ],
            'roottemplatetask' => [
                'type' => 'hidden',
                'value' => null,
            ],
            'tags'        => [
                'type'        => 'tags',
                'title'       => get_string('tags'),
                'description' => get_string('tagsdescprofile'),
                'help'        => true,
            ],
            'completed' => [
                'type' => 'switchbox',
                'defaultvalue' => null,
                'title' => get_string('completed', 'artefact.plans'),
                'description' => get_string('completeddesc', 'artefact.plans'),
            ],
        ];

        // set default values and organize input conditions from task when exists
        if (!empty($task)) {
            foreach ($elements as $field => $element) {
                switch ($field) {

                    case 'rootgrouptask': // cause it's a hidden field without default, we set its value manually
                        $elements['rootgrouptask']['value'] = $task->get('rootgrouptask');
                        break;

                    case 'template': // cause it's a hidden field without default, we set its value manually
                        $elements['template']['value'] = $task->get('template');
                        break;

                    case 'roottemplatetask': // cause it's a hidden field without default, we set its value manually
                        $elements['roottemplatetask']['value'] = $task->get('roottemplatetask');
                        break;

                    case 'view':
                        if ($parentPlan->is_selection_plan() || ($parentPlan->is_root_groupplan() && $task->is_chosen_grouptask())) {
                            // If task has a rootgrouptask (user has selected in group), then the user can't change the taskView,
                            // so we only need the entry of the assigned GroupTaskView
                            $taskViewId = $task->get('taskview');

                            if (!empty($task->get('rootgrouptask'))) {
                                require_once(get_config('docroot') . 'lib/view.php');
                                    if ($taskViewId) {
                                        $taskView = new \View($taskViewId);

                                        if ($taskView) {
                                            $elements['view']['options'][$taskView->get('id')] = $taskView->get('title');
                                            $elements['view']['defaultvalue'] = $taskView->get('id');
                                        }
                                    }
                                    $elements['view']['readonly'] = true;
                            }
                            else {
                                PlansTools::fillTaskViewSelectElement($elements['view'], $task->get('id'), $group);
                            }
                        }
                        else {
                            unset($elements['view']);
                        }
                        break;

                    case 'outcome':
                        if ($parentPlan->is_selection_plan() || ($parentPlan->is_root_groupplan() && $task->is_chosen_grouptask())) {
                            PlansTools::fillOutcomePortfolioSelectElementForTask($elements['outcome'], $task->get('id'), $group);
                        }
                        else {
                            unset($elements['outcome']);
                        }
                        break;

                    default:
                        $elements[$field]['defaultvalue'] = $task->get($field);
                }
            }
            $elements['taskid'] = ['type' => 'hidden', 'value' => $task->get('id')];

        }
        else { // new task
            if ($parentPlan->is_selection_plan()) {
                PlansTools::fillTaskViewSelectElement($elements['view'], null, $group);
                PlansTools::fillOutcomePortfolioSelectElementForTask($elements['outcome'], null, $group);
            }
            else {
                unset($elements['view']);
                unset($elements['outcome']);
            }

            $elements['taskid'] = ['type' => 'hidden', 'value' => null];
            // see if it's a template by checking it's parent plan template status
            $elements['template']['value'] = $parentPlan->is_template();
            $elements['roottemplatetask']['value'] = null;
        }

        if (get_config('licensemetadata')) {
            $elements['license'] = license_form_el_basic($task);
            $elements['license_advanced'] = license_form_el_advanced($task);
        }

        $elements['parent'] = ['type' => 'hidden', 'value' => $parentPlanId];

        // if task is template, some date-fields get their values from target group when imported there
        if ($elements['template']['value']) {
            $elements['startdate']['readonly'] = true;
            $elements['completiondate']['readonly'] = true;
            $elements['completed']['readonly'] = true;
        }

        if ($group) {
            $elements['groupid'] = ['type' => 'hidden', 'value' => $group->id]; // if set, it is a groupplan/-task

            if ($parentPlan->is_selection_plan()) {
                $elements['completed'] = ['type' => 'hidden', 'value' => $elements['completed']['defaultvalue']];
            }
        }

        return $elements;
    }

    /**
     * @param Pieform $form
     * @param array $values
     * @return bool
     * @throws ArtefactNotFoundException
     * @throws CollectionNotFoundException
     * @throws MaharaException
     * @throws PieformException
     * @throws SQLException
     * @throws SystemException
     */
    public static function validate(Pieform $form, $values) {
        global $USER;

        $now = new DateTime();

        $group = null;
        if (!empty($values['groupid'])) {
            $group = get_group_by_id($values['groupid']);
        }

        if (!empty($values['taskid'])) {
            $id = (int) $values['taskid'];
            $artefact = new ArtefactTypeTask($id);
            if (!$USER->can_edit_artefact($artefact)) {
                $form->set_error('submit', get_string('canteditdontowntask', 'artefact.plans'));
            }
        }

        if (!empty($values['outcome'])) {
            list($outcomePortfolioType, $outcomePortfolioId) = PlansTools::getOutcomePortfolioTypeAndIdFromTaskOutcomeSelection($values['outcome']);

            switch ($outcomePortfolioType) {
                case 'view':
                    if ($outcomePortfolioId == $values['view']) {
                        $form->set_error('outcome', get_string('viewandoutputcontainsameelement', 'artefact.plans'));
                    }
                    break;
                case 'collection':
                    require_once(get_config('libroot').'collection.php');
                    $collection = new \Collection($outcomePortfolioId);
                    if (in_array($values['view'], $collection->get_viewids())) {
                        $form->set_error('outcome', get_string('viewandoutputcontainsameelement', 'artefact.plans'));
                    }
                    break;
                default:
                    throw new \MaharaException(get_string('unsupportedportfoliotype','artefact.plans'));
            }
        }

        // if it's a template, no date validation is necessary
        if ($values['template']) {
            return true;
        }

        if ($values['startdate']) {
            if ($values['completiondate'] && $values['startdate'] > $values['completiondate']) {
                $form->set_error('startdate', get_string('startdatemustbebeforecompletiondate', 'artefact.plans'));
            }
        }

        if ($values['completiondate']) {
            // Check if the completion date is in the future. To allow setting today's date as the completion date
            // we need to check the posted date with current time
            $currenttime = $now->format('H:i:s');
            $completionday = date('Y-m-d', $values['completiondate']);
            $completiondate = strtotime($completionday . ' ' . $currenttime);
            if ($completiondate < $now->getTimestamp()) {
                $form->set_error('completiondate', get_string('completiondatemustbeinfuture', 'artefact.plans'));
            }
        }

        if ($values['reminder']) {
            if (!$values['completiondate']) {
                $form->set_error('reminder', get_string('completiondatemustbesetforreminder', 'artefact.plans'));
            }
        }
        return true;
    }

    /**
     * @param Pieform $form
     * @param array $values
     * @throws ArtefactNotFoundException
     * @throws SQLException
     * @throws SystemException
     */
    public static function submit(Pieform $form, $values) {
        global $USER, $SESSION, $view;

        $values['view'] = !empty($values['view']) ? $values['view'] : '';
        $values['outcome'] = !empty($values['outcome']) ? $values['outcome'] : '';
        if (!empty($values['taskid'])) {
            $id = (int) $values['taskid'];
            $artefact = new ArtefactTypeTask($id);
        }
        else {
            $artefact = new ArtefactTypeTask();

            if (!empty($values['groupid'])) {
                $artefact->set('group', $values['groupid']);
            }
            else {
                $artefact->set('owner', $USER->get('id'));
            }
            $artefact->set('parent', $values['parent']);
            $artefact->set('template', $values['template'] ? 1 : 0);
            $artefact->set('roottemplatetask', $values['roottemplatetask']);
        }

        $artefact->set('rootgrouptask', $values['rootgrouptask']);

        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);

        // Set flag for asking for a submission
        if ($artefact->has_outcome() && $artefact->get('completed') == 0 && $values['completed'] == 1) {
            $submissionUrl = PlansTools::createSubmissionUrlForCompletedTaskWithOutcome($artefact);
        }
        $artefact->set('completed', $values['completed'] ? 1 : 0);
        $artefact->set('startdate', $values['startdate']);
        $artefact->set('completiondate', $values['completiondate']);
        $artefact->set('reminder', $values['reminder']);

        $artefact->set('taskview', $values['view']);

        list($outcomeType, $outcomeId) = PlansTools::getOutcomePortfolioTypeAndIdFromTaskOutcomeSelection($values['outcome']);
        $artefact->set('outcome', $outcomeId);
        $artefact->set('outcometype', $outcomeType);

        if (get_config('licensemetadata')) {
            $artefact->set('license', $values['license']);
            $artefact->set('licensor', $values['licensor']);
            $artefact->set('licensorurl', $values['licensorurl']);
        }
        $artefact->set('tags', $values['tags']);
        $artefact->commit();

        $SESSION->add_ok_msg(get_string('tasksavedsuccessfully', 'artefact.plans'));

        if (!empty($submissionUrl)) {
            redirect($submissionUrl);
        }
        else if ($view && $USER->can_edit_view($view)) {
            redirect(get_config('wwwroot') . 'view/blocks.php?id=' . $view->get('id'));
        }
        else {
            $urlQuery = ['id' => $values['parent']];
            if ($artefact->is_grouptask()) {
                $urlQuery['group'] = $artefact->get('group');
            }
            redirect('/artefact/plans/plan/view.php?' . http_build_query($urlQuery));
        }
    }

    /**
     * This function returns a list of the current plans tasks.
     *
     * @param ArtefactTypePlan $plan
     * @param int $limit how many tasks to display per page
     * @param int $offset current page to display
     * @param array $tasks
     * @return array (count: integer, data: array)
     * @throws ArtefactNotFoundException
     * @throws SQLException
     * @throws SystemException
     */
    public static function get_tasks(ArtefactTypePlan $plan, $offset = 0, $limit = 10, $tasks = null) {
        require_once('tools/PlansTools.php');

        $d = new DateTime(); // time now to use for formatting tasks by completion
        $datenow = $d->getTimestamp();
        $d->setTime(0, 0, 0);
        $datebegin = $d->getTimestamp();
        $sql = "SELECT a.id, at.artefact AS task, " .
                    db_format_tsfield('startdate') . ", " .
                    db_format_tsfield('completiondate') . ", " .
                    "a.title, a.description, a.parent, a.owner, a.group, at.reminder, at.completed, " .
                    "at.taskview, at.outcometype, at.outcome, at.template, at.roottemplatetask, at.rootgrouptask " .
                "FROM {artefact} a JOIN {artefact_plans_task} at ON at.artefact = a.id " .
                "WHERE a.artefacttype = 'task' AND a.parent = ?";
        $values = array($plan->get('id'));
        if ($tasks) {
            $sql .= " AND a.id IN ( " . join(',', array_fill(0, count($tasks), '?')) . ")";
            $values = array_merge($values, $tasks);
        }
        if ($plan->is_template()) {
            $sql .= " ORDER BY a.id";
        }
        else {
            $sql .= " ORDER BY at.completed, at.completiondate ASC, a.id";
        }
        ($results = get_records_sql_array($sql, $values, $offset, $limit))
        || ($results = array());

        // format the date and setup completed for display if task is incomplete
        if (!empty($results)) {
            foreach ($results as $result) {
                if (!empty($result->completiondate)) {
                    // if record hasn't been completed and reminder time span is active mark it as time critical
                    if (!$result->completed && $result->reminder && ($result->completiondate - $result->reminder) < $datenow) {
                        $result->istimecritical = true;
                    }

                    // if record hasn't been completed and completiondate has passed mark as such for display
                    // we want to allow today's date to still be able to be completed
                    if ($result->completiondate < $datebegin && !$result->completed) {
                        $result->completed = -1;
                    }
                    $result->completiondate = format_date($result->completiondate, 'strftimedateshort');
                }
                if ($result->description) {
                    $result->description = '<p>' . preg_replace('/\n\n/','</p><p>', $result->description) . '</p>';
                }
                $result->tags = ArtefactType::artefact_get_tags($result->id);

                $result->isActiveRootGroupTask = false;

                // If it's a chosen task with an assigned outcome portfolio displayed in a group selection tasks list,
                // set the outcome fields with the user portfolio values so the user can go directly there and edit his portfolio
                if ($result->group && $plan->is_selection_plan()) {
                    $task = PlansTools::findCorrespondingUserTaskByRootGroupTaskId($result->id);
                    if ($task) {
                        $result->isActiveRootGroupTask = true;

                        $result->sourceoutcometype = $result->outcometype;
                        $result->sourceoutcome = $result->outcome;

                        $portfolioElement = PlansTools::getPortfolioElementByTypeAndId($result->sourceoutcometype,$result->sourceoutcome);

                        if ($portfolioElement) {
                            $result->sourceoutcomeurl = PlansTools::createOutcomeUrlForPortfolioElement($portfolioElement);

                            $result->outcometype = $task->get('outcometype');
                            $result->outcome = $task->get('outcome');
                        }
                    }
                }

                if ($result->outcometype && $result->outcome) {
                    $portfolioElement = PlansTools::getPortfolioElementByTypeAndId($result->outcometype, $result->outcome);

                    if ($portfolioElement) {
                        $result->outcomeurl = PlansTools::createOutcomeUrlForPortfolioElement($portfolioElement);
                        $result->outcomeiscurrentlysubmitted = $portfolioElement->is_submitted();

                        if (!empty($task)) {
                            $result->outcomesubmissionurl = PlansTools::createSubmissionUrlForCompletedTaskWithOutcome($task, $plan->get('id'));
                        }
                    }
                }
            }
        }

        $result = [
            'count'  => count_records('artefact', 'artefacttype', 'task', 'parent', $plan->get('id')),
            'data'   => $results,
            'offset' => $offset,
            'limit'  => $limit,
            'id'     => $plan->get('id'),
            'group' => $plan->get('group'),
            'selectiontasks' => $plan->get('selectionplan')
        ];

        return $result;
    }

    /**
     * Builds the tasks list table for current plan
     *
     * @param array $tasks (reference)
     * @param bool $canEdit
     * @throws ParameterException
     */
    public static function build_tasks_list_html(&$tasks, $canEdit) {
        $smarty = smarty_core();
        $smarty->assign('tasks', $tasks);
        $smarty->assign('canedit', $canEdit);
        $smarty->assign('showassignedview', get_string('showassignedview', 'artefact.plans'));
        $smarty->assign('showassignedoutcome', get_string('showassignedoutcome', 'artefact.plans'));
        $smarty->assign('editassignedoutcome', get_string('editassignedoutcome', 'artefact.plans'));
        $smarty->assign('submitassignedoutcome', get_string('submitassignedoutcome', 'artefact.plans'));

        $urlQuery = ['id' => $tasks['id']];
        if ($tasks['group']) {
            $urlQuery['group'] = $tasks['group'];
            $smarty->assign('group', $tasks['group']);
        }

        $tasks['tablerows'] = $smarty->fetch('artefact:plans:plan/viewtasks.tpl');
        $pagination = build_pagination([
            'id' => 'tasklist_pagination',
            'url' => get_config('wwwroot') . 'artefact/plans/plan/view.php?' . http_build_query($urlQuery),
            'jsonscript' => 'artefact/plans/plan/tasks.json.php',
            'datatable' => 'taskslist',
            'count' => $tasks['count'],
            'limit' => $tasks['limit'],
            'offset' => $tasks['offset'],
            'setlimit' => true,
            'jumplinks' => 6,
            'numbersincludeprevnext' => 2,
            'resultcounttext' => get_string('ntasks', 'artefact.plans', $tasks['count']),
        ]);
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
     * @param   boolean $editing    True if page is being edited
     * return   array   $tasks      The tasks array updated with rendered table html
     * @throws ParameterException
     * @throws ViewNotFoundException
     */
    public static function render_tasks(&$tasks, $template, $options, $pagination, $editing = false, $versioning = false) {
    global $USER;

        $smarty = smarty_core();
        $smarty->assign('tasks', $tasks);
        $smarty->assign('options', $options);
        $smarty->assign('view', (!empty($options['view']) ? $options['view'] : null));
        $smarty->assign('block', (!empty($options['block']) ? $options['block'] : null));
        $smarty->assign('editing', $editing);
        $smarty->assign('versioning', $versioning);
        if (!empty($options['view'])) {
            require_once('view.php');
            $view = new View($options['view']);
            $owner = $view->get('owner');
            if ($owner && $owner == $USER->get('id')) {
                if ($options && !empty($options['versioning'])) {
                    $smarty->assign('canedit', false);
                }
                else {
                    $smarty->assign('canedit', true);
                }
            }
        }

        $tasks['tablerows'] = $smarty->fetch($template);

        if ($tasks['limit'] && $pagination) {
            $pagination = build_pagination([
                'id' => $pagination['id'],
                'class' => 'center',
                'datatable' => $pagination['datatable'],
                'url' => $pagination['baseurl'],
                'jsonscript' => $pagination['jsonscript'],
                'count' => $tasks['count'],
                'limit' => $tasks['limit'],
                'offset' => $tasks['offset'],
                'numbersincludefirstlast' => false,
                'resultcounttext' => get_string('ntasks', 'artefact.plans', $tasks['count']),
            ]);
            $tasks['pagination'] = $pagination['html'];
            $tasks['pagination_js'] = $pagination['javascript'];
        }
    }

    /**
     * @return bool
     */
    public static function is_countable_progressbar() {
        return true;
    }
}
