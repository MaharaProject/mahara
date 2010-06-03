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
            'plans',
            'plan',
        );
    }

    public static function get_block_types() {
        return array();
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

    public static function get_event_subscriptions() {
        return array(
            (object)array(
                'plugin'       => 'plans',
                'event'        => 'createuser',
                'callfunction' => 'create_default_plans',
            ),
        );
    }

    /**
     * Sets up the default Plans artefact for all plan artefacts to have as parent.
     * Each user can only have one of these and it is created at their account creation.
     */
    public static function create_default_plans($event, $user) {
        $name = display_name($user, null, true);
        $plans = new ArtefactTypePlans(0, (object) array(
            'title'       => get_string('myplans', 'artefact.plans'),
            'owner'       => $user['id'],
        ));
        $plans->commit();
    }
}

/**
 * A Plans artefact is a collection of Plan artefacts.
 */
class ArtefactTypePlans extends ArtefactType {

    /**
     * This constant gives the per-page pagination for listing plans.
     */
    const pagination = 10;

    /**
     * We override the constructor to fetch the extra data.
     *
     * @param integer
     * @param object
     */
    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);
    }

    public function render_self($options) {
        $this->add_to_render_path($options);

        // current time/date for formatting
        $datenow = time();

        $smarty = smarty_core();
        if (isset($options['viewid'])) {
            $smarty->assign('artefacttitle', '<a href="' . get_config('wwwroot') . 'view/artefact.php?artefact='
                                             . $this->get('id') . '&view=' . $options['viewid']
                                             . '">' . hsc($this->get('title')) . '</a>');
        }
        else {
            $smarty->assign('artefacttitle', hsc($this->get('title')));
        }

        $page = (isset($options['page'])) ? abs(intval($options['page'])) : abs(param_integer('page', 1));
        $offset = $page ? $page * self::pagination - self::pagination : 1;

        // Get list of the plans the user has in plans
        $plans = array();
        if ($records = get_records_sql_array('
            SELECT ar.*, a.title, a.description
                FROM {artefact} a
                JOIN {artefact_plan} ar ON ar.plan = a.id
            WHERE a.parent = ? AND a.artefacttype = \'plan\'
            ORDER BY ar.completiondate DESC
            LIMIT ? OFFSET ?', array($this->id, self::pagination, $offset))) {
            foreach ($records as $record) {
                $planid = $record->plan;
                $plans[$planid]->title = $record->title;
                $plans[$planid]->description = $record->description;
                if (!$record->completed && $record->completiondate < $datenow) {
                    $plans[$planid]->completed = -1;
                } else {
                    $plans[$planid]->completed = $record->completed;
                }
                $plans[$planid]->completiondate = strftime(get_string('strftimedate'), $record->completiondate);
            }

            $count = (int)get_field('artefact', 'COUNT(*)', 'artefacttype', 'plan','parent',$this->get('id'));

            // Pagination
            if ($count > self::pagination) {
                $baselink = get_config('wwwroot') . 'view/artefact.php?artefact=' . $this->get('id');
                if ($options['viewid']) {
                    $baselink .= '&view=' . $options['viewid'];
                }

                if ($offset + self::pagination < $count) {
                    $smarty->assign('olderplanslink',  $baselink . '&page=' . ($page + 1));
                }
                if ($offset > 0) {
                    $smarty->assign('newerplanslink',  $baselink . '&page=' . ($page - 1));
                }
            }

            $smarty->assign('plans', $plans);
        }

        return array('html' => $smarty->fetch('blocktype:plans:content.tpl'));
    }

    /**
     * This function updates or inserts the artefact.  This involves putting
     * some data in the artefact table (handled by parent::commit())
     */
    public function commit() {
        // Just forget the whole thing when we're clean.
        if (empty($this->dirty)) {
            return;
        }

        // We need to keep track of newness before and after.
        $new = empty($this->id);

        // Commit to the artefact table.
        parent::commit();

        $this->dirty = false;
    }

    // ToDo: add Plans icon
    public static function get_icon($options=null) {
    }

    public static function is_singular() {
        return true;
    }

    public static function get_links($id) {
        return array();
    }

    /**
     * This function returns a list of the given user's plans.
     *
     * @param limit how many plans to display per page
     * @param offset current page to display
     * @return array (count: integer, data: array)
     */
    public function get_plans_list($offset) {
        global $USER;
        $datenow = time(); // time now to use for formatting plans by completion

        if (!$plans = get_field('artefact','id','artefacttype','plans','owner',$USER->get('id'))) {
            return;
        }

        ($results = get_records_sql_array("
            SELECT ap.*, a.title, a.description
                FROM {artefact} a
            JOIN {artefact_plan} ap ON ap.plan = a.id
            WHERE a.owner = ? AND a.artefacttype = 'plan' AND a.parent = ?
            ORDER BY ap.completiondate DESC 
            LIMIT ? OFFSET ?", array($USER->get('id'), (int)$plans, self::pagination, $offset)))
            || ($results = array());

        // format the date and setup completed for display if plan is incomplete
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

        $count = (int)get_field('artefact', 'COUNT(*)', 'owner', $USER->get('id'), 'artefacttype', 'plan','parent',(int)$plans);

        return array($count, $results);
    }

    /**
     * Builds the plans list table
     *
     * @param plans (reference)
     */
    public function build_plans_list_html(&$plans) {
        $smarty = smarty_core();
        $smarty->assign_by_ref('plans', $plans);
        $plans->tablerows = $smarty->fetch('artefact:plans:planslist.tpl');
        $pagination = build_pagination(array(
            'id' => 'planslist_pagination',
            'class' => 'center',
            'url' => get_config('wwwroot') . 'artefact/plans/index.php',
            'datatable' => 'planslist',
            'count' => $plans->count,
            'limit' => self::pagination,
            'offset' => $plans->offset,
            'firsttext' => '',
            'previoustext' => '',
            'nexttext' => '',
            'lasttext' => '',
            'numbersincludefirstlast' => false,
            'resultcounttextsingular' => get_string('plan', 'artefact.plans'),
            'resultcounttextplural' => get_string('plans', 'artefact.plans'),
        ));
        $plans->pagination = $pagination['html'];
    }
}

/**
 * Plan artefacts occur within Plans artefacts
 */
class ArtefactTypePlan extends ArtefactType {

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
            if ($pdata = get_record('artefact_plan', 'plan', $this->id)) {
                foreach($pdata as $name => $value) {
                    if (property_exists($this, $name)) {
                        $this->$name = $value;
                    }
                }
            }
            else {
                // This should never happen unless the user is playing around with plan IDs in the location bar or similar
                throw new ArtefactNotFoundException(get_string('plandoesnotexist', 'artefact.plans'));
            }
        }
    }

    public static function get_links($id) {
        return array();
    }

    /**
     * This method extends ArtefactType::commit() by adding additional data
     * into the artefact_plan table.
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

        $data = (object)array(
            'plan'  => $this->get('id'),
            'completed' => $this->get('completed'),
            'completiondate' => $this->get('completiondate'),
        );

        if ($new) {
            $success = insert_record('artefact_plan', $data);
        }
        else {
            $success = update_record('artefact_plan', $data, 'plan');
        }

        db_commit();

        $this->dirty = $success ? false : true;

        return $success;
    }

    /**
     * This function extends ArtefactType::delete() by also deleting anything
     * that's in plan.
     */
    public function delete() {
        if (empty($this->id)) {
            return;
        }

        $return = false;

        db_begin();
        delete_records('artefact_plan', 'plan', $this->id);

        parent::delete();
        db_commit();
    }

    /**
     * Checks that the person viewing this plan is the owner. If not, throws an
     * AccessDeniedException. Used in the plans section to ensure only the
     * owners of the plans can view or change them there. Other people see
     * plans when they are placed in views.
     */
    public function check_permission() {
        global $USER;
        if ($USER->get('id') != $this->owner) {
            throw new AccessDeniedException(get_string('youarenottheownerofthisplan', 'artefact.plans'));
        }
    }

    // ToDo: add Plan icon ?
    public static function get_icon($options=null) {
    }

    public static function is_singular() {
        return false;
    }

    /**
     * This function creates a new plan.
     *
     * @param User
     * @param array
     */
    public function new_plan(User $user, array $values) {
        $artefact = new ArtefactTypePlan();
        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->set('completed', ($values['completed'] ? 1 : 0));
        $artefact->set('completiondate', $values['completiondate']);
        $artefact->set('owner', $user->get('id'));
        $artefact->set('parent', $values['parent']);
        $artefact->commit();
        return true;
    }

    /*
     * This function updates an existing plan.
     *
     * @param User
     * @param array
     */
    public function edit_plan(User $user, array $values) {
        $artefact = new ArtefactTypePlan($values['id']);
        if ($user->get('id') != $artefact->get('owner')) {
            return false;
        }

        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->set('completed', $values['completed']);
        $artefact->set('completiondate', $values['completiondate']);
        $artefact->commit();
        return true;
    }

    public static function submit(Pieform $form, $values) {
        global $USER, $SESSION;

        $success = false;
        if (empty($values['artefact']) ){
            $success = ArtefactTypePlan::new_plan($USER, $values);
        }
        else {
            $success = ArtefactTypePlan::edit_plan($USER, $values);
        }

        if ($success) {
            $SESSION->add_ok_msg(get_string('plansavedsuccessfully', 'artefact.plans'));
        } else {
            $SESSION->add_error_msg(get_string('plannotsavedsuccessfully', 'artefact.plans'));
        }

        redirect('/artefact/plans/');
    }

    /**
    * Gets the new/edit plans pieform
    *
    */
    public static function get_form() {
        require_once(get_config('libroot') . 'pieforms/pieform.php');
        $elements = call_static_method(generate_artefact_class_name('plan'), 'get_plansform_elements');
        $elements['submit'] = array(
            'type' => 'submitcancel',
            'value' => array(get_string('saveplan','artefact.plans'), get_string('cancel')),
            'goto' => get_config('wwwroot') . 'artefact/plans/',
        );
        $plansform = array(
            'name' => 'addplans',
            'plugintype' => 'artefact',
            'pluginname' => 'plans',
            'successcallback' => array(generate_artefact_class_name('plan'),'submit'),
            'elements' => $elements,
        );

        return pieform($plansform);
    }

    /**
    * Gets the new/edit fields for the plans pieform
    *
    */
    public static function get_plansform_elements() {
        global $USER;

        $parent = get_field('artefact','id','artefacttype','plans','owner',$USER->get('id'));

        return array(
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
            'completed' => array(
                'type' => 'checkbox',
                'defaultvalue' => 0,
                'title' => get_string('completed', 'artefact.plans'),
                'description' => get_string('completeddesc', 'artefact.plans'),
            ),
            'parent' => array(
                'type' => 'hidden',
                'value' => (int) $parent,
            ),
        );
    }

    /**
    * Takes a pieform that's been set up by all the
    * subclass get_plansform_elements functions
    * and puts the default values in (and hidden id field)
    * ready to be an edit form
    *
    * @param $form pieform structure (before calling pieform() on it
    * passed by _reference_
    */
    public static function populate_form(&$form, $a) {
        $plan = new ArtefactTypePlan($a->get('id'));
        foreach ($form['elements'] as $k => $element) {
            if ($k == 'submit') {
                continue;
            }
            if (isset($plan->{$k})) {
                $form['elements'][$k]['defaultvalue'] = $plan->{$k};
            }
        }
        $form['elements']['plan'] = array(
            'type' => 'hidden',
            'value' => $plan->id,
        );
    }
}

?>
