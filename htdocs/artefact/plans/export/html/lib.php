<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans-export-html
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class HtmlExportPlans extends HtmlExportArtefactPlugin {

    public function pagination_data($artefact) {
        if ($artefact instanceof ArtefactTypePlan) {
            return array(
                'perpage'    => 10,
                'childcount' => $artefact->count_children(),
                'plural'     => get_string('plans', 'artefact.plans'),
            );
        }
    }

    public function dump_export_data() {
        foreach ($this->exporter->get('artefacts') as $artefact) {
            if ($artefact instanceof ArtefactTypePlan) {
                $this->paginate($artefact);
            }
        }
    }

    public function get_summary() {
        $smarty = $this->exporter->get_smarty();
        $plans = array();
        foreach ($this->exporter->get('artefacts') as $artefact) {
            if ($artefact instanceof ArtefactTypePlan) {
                $plans[] = array(
                    'link' => 'files/plans/' . PluginExportHtml::text_to_URLpath(PluginExportHtml::text_to_filename($artefact->get('title'))) . '/index.html',
                    'title' => $artefact->get('title'),
                );
            }
        }
        $smarty->assign('plans', $plans);

        return array(
            'title' => get_string('plans', 'artefact.plans'),
            'description' => $smarty->fetch('export:html/plans:summary.tpl'),
        );
    }

    public function get_summary_weight() {
        return 40;
    }
}
