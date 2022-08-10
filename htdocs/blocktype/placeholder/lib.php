<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-inbox
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypePlaceholder extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.placeholder');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.placeholder');
    }

    public static function get_categories() {
        return array('shortcut' => 500);
    }

    public static function is_active() {
        return get_field('blocktype_installed', 'active', 'name', 'placeholder');
    }

    /**
     * We want this blocktype to be the default blocktype so we
     * will prevent it being disabled.
     */
    public static function can_be_disabled() {
        return false;
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        global $USER, $THEME;
        $configdata = $instance->get('configdata');

        $smarty = smarty_core();
        $smarty->assign('placeholdertext', get_string('placeholdertext1', 'blocktype.placeholder'));
        return $smarty->fetch('blocktype:placeholder:body.tpl');
    }

    public static function has_instance_config(BlockInstance $instance) {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        global $USER;

        $view = $instance->get_view();

        $elements = array();
        $elements['types'] = array(
            'type' => 'fieldset',
            'legend' => get_string('contenttypes', 'blocktype.placeholder'),
            'help' => true,
            'helpcallback' => 'get_block_help',
            'elements' => array(
                'contenttypes' => array(
                    'type' => 'html',
                    'value' => '',
                ),
            ),
        );
        $offset = 0;
        $limit = 4;
        list($count, $types) = self::get_content_types($view, $offset, $limit);
        $pagination = build_showmore_pagination(array(
            'count'  => $count,
            'limit'  => $limit,
            'offset' => $offset,
            'orderby' => 'popular',
            'databutton' => 'showmorebtn',
            'jscall' => 'wire_blockoptions',
            'jsonscript' => 'blocktype/placeholder/blockoptions.json.php',
            'extra' => array('viewid' => $view->get('id'),
                             'blockid' => $instance->get('id')),
        ));
        if (empty($pagination['javascript'])) {
            // no pagination but we still need to wire the options
            $pagination['javascript'] = "window['wire_blockoptions']();";
        }
        $smarty = smarty_core();
        $smarty->assign('blockid', $instance->get('id'));
        $smarty->assign('types', $types);
        $typeslist = $smarty->fetch('blocktype:placeholder:contenttypeslist.tpl');
        $smarty->assign('typeslist', $typeslist);
        $smarty->assign('pagination', $pagination);
        $typeshtml = $smarty->fetch('blocktype:placeholder:contenttypes.tpl');

        $elements['types']['elements']['contenttypes']['value'] = $typeshtml;
        $elements['tags'] = array(
            'type'         => 'tags',
            'title'        => get_string('tags'),
            'description'  => get_string('tagsdescblock'),
            'defaultvalue' => $instance->get('tags'),
            'help'         => false,
        );

        return $elements;
    }

    public static function instance_config_save($values, $instance) {
        unset($values['contenttypes']);
        return $values;
    }

    public static function get_content_types($view, $offset = 0, $limit = 8) {
        $categories = $view->get('categorydata');
        $blocks = array();
        foreach ($categories as $c) {
            $blocktypes = PluginBlocktype::get_blocktypes_for_category($c['name'], $view);
            if ($c['name'] == 'shortcut') {
                foreach ($blocktypes as $key => $blocktype) {
                    if ($blocktype['name'] == 'placeholder') {
                        unset($blocktypes[$key]); // do not allow placeholder to select itself
                    }
                }
            }
            $blocks = array_merge($blocks, $blocktypes);
        }
        $count = count($blocks);
        // sort and return limit
        usort($blocks, function ($a, $b) {
            return $a['sortorder'] < $b['sortorder'] ? -1 : 1;
        });
        $blocks = array_slice($blocks, $offset, $limit);
        return array($count, $blocks);
    }
}
