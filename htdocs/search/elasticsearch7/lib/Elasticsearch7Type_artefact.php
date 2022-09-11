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

class Elasticsearch7Type_artefact extends Elasticsearch7Type {

    /**
     * @param array<string,mixed> $mapping
     */
    var $mapping = [];

    public function __construct($data) {

        // The field mapping for this content type.
        $this->mapping = [
            'indexsourcetype' => NULL,
            'mainfacetterm' => NULL,
            'secfacetterm' => NULL,
            'id' => NULL,
            'artefacttype' => NULL,
            'title' => NULL,
            'description' => NULL,
            'tags' => NULL,
            'owner' => NULL,
            'group' => NULL,
            'institution' => NULL,
            'access' => NULL,
            'ctime' => NULL,
            'sort' => NULL,
            'license' => NULL
        ];

        parent::__construct ( $data );
    }

    /**
     * Set if the record has to be indexed or removed from the index.
     */
    public function setIsDeleted() {
        parent::setIsDeleted();

        // Artefacts: If artefacttype is not selected, mark as deleted.
        $artefacttypes = explode(',', PluginSearchElasticsearch7::config_value_or_default('artefacttypes'));
        if (!in_array($this->mapping['artefacttype'], $artefacttypes)) {
            $this->isDeleted = true;
        }
    }

    /**
     * Fetch a record for an Artifact.
     *
     * @param string $type
     * @param int $id
     * @param array<string,mixed>|null $map
     *
     * @return bool|object The record or false if not found.
     */
    public static function get_record_by_id($type, $id, $map = null) {
        $record = parent::get_record_by_id($type, $id);
        if (!$record) {
            return false;
        }

        // Add index source to the record.
        self::add_index_source_type_for_record($record, __CLASS__);

        // Add any tags on the record.
        self::add_tags_for_record($record, $type);

        // Access: get all the views where the artefact is included.
        self::add_access_for_record($record);

        // Set the main and secondary facet terms.
        self::add_facet_terms_for_record($record, $map);

        // Process the record title if it is a country.
        self::add_title_if_artefacttype_is_country($record);

        // Special processing for Résumé descriptions.
        self::add_resume_descriptions_for_record($record, $map);

        // Add sort info.
        self::add_sort_for_record($record);

        return $record;
    }


    /**
     * Return the data for a single record of the specified type.
     *
     * @todo: refactor. This is way too large.
     *
     * @param string $type The type of record.
     * @param int $id      The id of the record.
     *
     * @return object|bool The record, or false if not found.
     */
    public static function get_record_data_by_id($type, $id) {
        global $USER;

        $sql = 'SELECT  a.id, a.artefacttype, a.parent, a.owner, a.title, a.description, a.institution, a.group, a.author,
        p.artefacttype AS parent_artefacttype, p.title AS parent_title, p.description  AS parent_description, a.license,
        afi.width, afi.height, a.note
        FROM {artefact} a
        LEFT OUTER JOIN {artefact} p ON p.id = a.parent
        LEFT OUTER JOIN {artefact_file_image} afi ON afi.artefact = a.id
        WHERE a.id = ?';

        $record = get_record_sql($sql, [$id]);
        if (!$record) {
            return false;
        }

        $record->title = str_replace(
            ["\r\n","\n","\r"],
            ' ',
            strip_tags($record->title)
        );

        if ($record->artefacttype == 'country') {
            // We need to display the actual name and not the iso code.
            $record->title = get_string("country.{$record->title}");
        }
        $record->description = str_replace(
            ["\r\n","\n","\r"],
            ' ',
            strip_tags($record->description)
        );
        // If user is owner.
        if ($USER->get ( 'id' ) == $record->owner) {
            switch ($record->artefacttype) {
                case 'image' :
                case 'video' :
                case 'audio' :
                case 'file' :
                    $record->link = 'artefact/file';
                    if (isset ( $record->parent ) && intval ( $record->parent ) > 0) {
                        $record->link .= '/index.php?folder=' . $record->parent;
                    }
                    break;
                case 'blogpost' :
                    if (isset ( $record->parent ) && intval ( $record->parent ) > 0) {
                        $record->link = 'artefact/blog/view/index.php?id=' . $record->parent;
                    }
                    break;
                case 'blog' :
                    $record->link = 'artefact/blog/view/index.php';
                    if ($USER->get_account_preference ( 'multipleblogs' )) {
                        $record->link .= '?id=' . $record->id;
                    }
                    break;
                case 'coverletter' :
                case 'personalinformation' :
                    $record->link = 'artefact/resume/index.php';
                    break;
                case 'educationhistory' :
                case 'employmenthistory' :
                    $record->link = 'artefact/resume/employment.php';
                    break;
                case 'book' :
                case 'certification' :
                case 'membership' :
                    $record->link = 'artefact/resume/achievements.php';
                    break;
                case 'academicgoal' :
                case 'careergoal' :
                case 'personalgoal' :
                case 'personalinformation' :
                case 'academicskill' :
                case 'personalskill' :
                case 'workskill' :
                    $record->link = 'artefact/resume/goalsandskills.php';
                    break;
                case 'interest' :
                    $record->link = 'artefact/resume/interests.php';
                    break;
                case 'plan' :
                    $record->link = 'artefact/plans/plan/view.php?id=' . $record->id;
                    break;
                case 'task' :
                    if (isset ( $record->parent ) && intval ( $record->parent ) > 0) {
                        $record->link = 'artefact/plans/plan/view.php?id=' . $record->parent;
                    }
                    break;
            }
        }

        // The socialprofile link is for external sites so we can display it for all users.
        if ($record->artefacttype == 'socialprofile') {
            safe_require ( 'artefact', 'internal' );
            $record->note = str_replace(
                ["\r\n","\n","\r"],
                ' ',
                strip_tags($record->note)
            );
            $socialprofile = new ArtefactTypeSocialprofile ( $record->id );
            $icons = $socialprofile->get_profile_icons([$record]);
            if (!empty($icons)) {
                $icon = $icons[0];
                $record->link = $icon->link;
                $record->icon = $icon->icon;
                $record->icon_class = $icon->icon_class;
            }
            else {
                // Instantiate the attribute used by the template.
                $record->link = '';
                $record->icon = '';
                $record->icon_class = '';
            }
        }

        require_once( get_config ( 'docroot' ) . 'artefact/resume/lib.php' );
        if (PluginArtefactResume::is_active ()) {
            // If the artefacttype is one of the résumé ones we need to fetch the related item info.
            $resumetypes = PluginArtefactResume::composite_tabs ();
            if (array_key_exists ( $record->artefacttype, $resumetypes )) {
                try {
                    $query = "SELECT * FROM {artefact_resume_" . $record->artefacttype . "} WHERE artefact = ?";
                    $results = get_records_sql_assoc ( $query, array (
                        $record->id
                    ) );
                }
                catch ( SQLException $e ) {
                    // Table doesn't exist.
                    $results = array ();
                }
                $record->resumeitems = $results;
            }
        }

        // VIEWS get all the views the artefact is included into.
        // artefact parents are folder, blog, plan, cpd
        $sql = 'SELECT COALESCE(v.id, vp.id) AS id, COALESCE(v.title, vp.title) AS title
        FROM {artefact} a
        LEFT OUTER JOIN {view_artefact} va ON va.artefact = a.id
        LEFT OUTER JOIN {view} v ON v.id = va.view
        LEFT OUTER JOIN {artefact} parent ON parent.id = a.parent
        LEFT OUTER JOIN {view_artefact} vap ON vap.artefact = parent.id
        LEFT OUTER JOIN {view} vp ON vp.id = vap.view
        WHERE a.id = ?';

        $views = get_records_sql_array($sql, [$id]);

        if ($views) {
            $record_views = array ();
            foreach ($views as $view) {
                if (isset($view->id)) {
                    $record_views[$view->id] = $view->title;
                }
            }
            if (!empty($record_views)) {
                $record_views = self::views_by_artefact_acl_filter($record_views);
            }
            $record->views = $record_views;
        }

        // Tags.
        $tags = get_column('tag', 'tag', 'resourcetype', 'artefact', 'resourceid', $id);
        if ($tags != false) {
            foreach ($tags as $tag) {
                $record->tags[] = $tag;
            }
        }
        else {
            $record->tags = null;
        }

        // Created by.
        if (intval($record->author) > 0) {
            $record->createdby = get_record('usr', 'id', $record->author);
            $record->createdbyname = display_name($record->createdby);
        }

        // Thumb.
        if ($record->artefacttype == 'image' || $record->artefacttype == 'profileicon') {
            $size = '';
            if (isset ( $record->width ) && isset ( $record->height ) && intval ( $record->width ) > 0 && intval ( $record->height ) > 0) {
                if ($record->width > $record->height) {
                    $size = '30x' . intval ( $record->height * 30 / $record->width );
                }
                else {
                    $size = intval ( $record->width * 30 / $record->height ) . 'x30';
                }
            }
            $vars = array (
                'id' => $id,
                'size' => $size
            );
            if (!empty($record->views)) {
                // Use the first view we can see.
                $vars['viewid'] = key($record->views);
            }
            $record->thumb = ArtefactTypeImage::get_icon ($vars);
        }

        return $record;
    }

    /**
     * Add Access check info to the Record.
     *
     * @param object $record The Record we are checking access for.
     *
     * @return void
     */
    public static function add_access_for_record($record) {
        $access = self::get_view_access_records($record->id);
        $access_restrictions = self::add_record_access_restrictions($access);
        if (!$access) {
            // File access: get viewable group media not attached to a view.
            $groupaccess = self::group_artefact_access_records($record->id);
            if ($groupaccess) {
                foreach ( $groupaccess as $access ) {
                    $access_restrictions['groups'][$access->role][] = $access->can_view;
                }
            }
        }
        $record->access = $access_restrictions;
    }

    /**
     * Add the main and secondary facet terms for the Record.
     *
     * The values here come from the Artefact types hierarchy set on the plugin
     * config form.
     *
     * @param object $record The Record we are working with.
     * @param array<string,string>|null $map The field map array, or null if there isn't one.
     *
     * @return void
     */
    public static function add_facet_terms_for_record($record, $map) {
        if (empty($map) || !isset($map[$record->artefacttype])) {
            // Not a thing we can check.
            return;
        }
        $terms = explode("|", $map[$record->artefacttype]);
        $record->mainfacetterm = $terms[2];
        $record->secfacetterm = $terms[1];
    }

    /**
     * Update the title with the Country name if needed.
     *
     * @param object $record The Record we are working with.
     *
     * @return void
     */
    public static function add_title_if_artefacttype_is_country($record) {
        if ($record->artefacttype != 'country') {
            // Not a record we need to process.
            return;
        }

        // We need to index the actual name and not the iso code.
        $title_string = "country." . $record->title;
        $record->title = get_string($title_string);
    }

    /**
     * Add Résumé descriptions.
     *
     * If the artefacttype is one of the résumé ones we need to get the
     * description from this artefact's related résumé table. There is a
     * one -> many relationship between the artefact and the items but seen as
     * all resume items are added to a page when choosing one résumé field,
     * rather than selecting them individually, we can just blob together all
     * the info for this résumé artefact into $record->description.
     *
     * @param object $record The Record we are working with.
     * @param array<string,string>|null $map The field map array, or null if there isn't one.
     *
     * @return void
     */
    public static function add_resume_descriptions_for_record($record, $map) {
        require_once(get_config('docroot') . 'artefact/resume/lib.php');
        if (!PluginArtefactResume::is_active()) {
            // Résumé is not active.
            return;
        }

        if (empty($map) || !isset($map[$record->artefacttype])) {
            // Not a thing we can check.
            return;
        }

        $terms = explode ("|", $map[$record->artefacttype]);
        $resumetypes = PluginArtefactResume::composite_tabs();
        if (!in_array($terms[0], $resumetypes)) {
            // This record is not a résumé type.
            return;
        }

        try {
            $query = "SELECT * FROM {artefact_resume_" . $terms[0] . "} WHERE artefact = ?";
            $results = get_records_sql_assoc($query, [$record->id]);
        }
        catch ( SQLException $e ) {
            // Checking for the existence of a table is... complicated.
            // We'll just handle the SQL Exception instead. Setting $results so
            // we can continue.
            $results = [];
        }
        $descriptions = [];
        if (!empty($record->description)) {
            $descriptions = [$record->description];
        }
        foreach ($results as $result) {
            $items = get_object_vars($result);
            foreach ($items as $key => $item) {
                if (!in_array($key, ['id', 'artefact', 'displayorder'])) {
                    $descriptions[] = $item;
                }
            }
        }
        $record->description = implode(' ', $descriptions);
    }

    /**
     * Return access records of views in which the artefact is included.
     *
     * Get all access records of the views in which the artefact is included
     * A UNION with parent artefact -> files and blog posts.
     *
     * @param int $artefactid
     *
     * @return array<object>
     */
    public static function get_view_access_records($artefactid) {
        $records = get_records_sql_array(
            '
            SELECT vac.view AS view_id, vac.accesstype, vac.group, vac.role, vac.usr, vac.institution
            FROM {view_access} vac
            INNER JOIN {view_artefact} vart
                ON vac.view = vart.view
            WHERE   vart.artefact = ?
                AND (vac.startdate IS NULL OR vac.startdate < current_timestamp)
                AND (vac.stopdate IS NULL OR vac.stopdate > current_timestamp)
            UNION
            SELECT vac.view AS view_id, vac.accesstype, vac.group, vac.role, vac.usr, vac.institution
            FROM {artefact} art
            INNER JOIN {view_artefact} vart
                ON art.parent = vart.artefact
            INNER JOIN {view_access} vac
                ON vac.view = vart.view
            WHERE   art.id = ?
                AND (vac.startdate IS NULL OR vac.startdate < current_timestamp)
                AND (vac.stopdate IS NULL OR vac.stopdate > current_timestamp)
            UNION
            SELECT vac.view AS view_id, vac.accesstype, vac.group, vac.role, vac.usr, vac.institution
            FROM {artefact} art
            INNER JOIN {artefact_peer_assessment} aps
                ON aps.assessment = art.id
            INNER JOIN {view_access} vac
                ON vac.view = aps.view
            WHERE art.id = ?
                AND (vac.startdate IS NULL OR vac.startdate < current_timestamp)
                AND (vac.stopdate IS NULL OR vac.stopdate > current_timestamp)
            ',
            [
                $artefactid,
                $artefactid,
                $artefactid
            ]
        );

        $type_check = get_field_sql(
            '
            SELECT v.type FROM {view} v
            JOIN {view_artefact} va
                ON va.view = v.id
            JOIN {artefact} a
                ON a.id = va.artefact
            WHERE a.id = ?
            ',
            [$artefactid]
        );
        // We remove the 'loggedin' option from access when 'is_isolated()' is
        // set this is for sites that have gone from non-isolated to isolated
        // so there may be entries where views still have 'lggedin' as shared
        // access in database.
        if (is_isolated() && $type_check == 'profile') {
            if ($records) {
                foreach ($records as $k => $access) {
                    if ($access->accesstype == 'loggedin') {
                        unset($records[$k]);
                    }
                }
                $records = array_values($records);
            }

            // Fetch the View id.
            $viewid = get_field(
                'view_artefact',
                'view',
                'artefact',
                $artefactid
            );

            $artefact_owner_in_institution_check = get_records_sql_array(
                "
                SELECT a.owner
                FROM {artefact} a
                JOIN {usr_institution} ui
                    ON ui.usr = a.owner
                WHERE a.id = ?
                ",
                [$artefactid]
            );
            if (!$artefact_owner_in_institution_check) {
                // Member of no institution so need to add the 'mahara'
                // institution option.
                $noinst = new StdClass();
                $noinst->view_id = $viewid;
                $noinst->accesstype = null;
                $noinst->group = null;
                $noinst->role = null;
                $noinst->usr = null;
                $noinst->institution = 'mahara';
                $records[] = $noinst;
            }

            // Add admins.
            $records += self::add_admins_to_view_access_record($viewid);
        }

        return $records;
    }

    /**
     * Get all access records of the group artefacts (called if not attached to view)
     *
     * @param int $artefactid The id of the artifact.
     *
     * @return array<object>
     */
    public static function group_artefact_access_records($artefactid) {
        $records = get_records_sql_array(
            '
            SELECT role, can_view
            FROM {artefact_access_role}
            WHERE artefact = ?
            ',
            [$artefactid]
        );
        return $records;
    }

    /**
     * Get views linked to a particular artefact, applying ACL.
     *
     * This is used to display the list of views in an artefact result, because
     * it's faster to retrieve the info from Elastic search that running the
     * SQL query.
     *
     * @param array<int,mixed> $views The views we are working on.
     *
     * @return array<string,string>
     */
    public static function views_by_artefact_acl_filter($views = array()) {
        global $USER;

        $acl = new Elasticsearch7FilterAcl($USER);
        $client = PluginSearchElasticsearch7::make_client();
        $index = PluginSearchElasticsearch7::get_write_indexname();
        $filter = [];

        $viewmap = function($value) {
            return 'view' . $value;
        };

        // How to respond if there are no $views?
        $terms_ids = array_map($viewmap, array_keys($views));
        $acl_params = $acl->get_params()['should'];

        if (!empty($terms_ids)) {
            // IDs are unique. No need for '_type' => 'doc'.
            $matching = [
                'terms' => [
                    '_id' => $terms_ids,
                ],
            ];
        }

        if (!empty($acl_params)) {
            $filter["bool"]['should'] = $acl_params;
        }

        $params = [
            'index' => $index,
            'body' => [
                'size' => 10,
                'query' => [],
            ],
        ];

        if (!empty($matching) || !empty($filter)) {
            $params['body']['query']['bool'] = [];
        }

        if (!empty($matching)) {
            $params['body']['query']['bool']['must'] = $matching;
        }
        if (!empty($filter)) {
            $params['body']['query']['bool']['filter'] = $filter;
        }

        try {
            $results = $client->search($params);
        }
        catch (Exception $e) {
            log_warn($e->getMessage());
        }
        $valid = array();
        if (!empty($results['hits'])) {
            foreach ($results['hits']['hits'] as $item) {
                // Views can be empty if we are seeing our own artefact that
                // is not on a page.
                if (!empty($views[$item['_source']['id']])) {
                    $valid[$item['_source']['id']] = $views[$item['_source']['id']];
                }
            }
        }
        return $valid;
    }

    /**
     * @inheritDoc
     */
    public function field_type_map() {
        $ret = parent::field_type_map();

        $map = [
            'artefacttype' => ['type' => 'text'],
            'tags' => ['type' => 'text'],
            'tag' => ['type' => 'text'],
            'institution' => ['type' => 'text'],
            'sort' => ['type' => 'text'],
            'license' => ['type' => 'text'],
            'ctime' => [
                'type' => 'date',
                'format' => 'YYYY-MM-dd HH:mm:ss',
            ],
        ];

        return array_merge($ret, $map);
    }

    /**
     * Requeue content for indexing.
     *
     * Clears the indexing queue table for this type and reloads all artefact
     * records for indexing.
     *
     * @todo requeue only $ids
     * @param array<int> $ids Optional array of IDs to restrict the action to.
     * @param string $artefacttype The type of artefact we are requeueing.
     *
     * @return void
     */
    public static function requeue_searchtype_contents($ids = [], $artefacttype = '') {
        $type = 'artefact';
        $condition = "artefacttype IN ";

        if (!empty($artefacttype)) {
            $condition .= "('{$artefacttype}')";
        }
        else {
            $condition .= Elasticsearch7Indexing::artefacttypes_filter_string();
        }

        $delete_sql = parent::searchtype_contents_delete_sql($type) .
            " AND " . $condition;
        $insert_sql = "INSERT INTO {search_elasticsearch_7_queue} (itemid, type, artefacttype)
            SELECT id, 'artefact', artefacttype
            FROM {artefact}
            WHERE " . $condition;

        execute_sql($delete_sql);
        execute_sql($insert_sql);
    }

    /**
     * Map fields that need actions taken on them.
     *
     * Currently we list fields that are copied to the 'catch_all' field.
     *
     * @return array<array<string,string>> The property mapping.
     */
    public static function get_mapping_properties() {
        return [
            'artefacttype' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
            'title' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
            'description' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
            'tags' => [
                'type' => 'text',
            ],
            'owner' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
            'group' => [
                'type' => 'text',
            ],
            'institution' => [
                'type' => 'text',
            ],
        ];
    }

}
