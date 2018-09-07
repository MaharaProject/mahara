<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-resumefield
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeResumefield extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.resume/resumefield');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.resume/resumefield');
    }

    public static function get_categories() {
        return array('internal' => 30000);
    }

    public static function get_blocktype_type_content_types() {
        return array('resumefield' => array('resume'));
    }

    /**
     * Optional method. If exists, allows this class to decide the title for
     * all blockinstances of this type
     */
    public static function get_instance_title(BlockInstance $bi) {
        $configdata = $bi->get('configdata');

        if (!empty($configdata['artefactid'])) {
            return $bi->get_artefact_instance($configdata['artefactid'])->get('title');
        }
        return '';
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        $smarty = smarty_core();
        $configdata = $instance->get('configdata');
        $configdata['viewid'] = $instance->get('view');

        // Get data about the resume field in this blockinstance
        if (!empty($configdata['artefactid'])) {
            $resumefield = $instance->get_artefact_instance($configdata['artefactid']);
            $rendered = $resumefield->render_self($configdata);
            $result = $rendered['html'];
            if (!empty($rendered['javascript'])) {
                $result .= '<script>' . $rendered['javascript'] . '</script>';
            }
            $smarty->assign('content', $result);
        }
        else if ($editing && !empty($configdata['templateid'])) {
            $result = '<p><strong>' . get_string($configdata['templateid'], 'artefact.resume') . '</strong></p>';
            $smarty->assign('content', $result);
        }
        else {
            $smarty->assign('editing', $editing);
            $smarty->assign('nodata', get_string('noresumeitemselectone', 'blocktype.resume/resumefield'));
        }
        return $smarty->fetch('blocktype:resumefield:content.tpl');;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');

        $form = array();
        $owner = $instance->get_view()->get('owner');
        if ($owner) {
            // Which resume field does the user want
            $form[] = self::artefactchooser_element((isset($configdata['artefactid'])) ? $configdata['artefactid'] : null);
            $form['message'] = array(
                'type' => 'html',
                'value' => '<p class="alert alert-info">' . get_string('filloutyourresume', 'blocktype.resume/resumefield', '<a href="' . get_config('wwwroot') . 'artefact/resume/index.php">', '</a>') .'</p>',
            );
            $form['tags'] = array(
                'type'         => 'tags',
                'title'        => get_string('tags'),
                'description'  => get_string('tagsdescblock'),
                'defaultvalue' => $instance->get('tags'),
                'help'         => false,
            );
        }
        else {
            if (isset($configdata['templateid']) && !empty($configdata['templateid'])) {
                safe_require('artefact', 'resume');
                $types = PluginArtefactResume::get_artefact_types();
                $configdata['artefactid'] = array_search($configdata['templateid'], $types);
                unset($configdata['templateid']);
            }
            $form[] = self::artefactchooser_element((isset($configdata['artefactid'])) ? $configdata['artefactid'] : null, $owner);
            $form['blocktemplatehtml'] = array(
                'type' => 'html',
                'value' => get_string('blockinstanceconfigownerchange', 'mahara'),
            );
            $form['blocktemplate'] = array(
                'type'    => 'hidden',
                'value'   => 1,
            );
        }
        return $form;
    }

    public static function instance_config_save($values, $instance) {
        if (isset($values['blocktemplate']) && !empty($values['blocktemplate'])) {
            // Need to adjust info to be a template
            $owner = $instance->get_view()->get('owner');
            $values['templateid'] = null;
            $element = self::artefactchooser_element(null, $owner);
            // Because the settings are radio we can treat empty response as picking the first option
            if (!empty($element['artefacttypes'][$values['artefactid']])) {
                $values['templateid'] = $element['artefacttypes'][$values['artefactid']];
            }
            else {
                $values['templateid'] = $element['artefacttypes'][0];
            }
            unset($values['artefactid']);
        }

        unset($values['message']);
        return $values;
    }

    // TODO: make decision on whether this should be abstract or not
    public static function artefactchooser_element($default=null,$owner=true) {
        safe_require('artefact', 'resume');
        return array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('fieldtoshow', 'blocktype.resume/resumefield'),
            'defaultvalue' => $default,
            'blocktype' => 'resumefield',
            'blocktemplate' => empty($owner),
            'limit'     => 655360, // 640K profile fields is enough for anyone!
            'selectone' => true,
            'search'    => false,
            'artefacttypes' => PluginArtefactResume::get_artefact_types(),
            'template'  => 'artefact:resume:artefactchooser-element.tpl',
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
        $artefactid = null;
        if ($view->get('owner') !== null) {
            $artefacttype = null;
            if (!empty($configdata['blocktemplate'])) {
                if (!empty($configdata['templateid'])) {
                    $artefacttype = $configdata['templateid'];
                }
                unset($configdata['blocktemplatehtml']);
                unset($configdata['templateid']);
                unset($configdata['blocktemplate']);
            }
            else if (!empty($configdata['artefactid'])) {
                $artefacttype = get_field('artefact', 'artefacttype', 'id', $configdata['artefactid']);
            }

            if ($artefacttype) {
                $artefactid = get_field('artefact', 'id', 'artefacttype', $artefacttype, 'owner', $view->get('owner'));
            }
        }
        $configdata['artefactid'] = $artefactid;
        return $configdata;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Resumefield blocktype is only allowed in personal views, because
     * there's no such thing as group/site resumes
     */
    public static function allowed_in_view(View $view) {
        return true;
    }

    /**
     * Export the name of the resume field being exported instead of a
     * reference to the artefact ID - mainly so that the fake "contact
     * information" field (which isn't exported) gets handled properly.
     *
     * @param BlockInstance $bi The blockinstance to export the config for.
     * @return array The config for the blockinstance
     */
    public static function export_blockinstance_config_leap(BlockInstance $bi) {
        $configdata = $bi->get('configdata');
        $result = array();

        if (!empty($configdata['artefactid'])) {
            if ($artefacttype = get_field('artefact', 'artefacttype', 'id', $configdata['artefactid'])) {
                $result['artefacttype'] = json_encode(array($artefacttype));
            }
        }

        return $result;
    }

    /**
     * Load the artefact ID for the field based on the field name that is in
     * the config (see export_blockinstance_config_leap).
     *
     * @param array $biconfig   The block instance config
     * @param array $viewconfig The view config
     * @return BlockInstance The newly made block instance
     */
    public static function import_create_blockinstance_leap(array $biconfig, array $viewconfig) {
        $configdata = array();

        // This blocktype is only allowed in personal views
        if (empty($viewconfig['owner'])) {
            return;
        }
        $owner = $viewconfig['owner'];

        if (isset($biconfig['config']) && is_array($biconfig['config'])) {
            $impcfg = $biconfig['config'];
            if (!empty($impcfg['artefacttype'])) {
                if ($artefactid = get_field_sql("SELECT id
                    FROM {artefact}
                    WHERE \"owner\" = ?
                    AND artefacttype = ?
                    AND artefacttype IN (
                        SELECT name
                        FROM {artefact_installed_type}
                        WHERE plugin = 'resume'
                    )", array($owner, $impcfg['artefacttype']))) {
                    $configdata['artefactid'] = $artefactid;
                }
            }
        }

        $bi = new BlockInstance(0,
            array(
                'blocktype'  => $biconfig['type'],
                'configdata' => $configdata,
            )
        );

        return $bi;
    }

}
