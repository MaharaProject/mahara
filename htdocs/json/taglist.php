<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

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
    $result = get_records_sql_array("
        SELECT tag, SUM(count) AS count
        FROM (
            SELECT tag,COUNT(*) AS count FROM {artefact_tag} t INNER JOIN {artefact} a ON t.artefact=a.id WHERE a.owner=? GROUP BY 1
            UNION ALL
            SELECT tag,COUNT(*) AS count FROM {view_tag} t INNER JOIN {view} v ON t.view=v.id WHERE v.owner=? GROUP BY 1
            UNION ALL
            SELECT tag,COUNT(*) AS count FROM {collection_tag} t INNER JOIN {collection} c ON t.collection=c.id WHERE c.owner=? GROUP BY 1
            " . $usertags . "
        ) tags
        GROUP BY tag
        ORDER BY LOWER(tag)
        ",
        array($userid, $userid, $userid)
    );
}

if (empty($result)) {
    $result = array();
}

json_headers();
print json_encode($result);
