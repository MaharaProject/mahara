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
 * @subpackage artefact-resume-export-html
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Dumps the user's resume as HTML
 */
class HtmlExportResume extends HtmlExportArtefactPlugin {

    public function dump_export_data() {
        if ($this->exporter->get('viewexportmode') == PluginExport::EXPORT_LIST_OF_VIEWS
            && $this->exporter->get('artefactexportmode') == PluginExport::EXPORT_ARTEFACTS_FOR_VIEWS) {
            // Dont' care about resume in this case
            return;
        }
        $smarty = $this->exporter->get_smarty('../../', 'resume');
        $smarty->assign('page_heading', get_string('resumeofuser', 'artefact.resume', full_name($this->exporter->get('user'))));
        $smarty->assign('breadcrumbs', array(
            array('text' => get_string('resume', 'artefact.resume'), 'path' => 'index.html'),
        ));

        if ($artefacts = get_column_sql("SELECT id
            FROM {artefact}
            WHERE \"owner\" = ?
            AND artefacttype IN
            (SELECT name FROM {artefact_installed_type} WHERE plugin = 'resume')",
            array($this->exporter->get('user')->get('id')))) {
            foreach ($artefacts as $id) {
                $artefact = artefact_instance_from_id($id);
                $rendered = $artefact->render_self(array());
                $smarty->assign($artefact->get('artefacttype'), $rendered['html']);
            }
        }
        $content = $smarty->fetch('export:html/resume:index.tpl');

        if (false === file_put_contents($this->fileroot . 'index.html', $content)) {
            throw new SystemException("Unable to create index.html for resume");
        }
    }

    public function get_summary() {
        $smarty = $this->exporter->get_smarty();
        return array(
            'title' => get_string('resume', 'artefact.resume'),
            'description' => $smarty->fetch('export:html/resume:summary.tpl'),
        );
    }

    public function get_summary_weight() {
        return 30;
    }

}
