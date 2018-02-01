<?php
class ElasticsearchType_interaction_forum_post extends ElasticsearchType {
    // New style v6 mapping
    public static $mappingconfv6 = array (
            'type' => array(
                'type' => 'keyword',
            ),
            'mainfacetterm' => array (
                    'type' => 'keyword',
            ),
            'secfacetterm' => array ( // set to Forumpost
                    'type' => 'keyword',
            ),
            'id' => array (
                    'type' => 'long',
            ),
            'subject' => array (
                    'type' => 'keyword',
                    'copy_to' => 'catch_all'
            ),
            'body' => array (
                    'type' => 'text',
                    'copy_to' => 'catch_all'
            ),
            // access to forum posts is granted to all members of the group
            'access' => array (
                    'type' => 'object',
                    'properties' => array (
                            'groups' => array (
                                'type' => 'object',
                                'properties' => array (
                                    'all' => array (
                                        'type' => 'integer',
                                        'copy_to' => 'group',
                                    ),
                                    'admin' => array (
                                        'type' => 'integer',
                                        'copy_to' => 'group',
                                    ),
                                    'member' => array (
                                        'type' => 'integer',
                                        'copy_to' => 'group',
                                    ),
                                    'tutor' => array (
                                        'type' => 'integer',
                                        'copy_to' => 'group',
                                    )
                                )
                            ),
                            'group' => array (
                                    'type' => 'integer'
                            )
                    )
            ),
            'ctime' => array (
                    'type' => 'date',
                    'format' => 'YYYY-MM-dd HH:mm:ss',
            ),
            // sort is the field that will be used to sort the results alphabetically
            'sort' => array (
                    'type' => 'keyword',
            )
    );

    public static $mainfacetterm = 'Text';
    public function __construct($data) {
        $this->conditions = array (
                'deleted' => 0
        );

        $this->mapping = array (
                'mainfacetterm' => NULL,
                'secfacetterm' => NULL,
                'id' => NULL,
                'subject' => NULL,
                'body' => NULL,
                'access' => NULL,
                'ctime' => NULL,
                'sort' => NULL
        );

        parent::__construct ( $data );
    }
    public static function getRecordById($type, $id, $map = null) {
        $sql = 'SELECT p.id, p.subject, p.body, i.group, p.deleted, p.ctime
        FROM {interaction_forum_post} p
        INNER JOIN {interaction_forum_topic} t ON t.id  = p.topic
        INNER JOIN {interaction_instance} i ON i.id  = t.forum
        WHERE p.id = ?';

        $record = get_record_sql ( $sql, array (
                $id
        ) );
        if (! $record || $record->deleted) {
            return false;
        }

        $record->ctime = self::checkctime ( $record->ctime );
        $public = get_field ( 'group', 'public', 'id', $record->group );
        $record->access ['general'] = (! empty ( $public )) ? 'public' : 'none';
        $record->access ['groups'] ['member'] = $record->group;
        $record->mainfacetterm = self::$mainfacetterm;
        $record->secfacetterm = 'Forumpost';
        $record->sort = strtolower ( strip_tags ( $record->subject ) );
        return $record;
    }
    public static function getRecordDataById($type, $id) {
        $sql = 'SELECT p1.id, p1.topic, p1.parent, p1.poster, COALESCE(p1.subject, p2.subject) AS subject, p2.subject,
        p1.body, p1.ctime, p1.deleted, p1.sent, p1.path,
        u.username, u.preferredname, u.firstname, u.lastname, u.profileicon,
        f.title as forumname, f.id as forumid,
        g.name as groupname, g.id as groupid
        FROM {interaction_forum_post} p1
        LEFT JOIN {interaction_forum_post} p2 ON p2.parent IS NULL AND p2.topic = p1.topic
        LEFT JOIN {usr} u ON u.id = p1.poster
        LEFT JOIN {interaction_forum_topic} ift on p1.topic = ift.id
        LEFT JOIN {interaction_instance} f ON ift.forum = f.id AND f.plugin=\'forum\'
        LEFT JOIN {group} g ON f.group = g.id
        WHERE p1.id = ?';

        $record = get_record_sql ( $sql, array (
                $id
        ) );
        if (! $record || $record->deleted) {
            return false;
        }

        $record->body = str_replace ( array (
                "\r\n",
                "\n",
                "\r"
        ), ' ', strip_tags ( $record->body ) );
        $record->ctime = format_date ( strtotime ( $record->ctime ) );
        $record->authorlink = '<a href="' . profile_url ( $record->poster ) . '" class="forumuser">' . display_name ( $record->poster, null, true ) . '</a>';
        return $record;
    }
}
