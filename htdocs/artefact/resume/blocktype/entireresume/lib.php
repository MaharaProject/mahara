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
 * @subpackage blocktype-entireresume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeEntireresume extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.resume/entireresume');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.resume/entireresume');
    }

    public static function get_categories() {
        return array('resume');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        $smarty = smarty_core();

        // Get data about the resume fields the user has
        $return = '';
        if ($artefacts = get_records_sql_array('
            SELECT va.artefact, a.artefacttype
            FROM {view_artefact} va
            INNER JOIN {artefact} a ON (va.artefact = a.id)
            WHERE va.view = ?
            AND va.block = ?', array($instance->get('view'), $instance->get('id')))) {
            foreach ($artefacts as $artefact) {
                $resumefield = $instance->get_artefact_instance($artefact->artefact);
                $rendered = $resumefield->render_self(array('viewid' => $instance->get('view')));
                $result = $rendered['html'];
                if (!empty($rendered['javascript'])) {
                    $result .= '<script type="text/javascript">' . $rendered['javascript'] . '</script>';
                }
                $smarty->assign($artefact->artefacttype, $result);
            }
        }
        return $smarty->fetch('blocktype:entireresume:content.tpl');
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
                'callfunction' => 'ensure_resume_artefacts_in_blockinstance',
            ),
        );
    }

    /**
     * Hook for making sure that all resume artefacts are associated with a 
     * blockinstance at blockinstance commit time
     */
    public static function ensure_resume_artefacts_in_blockinstance($event, $blockinstance) {
        if ($blockinstance->get('blocktype') == 'entireresume') {
            safe_require('artefact', 'resume');
            $artefacttypes = implode(', ', array_map('db_quote', PluginArtefactResume::get_artefact_types()));

            // Get all artefacts that are resume related and belong to the correct owner
            $artefacts = get_records_sql_array('
                SELECT id
                FROM {artefact}
                WHERE artefacttype IN(' . $artefacttypes . ')
                AND "owner" = (
                    SELECT "owner"
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
     * Entireresume blocktype is only allowed in personal views, because 
     * there's no such thing as group/site resumes
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

}
