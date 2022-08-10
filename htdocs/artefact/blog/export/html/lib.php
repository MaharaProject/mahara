<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-blog-export-html
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
            $count = $artefact->count_published_posts();
            return array(
                'perpage'    => ArtefactTypeBlog::pagination,
                'childcount' => $count,
                'plural'     => get_string('nblogs', 'artefact.blog', $count),
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
                    'link' => 'content/blog/' . PluginExportHtml::text_to_URLpath(PluginExportHtml::text_to_filename($artefact->get('title'))) . '/index.html',
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
