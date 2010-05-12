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

require_once('activity.php');

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
            $success = insert_record('artefact_plans_task', $data);
        }
        else {
            $success = update_record('artefact_plans_task', $data, 'artefact');
        }

        $this->dirty = false;

        return $success;
    }

}
