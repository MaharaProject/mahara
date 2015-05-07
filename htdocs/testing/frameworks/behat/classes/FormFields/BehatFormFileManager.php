<?php
/**
 * @package    mahara
 * @subpackage test/behat
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from Moodle Behat, 2013 David Monllaó
 *
 */

require_once(__DIR__ . '/BehatFormField.php');

/**
 * File manager form field.
 *
 * Simple filemanager field manager to allow
 * forms to be filled using TableNodes. It only
 * adds files and checks the field contents in the
 * root directory. If you want to run complex actions
 * that involves subdirectories or other repositories
 * than 'Upload a file' you should use steps related with
 * behat_filepicker::i_add_file_from_repository_to_filemanager
 * this is intended to be used with multi-field
 *
 * This field manager allows you to:
 * - Get: A comma-separated list of the root directory
 *   file names, including folders.
 * - Set: Add a file, in case you want to add more than
 *     one file you can always set two table rows using
 *     the same locator.
 * - Match: A comma-separated list of file names.
 *
 * @package    core_form
 * @category   test
 * @copyright  2014 David Monllaó
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class BehatFormFilemanager extends BehatFormField {

    /**
     * Gets the value.
     *
     * @return string A comma-separated list of the root directory file names.
     */
    public function get_value() {

        // Wait until DOM and JS is ready.
        $this->session->wait(BehatBase::TIMEOUT, BehatBase::PAGE_READY_JS);

        // Get the label to restrict the files to this single form field.
        $fieldlabel = $this->get_field_locator();

        // Get the name of the current directory elements.
        $xpath = "//label[contains(., '" . $fieldlabel . "')]" .
            "/ancestor::div[contains(concat(' ', normalize-space(@class), ' '), ' fitemtitle ')]" .
            "/following-sibling::div[contains(concat(' ', normalize-space(@class), ' '), ' ffilemanager ')]" .
            "/descendant::div[contains(concat(' ', normalize-space(@class), ' '), ' fp-filename ')]";

        // We don't need to wait here, also we don't have access to protected
        // contexts find* methods.
        $files = $this->session->getPage()->findAll('xpath', $xpath);

        if (!$files) {
            return '';
        }

        $filenames = array();
        foreach ($files as $filenode) {
            $filenames[] = $filenode->getText();
        }

        return implode(',', $filenames);
    }

    /**
     * Sets the field value.
     *
     * @param string $value
     * @return void
     */
    public function set_value($value) {

        // Getting the filemanager label from the DOM.
        $fieldlabel = $this->get_field_locator();

        // Getting the filepicker context and using the step definition
        // to upload the requested file.
        $uploadcontext = BehatContextHelper::get('behat_repository_upload');
        $uploadcontext->i_upload_file_to_filemanager($value, $fieldlabel);
    }

    /**
     * Matches the provided filename/s against the current field value.
     *
     * If the filemanager contains more than one file the $expectedvalue
     * value should include all the file names separating them by comma.
     *
     * @param string $expectedvalue
     * @return bool The provided value matches the field value?
     */
    public function matches($expectedvalue) {
        return $this->text_matches($expectedvalue);
    }

}
