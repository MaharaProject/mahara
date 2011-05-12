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
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_blog_upgrade($oldversion=0) {
    
    // There was no database prior to this version.
    if ($oldversion < 2006120501) {
        install_from_xmldb_file(
            get_config('docroot') .
            'artefact/blog/db/install.xml'
        );
    }

    if ($oldversion < 2006121501) {
        $table = new XMLDBTable('artefact_blog_blogpost_file_pending');

        $table->addFieldInfo('file', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addFieldInfo('when', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        
        $table->addKeyInfo('blogpost_file_pending_pk', XMLDB_KEY_PRIMARY, array('file'));
        $table->addKeyInfo('filefk', XMLDB_KEY_FOREIGN, array('file'), 'artefact', array('id'));

        if (!create_table($table)) {
            throw new SQLException($table . " could not be created, check log for errors.");
        }
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
    if ($oldversion < 2008020700) {
        $table = new XMLDBTable('artefact_blog_blog');
        drop_table($table);

        if (is_mysql()) {
            execute_sql('DROP INDEX {arteblogblog_blo2_ix} ON {artefact_blog_blogpost}');
            execute_sql('CREATE INDEX {arteblogblog_blo_ix} ON {artefact_blog_blogpost} (blogpost)');
            execute_sql('ALTER TABLE {artefact_blog_blogpost} DROP FOREIGN KEY {arteblogblog_blo2_fk}');
            // I can't quite get mysql to name this key correctly, so there 
            // will be a difference in the database if you upgrade from 0.9 
            // compared with installing from 1.0
            execute_sql('ALTER TABLE {artefact_blog_blogpost} ADD FOREIGN KEY (blogpost) REFERENCES {artefact} (id)');
        }
        else {
            // Rename indexes to keep things the same regardless of whether the 
            // user installed or upgraded to this release
            execute_sql('DROP INDEX {arteblogblog_blo2_ix}');
            execute_sql('CREATE INDEX {arteblogblog_blo_ix} ON {artefact_blog_blogpost} USING btree (blogpost)');
            execute_sql('ALTER TABLE {artefact_blog_blogpost} DROP CONSTRAINT {arteblogblog_blo2_fk}');
            execute_sql('ALTER TABLE {artefact_blog_blogpost} ADD CONSTRAINT {arteblogblog_blo_fk} FOREIGN KEY (blogpost) REFERENCES {artefact}(id)');
        }
    }

    if ($oldversion < 2008101602) {
        $table = new XMLDBTable('artefact_blog_blogpost_file_pending');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('oldextension', XMLDB_TYPE_TEXT, null);
        $table->addFieldInfo('filetype', XMLDB_TYPE_TEXT, null);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        create_table($table);
    }

    if ($oldversion < 2009033100) {
        $bloguploadbase = get_config('dataroot') . 'artefact/blog/uploads/';
        if (is_dir($bloguploadbase)) {
            if ($basedir = opendir($bloguploadbase)) {
                while (false !== ($sessionupload = readdir($basedir))) {
                    if ($sessionupload != "." && $sessionupload != "..") {
                        $sessionupload = $bloguploadbase . $sessionupload;
                        $subdir = opendir($sessionupload);

                        while (false !== ($uploadfile = readdir($subdir))) {
                            if ($uploadfile != "." && $uploadfile != "..") {
                                $uploadfile = $sessionupload . '/' . $uploadfile;
                                unlink($uploadfile);
                            }
                        }
                        closedir($subdir);
                        rmdir($sessionupload);
                    }
                }
            }
            @rmdir($bloguploadbase);
        }
    }

    if ($oldversion < 2009081800) {
        $subscription = (object) array('plugin' => 'blog', 'event' => 'createuser', 'callfunction' => 'create_default_blog');
        ensure_record_exists('artefact_event_subscription', $subscription, $subscription);
    }

    return true;
}
