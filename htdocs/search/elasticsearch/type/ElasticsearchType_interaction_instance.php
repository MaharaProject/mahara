<?php
class ElasticsearchType_interaction_instance extends ElasticsearchType {
    // New style v6 mapping
    public static $mappingconfv6 = array (
            'type' => array(
                'type' => 'keyword',
            ),
            'mainfacetterm' => array (
                    'type' => 'keyword',
            ),
            'secfacetterm' => array ( // set to Forum
                    'type' => 'keyword',
            ),
            'id' => array (
                    'type' => 'long',
            ),
            'title' => array (
                    'type' => 'text',
                    'copy_to' => 'catch_all'
            ),
            'description' => array (
                    'type' => 'text',
                    'copy_to' => 'catch_all'
            ),
            // access to forum topics is granted to all members of the group
            'access' => array (
                    'type' => 'object',
                    'properties' => array (
                            'general' => array (
                                    'type' => 'keyword',
                            ),
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
                'title' => NULL,
                'description' => NULL,
                'access' => NULL,
                'ctime' => NULL,
                'sort' => NULL
        );

        parent::__construct ( $data );
    }
    public static function getRecordById($type, $id, $map = null) {
        $record = parent::getRecordById ( $type, $id );
        if (! $record || $record->deleted) {
            return false;
        }
        $public = get_field ( 'group', 'public', 'id', $record->group );
        $record->access ['general'] = (! empty ( $public )) ? 'public' : 'none';
        $record->access ['groups'] ['member'] = $record->group;
        $record->mainfacetterm = self::$mainfacetterm;
        $record->secfacetterm = 'Forum';
        $record->sort = strtolower ( strip_tags ( $record->title ) );
        return $record;
    }
    public static function getRecordDataById($type, $id) {
        $record = parent::getRecordDataById ( $type, $id );
        if (! $record || $record->deleted) {
            return false;
        }

        return $record;
    }
}
