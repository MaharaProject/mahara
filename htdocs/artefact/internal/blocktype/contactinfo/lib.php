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
 * @subpackage blocktype-contactinfo
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeContactinfo extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.internal/contactinfo');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.internal/contactinfo');
    }

    public static function get_categories() {
        return array('internal');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        $smarty = smarty_core();
        $configdata = $instance->get('configdata');

        $data = array();

        // add in the selected email address
        if (!empty($configdata['email'])) {
            $configdata['artefactids'][] = $configdata['email'];
        }

        // Get data about the profile fields in this blockinstance
        if (!empty($configdata['artefactids'])) {
            $viewowner = get_field('view', 'owner', 'id', $instance->get('view'));
            foreach ($configdata['artefactids'] as $id) {
                try {
                    $artefact = artefact_instance_from_id($id);
                    if (is_a($artefact, 'ArtefactTypeProfile') && $artefact->get('owner') == $viewowner) {
                        $rendered = $artefact->render_self(array('link' => true));
                        $data[$artefact->get('artefacttype')] = $rendered['html'];
                    }
                }
                catch (ArtefactNotFoundException $e) {
                    log_debug('Artefact not found when rendering contactinfo block instance. '
                        . 'There might be a bug with deleting artefacts of this type? '
                        . 'Original error follows:');
                    log_debug($e->getMessage());
                }
            }
        }

        $smarty->assign('profileinfo', $data);

        return $smarty->fetch('blocktype:contactinfo:content.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');

        $form = array();

        // email addresses
        $result = get_records_sql_array('SELECT a.id, a.title, a.note
            FROM {artefact} a
            WHERE artefacttype = \'email\'
            AND a.owner = (
                SELECT "owner"
                FROM {view}
                WHERE id = ?
            )
            ORDER BY a.id', array($instance->get('view')));

        $options = array(
            0 => get_string('dontshowemail', 'blocktype.internal/contactinfo'),
        );

        foreach ($result as $email) {
            $options[$email->id] = $email->title;
        }

        $form['email'] = array(
            'type'    => 'radio',
            'title'   => get_string('email', 'artefact.internal'),
            'options' => $options,
            'defaultvalue' => (isset($configdata['email'])) ? $configdata['email'] : 0,
            'separator' => '<br>',
        );

        // Which fields does the user want
        $form[] = self::artefactchooser_element((isset($configdata['artefactids'])) ? $configdata['artefactids'] : null);

        return $form;
    }

    // TODO: make decision on whether this should be abstract or not
    public static function artefactchooser_element($default=null) {
        safe_require('artefact', 'internal');
        return array(
            'name'  => 'artefactids',
            'type'  => 'artefactchooser',
            'title' => get_string('fieldstoshow', 'blocktype.internal/contactinfo'),
            'defaultvalue' => $default,
            'blocktype' => 'contactinfo',
            'limit'     => 655360, // 640K profile fields is enough for anyone!
            'selectone' => false,
            'search'    => false,
            'artefacttypes' => array_diff(PluginArtefactInternal::get_contactinfo_artefact_types(), array('email')),
            'template'  => 'artefact:internal:artefactchooser-element.tpl',
        );
    }

    /**
     * Deliberately enforce _no_ sort order. The database will return them in 
     * the order they were inserted, which means roughly the order that they 
     * are listed in the profile screen
     */
    public static function artefactchooser_get_sort_order() {
        return '';
    }

    public static function rewrite_blockinstance_config(View $view, $configdata) {
        if ($view->get('owner') !== null) {
            $artefacttypes = array_diff(PluginArtefactInternal::get_contactinfo_artefact_types(), array('email'));
            $artefactids = get_column_sql('
                SELECT a.id FROM {artefact} a
                WHERE a.owner = ? AND a.artefacttype IN (' . join(',', array_map('db_quote', $artefacttypes)) . ')', array($view->get('owner')));
            $configdata['artefactids'] = $artefactids;
            if (isset($configdata['email'])) {
                if ($newemail = get_field('artefact_internal_profile_email', 'artefact', 'principal', 1, 'owner', $view->get('owner'))) {
                    $configdata['email'] = $newemail;
                }
                else {
                    unset($configdata['email']);
                }
            }
        }
        else {
            $configdata['artefactids'] = array();
        }
        return $configdata;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Contactinfo blocktype is only allowed in personal views, because 
     * there's no such thing as group/site profiles
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

    /**
     * Overrides the default implementation so we can export enough information
     * to reconstitute profile information again.
     *
     * Leap2A export doesn't export profile related artefacts as entries, so we
     * need to take that into account when exporting config for it.
     */
    public static function export_blockinstance_config_leap(BlockInstance $bi) {
        return PluginArtefactInternal::export_blockinstance_config_leap($bi);
    }

    /**
     * Sister method to export_blockinstance_config_leap (creates block
     * instance based of what that method exports)
     */
    public static function import_create_blockinstance_leap(array $biconfig, array $viewconfig) {
        return PluginArtefactInternal::import_create_blockinstance_leap($biconfig, $viewconfig);
    }

}
