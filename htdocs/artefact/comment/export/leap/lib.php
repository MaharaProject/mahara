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
 * @subpackage artefact-comment-export-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class LeapExportElementComment extends LeapExportElement {

    public static function setup_links(&$links, $viewids, $artefactids) {
        $viewlist = join(',', array_map('intval', $viewids));
        $artefactlist = join(',', array_map('intval', $artefactids));

        $records = get_records_select_array(
            'artefact_comment_comment',
            "artefact IN ($artefactlist) AND onview IN ($viewlist)",
            array(),
            '',
            'artefact,onview'
        );
        if ($records) {
            foreach ($records as &$r) {
                if (!isset($links->viewartefact[$r->onview][$r->artefact])) {
                    $links->viewartefact[$r->onview][$r->artefact] = array();
                }
                $links->viewartefact[$r->onview][$r->artefact][] = 'reflected_on_by';
                if (!isset($links->artefactview[$r->artefact][$r->onview])) {
                    $links->artefactview[$r->artefact][$r->onview] = array();
                }
                $links->artefactview[$r->artefact][$r->onview][] = 'reflects_on';
            }
        }

        $records = get_records_select_array(
            'artefact_comment_comment',
            "artefact IN ($artefactlist) AND onartefact IN ($artefactlist)",
            array(),
            '',
            'artefact,onartefact'
        );
        if ($records) {
            foreach ($records as &$r) {
                if (!isset($links->artefactartefact[$r->onartefact][$r->artefact])) {
                    $links->artefactartefact[$r->onartefact][$r->artefact] = array();
                }
                $links->artefactartefact[$r->onartefact][$r->artefact][] = 'reflected_on_by';
                if (!isset($links->artefactartefact[$r->artefact][$r->onartefact])) {
                    $links->artefactartefact[$r->artefact][$r->onartefact] = array();
                }
                $links->artefactartefact[$r->artefact][$r->onartefact][] = 'reflects_on';
            }
        }
    }

    public function get_content_type() {
        return 'html';
    }

    public function get_content() {
        return $this->artefact->render_self();
    }

    public function get_categories() {
        if ($this->artefact->get('private')) {
            return array(
                array(
                    'scheme' => 'audience',
                    'term'   => 'Private',
                )
            );
        }
        return array();
    }

}
