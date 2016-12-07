<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_file_upgrade($oldversion=0) {

    $status = true;

    if ($oldversion < 2009033000) {
        if (!get_record('artefact_config', 'plugin', 'file', 'field', 'uploadagreement')) {
            insert_record('artefact_config', (object) array('plugin' => 'file', 'field' => 'uploadagreement', 'value' => 1));
            insert_record('artefact_config', (object) array('plugin' => 'file', 'field' => 'usecustomagreement', 'value' => 1));
        }
    }

    if ($oldversion < 2009091700) {
        execute_sql("DELETE FROM {artefact_file_files} WHERE artefact IN (SELECT id FROM {artefact} WHERE artefacttype = 'folder')");
    }

    if ($oldversion < 2009091701) {
        $table = new XMLDBTable('artefact_file_files');
        $key = new XMLDBKey('artefactpk');
        $key->setAttributes(XMLDB_KEY_PRIMARY, array('artefact'));
        add_key($table, $key);

        $table = new XMLDBTable('artefact_file_image');
        $key = new XMLDBKey('artefactpk');
        $key->setAttributes(XMLDB_KEY_PRIMARY, array('artefact'));
        add_key($table, $key);
    }

    if ($oldversion < 2009092300) {
        insert_record('artefact_installed_type', (object) array('plugin' => 'file', 'name' => 'archive'));
        // update old files
        if (function_exists('zip_open')) {
            $files = get_records_select_array('artefact_file_files', "filetype IN ('application/zip', 'application/x-zip')");
            if ($files) {
                $checked = array();
                foreach ($files as $file) {
                    $path = get_config('dataroot') . 'artefact/file/originals/' . ($file->fileid % 256) . '/' . $file->fileid;
                    $zip = zip_open($path);
                    if (is_resource($zip)) {
                        $checked[] = $file->artefact;
                        zip_close($zip);
                    }
                }
                if (!empty($checked)) {
                    set_field_select('artefact', 'artefacttype', 'archive', "artefacttype = 'file' AND id IN (" . join(',', $checked) . ')', array());
                }
            }
        }
    }

    if ($oldversion < 2010012702) {
        if ($records = get_records_sql_array("SELECT * FROM {artefact_file_files} WHERE filetype='application/octet-stream'", array())) {
            require_once('file.php');
            foreach ($records as &$r) {
                $path = get_config('dataroot') . 'artefact/file/originals/' . $r->fileid % 256 . '/' . $r->fileid;
                set_field('artefact_file_files', 'filetype', file_mime_type($path), 'fileid', $r->fileid, 'artefact', $r->artefact);
            }
        }
    }

    if ($oldversion < 2011052500) {
        // Set default quota to 50MB
        set_config_plugin('artefact', 'file', 'defaultgroupquota', 52428800);
    }

    if ($oldversion < 2011070700) {

        // Create an images folder for everyone with a profile icon
        $imagesdir = get_string('imagesdir', 'artefact.file');
        $imagesdirdesc = get_string('imagesdirdesc', 'artefact.file');

        execute_sql("
            INSERT INTO {artefact} (artefacttype, container, owner, ctime, mtime, atime, title, description, author)
            SELECT 'folder', 1, owner, current_timestamp, current_timestamp, current_timestamp, ?, ?, owner
            FROM {artefact} WHERE owner IS NOT NULL AND artefacttype = 'profileicon'
            GROUP BY owner",
            array($imagesdir, $imagesdirdesc)
        );

        // Put profileicons into the images folder and update the description
        $profileicondesc = get_string('uploadedprofileicon', 'artefact.file');

        if (is_postgres()) {
            execute_sql("
                UPDATE {artefact}
                SET parent = f.folderid, description = ?
                FROM (
                    SELECT owner, MAX(id) AS folderid
                    FROM {artefact}
                    WHERE artefacttype = 'folder' AND title = ? AND description = ?
                    GROUP BY owner
                ) f
                WHERE artefacttype = 'profileicon' AND {artefact}.owner = f.owner",
                array($profileicondesc, $imagesdir, $imagesdirdesc)
            );
        }
        else {
            execute_sql("
                UPDATE {artefact}, (
                    SELECT owner, MAX(id) AS folderid
                    FROM {artefact}
                    WHERE artefacttype = 'folder' AND title = ? AND description = ?
                    GROUP BY owner
                ) f
                SET parent = f.folderid, description = ?
                WHERE artefacttype = 'profileicon' AND {artefact}.owner = f.owner",
                array($imagesdir, $imagesdirdesc, $profileicondesc)
            );
        }
    }

    if ($oldversion < 2011082200) {
        // video file type
        if (!get_record('artefact_installed_type', 'plugin', 'file', 'name', 'video')) {
            insert_record('artefact_installed_type', (object) array('plugin' => 'file', 'name' => 'video'));
        }

        // update existing records
        $videotypes = get_records_sql_array('
            SELECT DISTINCT description
            FROM {artefact_file_mime_types}
            WHERE mimetype ' . db_ilike() . ' \'%video%\'', array());
        if ($videotypes) {
            $mimetypes = array();
            foreach ($videotypes as $type) {
                $mimetypes[] = $type->description;
            }
            $files = get_records_sql_array('
                SELECT *
                FROM {artefact_file_files}
                WHERE filetype IN (
                    SELECT mimetype
                    FROM {artefact_file_mime_types}
                    WHERE description IN (' . join(',', array_map('db_quote', array_values($mimetypes))) . ')
                )', array());
            if ($files) {
                $checked = array();
                foreach ($files as $file) {
                    $checked[] = $file->artefact;
                }
                if (!empty($checked)) {
                    set_field_select('artefact', 'artefacttype', 'video', "artefacttype = 'file' AND id IN (" . join(',', $checked) . ')', array());
                }
            }
        }

        // audio file type
        if (!get_record('artefact_installed_type', 'plugin', 'file', 'name', 'audio')) {
            insert_record('artefact_installed_type', (object) array('plugin' => 'file', 'name' => 'audio'));
        }

        // update existing records
        $audiotypes = get_records_sql_array('
            SELECT DISTINCT description
            FROM {artefact_file_mime_types}
            WHERE mimetype ' . db_ilike() . ' \'%audio%\'', array());
        if ($audiotypes) {
            $mimetypes = array();
            foreach ($audiotypes as $type) {
                $mimetypes[] = $type->description;
            }
            $files = get_records_sql_array('
                SELECT *
                FROM {artefact_file_files}
                WHERE filetype IN (
                    SELECT mimetype
                    FROM {artefact_file_mime_types}
                    WHERE description IN (' . join(',', array_map('db_quote', array_values($mimetypes))) . ')
                 )', array());
             if ($files) {
                 $checked = array();
                 foreach ($files as $file) {
                     $checked[] = $file->artefact;
                 }
                 if (!empty($checked)) {
                     set_field_select('artefact', 'artefacttype', 'audio', "artefacttype = 'file' AND id IN (" . join(',', $checked) . ')', array());
                }
            }
        }
    }

    if ($oldversion < 2012050400) {
        if (!get_record('artefact_config', 'plugin', 'file', 'field', 'resizeonuploadenable')) {
            insert_record('artefact_config', (object) array('plugin' => 'file', 'field' => 'resizeonuploadenable', 'value' => 0));
            insert_record('artefact_config', (object) array('plugin' => 'file', 'field' => 'resizeonuploaduseroption', 'value' => 0));
            insert_record('artefact_config', (object) array('plugin' => 'file', 'field' => 'resizeonuploadmaxheight', 'value' => get_config('imagemaxheight')));
            insert_record('artefact_config', (object) array('plugin' => 'file', 'field' => 'resizeonuploadmaxwidth', 'value' => get_config('imagemaxwidth')));
        }
    }

    if ($oldversion < 2012092400) {
        $basepath = get_config('dataroot') . "artefact/file/originals/";
        try {
            check_dir_exists($basepath, true);
        }
        catch (Exception $e) {
            throw new SystemException("Failed to create " . $basepath);
        }
        $baseiter = new DirectoryIterator($basepath);
        foreach ($baseiter as $dir) {
            if ($dir->isDot()) continue;
            $dirpath = $dir->getPath() . '/' . $dir->getFilename();
            $fileiter = new DirectoryIterator($dirpath);
            foreach ($fileiter as $file) {
                if ($file->isDot()) continue;
                if (!$file->isFile()) {
                    log_error("Something was wrong about the dataroot in artefact/file/originals/$dir. Unexpected folder $file");
                    continue;
                }
                chmod($file->getPathname(), $file->getPerms() & 0666);
            }
        }
    }

    if ($oldversion < 2013031200) {
        // Update MIME types for Microsoft video files: avi, asf, wm, and wmv
        update_record('artefact_file_mime_types', (object) array('mimetype' => 'video/x-ms-asf', 'description' => 'asf'), (object) array('mimetype' => 'video/x-ms-asf'));
        update_record('artefact_file_mime_types', (object) array('mimetype' => 'video/x-ms-wm', 'description' => 'wm'), (object) array('mimetype' => 'video/x-ms-wm'));
        update_record('artefact_file_mime_types', (object) array('mimetype' => 'video/x-ms-wmv', 'description' => 'wmv'), (object) array('mimetype' => 'video/x-ms-wmv'));
    }

    if ($oldversion < 2014040800) {
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'audio/aac'), (object) array('mimetype' => 'audio/aac', 'description' => 'aac'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/msaccess'), (object) array('mimetype' => 'application/msaccess', 'description' => 'accdb'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'shockwave/director'), (object) array('mimetype' => 'shockwave/director', 'description' => 'cct'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/x-csh'), (object) array('mimetype' => 'application/x-csh', 'description' => 'cs'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'text/css'), (object) array('mimetype' => 'text/css', 'description' => 'css'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'text/csv'), (object) array('mimetype' => 'text/csv', 'description' => 'csv'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'video/x-dv'), (object) array('mimetype' => 'video/x-dv', 'description' => 'dv'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'), (object) array('mimetype' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'description' => 'docx'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.ms-word.document.macroEnabled.12'), (object) array('mimetype' => 'application/vnd.ms-word.document.macroEnabled.12', 'description' => 'docm'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template'), (object) array('mimetype' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template', 'description' => 'dotx'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.ms-word.template.macroEnabled.12'), (object) array('mimetype' => 'application/vnd.ms-word.template.macroEnabled.12', 'description' => 'dotm'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/x-director'), (object) array('mimetype' => 'application/x-director', 'description' => 'dcr'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/epub+zip'), (object) array('mimetype' => 'application/epub+zip', 'description' => 'epub'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/x-smarttech-notebook'), (object) array('mimetype' => 'application/x-smarttech-notebook', 'description' => 'gallery'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/mac-binhex40'), (object) array('mimetype' => 'application/mac-binhex40', 'description' => 'hqx'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'text/x-component'), (object) array('mimetype' => 'text/x-component', 'description' => 'htc'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/xhtml+xml'), (object) array('mimetype' => 'application/xhtml+xml', 'description' => 'xhtml'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'image/vnd.microsoft.icon'), (object) array('mimetype' => 'image/vnd.microsoft.icon', 'description' => 'ico'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'text/calendar'), (object) array('mimetype' => 'text/calendar', 'description' => 'ics'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/inspiration'), (object) array('mimetype' => 'application/inspiration', 'description' => 'isf'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/inspiration.template'), (object) array('mimetype' => 'application/inspiration.template', 'description' => 'ist'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/java-archive'), (object) array('mimetype' => 'application/java-archive', 'description' => 'jar'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/x-java-jnlp-file'), (object) array('mimetype' => 'application/x-java-jnlp-file', 'description' => 'jnlp'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.moodle.backup'), (object) array('mimetype' => 'application/vnd.moodle.backup', 'description' => 'mbz'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/x-msaccess'), (object) array('mimetype' => 'application/x-msaccess', 'description' => 'mdb'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'message/rfc822'), (object) array('mimetype' => 'message/rfc822', 'description' => 'mht'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.moodle.profiling'), (object) array('mimetype' => 'application/vnd.moodle.profiling', 'description' => 'mpr'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.oasis.opendocument.graphics-template'), (object) array('mimetype' => 'application/vnd.oasis.opendocument.graphics-template', 'description' => 'otg'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.oasis.opendocument.presentation-template'), (object) array('mimetype' => 'application/vnd.oasis.opendocument.presentation-template', 'description' => 'otp'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.oasis.opendocument.spreadsheet-template'), (object) array('mimetype' => 'application/vnd.oasis.opendocument.spreadsheet-template', 'description' => 'ots'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'audio/ogg'), (object) array('mimetype' => 'audio/ogg', 'description' => 'oga'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'video/ogg'), (object) array('mimetype' => 'video/ogg', 'description' => 'ogv'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'image/pict'), (object) array('mimetype' => 'image/pict', 'description' => 'pct'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'), (object) array('mimetype' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'description' => 'pptx'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12'), (object) array('mimetype' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12', 'description' => 'pptm'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.openxmlformats-officedocument.presentationml.template'), (object) array('mimetype' => 'application/vnd.openxmlformats-officedocument.presentationml.template', 'description' => 'potx'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.ms-powerpoint.template.macroEnabled.12'), (object) array('mimetype' => 'application/vnd.ms-powerpoint.template.macroEnabled.12', 'description' => 'potm'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12'), (object) array('mimetype' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12', 'description' => 'ppam'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow'), (object) array('mimetype' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow', 'description' => 'ppsx'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12'), (object) array('mimetype' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12', 'description' => 'ppsm'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'audio/x-realaudio-plugin'), (object) array('mimetype' => 'audio/x-realaudio-plugin', 'description' => 'ra'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'audio/x-pn-realaudio-plugin'), (object) array('mimetype' => 'audio/x-pn-realaudio-plugin', 'description' => 'ram'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.rn-realmedia-vbr'), (object) array('mimetype' => 'application/vnd.rn-realmedia-vbr', 'description' => 'rmvb'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'text/richtext'), (object) array('mimetype' => 'text/richtext', 'description' => 'rtx'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/x-stuffit'), (object) array('mimetype' => 'application/x-stuffit', 'description' => 'sit'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/smil'), (object) array('mimetype' => 'application/smil', 'description' => 'smi'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'image/svg+xml'), (object) array('mimetype' => 'image/svg+xml', 'description' => 'svg'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.sun.xml.writer'), (object) array('mimetype' => 'application/vnd.sun.xml.writer', 'description' => 'sxw'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.sun.xml.writer.template'), (object) array('mimetype' => 'application/vnd.sun.xml.writer.template', 'description' => 'stw'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.sun.xml.calc'), (object) array('mimetype' => 'application/vnd.sun.xml.calc', 'description' => 'sxc'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.sun.xml.calc.template'), (object) array('mimetype' => 'application/vnd.sun.xml.calc.template', 'description' => 'stc'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.sun.xml.draw'), (object) array('mimetype' => 'application/vnd.sun.xml.draw', 'description' => 'sxd'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.sun.xml.draw.template'), (object) array('mimetype' => 'application/vnd.sun.xml.draw.template', 'description' => 'std'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.sun.xml.impress'), (object) array('mimetype' => 'application/vnd.sun.xml.impress', 'description' => 'sxi'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.sun.xml.impress.template'), (object) array('mimetype' => 'application/vnd.sun.xml.impress.template', 'description' => 'sti'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.sun.xml.writer.global'), (object) array('mimetype' => 'application/vnd.sun.xml.writer.global', 'description' => 'sxg'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.sun.xml.math'), (object) array('mimetype' => 'application/vnd.sun.xml.math', 'description' => 'sxm'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'image/tiff'), (object) array('mimetype' => 'image/tiff', 'description' => 'tif'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/x-tex'), (object) array('mimetype' => 'application/x-tex', 'description' => 'tex'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/x-texinfo'), (object) array('mimetype' => 'application/x-texinfo', 'description' => 'texi'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'text/tab-separated-values'), (object) array('mimetype' => 'text/tab-separated-values', 'description' => 'tsv'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'video/webm'), (object) array('mimetype' => 'video/webm', 'description' => 'webm'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.ms-excel'), (object) array('mimetype' => 'application/vnd.ms-excel', 'description' => 'xls'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'), (object) array('mimetype' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'description' => 'xlsx'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.ms-excel.sheet.macroEnabled.12'), (object) array('mimetype' => 'application/vnd.ms-excel.sheet.macroEnabled.12', 'description' => 'xlsm'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template'), (object) array('mimetype' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template', 'description' => 'xltx'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.ms-excel.template.macroEnabled.12'), (object) array('mimetype' => 'application/vnd.ms-excel.template.macroEnabled.12', 'description' => 'xltm'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12'), (object) array('mimetype' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12', 'description' => 'xlsb'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/vnd.ms-excel.addin.macroEnabled.12'), (object) array('mimetype' => 'application/vnd.ms-excel.addin.macroEnabled.12', 'description' => 'xlam'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'application/xml'), (object) array('mimetype' => 'application/xml', 'description' => 'xml'));
    }

    if ($oldversion < 2014051200) {
        require_once(get_config('docroot') . '/lib/file.php');
        $mimetypes = get_records_assoc('artefact_file_mime_types', '', '', '', 'description,mimetype');

        // Re-examine only those files where their current identified mimetype is
        // different from how we would identify their mimetype based on file extension
        $rs = get_recordset_sql('
            select a.id, aff.oldextension, aff.filetype
            from
                {artefact} a
                inner join {artefact_file_files} aff
                    on a.id = aff.artefact
            where a.artefacttype = \'archive\'
                and not exists (
                    select 1 from {artefact_file_mime_types} afmt
                    where afmt.description = aff.oldextension
                    and afmt.mimetype = aff.filetype
                )
            order by a.id
        ');

        $total = 0;
        $done = 0;

        while ($zf = $rs->FetchRow()) {
            if ($done % 100 == 0) {
                log_debug('Verifying filetypes: ' . $done . '/' . $rs->RecordCount());
            }
            $done++;
            $file = artefact_instance_from_id($zf['id']);
            $path = $file->get_path();

            // Check what our improved file detection system thinks it is
            $guess = file_mime_type($path, 'foo.' . $zf['oldextension']);

            if ($guess != 'application/octet-stream') {
                $data = new stdClass();
                $data->filetype = $data->guess = $guess;
                foreach (array('video', 'audio', 'archive') as $artefacttype) {
                    $classname = 'ArtefactType' . ucfirst($artefacttype);
                    if (call_user_func_array(array($classname, 'is_valid_file'), array($file->get_path(), &$data))) {
                        set_field('artefact', 'artefacttype', $artefacttype, 'id', $zf['id']);
                        set_field('artefact_file_files', 'filetype', $data->filetype, 'artefact', $zf['id']);
                        continue 2;
                    }
                }

                // It wasn't any of those special ones, so just make it a normal file artefact
                set_field('artefact', 'artefacttype', 'file', 'id', $zf['id']);
                set_field('artefact_file_files', 'filetype', $data->filetype, 'artefact', $zf['id']);
            }
        }
        log_debug('Verifying filetypes: ' . $done . '/' . $rs->RecordCount());
        $rs->Close();
    }

    if ($oldversion < 2014060900) {
        $events = array(
            (object)array(
                'plugin'        => 'file',
                'event'         => 'saveartefact',
                'callfunction'  => 'eventlistener_savedeleteartefact',
            ),
            (object)array(
                'plugin'        => 'file',
                'event'         => 'deleteartefact',
                'callfunction'  => 'eventlistener_savedeleteartefact',
            ),
            (object)array(
                'plugin'        => 'file',
                'event'         => 'deleteartefacts',
                'callfunction'  => 'eventlistener_savedeleteartefact',
            ),
            (object)array(
                'plugin'        => 'file',
                'event'         => 'updateuser',
                'callfunction'  => 'eventlistener_savedeleteartefact',
            ),
        );
        foreach ($events as $event) {
            ensure_record_exists('artefact_event_subscription', $event, $event);
        }
        PluginArtefactFile::set_quota_triggers();
    }

    if ($oldversion < 2014061000) {
        // Remove the not needed quota notify on update config trigger from previous update
        if (is_postgres()) {
            $sql = 'DROP TRIGGER IF EXISTS {unmark_quota_exeed_notified_on_update_setting_trigger} ON {artefact_config};';
        }
        else {
            $sql = 'DROP TRIGGER IF EXISTS {unmark_quota_exeed_notified_on_update_setting_trigger};';
        }
        execute_sql($sql);
    }

    if ($oldversion < 2014111200) {
        // Create embedded images table
        $table = new XMLDBTable('artefact_file_embedded');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('fileid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addFieldInfo('resourcetype', XMLDB_TYPE_CHAR, '100', XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('resourceid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('artefactfk', XMLDB_KEY_FOREIGN, array('fileid'), 'artefact', array('id'));

        $status = $status && create_table($table);
    }

    if ($oldversion < 2015101900) {
        log_debug('Need to consolidate "textbox" and "editnote" embedded resource types as they are in fact the same thing');
        if ($records = get_records_sql_array('SELECT * FROM {artefact_file_embedded} WHERE resourcetype IN (?, ?)', array('editnote','textbox'))) {
            $newrecords = array();
            // Turn the results into something easier to check against
            foreach ($records as $k => $v) {
                $newrecords[$v->resourcetype . '_' . $v->resourceid . '_' . $v->fileid] = $v;
            }

            foreach ($newrecords as $nk => $nv) {
                // need to sort out the 'editnote' options
                if (preg_match('/^editnote_(.*)$/', $nk, $match)) {
                    // Check to see if there is a corresponding 'textbox' one - if not we need to make one
                    if (!array_key_exists('textbox_' . $match[1], $newrecords)) {
                        insert_record('artefact_file_embedded', (object) array(
                            'fileid' => $nv->fileid,
                            'resourcetype' => 'textbox',
                            'resourceid' => $nv->resourceid,
                        ));
                    }
                    // now delete the 'editnote' one
                    delete_records('artefact_file_embedded', 'id', $nv->id);
                }
            }
        }
    }

    if ($oldversion < 2016082900) {
        log_debug('Drop the old primary key constraint and add new id column from/to the table artefact_file_mime_types');
        if (is_postgres()) {
            execute_sql('ALTER TABLE {artefact_file_mime_types}
                            DROP CONSTRAINT IF EXISTS {artefilemimetype_mim_pk},
                            ADD COLUMN id SERIAL PRIMARY KEY');
        }
        else if (is_mysql()) {
            execute_sql('ALTER TABLE {artefact_file_mime_types}
                            DROP PRIMARY KEY,
                            ADD id INT AUTO_INCREMENT NOT NULL,
                            ADD PRIMARY KEY ( id )');
        }


        log_debug('Update MIME types for more media files');
        // 1. OGG See more https://wiki.xiph.org/index.php/MIME_Types_and_File_Extensions
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'audio/ogg'), (object) array('mimetype' => 'audio/ogg', 'description' => 'ogg'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'audio/ogg'), (object) array('mimetype' => 'audio/ogg', 'description' => 'oga'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'video/ogg'), (object) array('mimetype' => 'video/ogg', 'description' => 'ogv'));
        // 2. 3GPP - https://en.wikipedia.org/wiki/3GP_and_3G2
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'audio/3gpp'), (object) array('mimetype' => 'audio/3gpp', 'description' => '3gp'));
        ensure_record_exists('artefact_file_mime_types', (object) array('mimetype' => 'video/3gpp'), (object) array('mimetype' => 'video/3gpp', 'description' => '3gp'));

        log_debug('Add an index of mimetype to the table artefact_file_mime_types');
        execute_sql('CREATE INDEX mimetypeix ON {artefact_file_mime_types} (mimetype)');
    }

    if ($oldversion < 2016082901) {
        log_debug('Recreate artefact_file_mime_types table');

        $table = new XMLDBTable('artefact_file_mime_types');
        drop_table($table);

        $table = new XMLDBTable('artefact_file_mime_types');
        $table->addFieldInfo('mimetype', XMLDB_TYPE_CHAR, 128, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addFieldInfo('description', XMLDB_TYPE_CHAR, 32, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('mimetype, description'));
        create_table($table);

        log_debug('Update config record for mp4 internalmedia type');

        if ($data = get_config_plugin('blocktype', 'internalmedia', 'enabledtypes')) {
            $data = unserialize($data);
            $key = array_search('mp4_video',$data);

            if (!empty($key)) {
                $data[$key] = 'mp4';
                set_config_plugin('blocktype', 'internalmedia', 'enabledtypes', serialize($data));
            }
        }
    }

    return $status;
}
