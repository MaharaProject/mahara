<?php
class ElasticsearchType_interaction_instance extends ElasticsearchType {
    public static $mappingconf = array (
            'mainfacetterm' => array (
                    'type' => 'keyword',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'secfacetterm' => array ( // set to Forum
                    'type' => 'keyword',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'id' => array (
                    'type' => 'long',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
            ),
            'title' => array (
                    'type' => 'text',
                    'include_in_all' => TRUE
            ),
            'description' => array (
                    'type' => 'text',
                    'include_in_all' => TRUE
            ),
            // access to forum topics is granted to all members of the group
            'access' => array (
                    'type' => 'object',
                    'include_in_all' => FALSE,
                    'properties' => array (
                            'general' => array (
                                    'type' => 'keyword',
                                    'index' => 'not_analyzed',
                                    'include_in_all' => FALSE
                            ),
                            'groups' => array (
                                    'type' => 'integer',
                                    'index' => 'not_analyzed',
                                    'copy_to' => 'group',
                                    'include_in_all' => false
                            ),
                            'group' => array (
                                    'type' => 'integer'
                            )
                    )
            ),
            'ctime' => array (
                    'type' => 'date',
                    'format' => 'YYYY-MM-dd HH:mm:ss',
                    'include_in_all' => FALSE
            ),
            // sort is the field that will be used to sort the results alphabetically
            'sort' => array (
                    'type' => 'keyword',
                    'index' => 'not_analyzed',
                    'include_in_all' => FALSE
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
