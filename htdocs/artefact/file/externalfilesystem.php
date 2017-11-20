<?php
/**
 * Interface external_file_system.
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * External file systems should use these statuses for the files.
 *
 * @see external_file_system::get_file_location_status()
 */
define('FILE_LOCATION_ERROR', -1);
define('FILE_LOCATION_LOCAL', 0);
define('FILE_LOCATION_DUPLICATED', 1);
define('FILE_LOCATION_REMOTE', 2);

/**
 * Describes an external file system class behavior.
 * All external file system modules have to implement this interface.
 */
interface external_file_system {

    /**
     * Return an external file path.
     *
     * @param object $fileartefact This is the file object.
     *
     * @return string External path to the file
     */
    public function get_path($fileartefact);

    /**
     * Check to see whether or not the external file is readable.
     *
     * @param object $fileartefact This is the file object.
     *
     * @return bool True if external file is readable, false otherwise
     */
    public function is_file_readable($fileartefact);

    /**
     * Ensure that the file is local, copies the file from external
     * to local if it is currently external.
     *
     * @param object $fileartefact This is the file object
     *
     * @return void
     */
    public function ensure_local($fileartefact);

    /**
     * Return location status of a file. The file can be in 4 states:
     *
     *  - FILE_LOCATION_ERROR - error status.
     *  - FILE_LOCATION_LOCAL - a file is stored locally only.
     *  - FILE_LOCATION_DUPLICATED - a file is stored locally and remotely.
     *  - FILE_LOCATION_REMOTE - a file is stored remotely only.
     *
     * @param object $fileartefact This is the file object.
     *
     * @return int status of file location
     */
    public function get_file_location_status($fileartefact);

    /**
     * Copies a file from and external location to a local location.
     *
     * @param object $fileartefact This is the file object.
     *
     * @return int status of final file location
     */
    public function copy_file_from_external_to_local($fileartefact);

    /**
     * Copies a file from a local location to an external location.
     *
     * @param object $fileartefact This is the file object.
     *
     * @return int status of final file location
     */
    public function copy_file_from_local_to_external($fileartefact);

    /**
     * Delete the file.
     *
     * @param object $fileartefact This is the file object.
     *
     * @return int status of final file location
     */
    public function delete_file($fileartefact);

    /**
     * Returns a file pointer resource of stream type.
     *
     * @param string $path File path.
     * @param string $mode Parameter specifies the type of access you require to the stream. E.g. "r", "rb".
     *
     * @return resource
     */
    public function get_file_handle($path, $mode);
}
