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

        $plans = ArtefactTypePlan::get_plans();
        self::build_plans_html($plans, $editing, $instance);
        $smarty = smarty_core();
        $smarty->assign('blockid', $instance->get('id'));
        $smarty->assign('plans', $plans);
        return $smarty->fetch('blocktype:plans:content.tpl');
    }

    public static function build_plans_html(&$plans, $editing=false, BlockInstance $instance) {
        $smarty = smarty_core();
        $smarty->assign_by_ref('plans', $plans);
        $plans['tablerows'] = $smarty->fetch('blocktype:plans:planrows.tpl');
        if ($editing) {
            return;
        }
        $blockid = $instance->get('id');
        $baseurl = $instance->get_view()->get_url() . '&block=' . $blockid;
        $pagination = build_pagination(array(
            'id' => 'block' . $blockid . '_pagination',
            'class' => 'center nojs-hidden-block',
            'datatable' => 'planstable_' . $blockid,
            'url' => $baseurl,
            'jsonscript' => 'artefact/plans/blocktype/plans/plans.json.php',
            'count' => $plans['count'],
            'limit' => $plans['limit'],
            'offset' => $plans['offset'],
            'numbersincludefirstlast' => false,
            'resultcounttextsingular' => get_string('plan', 'artefact.plans'),
            'resultcounttextplural' => get_string('plans', 'artefact.plans'),
        ));
        $plans['pagination'] = $pagination['html'];
        $plans['pagination_js'] = 'var paginator' . $blockid . ' = ' . $pagination['javascript'];
    }

    // My Plans blocktype only has 'title' option so next two functions return as normal
    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        return array();
    }

    public static function artefactchooser_element($default=null) {
        return array();
    }
}

?>
