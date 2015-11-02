<?php
require_once(get_config('docroot') . 'lib/form/elements/autocomplete.php');
/**
 *
 * @package    mahara
 * @subpackage form-element
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Provides a tag input field
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The HTML for the element
 */
function pieform_element_tags(Pieform $form, $element) {

    $newelement = array(
        'type' => 'autocomplete',
        'title' => $element['title'],
        'id' => $element['id'],
        'name' => $element['name'],
        'defaultvalue' => isset($element['defaultvalue']) ? $element['defaultvalue'] : null,
        'description' => isset($element['description']) ? $element['description'] : null,
        'help' => isset($element['help']) ? $element['help'] : false,
        'ajaxurl' => get_config('wwwroot') . 'json/taglist.php',
        'multiple' => true,
        'allowclear' => false,
        'initfunction' => 'translate_tags_to_names',
        'ajaxextraparams' => array(),
        'extraparams' => array('tags' => true),
        'width' => '280px',
    );
    return pieform_element_autocomplete($form, $newelement);
}

function translate_tags_to_names(array $ids) {
    global $USER;
    // for an empty list, the element '' is transmitted
    $ids = array_diff($ids, array(''));
    $results = array();
    $alltags = get_all_tags_for_user();

    foreach ($ids as $id) {
        if (isset($alltags['tags'][$id])) {
            $results[] = (object) array('id' => $id, 'text' => display_tag($id, $alltags['tags']));
        }
        else {
            $results[] = (object) array('id' => $id, 'text' => hsc($id));
        }
    }
    return $results;
}

/**
 * Display formatted tag
 * Currently is tag name plus the usage count
 *
 * @param string $name    Tag name
 * @param string $alltags  Array of tags to get the information from
 * @return $tag Formatted tag
 */
function display_tag($name, $alltags) {
    return $name . ' (' . $alltags[$name]->count . ')';
}

/**
 * Get all tags created by this user
 *
 * @param string $query Search option
 * @param int $limit
 * @param int $offset
 * @retun array $tags  The tags this user has created
 */
function get_all_tags_for_user($query = null, $limit = null, $offset = null) {
    global $USER;
    if ($USER->is_logged_in()) {
        $usertags = "";
        $userid = $USER->get('id');
        if ($USER->get('admin')) {
            $usertags = "
                UNION ALL
                SELECT tag,COUNT(*) AS count FROM {usr_tag} t INNER JOIN {usr} u ON t.usr=u.id GROUP BY 1";
        }
        else if ($admininstitutions = $USER->get('admininstitutions')) {
            $insql = "'" . join("','", $admininstitutions) . "'";
            $usertags = "
                UNION ALL
                SELECT tag,COUNT(*) AS count FROM {usr_tag} t INNER JOIN {usr} u ON t.usr=u.id INNER JOIN {usr_institution} ui ON ui.usr=u.id WHERE ui.institution IN ($insql) GROUP BY 1";
        }
        $values = array($userid, $userid, $userid);
        $querystr = '';
        if ($query) {
            $querystr = " WHERE tag LIKE '%' || ? || '%'";
            $values[] = $query;
        }
        $sql = "
            SELECT tag, SUM(count) AS count
            FROM (
                SELECT tag,COUNT(*) AS count FROM {artefact_tag} t INNER JOIN {artefact} a ON t.artefact=a.id WHERE a.owner=? GROUP BY 1
                UNION ALL
                SELECT tag,COUNT(*) AS count FROM {view_tag} t INNER JOIN {view} v ON t.view=v.id WHERE v.owner=? GROUP BY 1
                UNION ALL
                SELECT tag,COUNT(*) AS count FROM {collection_tag} t INNER JOIN {collection} c ON t.collection=c.id WHERE c.owner=? GROUP BY 1
                " . $usertags . "
            ) tags
            " . $querystr . "
            GROUP BY tag
            ORDER BY LOWER(tag)
            ";
        $result = get_records_sql_assoc($sql, $values, $offset, $limit);
    }
    $results = !empty($result) ? $result : array();
    $return = array('tags' => $results,
                    'count' => count($results),
    );

    return $return;
}

function pieform_element_tags_get_headdata($element) {
    return pieform_element_autocomplete_get_headdata($element);
}

function pieform_element_tags_get_value(Pieform $form, $element) {
    return pieform_element_autocomplete_get_value($form, $element);
}
