<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_blog_upgrade($oldversion=0) {

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

    if ($oldversion < 2011091400) {
        delete_records('artefact_cron', 'plugin', 'blog', 'callfunction', 'clean_post_files');
    }

    if ($oldversion < 2015011500) {
        delete_records('institution_config', 'field', 'progressbaritem_blog_blog');
    }

    if ($oldversion < 2015082600) {
        $records = get_records_select_array('artefact', "artefacttype = ? AND description LIKE '%artefact/file/download.php%'", array('blogpost'), 'id', 'id, description, author');
        if ($records) {
            require_once('embeddedimage.php');
            foreach ($records as $rec) {
                set_field(
                        'artefact',
                        'description',
                        EmbeddedImage::prepare_embedded_images(
                                $rec->description,
                                'blogpost',
                                $rec->id,
                                null,
                                $rec->author
                        ),
                        'id',
                        $rec->id
                );
            }
        }
    }
    return true;
}
