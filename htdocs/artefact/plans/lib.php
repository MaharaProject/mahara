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

}

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
     * into the plan table.
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

        db_begin();
        delete_records('artefact_plan', 'plan', $this->id);

        parent::delete();
        db_commit();
    }

    // ToDo: add Plan icon ?
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
        $datenow = time(); // time now to use for formatting plans by completion

        ($results = get_records_sql_array("
            SELECT ap.*, a.title, a.description
                FROM {artefact} a
            JOIN {artefact_plan} ap ON ap.plan = a.id
            WHERE a.owner = ? AND a.artefacttype = 'plan'
            ORDER BY ap.completiondate DESC
            LIMIT ? OFFSET ?", array($USER->get('id'), $limit, $offset)))
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

        $result = array(
            'count'  => count_records('artefact', 'owner', $USER->get('id'), 'artefacttype', 'plan'),
            'data'   => $results,
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
    public function build_plans_list_html(&$plans) {
        $smarty = smarty_core();
        $smarty->assign_by_ref('plans', $plans);
        $plans['tablerows'] = $smarty->fetch('artefact:plans:planslist.tpl');
        $pagination = build_pagination(array(
            'id' => 'planslist_pagination',
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

        if (!empty($values['plan'])) {
            $id = (int) $values['plan'];
            $artefact = new ArtefactTypePlan($id);
            $USER->can_edit_artefact($id);
        }
        else {
            $artefact = new ArtefactTypePlan();
            $artefact->set('owner', $USER->get('id'));
        }

        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->set('completed', $values['completed'] ? 1 : 0);
        $artefact->set('completiondate', $values['completiondate']);
        $artefact->commit();

        $SESSION->add_ok_msg(get_string('plansavedsuccessfully', 'artefact.plans'));

        redirect('/artefact/plans/');
    }

    /**
    * Gets the new/edit plans pieform
    *
    */
    public static function get_form($plan=null) {
        require_once(get_config('libroot') . 'pieforms/pieform.php');
        $elements = call_static_method(generate_artefact_class_name('plan'), 'get_plansform_elements', $plan);
        $elements['submit'] = array(
            'type' => 'submitcancel',
            'value' => array(get_string('saveplan','artefact.plans'), get_string('cancel')),
            'goto' => get_config('wwwroot') . 'artefact/plans/',
        );
        $plansform = array(
            'name' => empty($plan) ? 'addplans' : 'editplan',
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
    public static function get_plansform_elements($plan) {
        $elements = array(
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
                'defaultvalue' => null,
                'title' => get_string('completed', 'artefact.plans'),
                'description' => get_string('completeddesc', 'artefact.plans'),
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

?>
