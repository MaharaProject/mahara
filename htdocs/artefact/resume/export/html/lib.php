<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume-export-html
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Dumps the user's resume as HTML
 */
class HtmlExportResume extends HtmlExportArtefactPlugin {

    public function dump_export_data() {
        if (($this->exporter->get('viewexportmode') == PluginExport::EXPORT_LIST_OF_VIEWS
            || $this->exporter->get('viewexportmode') == PluginExport::EXPORT_LIST_OF_COLLECTIONS)
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
