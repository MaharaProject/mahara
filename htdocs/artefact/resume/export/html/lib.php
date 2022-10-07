<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume-export-html
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
            // Don't care about resume in this case
            return;
        }
        $rootpath = $this->exporter->get_root_path(3);
        $smarty = $this->exporter->get_smarty($rootpath, 'resume');
        $smarty->assign('page_heading', get_string('resumeofuser', 'artefact.resume', full_name($this->exporter->get('user'))));
        $smarty->assign('breadcrumbs', array(
            array('text' => get_string('resume', 'artefact.resume'), 'path' => 'index.html'),
        ));

        $attachmentids = array();
        $exportedmodals = '';
        $options = array(
            'details' => true,
            'metadata' => 1,
            'modal' => true,
        );
        require_once(get_config('docroot') . 'export/html/lib.php');
        $outputfilter = new HtmlExportOutputFilter($this->exporter);

        if ($artefacts = get_column_sql("SELECT id
            FROM {artefact}
            WHERE \"owner\" = ?
            AND artefacttype IN
            (SELECT name FROM {artefact_installed_type} WHERE plugin = 'resume')",
            array($this->exporter->get('user')->get('id')))) {
            foreach ($artefacts as $id) {
                $artefact = artefact_instance_from_id($id);
                $rendered = $artefact->render_self(array());
                $attachments = $artefact->get_attachments();
                if (!empty($attachments)) {
                    foreach ($attachments as $file) {
                        if (!in_array($file->{'id'}, $attachmentids)) {
                            array_push($attachmentids, $file->{'id'});
                            $html = '';
                            $a = artefact_instance_from_id($file->{'id'});
                            $modalcontent = $a->render_self($options);
                            if (!empty($modalcontent['javascript'])) {
                                $html = '<script>' . $modalcontent['javascript'] . '</script>';
                            }
                            $html .= $modalcontent['html'];
                            $smarty->assign('artefactid', $file->{'id'});
                            $smarty->assign('content', $html);
                            $smarty->assign('title', $a->get('title'));
                            $exportedmodals .= $smarty->fetch('export:html:modal.tpl');
                        }
                    }
                }
                $content = $outputfilter->filter($rendered['html']);
                $smarty->assign($artefact->get('artefacttype'), $rendered['html']);
            }
        }

        $content = $smarty->fetch('export:html/resume:index.tpl');
        $exportedmodals = $outputfilter->filter($exportedmodals);
        $content .= $exportedmodals;

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

    public function pagination_data($artefact) {
    }

}
