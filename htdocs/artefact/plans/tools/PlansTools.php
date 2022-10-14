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

use ArtefactTypePlan;
use ArtefactTypeTask;
use View;

class PlansTools {

    /**
     * @param ArtefactTypePlan $rootGroupPlan
     * @return ArtefactTypePlan
     * @throws \ArtefactNotFoundException
     * @throws \SQLException
     * @throws \SystemException
     */
    public static function createUserPlanFromGroupPlan(ArtefactTypePlan $rootGroupPlan) {
        global $USER;
        $userPlan = new ArtefactTypePlan();
        $rootGroup = get_group_by_id((int)$rootGroupPlan->get('group'));

        // artefact fields
        $userPlan->set('owner', $USER->get('id'));
        $userPlan->set('parent', $userPlan->get('id'));
        $userPlan->set('title', $rootGroupPlan->get('title'));
        $userPlan->set('description', $rootGroupPlan->get('description'));
        $userPlan->set('license', $rootGroupPlan->get('license'));
        $userPlan->set('licensor', $rootGroupPlan->get('licensor'));
        $userPlan->set('licensorurl', $rootGroupPlan->get('licensorurl'));

        $tags = $rootGroupPlan->get('tags');
        $tags[] = $rootGroup->name;

        $userPlan->set('tags', $tags);

        // plan fields
        $userPlan->set('template', 0);
        $userPlan->set('rootgroupplan', $rootGroupPlan->get('id'));

        $userPlan->commit();

        return $userPlan;
    }

    /**
     * @param ArtefactTypeTask $rootGroupTask
     * @param ArtefactTypePlan $userPlan
     * @return ArtefactTypeTask
     * @throws \ArtefactNotFoundException
     * @throws \SQLException
     * @throws \SystemException
     */
    public static function createUserTaskFromGroupTask(ArtefactTypeTask $rootGroupTask, ArtefactTypePlan $userPlan) {
        global $USER;

        $userTask = new ArtefactTypeTask();

        // artefact fields
        $userTask->set('owner', $USER->get('id'));
        $userTask->set('parent', $userPlan->get('id'));
        $userTask->set('title', $rootGroupTask->get('title'));
        $userTask->set('description', $rootGroupTask->get('description'));

        $userTask->set('license', $rootGroupTask->get('license'));
        $userTask->set('licensor', $rootGroupTask->get('licensor'));
        $userTask->set('licensorurl', $rootGroupTask->get('licensorurl'));

        $tags = $rootGroupTask->get('tags');
        $userTask->set('tags', $tags);

        // task fields
        $userTask->set('completed', 0);
        $userTask->set('completiondate', $rootGroupTask->get('completiondate'));
        $userTask->set('startdate', $rootGroupTask->get('startdate'));
        $userTask->set('reminder', $rootGroupTask->get('reminder'));
        $userTask->set('taskview', $rootGroupTask->get('taskview'));
        $userTask->set('template', 0);
        $userTask->set('rootgrouptask', $rootGroupTask->get('id'));

        if ($rootGroupTask->get('outcome')) {
            $artefactCopies = [];
            switch ($rootGroupTask->get('outcometype')) {
                case 'view':
                    require_once(get_config('libroot').'view.php');
                    $userTaskOutcome = \View::create_from_template(['owner' => $USER->get('id')], $rootGroupTask->get('outcome'), null, false, true, $artefactCopies)[0];
                    break;
                case 'collection':
                    require_once(get_config('libroot').'collection.php');
                    $userTaskOutcome = \Collection::create_from_template(['owner' => $USER->get('id')], $rootGroupTask->get('outcome'), null, false, true)[0];
                    break;
            }

            if (isset($userTaskOutcome)) {
                $userTask->set('outcometype', $rootGroupTask->get('outcometype'));
                $userTask->set('outcome', $userTaskOutcome->get('id'));
            }
        }

        $userTask->commit();

        return $userTask;
    }

    /**
     * @param ArtefactTypeTask $userTaskTemplate
     * @param ArtefactTypePlan $targetGroupPlan
     * @param \stdClass $group
     * @param \Collection|null $targetCollection
     * @return ArtefactTypeTask
     * @throws \ArtefactNotFoundException
     * @throws \SQLException
     * @throws \SystemException
     */
    public static function createGroupTaskFromUserTaskTemplate(ArtefactTypeTask $userTaskTemplate, ArtefactTypePlan $targetGroupPlan, \stdClass $group, \Collection $targetCollection = null) {

        $groupTask = new ArtefactTypeTask();

        // artefact fields
        $groupTask->set('group', $targetGroupPlan->get('group'));
        $groupTask->set('parent', $targetGroupPlan->get('id'));
        $groupTask->set('title', $userTaskTemplate->get('title'));
        $groupTask->set('description', $userTaskTemplate->get('description'));

        $groupTask->set('license', $userTaskTemplate->get('license'));
        $groupTask->set('licensor', $userTaskTemplate->get('licensor'));
        $groupTask->set('licensorurl', $userTaskTemplate->get('licensorurl'));

        $groupTask->set('tags', $userTaskTemplate->get('tags'));
        // task fields
        $groupTask->set('completed', 0);
        if ($group->editwindowstart) {
            $groupTask->set('startdate', $group->editwindowstart);
        }
        if ($group->editwindowend) {
            $groupTask->set('completiondate', $group->editwindowend);
            $groupTask->set('reminder', $userTaskTemplate->get('reminder'));
        }
        // Create GroupTaskView from task assigned UserTaskView. If (TaskView-)collection is given, assign view to it
        if ($userTaskTemplate->get('taskview')) {
            try {
                require_once(get_config('libroot').'view.php');
                $userTaskViewTemplate = new \View($userTaskTemplate->get('taskview'));
                $newViewTitle = self::createUniqueStringForDBField(
                    'view',
                    'title',
                    $userTaskViewTemplate->get('title'),
                    'group',
                    $group->id,
                    null,
                    null
                );

                $artefactCopies = [];
                $targetTaskView = View::create_from_template(['group' => $group->id, 'title' => $newViewTitle], $userTaskTemplate->get('taskview'), null, false, false, $artefactCopies)[0];
                if ($targetTaskView) {
                    if ($targetCollection) {
                        $targetCollection->add_views(['view_' . $targetTaskView->get('id') => true]);
                    }
                    $groupTask->set('taskview', $targetTaskView->get('id'));
                }
            }
            catch (\Exception $e) {
            }
        }

        // Create GroupView or GroupCollection from task assigned UserView or UserCollection.
        if ($userTaskTemplate->get('outcome')) {
            try {
                $artefactCopies = [];
                switch ($userTaskTemplate->get('outcometype')) {
                    case 'view':
                        require_once(get_config('libroot').'view.php');
                        $userTaskOutcomeViewTemplate = new \View($userTaskTemplate->get('outcome'));
                        $newViewTitle = self::createUniqueStringForDBField(
                            'view',
                            'title',
                            $userTaskOutcomeViewTemplate->get('title'),
                            'group',
                            $group->id,
                            null,
                            null
                        );
                        // Unique name check is handled here for this situation and not global for all views at creation time
                        $targetTaskOutcome = \View::create_from_template(['group' => $group->id, 'title' => $newViewTitle], $userTaskTemplate->get('outcome'), null, false, false, $artefactCopies)[0];
                        break;
                    case 'collection':
                        require_once(get_config('libroot').'collection.php');
                        $userTaskOutcomeCollectionTemplate = new \Collection($userTaskTemplate->get('outcome'));

                        if (!empty($userTaskOutcomeCollectionTemplate->get_viewids())) {
                            // Unique name check is handled at create_collection_event
                            $targetTaskOutcome = \Collection::create_from_template(['group' => $group->id], $userTaskTemplate->get('outcome'), null, false, false)[0];
                        }
                        break;
                }
            }
            catch (\Exception $e) {
            }

            if (isset($targetTaskOutcome)) {
                $groupTask->set('outcometype', $userTaskTemplate->get('outcometype'));
                $groupTask->set('outcome', $targetTaskOutcome->get('id'));
            }
        }

        $groupTask->set('template', 0);
        $groupTask->set('roottemplatetask', $userTaskTemplate->get('id'));
        $groupTask->set('rootgrouptask', null);

        $groupTask->commit();

        return $groupTask;
    }

    /**
     * @param ArtefactTypePlan $targetGroupPlan
     * @param int $sourceUserPlanTemplateId
     * @return bool
     * @throws \ArtefactNotFoundException
     * @throws \CollectionNotFoundException
     * @throws \SQLException
     * @throws \SystemException
     */
    public static function createGroupPlanTasksFromUserPlanTemplate(ArtefactTypePlan $targetGroupPlan, $sourceUserPlanTemplateId) {

        $sql = 'SELECT id FROM {artefact} WHERE artefacttype = ? AND parent = ?'; // artefacttype in WHERE should be unnecessary
        $sourceUserTaskTemplateArray = get_records_sql_array($sql, ['task', $sourceUserPlanTemplateId]);

        if ($sourceUserTaskTemplateArray) {
            $group = get_group_by_id((int)$targetGroupPlan->get('group'));
            $targetTaskViewCollection = null;

            foreach ($sourceUserTaskTemplateArray as $sourceUserTaskTemplate) {
                $sourceUserTask = new ArtefactTypeTask($sourceUserTaskTemplate->id);

                if ($sourceUserTask->get('taskview') && is_null($targetTaskViewCollection)) {
                    require_once(get_config('libroot').'collection.php');

                    $targetTaskViewCollection = new \Collection(null,
                                                                    [
                                                                        'group' => $targetGroupPlan->get('group'),
                                                                        'name' => get_string('targetgroupplancollectiontitleprefix', 'artefact.plans') . $targetGroupPlan->get('title'),
                                                                        'description' => get_string('taskviewsfortemplateplan', 'artefact.plans', $targetGroupPlan->get('title')),
                                                                        'navigation' => 1,
                                                                        'submittedstatus' => 0,
                                                                        'progresscompletion' => 0,
                                                                        'lock' => 0,
                                                                        'autocopytemplate' => 0,
                                                                        'template' => 0,
                                                                    ]
                                                                );
                    $targetTaskViewCollection->commit();
                }

                $groupTask = self::createGroupTaskFromUserTaskTemplate($sourceUserTask, $targetGroupPlan, $group, $targetTaskViewCollection);
            }
        }

        return true;
    }

    /**
     * @param int $rootGroupTaskId
     * @return ArtefactTypeTask|bool
     * @throws \ArtefactNotFoundException
     * @throws \SQLException
     * @throws \SystemException
     */
    public static function findCorrespondingUserTaskByRootGroupTaskId($rootGroupTaskId) {
        global $USER;

        $sql = 'SELECT id FROM {artefact} AS a
                INNER JOIN {artefact_plans_task} AS p ON a.id = p.artefact
                WHERE a.owner = ? AND p.rootgrouptask = ?';
        $result = get_record_sql($sql, [$USER->get('id'), $rootGroupTaskId]);

        if ($result) {
            return new ArtefactTypeTask($result->id);
        }
        return false;
    }

    /**
     * @param int $groupTaskId
     * @return bool
     * @throws \SQLException
     */
    public static function groupTaskHasCorrespondingUserTask($groupTaskId) {
        global $USER;

        $sql = 'SELECT * FROM {artefact_plans_task} AS p
                INNER JOIN {artefact} AS a ON p.artefact = a.id
                WHERE p.rootgrouptask = ? AND a.owner = ?';

        return record_exists_sql($sql, [$groupTaskId, $USER->get('id')]);
    }

    /**
     * @param int $rootGroupPlanId
     * @return ArtefactTypePlan|bool
     * @throws \ArtefactNotFoundException
     * @throws \SQLException
     * @throws \SystemException
     */
    public static function findCorrespondingUserPlanByRootGroupPlanId($rootGroupPlanId) {
        global $USER;

        $sql = 'SELECT id FROM {artefact} AS a
                INNER JOIN {artefact_plans_plan} AS p ON a.id = p.artefact
                WHERE a.owner = ? AND p.rootgroupplan = ?';
        $result = get_record_sql($sql, [$USER->get('id'), $rootGroupPlanId]);

        if ($result) {
            return new ArtefactTypePlan($result->id);
        }
        return false;
    }

    /**
     * @return array|false
     * @throws \SQLException
     */
    public static function getIdTitleArrayOfUserPlanTemplates() {
        global $USER;

        $sql = 'SELECT id, title FROM {artefact} AS a
                INNER JOIN {artefact_plans_plan} AS p ON a.id = p.artefact
                WHERE a.owner = ? AND p.template = ?';
        $result = get_records_sql_array($sql, [$USER->get('id'), 1]);

        return $result;
    }

    /**
     * @param ArtefactTypePlan $plan
     * @return array
     */
    public static function getArrayOfUserPlanTemplateForForm(ArtefactTypePlan $plan) {

        $formFieldArray = ['title' => $plan->get('title'),
            'description' => $plan->get('description'),
            'template' => false,
            'rootgroupplan' => $plan->get('rootgroupplan'),
            'selectionplan' => (int)$plan->get('selectionplan') === 1,
            'tags' => $plan->get('tags')
        ];

        return $formFieldArray;
    }

    /**
     * @param \User $user
     * @param int $taskId
     * @param \stdClass|null $group
     * @return array of \stdClass
     * @throws \SQLException
     */
    public static function findViewsByGroupOrOwnerAsIdNameArrayForTaskViewSelection(\User $user, $taskId, \stdClass $group = null) {
        if (is_null($group)) {
            $searchField = 'owner';
            $searchValue = $user->get('id');
        }
        else {
            $searchField = 'group';
            $searchValue = $group->id;
        }

        $pgBooleanConversion = (is_postgres() ? '::int' : '');

        // Select all views, which are:
        // - Owned by owner (group or user)
        // - Currently not submitted
        // - Not a mahara default view
        // - Not already assigned as a taskview (tv) or view is the selected taskview of this task
        // - Not assigned as an outcome view (tov)
        // - Not indirectly assigned as an outcome as part of a collection which is assigned as outcome (tc)
        // plus the currently selected taskview
        $sql = "SELECT v.id, v.title, c.name, (tv.artefact = ?)" . $pgBooleanConversion . " AS selected
                FROM {view} AS v
                LEFT JOIN {artefact_plans_task} AS tv ON tv.taskview = v.id
                LEFT JOIN {artefact_plans_task} AS tov ON tov.outcome = v.id AND tov.outcometype = 'view'
                LEFT JOIN ({collection_view} AS cv
                    INNER JOIN {collection} AS c ON cv.collection = c.id
                    LEFT JOIN {artefact_plans_task} AS tc ON tc.outcome = c.id AND tc.outcometype = 'collection')
                ON v.id = cv.view
                WHERE v." . $searchField . " = ?
                AND v.submittedstatus = 0 AND v.type NOT IN ('profile', 'dashboard', 'grouphomepage', 'progress')
                AND (tv.taskview IS NULL OR tv.artefact = ?)
                AND tov.outcome IS NULL AND tc.outcome IS NULL
                ORDER BY c.name, v.title";
        $views = get_records_sql_array($sql, [$taskId, $searchValue, $taskId]);

        if ($views === false) {
            return [];
        }
        return $views;
    }

    /**
     * This function fills the options of a Pieform selection element with view data for the edit task form
     *
     * @param array $viewSelectElement
     * @param \stdClass|null $group
     * @throws \SQLException
     */
    public static function fillTaskViewSelectElement(array &$viewSelectElement, $taskId, \stdClass $group = null) {
        global $USER;

        if ($group) {
            $searchField = 'group';
            $searchValue = $group->id;
        }
        else {
            $searchField = 'owner';
            $searchValue = $USER->get('id');
        }

        $views = self::findViewsByGroupOrOwnerAsIdNameArrayForTaskViewSelection($USER, $taskId, $group);
        foreach ($views as $view) {
            $collection = ($view->name ? ' [' . $view->name . ']' : null);
            $viewSelectElement['options'][$view->id] = $view->title . $collection;

            if ($view->selected) {
                $viewSelectElement['defaultvalue'] = $view->id;
            }
        }
    }

    /**
     * @param \USER $user
     * @param \stdClass|null $group
     * @return array of \stdClass
     * @throws \SQLException
     */
    public static function findViewsByGroupOrOwnerAsIdNameArrayForTaskOutcomeSelection(\User $user, $taskId, \stdClass $group = null) {
        if (is_null($group)) {
            $searchField = 'owner';
            $searchValue = $user->get('id');
        }
        else {
            $searchField = 'group';
            $searchValue = $group->id;
        }

        $pgBooleanConversion = (is_postgres() ? '::int' : '');

        // Select all views which:
        // Have the owner
        // are not currently submitted
        // are no default page
        // are not in a collection (cv)
        // are not assigned as taskview to a task (tv)
        // are not assigned as outcome to a task (tov)
        // or view is already assigned as outcome to this task
        $sql = "SELECT v.id, v.title, (tov.artefact IS NOT NULL)" . $pgBooleanConversion . " AS selected
                FROM {view} AS v
                LEFT JOIN {artefact_plans_task} AS tv ON tv.taskview = v.id
                LEFT JOIN {artefact_plans_task} AS tov ON tov.outcome = v.id AND tov.outcometype = 'view'
                LEFT JOIN {collection_view} AS cv ON cv.view = v.id
                WHERE v." . $searchField . " = ? AND v.submittedstatus = 0
                AND v.type NOT IN ('profile', 'dashboard', 'grouphomepage')
                AND cv.view IS NULL AND tv.taskview IS NULL AND tov.outcome IS NULL
                OR tov.artefact = ?
                ORDER BY v.title";
        $views = get_records_sql_array($sql, [$searchValue, $taskId]);

        if ($views === false) {
            return [];
        }
        return $views;
    }

    /**
     * @param \USER $user
     * @param \stdClass|null $group
     * @return array of \stdClass
     * @throws \SQLException
     */
    public static function findCollectionsByGroupOrOwnerAsIdTitleArrayForTaskOutcomeSelection(\USER $user, $taskId, \stdClass $group = null) {
        if (is_null($group)) {
            $searchField = 'owner';
            $searchValue = $user->get('id');
        }
        else {
            $searchField = 'group';
            $searchValue = $group->id;
        }

        $pgBooleanConversion = (is_postgres() ? '::int' : '');

        // Select all collections which:
        // Have the owner
        // Are not currently submitted
        // Are not assigned as outcome to a task (t)
        // Don't contain a view which is assigned as taskview to a task (tv)
        // Or collection is already assigned to this task
        $sql = "SELECT c.id, c.name AS title, (t.artefact IS NOT NULL)" . $pgBooleanConversion . " AS selected
                FROM {collection} AS c
                LEFT JOIN {artefact_plans_task} AS t ON (t.outcome = c.id AND t.outcometype = 'collection')
                LEFT JOIN ({collection_view} AS cv
                    INNER JOIN {view} AS v ON v.id = cv.view
                    INNER JOIN {artefact_plans_task} AS tv ON tv.taskview = v.id)
                ON cv.collection = c.id
                WHERE c." . $searchField . " = ? AND c.submittedstatus = 0
                AND t.outcome IS NULL AND tv.taskview IS NULL
                OR t.artefact = ? ORDER BY c.name";
        $collections = get_records_sql_array($sql, [$searchValue, $taskId]);

        if ($collections === false) {
            return [];
        }
        return $collections;
    }

    /**
     * @param string $selectionElementValue
     * @return array
     */
    public static function getOutcomePortfolioTypeAndIdFromTaskOutcomeSelection($selectionElementValue) {
        $portfolioType = ['v' => 'view', 'c' => 'collection'];

        if (empty($selectionElementValue)) {
            return [null, null];
        }
        list($portfolioTypeIndicator, $portfolioId) = explode(':', $selectionElementValue, 2);
        return [$portfolioType[$portfolioTypeIndicator], $portfolioId];
    }

    /**
     * @param array $portfolioSelectElement
     * @param int $taskId
     * @param \stdClass|null $group
     * @throws \SQLException
     */
    public static function fillOutcomePortfolioSelectElementForTask(array &$portfolioSelectElement, $taskId, \stdClass $group = null) {
        global $USER;

        $views = self::findViewsByGroupOrOwnerAsIdNameArrayForTaskOutcomeSelection($USER, $taskId, $group);
        $collections = self::findCollectionsByGroupOrOwnerAsIdTitleArrayForTaskOutcomeSelection($USER, $taskId, $group);

        $viewOptions = $collectionOptions = [];

        foreach ($views as $view) {
            $viewOptions['v:' . $view->id] = $view->title;
            if ($view->selected) {
                $portfolioSelectElement['defaultvalue'] = 'v:' . $view->id;
            }
        }

        foreach ($collections as $collection) {
            $collectionOptions['c:' . $collection->id] = $collection->title;
            if ($collection->selected) {
                $portfolioSelectElement['defaultvalue'] = 'c:' . $collection->id;
            }
        }

        $optGroups = $portfolioSelectElement['optgroups'];

        if (!empty($viewOptions)) {
            $optGroups['views'] = [
                'label' => get_string('Views', 'view'),
                'options' => $viewOptions
            ];
        }

        if (!empty($collectionOptions)) {
            $optGroups['collections'] = [
                'label' => get_string('Collections', 'collection'),
                'options' => $collectionOptions
            ];
        }

        $portfolioSelectElement['optgroups'] = $optGroups;
    }

    /**
     * @param \View|\Collection $portfolioElement
     * @return ArtefactTypeTask|false
     * @throws \ArtefactNotFoundException
     * @throws \SQLException
     * @throws \SystemException
     */
    public static function findCorrespondingGroupTaskByPortfolioElement($portfolioElement) {

        $portfolioElementType = strtolower(get_class($portfolioElement));

        $sql = 'SELECT gt.artefact AS id FROM {artefact_plans_task} AS pt
                    INNER JOIN {artefact_plans_task} gt ON gt.artefact = pt.rootgrouptask
                    WHERE pt.outcometype = ? AND pt.outcome = ?';

        $result = get_record_sql($sql, [$portfolioElementType, $portfolioElement->get('id')]);

        if ($result) {
            return new ArtefactTypeTask($result->id);
        }
        return false;
    }

    /**
     * @param int $viewId
     * @return bool
     * @throws \SQLException
     */
    public static function removeTaskAssignmentsToViewByViewId($viewId) {
        $sql = 'UPDATE {artefact_plans_task} SET taskview=NULL WHERE taskview = ?';

        return execute_sql($sql, [$viewId]);
    }

    /**
     * @param int $rootGroupTaskId
     * @return bool
     * @throws \SQLException
     */
    public static function removeTaskAssignmentsDependingOnRootGroupTaskByRootGroupTaskId($rootGroupTaskId) {
        $sql = 'UPDATE {artefact_plans_task} SET rootgrouptask = NULL, taskview = NULL WHERE rootgrouptask = ?';
         return execute_sql($sql, [$rootGroupTaskId]);
    }

    /**
     * @param int $rootTemplateTaskId
     * @return bool
     * @throws \SQLException
     */
    public static function removeTaskAssignmentsDependingOnRootTemplateTaskByRootTemplateTaskId($rootTemplateTaskId) {
        $sql = 'UPDATE {artefact_plans_task} SET roottemplatetask = 0 WHERE roottemplatetask = ?';
        return execute_sql($sql, [$rootTemplateTaskId]);
    }

    /**
     * @param int $rootGroupPlanId
     * @return bool
     * @throws \SQLException
     */
    public static function removePlanAssignmentsDependingOnRootGroupPlanByRootGroupPlanId($rootGroupPlanId) {
        $sql = 'UPDATE {artefact_plans_plan} SET rootgroupplan = NULL WHERE rootgroupplan = ?';
        return execute_sql($sql, [$rootGroupPlanId]);
    }

    /**
     * @param int $rootTemplatePlanId
     * @return bool
     * @throws \SQLException
     */
    public static function removePlanAssignmentsDependingOnRootTemplatePlanByRootTemplatePlanId($rootTemplatePlanId) {
        $sql = 'UPDATE {artefact_plans_plan} SET roottemplate = 0 WHERE roottemplate = ?';
        return execute_sql($sql, [$rootTemplatePlanId]);
    }

    /**
     * @param View $view
     */
    public static function deleteViewAndAssignedArtefacts(\View $view) {
        $instances = $view->get_artefact_instances();

        // Delete artefacts with no parent and collect parents
        $parentIdChildInstancesArray = [];

        /** @var \ArtefactType $instance */
        foreach ($instances as $instance) {
            $parentId = $instance->get('parent');
            if (!empty($parentId)) {
                $parentIdChildInstancesArray[$parentId][] = $instance;
            }
            else {
                $instance->delete();
            }
        }

        // Delete artefacts with parent by deleting parent
        foreach ($parentIdChildInstancesArray as $parentId => $childInstances) {
            try {
                $sql = 'SELECT a.*, i.name, i.plugin FROM {artefact} AS a
                        INNER JOIN {artefact_installed_type} AS i ON i.name = a.artefacttype
                        WHERE a.id = ?';
                $parentRecord = get_record_sql($sql, [$parentId], 0);
                if ($parentRecord) {
                    safe_require('artefact', $parentRecord->plugin);
                    $classname = generate_artefact_class_name($parentRecord->artefacttype);

                    /** @var \ArtefactType $parentInstance */
                    $parentInstance = new $classname($parentRecord->id, $parentRecord);
                    $parentInstance->delete();
                }
                else {
                    // parent already gone away - can happen if loop deletes folder before folder item
                }
            }
            // If for some reasons the deletion of the parent fails, make sure that at least it's children are deleted
            catch (\Exception $e) {
                /** @var \ArtefactType $childInstance */
                foreach ($childInstances as $childInstance) {
                    $childInstance->delete();
                }
            }
        }

        $view->delete();
    }

    /**
     * @param string $name
     * @return int|null
     * @throws \ParameterException
     */
    public static function param_integer_or_null($name) {
        if (param_exists($name) && empty(param_variable($name))) {
            return null;
        }
        return param_integer($name);
    }

    /**
     * @param string $name
     * @return int|null
     * @throws \ParameterException
     */
    public static function param_missing_or_integer_or_null($name) {
        if (param_exists($name) && !empty(param_variable($name))) {
            return param_integer($name);
        }
        return null;
    }

    /**
     * @param int $collectionId
     * @return bool
     * @throws \SQLException
     */
    public static function collectionIsAssignedAsOutcomeToSelectionGroupTask($collectionId) {

        $sql = "SELECT * FROM {artefact_plans_task} AS t
                INNER JOIN {artefact} AS a ON a.id = t.artefact
                INNER JOIN {artefact_plans_plan} AS p ON p.artefact = a.parent
                WHERE a.group IS NOT NULL AND a.owner IS NULL
                AND t.outcome = ? AND t.outcometype = 'collection'
                AND p.selectionplan = 1 AND p.template = 0";

        $result = get_records_sql_array($sql, [$collectionId]);

        return !empty($result);
    }

    /**
     * @param string $table
     * @param string $field
     * @param string $baseStringToCheck
     * @param null|string $condField1
     * @param null|mixed $condValue1
     * @param null|string $condField2
     * @param null|mixed $condValue2
     * @param null|string $exceptCondField
     * @param null|mixed $exceptCondValue
     * @return string
     * @throws \SQLException
     */
    public static function createUniqueStringForDBField($table,
                                                        $field,
                                                        $baseStringToCheck,
                                                        $condField1 = null,
                                                        $condValue1 = null,
                                                        $condField2 = null,
                                                        $condValue2 = null,
                                                        $exceptCondField = null,
                                                        $exceptCondValue = null) {
        $extText = get_string('version.', 'mahara');

        $whereClause = where_clause($condField1, $condValue1, $condField2, $condValue2);
        $whereClause .= empty($whereClause) ? "WHERE " . db_quote_identifier($field) . " LIKE ?" : " AND " . db_quote_identifier($field) . " LIKE ?";

        $whereValues = [$baseStringToCheck . '%'];
        if (!is_null($exceptCondValue) && !is_null($exceptCondField)) {
            $whereClause .= " AND " . db_quote_identifier($exceptCondField) . " != ?";
            array_push($whereValues, $exceptCondValue);
        }

        $taken = get_column_sql("SELECT " . db_quote_identifier($field) . " FROM " . db_table_name($table) . " " . $whereClause, $whereValues);

        $i = 1;
        $stringToCheck = $baseStringToCheck;

        while (in_array($stringToCheck, $taken)) {
                $stringToCheck = $baseStringToCheck . ' ' . $extText . ++$i;
        }
        return $stringToCheck;
    }

    /**
     * @param mixed $object
     * @return array
     * @throws \MaharaException
     */
    public static function getOwnerTypeAndOwnerIdFromMaharaObject($object) {
        switch (true) {
            case !empty($object->get('institution')):
                return ['institution', $object->get('institution')];
            case !empty($object->get('group')):
                return ['group', $object->get('group')];
            case !empty($object->get('owner')):
                return ['owner', $object->get('owner')];
            default:
                throw new \MaharaException(get_string('ownerfieldsnotset','artefact.plans'));
        }
    }

    /**
     * When a page is copied as part of the copying of a plan task, it may take a few seconds to complete the copying process
     * especially when the page contains a lot of artefacts. The within 5 second check here is to allow for a slight margin
     * between page's 'ctime' and 'mtime' values without regarding the page as having been modified.
     * Because once a page has been regarded as modified, different deletion rules apply to the deletion of artefacts.
     *
     * @param View $view
     * @return bool
     * @throws \Exception
     */
    public static function viewHasNotBeenModifiedByUserSinceCreation(\View $view) {
        $cTime = new \DateTime($view->get('ctime'));
        $mTime = new \DateTime($view->get('mtime'));
        $diff = $mTime->getTimestamp() - $cTime->getTimestamp();
        return $diff < 5;
    }

    /**
     * Checks if the current user can edit the view, the view is currently not submitted and it has not been edited since creation
     * (So it has no content and if yes, it's an automatically created (group) view assigned to an imported group task
     * from a user template task or a user task chosen and so copied from a selection group task or a copy of a view and
     * so the original content is still available to the user and so the user won't loose his work)
     *
     * @param View $view
     * @return bool
     * @throws \Exception
     */
    public static function viewIsAutomaticallyDeletable(\View $view) {
        global $USER;

        return $USER->can_edit_view($view) && !$view->is_submitted() && self::viewHasNotBeenModifiedByUserSinceCreation($view);
    }


    /**
     * @param \View|\Collection $portfolioElement
     * @return string
     * @throws \MaharaException
     */
    public static function createOutcomeUrlForPortfolioElement($portfolioElement) {
        $portfolioElementType = strtolower(get_class($portfolioElement));

        switch ($portfolioElementType) {
            case 'view':
                return $portfolioElement->get_url(true, true);
            case 'collection':
                // To circumvent the log_warn in the get_url() for an empty collection
                if (empty($portfolioElement->views())) {
                    return get_config('wwwroot') . 'collection/views.php?id=' . $portfolioElement->get('id');
                }
                else {
                    return $portfolioElement->get_url(true, true);
                }
            default:
                throw new \MaharaException(get_string('unsupportedportfoliotype','artefact.plans'));
        }
    }

    /**
     * Creates a redirection URL for the submit_yes_no form.
     * If a planId is given, this planId is used instead of the task's parentPlanId for correct redirection after
     * submitting a grouptask outcome
     *
     * @param ArtefactTypeTask $task
     * @return string|null
     */
    public static function createSubmissionUrlForCompletedTaskWithOutcome(\ArtefactTypeTask $task, $planId = null) {
        if (is_null($planId)) {
            $planId = $task->get('parent');
        }
        $groupId = null;
        $rootGroupTaskId = $task->get('rootgrouptask');
        if (!empty($rootGroupTaskId)) {
            try {
                $rootGroupTask = new ArtefactTypeTask($rootGroupTaskId);
                $groupId = $rootGroupTask->get('group');
            }
            catch (\Exception $e) {
                // Try Catch only to be sure that the code continues
            }
        }
        else if ($task->is_grouptask()) {
            return null;
        }

        // If it's assigned to a group being a selected group task, we have all we need for a submission
        // otherwise we redirect the user to the portfolio element to choose a group for submission
        $portfolioElement = self::getPortfolioElementByTypeAndId($task->get('outcometype'), $task->get('outcome'));

        if (empty($portfolioElement) || $portfolioElement->is_submitted()) {
            return null;
        }

        if (!empty($groupId)) {
            $urlQuery = [
                            'portfoliotype' => $task->get('outcometype'),
                            'portfolio' => $task->get('outcome'),
                            'group' => $groupId,
                            'returntoplan' => $planId
                        ];
            return get_config('wwwroot') . 'artefact/plans/view/submit.php?' . http_build_query($urlQuery);
        }
        else {
            return $portfolioElement->get_url();
        }
    }

    /**
     * @param \View|\Collection $portfolioElement
     * @return string
     * @throws \MaharaException
     */
    public static function getPortfolioElementTitle($portfolioElement) {
        switch (get_class($portfolioElement)) {
            case 'View':
                return $portfolioElement->get('title');
                break;
            case 'Collection':
                return $portfolioElement->get('name');
                break;
            default:
                throw new \MaharaException(get_string('unsupportedportfoliotype','artefact.plans'));
        }
    }

    /**
     * @param string $type
     * @param int $id
     * @return \Collection|null|\View
     */
    public static function getPortfolioElementByTypeAndId($type, $id) {
        try {
            /** @var \View|\Collection|null $portfolioElement */
            switch ($type) {
                case 'view':
                    require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/lib/view.php');
                    return new \View($id);
                case 'collection':
                    require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/lib/collection.php');
                    return new \Collection($id);
            }
        }
        catch (\Exception $e) {
        }
        return null;
    }
}
