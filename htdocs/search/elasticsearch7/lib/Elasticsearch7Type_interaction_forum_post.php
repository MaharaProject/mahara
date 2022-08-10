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

class Elasticsearch7Type_interaction_forum_post extends Elasticsearch7Type {

    public static $mainfacetterm = 'Text';
    public static $secfacetterm = 'Forumpost';

    public function __construct($data) {
        $this->conditions = ['deleted' => false];

        $this->mapping = array(
            'indexsourcetype' => NULL,
            'mainfacetterm' => NULL,
            'secfacetterm' => NULL,
            'id' => NULL,
            'subject' => NULL,
            'body' => NULL,
            'owner' => NULL,
            'access' => NULL,
            'ctime' => NULL,
            'sort' => NULL
        );

        parent::__construct($data);
    }

    /**
     * Fetch a record for a forum post reply.
     *
     * These are the replies to a forum post. They can be a reply to a post or
     * a reply to a post.
     *
     * @param string $type
     * @param int $id
     * @param array<string,array<string,string>>|null $map
     *
     * @return bool|object The record or false if not found.
     */
    public static function get_record_by_id($type, $id, $map = null) {
        $sql = '
            SELECT
                p.id, p.subject, p.body,  p.deleted, p.ctime, p.parent, p.poster as owner,
                t.deleted as topic_deleted,
                f.group, f.deleted as forum_deleted,
                g.deleted as group_deleted
            FROM {interaction_forum_post} p
            INNER JOIN {interaction_forum_topic} t ON t.id  = p.topic
            INNER JOIN {interaction_instance} f ON f.id  = t.forum
            LEFT JOIN {group} g ON f.group = g.id
            WHERE p.id = ?
        ';
        $params = [$id];

        $record = get_record_sql($sql, $params);
        if (!$record || $record->deleted || $record->topic_deleted || $record->forum_deleted || $record->group_deleted) {
            return false;
        }

        // Set the Main Facet term.
        $record->mainfacetterm = self::$mainfacetterm;

        // Add the secondary facet term.
        $record->secfacetterm = self::$secfacetterm;

        // Add index source to the record.
        self::add_index_source_type_for_record($record, __CLASS__);

        // Sanity check the ctime.
        $record->ctime = self::checkctime($record->ctime);

        // Add access info.
        self::add_access_for_record($record);

        // Add sort info.
        self::add_sort_for_interaction_record($record);

        return $record;
    }

    /**
     * Set if the record has to be indexed or removed from the index.
     *
     * Check the post, topic, forum and group. If any are deleted this is
     * deleted.
     *
     * @return void
     */
    public function setIsDeleted() {
        parent::setIsDeleted();

        // Is the post deleted?
        if (
               $this->item_to_index->deleted
            || $this->item_to_index->topic_deleted
            || $this->item_to_index->forum_deleted
            || $this->item_to_index->group_deleted
        ) {
            $this->isDeleted = true;
        }
    }

    /**
     * Set the Subject to sort on.
     *
     * If the interaction has no subject check the interaction this is a reply
     * to. Continue until the subject is not empty.
     *
     * @param object $record
     *
     * @return void
     */
    public static function add_sort_for_interaction_record($record) {
        $subject = self::get_interaction_subject($record);
        $record->sort = strtolower(strip_tags($subject));
    }

    /**
     * Iterate until we can return a string.
     *
     * @param object $record
     *
     * @return string|object
     */
    private static function get_interaction_subject($record) {
        $subject = null;
        // If it has a parent, recurse back to find the subject
        if ($record->parent) {
            $parent = get_record('interaction_forum_post', 'id', $record->parent);
            return self::get_interaction_subject($parent);
        }
        else if (!empty($record->subject)) {
            $subject = $record->subject;
        }
        return $subject;
    }

    /**
     * Add Access check info to the Record.
     *
     * @param object $record The Record we are checking access for.
     *
     * @return void
     */
    public static function add_access_for_record($record) {
        $public = get_field('group', 'public', 'id', $record->group);
        $record->access['general'] = (!empty($public)) ? 'public' : 'none';
        $record->access['groups']['member'] = $record->group;
    }

    /**
     * Return the data for a single record of the specified type.
     *
     * @param string $type The type of record.
     * @param int $id      The id of the record.
     *
     * @return object|bool The record, or false if not found.
     */
    public static function get_record_data_by_id($type, $id) {
        $sql = 'SELECT p1.id, p1.topic, p1.parent, p1.poster, COALESCE(p1.subject, p2.subject) AS subject, p2.subject,
        p1.body, p1.ctime, p1.deleted, p1.sent, p1.path,
        u.username, u.preferredname, u.firstname, u.lastname, u.profileicon,
        f.title as forumname, f.id as forumid, f.deleted as forum_deleted,
        g.name as groupname, g.id as groupid, g.deleted as group_deleted,
        ift.deleted as topic_deleted
        FROM {interaction_forum_post} p1
        LEFT JOIN {interaction_forum_post} p2 ON p2.parent IS NULL AND p2.topic = p1.topic
        LEFT JOIN {usr} u ON u.id = p1.poster
        LEFT JOIN {interaction_forum_topic} ift on p1.topic = ift.id
        LEFT JOIN {interaction_instance} f ON ift.forum = f.id AND f.plugin=\'forum\'
        LEFT JOIN {group} g ON f.group = g.id
        WHERE p1.id = ?';

        $record = get_record_sql($sql, [$id]);
        if (!$record || $record->deleted || $record->topic_deleted || $record->forum_deleted || $record->group_deleted) {
            return false;
        }
        $record->body = str_replace(["\r\n","\n","\r"], ' ', strip_tags($record->body));
        $record->ctime = format_date(strtotime($record->ctime));
        $record->authorlink = '<a href="' . profile_url ( $record->poster ) . '" class="forumuser">' . display_name ( $record->poster, null, true ) . '</a>';
        return $record;
    }

    /**
     * Requeue content for indexing.
     *
     * Clears the indexing queue table for this type and reloads all usr
     * records for indexing.
     *
     * @todo requeue only $ids
     * @param array<int> $ids Optional array of IDs to restrict the action to.
     *
     * @return void
     */
    public static function requeue_searchtype_contents($ids = []) {
        $type = 'interaction_forum_post';
        parent::searchtype_contents_requeue_all($type, $ids);
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
            'subject' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
            'body' => [
                'type' => 'text',
                'copy_to' => 'catch_all',
            ],
        ];
    }

}
