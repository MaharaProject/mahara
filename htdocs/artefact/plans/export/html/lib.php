<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class HtmlExportPlans extends HtmlExportArtefactPlugin {

    public function pagination_data($artefact) {
        if ($artefact instanceof ArtefactTypePlan) {
            $count = $artefact->count_children();
            return array(
                'perpage'    => 10,
                'childcount' => $count,
                'plural'     => get_string('nplans', 'artefact.plans', $count),
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
                    'link' => 'content/plans/' . PluginExportHtml::text_to_URLpath(PluginExportHtml::text_to_filename($artefact->get('title'))) . '/index.html',
                    'title' => $artefact->get('title'),
                );
            }
        }
        $smarty->assign('plans', $plans);

        return array(
            'title' => get_string('Plans', 'artefact.plans'),
            'description' => $smarty->fetch('artefact:plans:export/html/summary.tpl'),
        );
    }

    public function get_summary_weight() {
        return 40;
    }
}
