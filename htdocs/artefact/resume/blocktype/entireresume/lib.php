<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-entireresume
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeEntireresume extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.resume/entireresume');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.resume/entireresume');
    }

    public static function get_categories() {
        return array('internal' => 29000);
    }

    public static function get_viewtypes() {
        return array('dashboard', 'portfolio', 'profile');
    }

    public static function get_blocktype_type_content_types() {
        return array('entireresume' => array('resume'));
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        $smarty = smarty_core();
        $configdata = $instance->get('configdata');
        $configdata['showcommentcount'] = true;
        $configdata['viewid'] = $instance->get('view');
        $configdata['editing'] = $editing;
        // Get data about the resume fields the user has
        if ($artefacts = get_records_sql_array('
            SELECT va.artefact, a.artefacttype
            FROM {view_artefact} va
            INNER JOIN {artefact} a ON (va.artefact = a.id)
            WHERE va.view = ?
            AND va.block = ?', array($instance->get('view'), $instance->get('id')))) {
            foreach ($artefacts as $artefact) {
                $resumefield = $instance->get_artefact_instance($artefact->artefact);
                $rendered = $resumefield->render_self($configdata);
                $result = $rendered['html'];
                if (!empty($rendered['javascript'])) {
                    $result .= '<script>' . $rendered['javascript'] . '</script>';
                }
                $smarty->assign($artefact->artefacttype, $result);
            }
        }
        else {
            $smarty->assign('editing', $editing);
            $smarty->assign('noresume', get_string('noresumeselectone', 'blocktype.resume/entireresume'));
        }
        return $smarty->fetch('blocktype:entireresume:content.tpl');
    }

    public static function has_instance_config(BlockInstance $instance) {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $owner = $instance->get_view()->get('owner');
        if ($owner) {
            $elements = array(
                'tags'  => array(
                    'type'         => 'tags',
                    'title'        => get_string('tags'),
                    'description'  => get_string('tagsdescblock'),
                    'defaultvalue' => $instance->get('tags'),
                    'help'         => false,
                )
            );
        }
        else {
            $elements['blocktemplatehtml'] = array(
                'type' => 'html',
                'value' => get_string('blockinstanceconfigownerauto', 'mahara'),
            );
            $elements['blocktemplate'] = array(
                'type'    => 'hidden',
                'value'   => 1,
            );
        }
        return $elements;
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
                    /* There are multiple calls to the DB via asynch json calls causing multiple records to be created
                    * * until that issue is fixed, we ignore the warnings for now */
                    ensure_record_exists('view_artefact', $record, $record, false, false , IGNORE_MULTIPLE);
                }
            }
        }
    }

   /**
    * Fetch the associated artefacts including embedded images.
    *
    * The result of this method is used to populate the view_artefact table, and
    * thus decide whether an artefact is in a view for the purposes of access.
    *
    * @param BlockInstance $instance
    * @return array ids of artefacts in this block instance
    */
    public static function get_artefacts(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $return = array();
        safe_require('artefact', 'resume');
        $artefacttypes = implode(', ', array_map('db_quote', PluginArtefactResume::get_artefact_types()));
        // Get all artefacts that are resume related and belong to the correct owner
        if ($artefacts = get_records_sql_array('
                SELECT id
                FROM {artefact}
                WHERE artefacttype IN(' . $artefacttypes . ')
                AND "owner" = (
                    SELECT "owner"
                    FROM {view}
                    WHERE id = ?
                )', array($instance->get('view')))) {
            foreach ($artefacts as $artefact) {
                $return[] = $artefact->id;
                // Check if there are any embedded images associated with this
                $embedded = get_column_sql("SELECT afe.fileid
                                            FROM {artefact} a
                                            JOIN {artefact_file_embedded} afe ON afe.resourcetype = a.artefacttype
                                            WHERE a.owner = afe.resourceid
                                            AND a.id = ?", array($artefact->id));
                if (!empty($embedded)) {
                    $return = array_merge($return, $embedded);
                }
                // Check if there are any associated attachments
                $attachments = get_column_sql("SELECT aa.attachment FROM {artefact_attachment} aa WHERE aa.artefact = ?", array($artefact->id));
                if (!empty($attachments)) {
                    $return = array_merge($return, $attachments);
                }
            }
        }
        return $return;
    }

    public static function default_copy_type(BlockInstance $instance, View $view) {
        return 'shallow';
    }

    /**
     * Entireresume blocktype is only allowed in personal views, because
     * there's no such thing as group/site resumes
     */
    public static function allowed_in_view(View $view) {
        return true;
    }
}
