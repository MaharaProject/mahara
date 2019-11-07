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
        'institution' => isset($element['institution']) ? $element['institution'] : null,
    );
    return pieform_element_autocomplete($form, $newelement);
}

function translate_tags_to_names(array $ids) {
    global $USER;
    // for an empty list, the element '' is transmitted
    $ids = array_diff($ids, array(''));
    $institutions = $USER->get('institutions');
    if (!empty($institutions)) {
        $institutions = array_keys($institutions);
        // Fetch valid institution tags
        $validinstitutiontags = get_column_sql("SELECT tag FROM {tag}
                                                WHERE ownertype = 'institution'
                                                ANd resourcetype = 'institution'
                                                AND ownerid IN ('" . join("','", $institutions) . "')");
    }
    else if ($USER->get('admin')) {
        $validinstitutiontags = get_column_sql("SELECT tag FROM {tag}
                                                WHERE ownertype = 'institution'
                                                ANd resourcetype = 'institution'");
    }
    else {
        $validinstitutiontags = array();
    }

    $ids = array_map(function($a) use ($validinstitutiontags) {
        if (strpos($a, ': ')) {
            if (in_array($a, $validinstitutiontags)) {
                $arr = explode(': ', $a);
                return trim($arr[1]);
            }
        }
        return $a;
    }, $ids);

    $results = array();
    $alltags = get_all_tags_for_user();

    foreach ($ids as $id) {
        // if institution tag, we need to remove the prefix
        $tagname = remove_prefix($id);

        if (isset($alltags['tags'][$tagname])) {
            $results[] = (object) array('id' => $tagname, 'text' => display_tag($tagname, $alltags['tags'], true));
        }
        else {
            $results[] = (object) array('id' => $tagname, 'text' => $tagname);
        }
    }
    return $results;
}

/**
 * Display formatted tag
 * Currently is tag name plus the usage count
 *
 * @param string  $name       Tag name
 * @param string  $alltags    Array of tags to get the information from
 * @param boolean $showcount  Whether or not to add the tag count to output
 * @return $tag Formatted tag
 */
function display_tag($name, $alltags, $showcount = false) {
    if ($alltags[$name]->prefix && !empty($alltags[$name]->prefix)) {
        $prefix = $alltags[$name]->prefix;
        $tag = $prefix . ': '. $alltags[$name]->tag;
    }
    else {
        $tag = $alltags[$name]->tag;
    }
    if ($showcount && $alltags[$name]->count > 0) {
        $tag .= ' (' . $alltags[$name]->count . ')';
    }
    return $tag;
}

/**
 * Return a tag name without institution prefix if it has one
 * @param string $tagname  the tag name
 * @return string Institution tag without prefix or same tagname if it not an institution tag
 */
function remove_prefix($tagname) {
    $institutions = get_column_sql('SELECT displayname FROM {institution} WHERE name != ?', array('mahara'));
    foreach ($institutions as $institution) {
        $prefix = $institution . ': ';
        if (substr($tagname, 0, strlen($prefix)) == $prefix) {
            $tagname = substr($tagname, strlen($prefix));
            break;
        }
    }
    return $tagname;
}

/**
 * Get all tags available for this user
 *
 * @param string $query Search option
 * @param int $limit
 * @param int $offset
 * @param string $institution name
 * @retun array $tags  The tags this user has created
 */
function get_all_tags_for_user($query = null, $limit = null, $offset = null, $institution = null) {
    global $USER;
    if ($USER->is_logged_in()) {
        $userid = $USER->get('id');
        $typecast = is_postgres() ? '::varchar' : '';

        // get all the institution tags the user can use
        $values = array($userid);
        if ($USER->get('institutions')) {
            $userinstitutiontags = "
                UNION ALL
                SELECT t.tag, 0 AS count, NULL AS prefix
                FROM {tag} t
                JOIN {institution} i ON i.name = t.ownerid AND t.ownertype = 'institution'
                JOIN {usr_institution} ui ON ui.institution = i.name AND ui.usr = ?
                WHERE t.resourcetype IN ('artefact', 'view', 'collection', 'blocktype') AND tag NOT LIKE ('tagid_%')";
            $values[] = $userid;
        }
        else if ($USER->get('admin')) {
            $userinstitutiontags = "
                UNION ALL
                SELECT t.tag, 0 AS count, NULL AS prefix
                FROM {tag} t
                WHERE t.ownertype = 'institution'
                AND t.resourcetype IN ('artefact', 'view', 'collection', 'blocktype') AND tag NOT LIKE ('tagid_%')";
        }
        else {
            $userinstitutiontags = "
                UNION ALL
                SELECT t.tag, 0 AS count, NULL AS prefix
                FROM {tag} t
                WHERE t.ownertype = 'institution' AND t.ownerid = 'mahara'
                AND t.resourcetype IN ('artefact', 'view', 'collection', 'blocktype') AND tag NOT LIKE ('tagid_%')";
        }
        $values[] = $userid;
        if ($USER->get('admin') && isset($institution)) {
          $values[] = $institution;
          $insttagsforuser = "
              UNION ALL
              SELECT t.tag, 0 AS count, i.displayname AS prefix
              FROM {tag} t
              JOIN {institution} i ON i.name = t.ownerid AND i.tags = 1 AND t.resourcetype='institution' AND i.name = ?";
        }
        else {
          $values[] = $userid;
          $insttagsforuser = "
              UNION ALL
              SELECT t.tag, 0 AS count, i.displayname AS prefix
              FROM {tag} t
              JOIN {institution} i ON i.name = t.ownerid AND i.tags = 1 AND t.resourcetype='institution'
              JOIN {usr_institution} ui ON ui.institution = i.name AND ui.usr = ?";
        }

        $querystr = '';
        if ($query) {
            $querystr = " WHERE tag " . db_ilike() . " '%' || ? || '%'";
            $values[] = $query;
            // Also do matching by institution name so we can list valid institution tags
            // if we only know institution name
            $querystr .= " OR prefix " . db_ilike() . " '%' || ? || '%'";
            $values[] = $query;
        }

        // get all the tags the logged in user already has
        $sql = "
            SELECT tag, SUM(count) AS count, prefix
            FROM (
                -- Selecting tags used in user section that you own
                SELECT
                  (CASE
                    WHEN t.tag LIKE 'tagid_%' THEN t2.tag
                   ELSE t.tag
                  END) AS tag, COUNT(*) AS count, i.displayname AS prefix
                FROM {tag} t
                LEFT JOIN {tag} t2 ON t2.id" . $typecast . " = SUBSTRING(t.tag, 7)
                LEFT JOIN {institution} i ON i.name = t2.ownerid AND t2.resourcetype = 'institution'
                WHERE t.ownertype='user' AND t.ownerid=? AND t.resourcetype IN ('artefact', 'view', 'collection', 'blocktype')
                GROUP BY 1, 3
                -- Selecting tags used in institution section that you belong to
                " . $userinstitutiontags . "
                -- Selecting tags used in groups that you belong to
                UNION ALL
                SELECT t.tag, 0 AS count, NULL AS prefix
                FROM {tag} t
                JOIN {group} g ON g.id" . $typecast . " = t.ownerid AND t.ownertype='group'
                JOIN {group_member} gm ON gm.group = g.id AND gm.member = ?
                WHERE t.resourcetype IN ('artefact', 'view', 'collection', 'blocktype') AND tag NOT LIKE ('tagid_%')
                -- Selecting the special 'Institution tags' tags that you can see
                " . $insttagsforuser . "
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
