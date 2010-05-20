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
        );
    }

    public static function get_block_types() {
    }

    public static function get_plugin_name() {
        return 'plans';
    }

    public static function menu_items() {
        return array(
            array(
                'path' => 'profile/plans',
                'url' => 'artefact/plans/',
                'title' => get_string('plans', 'artefact.plans'),
                'weight' => 40,
            ),
        );
    }
}

class ArtefactTypePlans extends ArtefactType {

    protected $title;
    protected $description;
    protected $completiondate;
    protected $completed;

    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if ($artefact = get_record('artefact_plans_plan', 'artefact', $id)) {
            foreach ($fields = $this->get_plan_fields() as $field) {
              $this->$field = $artefact->$field;
            }
        }
    }

    public static function is_singular() {
        return true;
    }

    public static function get_links($id) {
        // @todo Catalyst IT Ltd
    }

    public static function get_icon($options=null) {
        // @todo Catalyst IT Ltd
    }

    public function get_plan_fields() {
        return array(
            'title',
            'description',
            'completiondate',
            'completed',
        );
    }

    public function commit() {

        // Return whether or not the commit worked
        $success = false;

        // Just forget the whole thing when we're clean.
        if (empty($this->dirty)) {
            return true;
        }

        // We need to keep track of newness before and after.
        $new = empty($this->id);

        parent::commit();

        // Reset dirtyness for the time being.
        $this->dirty = true;

        $data = (object)array(
            'artefact'       => $this->get('id'),
            'title'          => $this->get('title'),
            'description'    => $this->get('description'),
            'completiondate' => $this->get('completiondate'),
            'completed'      => $this->get('completed'),
        );

        if ($new) {
            $success = insert_record('artefact_plans_plan', $data);
        }
        else {
            $success = update_record('artefact_plans_plan', $data, 'artefact');
        }

        $this->dirty = false;

        return $success;
    }

    // users existing plans (used in displaying list)
    static function get_plans() {
        global $USER;

        $records = array();
        $owner = $USER->get('id');

        $sql = "SELECT ar.artefact, ar.id, ar.completiondate, ar.completed, ar.title, ar.description, a.owner
            FROM {artefact} a
            JOIN {artefact_plans_plan} ar ON ar.artefact = a.id
            WHERE a.owner = ? AND a.artefacttype = 'plans'
            GROUP BY ar.artefact, ar.id, ar.completiondate, ar.completed, ar.title, ar.description, a.owner
            ORDER BY ar.completiondate ASC";

        $records = get_records_sql_array($sql, array($owner));

        return $records;
    }

    // get new plan form
    static function get_form() {
        require_once(get_config('libroot') . 'pieforms/pieform.php');
        $elements = call_static_method(generate_artefact_class_name('plans'), 'get_plansform_elements');
        $elements['submit'] = array(
            'type' => 'submit',
            'value' => get_string('save'),
        );
        $plansform = array(
            'name' => 'addplans',
            'plugintype' => 'artefact',
            'pluginname' => 'plans',
            'successcallback' => array(generate_artefact_class_name('plans'),'plansform_submit'),
            'elements' => $elements,
        );

        return pieform($plansform);
    }

    // new plan form elements
    static function get_plansform_elements() {
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
            )
        );
    }

    public function plansform_submit(Pieform $form, $values) {
        global $USER, $SESSION;

        $userid = $USER->get('id');

        if (empty($values['artefact'])) {
            $artefact = new ArtefactTypePlans(0, array(
                'owner' => $userid,
                'title' => $values['title'],
                'description' => !empty($values['description']) ? $values['description'] : '',
                'completiondate' => $values['completiondate'],
                'completed' => $values['completed'] ? $values['completed'] : 0,
            ));

        }
        else if (!empty($values['artefact'])) {
            if (!$data = get_record('artefact','id',$values['artefact'])) {
                throw new ArtefactNotFoundException(get_string('artefactnotfound', 'error', $values['artefact']));
            }
            $artefact = new ArtefactTypePlans($data->id, $data);
            $artefact->set('title', $values['title']);
            $artefact->set('description', $values['description']);
            $artefact->set('completiondate', $values['completiondate']);
            if (!$values['completed']) {
                $artefact->set('completed', 0);
            }
            else {
                $artefact->set('completed', $values['completed']);
            }

        }

        if ($artefact->commit()) {
            $SESSION->add_ok_msg(get_string('tasksavedsuccessfully', 'artefact.plans'));
        }
        else {
            $SESSION->add_error_msg(get_string('tasknotsavedsuccessfully', 'artefact.plans'));
        }

        redirect('/artefact/plans/');
    }

    /**
    * Takes a pieform that's been set up by all the
    * subclass get_addform_elements functions
    * and puts the default values in (and hidden id field)
    * ready to be an edit form
    *
    * @param $form pieform structure (before calling pieform() on it
    * passed by _reference_
    */
    public static function populate_form(&$form, $id) {
        if (!$plan = get_record('artefact_plans_plan', 'id', $id)) {
            throw new InvalidArgumentException("Couldn't find plan with id $id");
        }
        foreach ($form['elements'] as $k => $element) {
            if ($k == 'submit') {
                continue;
            }
            if (isset($plan->{$k})) {
                $form['elements'][$k]['defaultvalue'] = $plan->{$k};
            }
        }
        $form['elements']['id'] = array(
            'type' => 'hidden',
            'value' => $id,
        );
        $form['elements']['artefact'] = array(
            'type' => 'hidden',
            'value' => $plan->artefact,
        );
    }

    public function delete() {
        db_begin();

        delete_records('artefact_plans_plan', 'artefact', $this->id);
        parent::delete();

        db_commit();
    }
}
