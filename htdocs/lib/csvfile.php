<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

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
        $csvfile = new stdClass();
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

        $delimiter = $this->detectDelimiter();
        $i = 0;
        while (($line = fgetcsv($this->filehandle, MAX_LINE_LENGTH, $delimiter)) !== false) {
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
            $this->add_error('file', get_string('uploadcsverrorunspecifiedproblem1', 'admin'));
        }
    }

    /**
     * detect the delimiter using the first line that should consist only of
     * the header fields, which strictly consist of the characters [a-zA-Z0-9_]
     * so the known delimiters (so far comma and semicolon) don't appear in those
     * fields. <br/>
     * Background is that Microsoft separates the fields in csv-files with
     * semicolons when the System language is set to German
     * @return string the delimiter used to separate the fields in the file
     */
    private function detectDelimiter() {
        static $knowndelimiters = array(
            ',',
            ';',
            ':',
            "\t",
            ' '
        );
        $firstline = fgets($this->filehandle);
        fseek($this->filehandle, 0);
        foreach ($knowndelimiters as $delimiter) {
            if (strpos($firstline, $delimiter) > 0) {
                return $delimiter;
            }
        }
        // Default: the comma. In case we have a file with only one field per
        // line, we cannot detect the delimiter. Luckily Mahara always expects
        // more than one mandatory fields, so getting here usually means the
        // file cannot be imported anyway
        return ',';
    }
}

class CSVErrors {

    private $csverrors = array();

    function add($line, $msg) {
        if (!isset($this->csverrors[$line])) {
            $this->csverrors[$line] = array();
        }
        $this->csverrors[$line][] = $msg;
    }

    function process() {
        if (empty($this->csverrors)) {
            return;
        }
        ksort($this->csverrors);
        $errorstring = implode("<br>\n", array_shift($this->csverrors));
        while ($lineerrors = array_shift($this->csverrors)) {
            $errorstring .= "<br>\n" . implode("<br>\n", $lineerrors);
        }
        return $errorstring;
    }

}
