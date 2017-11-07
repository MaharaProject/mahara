<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-blog-export-html
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Dumps all of the user's blogs as static HTML
 */
class HtmlExportBlog extends HtmlExportArtefactPlugin {

    public function pagination_data($artefact) {
        if ($artefact instanceof ArtefactTypeBlog) {
            return array(
                'perpage'    => ArtefactTypeBlog::pagination,
                'childcount' => $artefact->count_published_posts(),
                'plural'     => get_string('blogs', 'artefact.blog'),
            );
        }
    }

    public function dump_export_data() {
        foreach ($this->exporter->get('artefacts') as $artefact) {
            if ($artefact instanceof ArtefactTypeBlog) {
                $this->paginate($artefact);
            }
        }
    }

    public function get_summary() {
        $smarty = $this->exporter->get_smarty();
        $blogs = array();
        foreach ($this->exporter->get('artefacts') as $artefact) {
            if ($artefact->get('artefacttype') == 'blog') {
                $blogs[] = array(
                    'link' => 'files/blog/' . PluginExportHtml::text_to_URLpath(PluginExportHtml::text_to_filename($artefact->get('title'))) . '/index.html',
                    'title' => $artefact->get('title'),
                );
            }
        }
        if ($blogs) {
            $smarty->assign('blogs', $blogs);
            $stryouhaveblogs = get_string('youhavenblog', 'artefact.blog', count($blogs), count($blogs));
        }
        else {
            $stryouhaveblogs = get_string('youhavenoblogs', 'artefact.blog');
        }

        $smarty->assign('stryouhaveblogs', $stryouhaveblogs);
        return array(
            'title' => get_string('Blogs', 'artefact.blog'),
            'description' => $smarty->fetch('export:html/blog:summary.tpl'),
        );
    }

    public function get_summary_weight() {
        return 10;
    }

}
