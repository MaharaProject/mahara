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
 * @subpackage artefact-internal-export-html
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class HtmlExportInternal extends HtmlExportArtefactPlugin {

    public function dump_export_data() {
        // This space intentionally left blank
    }

    public function get_summary() {
        $smarty = $this->exporter->get_smarty();
        $smarty->assign('introduction', get_profile_field($this->exporter->get('user')->get('id'), 'introduction'));
        $iconid = $this->exporter->get('user')->get('profileicon');
        if ($iconid) {
            $icon = artefact_instance_from_id($iconid);
            // TODO: protect title from /'s
            $smarty->assign('icon', '<img src="static/profileicons/200px-' . $icon->get('title') . '" alt="Profile Icon">');
        }
        return array(
            'title' => 'Profile',
            'description' => $smarty->fetch('export:html/internal:summary.tpl'),
        );
    }

    public function get_summary_weight() {
        return 0;
    }

}

?>
