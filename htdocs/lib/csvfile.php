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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();
raise_memory_limit("512M");

define('MAX_LINE_LENGTH', 1024);

/**
 * TODO: Document how this class should be used.
 */
class CsvFile {
    protected $allowedkeys = array();
    protected $data;
    protected $errors = array();
    protected $filehandle = false;
    protected $format = array();
    protected $headerExists = true;
    protected $mandatoryfields;

    public function __construct($filename = '') {
        if (!empty($filename) && file_exists($filename)) {
            if (($this->filehandle = fopen($filename, 'r')) !== false) {
                return;
            }
        }
        $this->errors['file'] = get_string('invalidfilename', 'admin', $filename);
    }

    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
        }
        return $this->{$field};
    }

    public function set($field, $value) {
        if (property_exists($this, $field)) {
            if ($this->{$field} != $value) {
                // only set it to dirty if it's changed
                $this->dirty = true;
            }
            $this->{$field} = $value;
            if ($field == 'parent') {
                $this->parentdirty = true;
            }
            $this->mtime = time();
            return true;
        }
        throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
    }

    public function get_data() {
        $csvfile = new StdClass;
        if (!empty($this->errors)) {
            $csvfile->errors = $this->errors;
            return $csvfile;
        }
        $this->parse_data();
        if ($this->filehandle !== false) {
            fclose($this->filehandle);
        }
        $csvfile->errors = $this->errors;
        if (empty($this->format) && empty($this->errors)) {
            throw new SystemException('CSV File has no headers');
        }
        else {
            $csvfile->format = $this->format;
        }
        $csvfile->data = $this->data;

        return $csvfile;
    }

    public function add_error($key, $value) {
        $this->errors[$key] = $value;
    }

    private function parse_data() {
        if (false === $this->filehandle) {
            return; // file is not open
        }

        $i = 0;
        while (($line = fgetcsv($this->filehandle, MAX_LINE_LENGTH)) !== false) {
            $i++;
            // Get the format of the file
            if ($this->headerExists && $i == 1) {
                foreach ($line as &$potentialkey) {
                    $potentialkey = trim($potentialkey);
                    if (!in_array($potentialkey, $this->allowedkeys)) {
                        $this->add_error('file', get_string('uploadcsverrorinvalidfieldname', 'admin', $potentialkey));
                        return;
                    }
                }

                // Now we know all of the field names are valid, we need to make
                // sure that the required fields are included
                foreach ($this->mandatoryfields as $field) {
                    if (!in_array($field, $line)) {
                        $this->add_error('file', get_string('uploadcsverrorrequiredfieldnotspecified', 'admin', $field));
                        return;
                    }
                }

                // The format line is valid
                $this->format = $line;
                log_info('FORMAT:');
                log_info($this->format);
            }
            else {
                // Trim non-breaking spaces -- they get left in place by File_CSV
                foreach ($line as &$field) {
                    $field = preg_replace('/^(\s|\xc2\xa0)*(.*?)(\s|\xc2\xa0)*$/', '$2', $field);
                }

                // All OK!
                $this->data[] = $line;
            }

        }

        if ($this->headerExists && $i == 1) {
            // There was only the title row :(
            $this->add_error('file', get_string('uploadcsverrornorecords', 'admin'));
            return;
        }

        if ($this->data === null) {
            // Oops! Couldn't get CSV data for some reason
            $this->add_error('file', get_string('uploadcsverrorunspecifiedproblem', 'admin'));
        }
    }

}
