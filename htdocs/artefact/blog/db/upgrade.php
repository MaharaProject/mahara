<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage artefact-internal
 * @author     Alastair Pharo <alastair@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_blog_upgrade($oldversion=0) {
    
    $status = true;

    // There was no database prior to this version.
    if ($status && $oldversion < 2006120501) {
        $status = $status && install_from_xmldb_file(
            get_config('docroot') .
            'artefact/blog/db/install.xml'
        );
    }

    if ($status && $oldversion < 2006121501) {
        $table = new XMLDBTable('artefact_blog_blogpost_file_pending');

        $table->addFieldInfo('file', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addFieldInfo('when', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        
        $table->addKeyInfo('blogpost_file_pending_pk', XMLDB_KEY_PRIMARY, array('file'));
        $table->addKeyInfo('filefk', XMLDB_KEY_FOREIGN, array('file'), 'artefact', array('id'));

        $status = $status && create_table($table);
    }

    if ($oldversion < 2008012200) {
        // From 0.9, some files were not having their temporary download paths 
        // translated to proper artefact/file/download.php paths. This upgrade 
        // attempts to fix them. It should work in the vast majority of cases, 
        // the largest assumption made is that artefacts were inserted in 
        // ascending ID order when the post was created, which is a pretty safe 
        // bet.
        if ($blogfiles = get_records_array('artefact_blog_blogpost_file', '', '', 'blogpost ASC, file ASC')) {
            $blogpostids = join(', ', array_map(create_function('$a', 'return $a->blogpost;'), $blogfiles));
            // Find all blogposts that have attached files
            if ($blogposts = get_records_select_array('artefact', 'id IN(' . $blogpostids . ')', null, 'id ASC')) {
                foreach ($blogposts as $post) {
                    log_debug("Checking post {$post->id}");
                    // Only doublecheck posts that are likely to have a broken URL in them
                    if (false !== strpos($post->description, 'createid')) {
                        log_debug(" * Looks like post " . $post->id . " has a createid in it");
                        $i = 0;
                        $body = $post->description;
                        foreach ($blogfiles as $file) {
                            if ($file->blogpost == $post->id) {
                                // This file is connected to this post, so likely it is to be displayed
                                $i++;
                                log_debug('* Replace uploadnumber = ' . $i . ' with artefact id ' . $file->file);
                                $regexps = array('/<img([^>]+)src="([^>]+)downloadtemp.php\?uploadnumber=' . $i .'&amp;createid=\d+/',
                                                 '/alt="uploaded:' . $i . '"/');
                                $subs = array('<img$1src="' . get_config('wwwroot') . 'artefact/file/download.php?file=' . $file->file,
                                              'alt="artefact:' . $file->file . '"');
                                $body = preg_replace($regexps, $subs, $body);
                            }
                        }

                        // Update the post if necessary
                        if ($body != $post->description) {
                            $postobj = new ArtefactTypeBlogPost($post->id, null);
                            $postobj->set('description', $body);
                            $postobj->commit();
                        }
                    }
                }
            }
        }
    }

    return $status;
}

?>
