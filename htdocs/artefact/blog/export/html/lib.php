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
 * @subpackage artefact-blog-export-html
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Dumps all of the user's blogs as static HTML
 */
class HtmlExportBlog extends HtmlExportArtefactPlugin {

    public function dump_export_data() {
        foreach ($this->exporter->get('artefacts') as $artefact) {
            if ($artefact->get('artefacttype') == 'blog') {
                // Create directory for storing the blog
                $dirname = PluginExportHtml::text_to_path($artefact->get('title'));
                if (!check_dir_exists($this->fileroot . $dirname)) {
                    throw new SystemException("Couldn't create blog directory {$this->fileroot}{$dirname}");
                }

                // Render the first page of the blog (the only one if there's 
                // not many posts)
                $smarty = $this->exporter->get_smarty('../../../', 'blog');
                $smarty->assign('page_heading', $artefact->get('title'));
                $smarty->assign('breadcrumbs', array(
                    array('text' => get_string('blogs', 'artefact.blog')),
                    array('text' => $artefact->get('title'), 'path' => 'index.html'),
                ));
                $rendered = $artefact->render_self(array('hidetitle' => true));
                $outputfilter = new HtmlExportOutputFilter('../../../');
                $smarty->assign('rendered_blog', $outputfilter->filter($rendered['html']));
                $content = $smarty->fetch('export:html/blog:index.tpl');

                if (false === file_put_contents($this->fileroot . $dirname . '/index.html', $content)) {
                    throw new SystemException("Unable to create index.html for blog $blogid");
                }

                // If the blog has many posts, we'll need to write out archive pages
                $postcount = $artefact->count_published_posts();
                $perpage   = ArtefactTypeBlog::pagination;
                if ($postcount > $perpage) {
                    for ($i = 2; $i <= ceil($postcount / $perpage); $i++) {
                        $rendered = $artefact->render_self(array('page' => $i));
                        $smarty->assign('rendered_blog', $outputfilter->filter($rendered['html']));
                        $content = $smarty->fetch('export:html/blog:index.tpl');

                        if (false === file_put_contents($this->fileroot . $dirname . "/{$i}.html", $content)) {
                            throw new SystemException("Unable to create {$i}.html for blog {$artefact->get('id')}");
                        }
                    }
                }
            }
        }
    }

    public function get_summary() {
        $smarty = $this->exporter->get_smarty();
        $blogs = array();
        foreach ($this->exporter->get('artefacts') as $artefact) {
            if ($artefact->get('artefacttype') == 'blog') {
                $blogs[] = array(
                    'link' => 'files/blog/' . PluginExportHtml::text_to_path($artefact->get('title')) . '/index.html',
                    'title' => $artefact->get('title'),
                );
            }
        }
        if ($blogs) {
            $smarty->assign('blogs', $blogs);

            $stryouhaveblogs = (count($blogs) == 1)
                ? get_string('youhaveoneblog', 'artefact.blog')
                : get_string('youhaveblogs', 'artefact.blog', count($blogs));
        }
        else {
            $stryouhaveblogs = get_string('youhavenoblogs', 'artefact.blog');
        }

        $smarty->assign('stryouhaveblogs', $stryouhaveblogs);
        return array(
            'title' => get_string('blogs', 'artefact.blog'),
            'description' => $smarty->fetch('export:html/blog:summary.tpl'),
        );
    }

    public function get_summary_weight() {
        return 10;
    }

}

?>
