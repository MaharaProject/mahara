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
 * @subpackage artefact-blog-export-html
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Dumps all of the user's blogs as static HTML
 */
class HtmlExportBlog extends HtmlExportArtefactPlugin {

    private $blogcount;

    public function dump_export_data() {
        if ($blogs = get_column('artefact', 'id', 'owner', $this->exporter->get('user')->get('id'), 'artefacttype', 'blog')) {
            foreach ($blogs as $blogid) {
                $blog = artefact_instance_from_id($blogid);

                // Create directory for storing the blog
                $dirname = preg_replace('#[^a-zA-Z0-9_-]+#', '-', $blog->get('title'));
                if (!check_dir_exists($this->fileroot . $dirname)) {
                    throw new SystemException("Couldn't create blog directory {$this->fileroot}{$dirname}");
                }

                $smarty = $this->exporter->get_smarty('../../../');
                $smarty->assign('title', $blog->get('title'));
                $rendered = $blog->render_self(array());
                $smarty->assign('rendered_blog', $rendered['html']);
                $content = $smarty->fetch('export:html/blog:index.tpl');

                if (false === file_put_contents($this->fileroot . $dirname . '/index.html', $content)) {
                    throw new SystemException("Unable to create index.html for blog $blogid");
                }
            }
        }

        $this->blogcount = count($blogs);

    }

    public function get_summary() {
        return array(
            'title' => 'Blogs',
            'description' => "<p>You have {$this->blogcount} blogs</p>",
        );
    }

    public function get_summary_weight() {
        return 10;
    }

}

?>
