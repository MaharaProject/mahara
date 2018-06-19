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

    $ids = array_map(function($a) {
        if (strpos($a, ':')) {
            $arr = explode(': ', $a);
            return $arr[1];
        }
        return $a;
    }, $ids);

    $results = array();
    $alltags = get_all_tags_for_user();

    foreach ($ids as $id) {
        if (isset($alltags['tags'][$id])) {
            $results[] = (object) array('id' => $id, 'text' => display_tag($id, $alltags['tags']));
        }
        else {
            $results[] = (object) array('id' => $id, 'text' => $id);
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
    if ($alltags[$name]->prefix && !empty($alltags[$name]->prefix)) {
        $prefix = $alltags[$name]->prefix;
        return $prefix . ': '. $alltags[$name]->tag . ' (' . $alltags[$name]->count . ')';
    }
    return $alltags[$name]->tag . ' (' . $alltags[$name]->count . ')';
}

/**
 * Get all tags available for this user
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
        $typecast = is_postgres() ? '::varchar' : '';
        if ($USER->get('admin')) {
            $usertags = "
                UNION ALL
                SELECT tag, COUNT(*) AS count, 'lastinstitution' AS prefix FROM {tag} t INNER JOIN {usr} u ON (t.resourcetype = 'usr' AND t.resourceid = u.id" . $typecast . ") GROUP BY 1";
        }
        else if ($admininstitutions = $USER->get('admininstitutions')) {
            $insql = "'" . join("','", $admininstitutions) . "'";
            $usertags = "
                UNION ALL
                SELECT tag, COUNT(*) AS count, 'lastinstitution' AS prefix FROM {tag} t INNER JOIN {usr} u ON (t.resourcetype = 'usr' AND t.resourceid = u.id" . $typecast . ") INNER JOIN {usr_institution} ui ON ui.usr=u.id WHERE ui.institution IN ($insql) GROUP BY 1";
        }
        $values = array($userid, $userid);
        $querystr = '';
        if ($query) {
            $querystr = " WHERE tag LIKE '%' || ? || '%'";
            $values[] = $query;
        }
        $sql = "
            SELECT tag, SUM(count) AS count, prefix
            FROM (
                SELECT
                  (CASE
                    WHEN t.tag LIKE 'tagid_%' THEN t2.tag
                   ELSE t.tag
                  END) AS tag, COUNT(*) AS count, i.displayname AS prefix
                FROM {tag} t
                LEFT JOIN {tag} t2 ON t2.id" . $typecast . " = SUBSTRING(t.tag, 7)
                LEFT JOIN {institution} i ON i.name = t2.ownerid
                WHERE t.ownerid=? AND t.resourcetype IN ('artefact', 'view', 'collection')
                GROUP BY 1, 3
                UNION ALL
                SELECT t.tag, 0 AS count, i.displayname AS prefix
                FROM {tag} t
                JOIN {institution} i ON i.name = t.ownerid AND i.tags = 1
                JOIN {usr_institution} ui ON ui.institution = i.name AND ui.usr = ?
                " . $usertags . "
            ) tags
            " . $querystr . "
            GROUP BY tag, prefix
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
