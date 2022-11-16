<?php
/**
 * Outcomes categories for select options
 * @param Collection $collection
 * @return array
 */
function get_outcomes_type_options($collection) {
    $institution = get_field('group', 'institution', 'id', $collection->get('group'));

    $sql = '
        SELECT
            ot.id, ot.title
        FROM {outcome_category} oc
        INNER JOIN {outcome_type} ot ON oc.id = ot.outcome_category
        WHERE oc.institution = ?
        AND oc.id = ?
        ORDER BY ot.title ASC';
    $values = array($institution, $collection->get('outcomecategory'));
    $outcome_types = get_records_sql_array($sql, $values);
    $options = array( 0 => '');
    foreach($outcome_types as $type) {
        $options[$type->id] = $type->title;
    }
    return $options;
}

/**
  * Get outcomes form the database associated with this collection
  * @param $collectionid int
  * @return array|false
  */
function get_outcomes($collectionid) {
    return get_records_select_array('outcome', "collection = ?", array($collectionid), 'id');
}

/**
 * Creates a new outcomes pieform
 * @param string $name Name of the pieform
 * @param string $title Title to show on the form
 * @param Collection $collection The outcome collection
 * @param boolean $new If this is a new empty form or it needs default values
 * @param object $outcome Default values
 * @return string
 */
function create_outcome_form($name, $title, $collection, $new=true, $outcome=null ) {

  $options = get_outcomes_type_options($collection);

  $elements = array(
    $name =>  array(
      'type'        => 'container',
      'title'       => $title,
      'isformgroup' => false,
      'elements'    => array(
        'id'          => array(
          'type'        => 'hidden',
          'value'       => $new ? null : $outcome->id,
        ),
        'short_title' => array(
          'type'        => 'text',
          'title'       => get_string('shorttitle', 'collection'),
          'description' => get_string('shorttitledesc', 'collection'),
          'defaultvalue'=> $new ? null : $outcome->short_title,
          'size'        => 70,
        ),
        'full_title'  => array(
          'type'        => 'textarea',
          'title'       => get_string('fulltitle', 'collection'),
          'defaultvalue'=> $new ? null : $outcome->full_title,
          'rows'        => 5,
          'cols'        => 30,
        ),
        'outcome_type' => array(
          'type'         => 'select',
          'title'        => get_string('outcometype', 'collection'),
          'description'  => get_string('outcometypedesc', 'collection'),
          'options'      => $options,
          'defaultvalue' => $new ? null : $outcome->outcome_type,
        ),
      ),
    )
  );

  return pieform(array(
    'name'     => $name,
    'class'    => 'outcomeform',
    'elements' => $elements,
  ));
}