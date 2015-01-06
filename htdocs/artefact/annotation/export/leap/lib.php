<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-annotation-export-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class LeapExportElementAnnotation extends LeapExportElement {

    public static function setup_links(&$links, $viewids, $artefactids) {
        $viewlist = join(',', array_map('intval', $viewids));
        $artefactlist = join(',', array_map('intval', $artefactids));

        // Get the annotations that are on these views.
        $records = get_records_select_array(
            'artefact_annotation',
            "view IN ($viewlist)",
            array(),
            '',
            'annotation,view'
        );
        if ($records) {
            foreach ($records as &$r) {
                // view is reflected_on_by annotation (at the current moment).
                if (!isset($links->viewartefact[$r->view][$r->annotation])) {
                    $links->viewartefact[$r->view][$r->annotation] = array();
                }
                $links->viewartefact[$r->view][$r->annotation][] = 'reflected_on_by';

                // annotation reflects_on view (at the current moment).
                if (!isset($links->artefactview[$r->annotation][$r->view])) {
                    $links->artefactview[$r->annotation][$r->view] = array();
                }
                $links->artefactview[$r->annotation][$r->view][] = 'reflects_on';

                // Get the embedded images in the annotation.
                $sql = "SELECT fileid
                        FROM {artefact_file_embedded}
                        WHERE resourceid = ?";
                if ($files = get_records_sql_array($sql, array($r->annotation))) {
                    foreach ($files as $file) {
                        $links->attachments[$r->annotation][$file->fileid] = 1;
                    }
                }

                // Get the feedback on the annotation.
                $sql = "SELECT f.artefact as feedback
                        FROM {artefact_annotation_feedback} f
                        WHERE f.onannotation = ?
                        AND f.deletedby IS NULL
                        ORDER BY f.artefact DESC";
                if ($annotationfeedback = get_records_sql_array($sql, array($r->annotation))) {
                    foreach ($annotationfeedback as $f) {
                        // feedback reflects_on annotation.
                        if (!isset($links->artefactartefact[$f->feedback][$r->annotation])) {
                            $links->artefactartefact[$f->feedback][$r->annotation] = array();
                        }
                        $links->artefactartefact[$f->feedback][$r->annotation][] = 'reflects_on';

                        // annotation is reflected_on_by feedback.
                        if (!isset($links->artefactartefact[$r->annotation][$f->feedback])) {
                            $links->artefactartefact[$r->annotation][$f->feedback] = array();
                        }
                        $links->artefactartefact[$r->annotation][$f->feedback][] = 'reflected_on_by';

                        // Get the embedded images in the annotation feedback.
                        $sql = "SELECT *
                                FROM {artefact_file_embedded}
                                WHERE resourceid = ?";
                        if ($files = get_records_sql_array($sql, array($f->feedback))) {
                            foreach ($files as $file) {
                                $links->attachments[$f->feedback][$file->fileid] = 1;
                            }
                        }

                    }
                }
            }
        }

    }

    public function get_content_type() {
        return 'html';
    }

    public function get_content() {
        return clean_html($this->artefact->get('description'));
    }

    public function get_categories() {
        return array(
            array(
                'scheme' => 'readiness',
                'term'   => ($this->artefact->get('allowcomments') ? 'Ready' : 'Unready'),
            ),
        );
    }
}

class LeapExportElementAnnotationfeedback extends LeapExportElement {

    public function get_content_type() {
        return 'html';
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
        else {
            // public feedback.
            return array(
                array(
                    'scheme' => 'audience',
                    'term'   => 'Shareable',
                )
            );
        }
        return array();
    }

    public function get_content() {
        return clean_html($this->artefact->get('description'));
    }
}