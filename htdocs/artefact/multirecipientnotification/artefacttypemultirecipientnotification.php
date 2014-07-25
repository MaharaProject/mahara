<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-multirecipientnotification
 * @author     David Ballhausen, Tobias Zeuch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * artefacttype - plugin class. Used for corresponding with the Mahara API and containing all needed functionality for Multirecipientnotification function
 */
class ArtefactTypeMultiRecipientNotification extends ArtefactType {
    /**
     * API-Function. This Artefact does not provide any blocks or items to render
     * @see ArtefactType::render_self()
     */
    public function render_self($options) {
        return get_string('nothingtorender', 'artefact.multirecipientnotification');
    }

    /**
     * API-Function. Get Plugin-Artefacttype Icon. Here (No icon provided)
     * @param string $options
     */
    public static function get_icon($options=null) {
    }

    /**
     * API-Function. Is it possible to use more Artefacttypes than one of this type? Here: No!
     */
    public static function is_singular() {
        return true;
    }

    /**
     * API-Function: Get plugin-Artefacttype specific links: Here: None
     * @param integer $id
     */
    public static function get_links($id) {
    }
} // ArtefactTypeMultiRecipientNotification
