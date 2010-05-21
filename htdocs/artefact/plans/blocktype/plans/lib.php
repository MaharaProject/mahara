<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @subpackage blocktype-plans
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypePlans extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.plans/plans');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.plans/plans');
    }

    public static function get_categories() {
        return array('general');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        safe_require('artefact','plans');

        $view = get_record('view','id', $instance->get('view'));
        $smarty = smarty_core();

        // Get data about the plans the user has
        $return = array();
        if ($records = get_records_sql_array('
            SELECT a.id, a.owner
                FROM {artefact} a
                JOIN {artefact_plans_plan} ar ON ar.artefact = a.id
            WHERE a.owner = ? AND a.artefacttype = \'plans\'
            ORDER BY ar.completiondate ASC    ', array($view->owner))) {
            foreach ($records as $record) {
                $artefact = new ArtefactTypePlans($record->id);
                $artefactid = $artefact->get('id');
                $return[$artefactid]->title = $artefact->get('title');
                $return[$artefactid]->description = $artefact->get('description');
                $return[$artefactid]->completiondate = strftime(get_string('strftimedate'), $artefact->get('completiondate'));
                $return[$artefactid]->completed = $artefact->get('completed');
            }

            $smarty->assign('rows', $return);
       }
        return $smarty->fetch('blocktype:plans:content.tpl');
    }

    // Yes, we do have instance config. People are allowed to specify the title
    // of the block, nothing else at this time. So in the next two methods we
    // say yes and return no fields, so the title will be configurable.
    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form() {
        return array();
    }

    public static function artefactchooser_element($default=null) {
    }

    /**
     * Subscribe to the blockinstancecommit event to make sure all artefacts 
     * that should be in the blockinstance are
     */
    public static function get_event_subscriptions() {
        return array(
            (object)array(
                'event'        => 'blockinstancecommit',
                'callfunction' => 'ensure_plans_artefacts_in_blockinstance',
            ),
        );
    }

    /**
     * Hook for making sure that all plans artefacts are associated with a
     * blockinstance at blockinstance commit time
     */
    public static function ensure_plans_artefacts_in_blockinstance($event, $blockinstance) {
        if ($blockinstance->get('blocktype') == 'plans') {
            safe_require('artefact', 'plans');

            // Get all artefacts that are plans and belong to the correct owner
            $artefacts = get_records_sql_array('
                SELECT id
                FROM {artefact}
                WHERE artefacttype = \'plans\'
                AND owner = (
                    SELECT owner
                    FROM {view}
                    WHERE id = ?
                )', array($blockinstance->get('view')));

            if ($artefacts) {
                // Make sure they're registered as being in this view
                foreach ($artefacts as $artefact) {
                    $record = (object)array(
                        'view' => $blockinstance->get('view'),
                        'artefact' => $artefact->id,
                        'block' => $blockinstance->get('id'),
                    );
                    ensure_record_exists('view_artefact', $record, $record);
                }
            }
        }
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * My Plans blocktype is only allowed in personal views
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

}

?>
