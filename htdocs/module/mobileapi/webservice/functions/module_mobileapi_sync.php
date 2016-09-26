<?php
/**
 *
 * @package    mahara
 * @subpackage module-mobileapi
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */
if (!defined('INTERNAL')) {
    die();
}
require_once(get_config('docroot') . 'webservice/lib.php');

/**
 * Functions needed by the Mahara Mobile app. The functions in this class fetch similar data
 * to the legacy api/mobile/sync.php script.
 */
class module_mobileapi_sync extends external_api {

    public static function get_user_profileicon_parameters() {
        return new external_function_parameters(
            array(
                'maxdimension' => new external_value(PARAM_INT, "Scale icon so that height or width is this size (in px)", VALUE_DEFAULT, 0)
            )
        );
    }

    public static function get_user_profileicon_returns() {
        return new external_single_structure(
            array(
                'name' => new external_value(PARAM_RAW, "Original filename of the profile icon"),
                'desc' => new external_value(PARAM_RAW, "Descripion of the icon (usually same as filename)"),
                'mimetype' => new external_value(PARAM_RAW, "Mimetype of the file"),
                'bytes' => new external_value(PARAM_INT, "Size of the file, in bytes"),
            ),
            "Metadata about the user's current profile icon"
        );
    }

    public static function get_user_profileicon($maxdimension = 0) {
        global $USER;

        // Convert ID of user to the ID of a profileicon
        $data = get_record_sql('
            SELECT f.size, a.title, a.note, f.filetype
            FROM
                {usr} u
                JOIN {artefact_file_files} f
                    ON u.profileicon = f.artefact
                JOIN {artefact} a
                    ON a.id = u.profileicon
                    AND a.artefacttype=\'profileicon\'
            WHERE u.id = ?',
            array($USER->get('id'))
        );

        // TODO: Gravatar support
        if (!$data) {
            // No profile icon selected.
            return null;
        }

        return array(
            'name' => $data->note,
            'desc' => $data->title,
            'mimetype' => $data->filetype,
            'bytes' => (int) $data->size,
        );
    }
}