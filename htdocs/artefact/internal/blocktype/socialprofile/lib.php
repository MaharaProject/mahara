<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-socialprofile
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2014 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeSocialprofile extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.internal/socialprofile');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.internal/socialprofile');
    }

    public static function get_categories() {
        return array('internal' => 27000);
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {

        $configdata = $instance->get('configdata');
        $type = (isset($configdata['displaytype']) ? $configdata['displaytype'] : 'texticon');
        $showicon = ($type == 'icononly' || $type == 'texticon' ? true : false);
        $showtext = ($type == 'textonly' || $type == 'texticon' ? true : false);
        $owner = $instance->get_view()->get('owner');

        // Whether to include email button
        if (isset($configdata['displayemail']) && $configdata['displayemail'] && $owner) {
            $email = get_field('artefact_internal_profile_email', 'email', 'principal', 1, 'owner', $owner);
        }
        else if (isset($configdata['displayemail']) && $configdata['displayemail']) {
            $email = true;
        }
        else {
            $email = false;
        }

        $data = array();
        $smarty = smarty_core();
        if (isset($configdata['artefactids']) && !empty($configdata['artefactids'])) {
            // Include selected social profiles
            $sql = 'SELECT title, description, note FROM {artefact}
                WHERE id IN (' . join(',', $configdata['artefactids']) . ')
                    AND owner = ? AND artefacttype = ?
                ORDER BY description ASC';
            $artefactdata = get_records_sql_array($sql, array($owner, 'socialprofile'));
            $data = !empty($artefactdata) ? $artefactdata : $data;
        }
        else if ($editing && !empty($configdata['templateids'])) {
            foreach ($configdata['templateids'] as $socialtype) {
                $stype = new stdClass();
                $stype->title = '';
                $stype->description = get_string($socialtype, 'artefact.internal');
                $stype->note = $socialtype;
                $data[] = $stype;
            }
        }
        else if ($editing && !$email) {
            $smarty->assign('editing', $editing);
            $smarty->assign('noitems', get_string('noitemsselectone', 'blocktype.internal/socialprofile'));
        }

        safe_require('artefact', 'internal');
        $data = ArtefactTypeSocialprofile::get_profile_icons($data);
        $smarty->assign('showicon', $showicon);
        $smarty->assign('showtext', $showtext);
        $smarty->assign('profiles', $data);
        $smarty->assign('email', $email);

        return $smarty->fetch('blocktype:socialprofile:content.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $owner = $instance->get_view()->get('owner');
        $form = array();
        if (!$owner) {
            $configdata['artefactids'] = array();
            if (isset($configdata['templateids']) && !empty($configdata['templateids'])) {
                $element = self::artefactchooser_element(null, $owner);
                foreach ($element['artefacttypes'] as $key => $type) {
                    if (array_search($type, $configdata['templateids']) !== false) {
                        $configdata['artefactids'][] = $key;
                    }
                }
                $configdata['templateids'] = array();
            }
            $form['blocktemplatehtml'] = array(
                'type' => 'html',
                'value' => get_string('blockinstanceconfigownerchange', 'mahara'),
            );
            $form['blocktemplate'] = array(
                'type'    => 'hidden',
                'value'   => 1,
            );
        }
        // Which social profiles does the user want
        $form[] = self::artefactchooser_element((isset($configdata['artefactids'])) ? $configdata['artefactids'] : null, $owner);

        $form['settings'] = array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'class'        => 'first last',
            'legend'       => get_string('displaysettings', 'blocktype.internal/socialprofile'),
            'elements'     => array(
                'displaytype' => array(
                    'type' => 'radio',
                    'labelhtml' => '<span class="pseudolabel">' . get_string('displayaddressesas', 'blocktype.internal/socialprofile') . '</span>',
                    'defaultvalue' => (!empty($configdata['displaytype']) ? $configdata['displaytype'] : 'texticon'),
                    'options' => array(
                        'icononly' => get_string('optionicononly', 'blocktype.internal/socialprofile'),
                        'texticon'  => get_string('optiontexticon', 'blocktype.internal/socialprofile'),
                        'textonly'  => get_string('optiontextonly', 'blocktype.internal/socialprofile'),
                    )
                ),
                'displayemail' => array(
                    'type' => 'switchbox',
                    'labelhtml' => '<span class="pseudolabel">' . get_string('displaydefaultemail', 'blocktype.internal/socialprofile') . '</span>',
                    'defaultvalue' => (!empty($configdata['displayemail']) ? $configdata['displayemail'] : 0),
                ),
            )
        );

        return $form;
    }

    public static function instance_config_save($values, $instance) {
        if (isset($values['blocktemplate']) && !empty($values['blocktemplate'])) {
            // Need to adjust info to be a template
            $owner = $instance->get_view()->get('owner');
            $values['templateids'] = array();
            $element = self::artefactchooser_element(null, $owner);
            foreach ($element['artefacttypes'] as $key => $type) {
                if (array_search($key, $values['artefactids']) !== false) {
                    $values['templateids'][] = $type;
                }
            }
            $values['artefactids'] = array();
        }
        return $values;
    }

    public static function artefactchooser_element($default=null, $owner=true) {
        safe_require('artefact', 'internal');
        if (!$owner) {
            $artefacttypes = ArtefactTypeSocialprofile::$socialnetworks;
        }
        else {
            $artefacttypes = array('socialprofile');
        }

        return array(
            'name'  => 'artefactids',
            'type'  => 'artefactchooser',
            'title' => get_string('profilestoshow', 'blocktype.internal/socialprofile'),
            'defaultvalue' => $default,
            'blocktype' => 'socialprofile',
            'blocktemplate' => empty($owner),
            'limit'     => 655360, // 640K profile fields is enough for anyone!
            'selectone' => false,
            'search'    => false,
            'artefacttypes' => $artefacttypes,
            'template'  => 'artefact:internal:artefactchooser-element.tpl',
        );
    }

    public static function rewrite_blockinstance_config(View $view, $configdata) {
        safe_require('artefact', 'internal');
        $configdata['artefactids'] = array();
        if ($view->get('owner') !== null) {
            if (!empty($configdata['blocktemplate'])) {
                if (!empty($configdata['templateids'])) {
                    $artefactids = get_column_sql('
                        SELECT a.id FROM {artefact} a
                        WHERE a.owner = ? AND a.artefacttype = ? AND a.note IN (' . join(',', array_map('db_quote', $configdata['templateids'])) . ')', array($view->get('owner'), 'socialprofile'));
                }
                else {
                    $artefactids = array();
                }
                unset($configdata['blocktemplatehtml']);
                unset($configdata['templateids']);
                unset($configdata['blocktemplate']);
                $configdata['artefactids'] = $artefactids;
            }
        }
        return $configdata;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Profileinfo blocktype is only allowed in personal views, because
     * there's no such thing as group/site profiles
     *
     * @param View     $view The View to check
     * @return boolean Whether blocks of this blocktype are allowed in the
     *                 given view.
     */
    public static function allowed_in_view(View $view) {
        return true;
    }

    /**
     * Overrides the default implementation so we can export enough information
     * to reconstitute profile information again.
     *
     * Leap2A export doesn't export profile related artefacts as entries, so we
     * need to take that into account when exporting config for it.
     *
     * @param BlockInstance $bi The block instance to export config for
     * @return array The configuration required to import the block again later
     */
    public static function export_blockinstance_config_leap(BlockInstance $bi) {
        return PluginArtefactInternal::export_blockinstance_config_leap($bi);
    }

    /**
     * Sister method to export_blockinstance_config_leap (creates block
     * instance based of what that method exports)
     *
     * @param array $biconfig   The block instance config
     * @param array $viewconfig The view config
     * @return BlockInstance The newly made block instance
     */
    public static function import_create_blockinstance_leap(array $biconfig, array $viewconfig) {
        return PluginArtefactInternal::import_create_blockinstance_leap($biconfig, $viewconfig);
    }

}
