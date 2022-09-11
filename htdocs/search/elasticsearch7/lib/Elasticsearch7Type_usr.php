<?php

/**
 *
 * @package    mahara
 * @subpackage search-elasticsearch
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

require_once(dirname(__FILE__) . '/Elasticsearch7Type.php');

class Elasticsearch7Type_usr extends Elasticsearch7Type {

    public static $mainfacetterm = 'User';

    public function __construct($data) {
        // The conditions for this content type to be indexed.
        $this->conditions = [
            'active' => true,
            'deleted' => false,
        ];

        // The field mapping for this content type.
        $this->mapping = [
            'indexsourcetype' => NULL,
            'mainfacetterm' => NULL,
            'id' => NULL,
            'email' => NULL,
            'username' => NULL,
            'firstname' => NULL,
            'lastname' => NULL,
            'preferredname' => NULL,
            'studentid' => NULL,
            'institutions' => NULL,
            'access' => NULL,
            'ctime' => NULL,
            'sort' => NULL
        ];

        parent::__construct($data);
    }

    /**
     * Look up the account in the database.
     *
     * @param string $type
     * @param int $id
     * @param array<string,array<string,string>>|null $map
     *
     * @return bool|object The record or false if not found.
     */
    public static function get_record_by_id($type, $id, $map = null) {
        $record = self::get_record_for_user_by_id($id);
        if (!$record || $record->deleted) {
            return false;
        }

        // Sanitize ctime.
        $record->ctime = self::checkctime($record->ctime);

        // Set the Main Facet term.
        $record->mainfacetterm = self::$mainfacetterm;

        // Add index source to the record.
        self::add_index_source_type_for_record($record, __CLASS__);

        // Add Institutions.
        self::add_institutions_for_record($record);

        // Extra e-mail addresses. Some users register several e-mail addresses
        // as an artefact.
        $record->email = [$record->email];
        self::add_emails_for_record($record);

        // Check to see if the user's profile page is viewable and which is the
        // most 'open' access.
        self::add_access_for_record($record);

        // Set sorting taking into account real name visibility preferences.
        self::add_sorting_for_record($record);

        return $record;
    }

    /**
     * Find the user we are indexing.
     *
     * If Hide Real name is set in the account preferences we set the first and
     * last names to null.
     *
     * @param int $id The Users id.
     *
     * @return object|boolean A usr table record or false if not found.
     */
    private static function get_record_for_user_by_id($id) {
        // Look up the account in the usr table. If Hide Real name is set in
        // the account preferences we set the first and last names to null.
        $sql = '
            SELECT u.id, u.username, u.preferredname, ap.value AS hidenamepref,
                CASE ap.value WHEN \'1\' THEN NULL ELSE u.firstname END AS firstname,
                CASE ap.value WHEN \'1\' THEN NULL ELSE u.lastname END AS lastname,
                u.studentid,
                u.active, u.deleted, u.email, u.ctime
            FROM {usr} u
            LEFT JOIN {usr_account_preference} ap
                ON (u.id = ap.usr AND ap.field = \'hiderealname\')
            WHERE u.id = ?
        ';

        $record = get_record_sql(
            $sql,
            [$id]
        );

        return $record;
    }

    /**
     * Add Institutions the user belongs to.
     *
     * @param object $record The Record we are working with.
     * @todo should is_isolated() be checked first?
     *
     * @return void
     */
    private static function add_institutions_for_record($record) {
        $institutions = get_records_array('usr_institution', 'usr', $record->id);

        if ($institutions != false) {
            foreach ($institutions as $institution) {
                $record->institutions[] = $institution->institution;
            }
        }
        else if (is_isolated()) {
            $record->institutions[] = 'mahara';
        }
        else {
            $record->institutions = false;
        }
    }

    /**
     * Add Access check info to the Record.
     *
     * @param object $record The Record we are checking access for.
     *
     * @return void
     */
    private static function add_access_for_record($record) {
        $access_rank = [
            'loggedin',
            'friends',
        ];

        $search_users_public = get_config('searchuserspublic');
        if ($search_users_public) {
            // User search is public. Prepend $access_rank.
            array_unshift($access_rank, 'public');
        }

        // Get all accesses of accounts's profile page ordered by the
        // $access_rank array so that the first result will be the most 'open'
        // access allowed.
        if (is_postgres()) {
            $joins = [];
            $count = 0;

            foreach ($access_rank as $key => $access) {
                $count ++;
                $joins[] = "('" . $access . "', " . $key . ")";
            }
            $join = implode(', ', $joins);

            $sql = "
                SELECT va.accesstype
                FROM {view} v, {view_access} va
                JOIN (VALUES" . $join . ") AS x (access_type, ordering)
                    ON va.accesstype = x.access_type
                WHERE v.id = va.view
                    AND v.type = 'profile'
                    AND v.owner = ?
                ORDER BY x.ordering
            ";
        }
        else {
            $join = "'" . implode("', '", $access_rank) . "'";
            $sql = "
                SELECT va.accesstype
                FROM {view} v, {view_access} va
                WHERE v.id = va.view
                    AND v.type = 'profile'
                    AND v.owner = ?
                    AND accesstype IN (" . $join . ")
                ORDER BY FIELD(va.accesstype, " . $join . ")
            ";
        }
        $profile_view_access = get_records_sql_array(
            $sql,
            [$record->id]
        );

        if (empty($profile_view_access) || is_isolated()) {
            $record->access['general'] = 'none';
            // They either have no open access or isolated institutions are on.
            // In these cases open access is not allowed. We check if they have
            // any institution rules set.
            $sql = "
                SELECT va.institution
                FROM {view} v
                JOIN {view_access} va
                    ON va.view = v.id
                WHERE v.type = 'profile'
                    AND va.institution IS NOT NULL
                    AND v.owner = ?
            ";
            $profile_view_institution = get_column_sql(
                $sql,
                [$record->id]
            );
            if ($profile_view_institution) {
                $record->access['institutions'] = $profile_view_institution;
            }

            if ($record->institutions === false) {
                $record->access['institutions'] = ['mahara'];
            }

            // Make sure site admins can still be seen by everyone.
            if (get_field('usr', 'admin', 'id', $record->id)) {
                $record->access['general'] = 'loggedin';
            }
        }
        else {
            $record->access['general'] = $profile_view_access[0]->accesstype;
        }

        // Always allow account owner to search themselves for vanity reasons
        // and allow all site admins to search them also.
        $record->access['usrs'] = array_merge(
            [$record->id],
            get_column('usr', 'id', 'admin', 1)
        );
    }

    /**
     * Add additional e-mails if there are any.
     *
     * @param object $record The Record we are working with.
     *
     * @return void
     */
    private static function add_emails_for_record($record) {
        $sql_email = "
            SELECT a.title AS email
            FROM {usr} u
            INNER JOIN {artefact} a
                ON a.owner = u.id AND a.artefacttype = 'email'
            WHERE u.email != a.title
                AND u.id = ?
                AND a.title != ?
        ";
        $records = get_recordset_sql(
            $sql_email,
            [
                $record->id,
                $record->email[0],
            ]
        );

        $emails = recordset_to_array($records);
        if ($emails != false) {
            foreach ($emails as $email) {
                $record->email[] = $email->email;
            }
        }
    }

    /**
     * Add a string for sorting on.
     *
     * This is a little more involved than most as we need to observe real name
     * visibility preferences.
     *
     * @param object $record The Record we are working with.
     *
     * @return void
     */
    private static function add_sorting_for_record($record) {
        $allow_hide_name = get_config('userscanhiderealnames');
        $show_user_name = !get_config('nousernames');
        $sort = display_name($record, null, false, !$allow_hide_name || !$record->hidenamepref, $show_user_name);
        $sort = strip_tags($sort);
        $record->sort = strtolower($sort);
    }

    /**
     * Fetch a User record for search display.
     *
     * @param string $type Unused for Users.
     * @param int $id The ID of the User record.
     *
     * @return object|false A User record for display.
     */
    public static function get_record_data_by_id($type, $id) {
        global $USER;
        $record = get_record('usr', 'id', $id);
        if (! $record || $record->deleted) {
            return false;
        }

        $record->display_name = display_name ( $record );
        $record->introduction = get_field ( 'artefact', 'description', 'owner', $id, 'artefacttype', 'introduction' );
        $record->show_masquerade = $USER->is_supportadmin_for_user($record);

        return $record;
    }

    /**
     * Requeue content for indexing.
     *
     * Clears the indexing queue table for this type and reloads all usr
     * records for indexing.
     *
     * Note: We do not use the parent::searchtype_contents_requeue_all() here
     * to avoid indexing usr with id = 0
     *
     * @param array<int> $ids Optional array of IDs to restrict the action to.
     *
     * @return void
     */
    public static function requeue_searchtype_contents($ids = []) {
        $type = 'usr';
        $delete_sql = parent::searchtype_contents_delete_sql($type, $ids);
        $insert_sql = parent::searchtype_contents_insert_sql(
            $type,
            "id != 0",
            $ids
        );

        execute_sql($delete_sql);
        execute_sql($insert_sql);
    }

    /**
     * Map fields that need actions taken on them.
     *
     * Currently we list fields that are copied to the 'catch_all' field.
     *
     * @return array<string,array<string,string>> The property mapping.
     */
    public static function get_mapping_properties() {
        return [
            'email' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
            'username' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
            'firstname' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
            'lastname' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
            'preferredname' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
            'studentid' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
            'institutions' => [
                'type' => 'text',
            ],
        ];
    }

}
