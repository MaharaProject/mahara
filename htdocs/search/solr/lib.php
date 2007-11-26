<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage search-internal
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

safe_require('search', 'internal');

/**
 * The Solr search plugin which searches using the Solr search engine (mostly)
 */
class PluginSearchSolr extends PluginSearchInternal {
    public static function get_cron() {
        return array(
            (object)array(
                'callfunction' => 'rebuild_all',
                'hour'         => '4',
                'minute'       => '25',
            ),
        );
    }

    public static function get_event_subscriptions() {
        $subscriptions = array(
            (object)array('plugin' => 'solr', 'event' => 'createuser',     'callfunction' => 'event_reindex_user'   ),
            (object)array('plugin' => 'solr', 'event' => 'updateuser',     'callfunction' => 'event_reindex_user'   ),
            (object)array('plugin' => 'solr', 'event' => 'suspenduser',    'callfunction' => 'event_reindex_user'   ),
            (object)array('plugin' => 'solr', 'event' => 'unsuspenduser',  'callfunction' => 'event_reindex_user'   ),
            (object)array('plugin' => 'solr', 'event' => 'deleteuser',     'callfunction' => 'event_reindex_user'   ),
            (object)array('plugin' => 'solr', 'event' => 'undeleteuser',   'callfunction' => 'event_reindex_user'   ),
            (object)array('plugin' => 'solr', 'event' => 'expireuser',     'callfunction' => 'event_reindex_user'   ),
            (object)array('plugin' => 'solr', 'event' => 'unexpireuser',   'callfunction' => 'event_reindex_user'   ),
            (object)array('plugin' => 'solr', 'event' => 'deactivateuser', 'callfunction' => 'event_reindex_user'   ),
            (object)array('plugin' => 'solr', 'event' => 'activateuser',   'callfunction' => 'event_reindex_user'   ),
            (object)array('plugin' => 'solr', 'event' => 'saveartefact',   'callfunction' => 'event_saveartefact'   ),
            (object)array('plugin' => 'solr', 'event' => 'deleteartefact', 'callfunction' => 'event_deleteartefact' ),
            (object)array('plugin' => 'solr', 'event' => 'saveview',       'callfunction' => 'event_saveview'       ),
            (object)array('plugin' => 'solr', 'event' => 'deleteview',     'callfunction' => 'event_deleteview'     ),
        );

        return $subscriptions;
    }

    public static function event_reindex_user($event, $user) {
        if (!self::config_is_sane()) {
            return;
        }
        self::index_user($user);
        self::commit();
    }
    public static function event_saveartefact($event, $artefact) {
        if (!self::config_is_sane()) {
            return;
        }
        self::index_artefact($artefact);
        self::commit();
    }
    public static function event_deleteartefact($event, $artefact) {
        if (!self::config_is_sane()) {
            return;
        }
        self::delete_byidtype($artefact->get('id'), 'artefact');
        self::commit();
    }

    public static function event_saveview($event, $view) {
        if (!self::config_is_sane()) {
            return;
        }
        self::index_view($view);
        self::commit();
    }

    public static function event_deleteview($event, $view) {
        if (!self::config_is_sane()) {
            return;
        }
        self::delete_byidtype($view['id'], 'view');
        self::commit();
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $elements = array();

        $enc_complete = json_encode(get_string('complete'));

        $script = <<<END
<script type="text/javascript">
    function solr_reindex(link, type) {
        var td = link.parentNode;
        var progress = TD(null, IMG({'src': get_themeurl('images/loading.gif')}));
        insertSiblingNodesAfter(td, progress);

        sendjsonrequest(config.wwwroot + 'search/solr/reindex.json.php', {'type': type}, 'POST', function (data) {
            replaceChildNodes(progress, {$enc_complete});
        });
    }
</script>
END;

        $elements['solrurl'] = array(
            'type'         => 'text',
            'title'        => get_string('solrurl', 'search.solr'), 
            'defaultvalue' => get_config_plugin('search', 'solr', 'solrurl'),
        );
        $elements['indexcontrol'] = array(
            'type'     => 'fieldset',
            'legend'   => get_string('indexcontrol', 'search.solr'),
            'collapsible' => true,
            'collapsed' => true,
            'elements' => array(
                array(
                    'type'  => 'html',
                    'value' => $script,
                ),
                array(
                    'type'  => 'html',
                    'value' => '<table><tbody>' 
                    . '<tr><td><a href="" onclick="solr_reindex(this, \'user\'); return false;">' . hsc(get_string('reindexusers','search.solr')) . '</a></td></tr>'
                    . '<tr><td><a href="" onclick="solr_reindex(this, \'artefact\'); return false;">' . hsc(get_string('reindexartefacts','search.solr')) . '</a></td></tr>'
                    . '<tr><td><a href="" onclick="solr_reindex(this, \'view\'); return false;">' . hsc(get_string('reindexviews','search.solr')) . '</a></td></tr>'
                    . '</tbody></table>',
                ),
            ),
        );

        return array(
            'elements' => $elements,
            'renderer' => 'table'
        );
    }

    public static function save_config_options($values) {
        if (substr($values['solrurl'], -1) != '/') {
            $values['solrurl'] .= '/';
        }
        set_config_plugin('search', 'solr', 'solrurl', $values['solrurl']);
    }

    private static function remove_key_prefix($results) {
        if (is_array($results['data'])) {
            foreach ($results['data'] as &$result) {
                $new_result = array();
                foreach ($result as $key => &$value) {
                    if ($key == 'id') {
                        $new_result[$key] = $value;
                        continue;
                    }

                    $key_parts = explode('_', $key);

                    if (count($key_parts) != 2) {
                        continue;
                    }

                    if ($key_parts[0] == 'store' || $key_parts[0] == 'text' || $key_parts[0] == 'string' 
                        || $key == 'ref_institution') {
                        $new_result[$key_parts[1]] = $value;
                    }
                }
                $result = $new_result;
            }
        }
    }

    public static function search_user($query_string, $limit, $offset = 0) {
        if (!empty($query_string)) {
            $query_string = 'index_name:' . $query_string . '*';
        }
        $results = self::send_query($query_string, $limit, $offset,
                                    array('type' => 'user', 'index_active' => 1));
        self::remove_key_prefix(&$results);
        return $results;
    }


    public static function admin_search_user($queries, $constraints, $offset, $limit, $sortby, $sortdir) {
        $q = '';
        $solrfields = array(
            'id'          => 'id',
            'institution' => 'ref_institution',
            'email'       => 'string_email',
            'username'    => 'text_username',
            'firstname'   => 'text_firstname',
            'lastname'    => 'text_lastname'
        );
        if (!empty($queries)) {
            $terms = array();
            foreach ($queries as $f) {
                if ($f['field'] == 'email' && $f['type'] == 'contains' && strpos($f['string'],'@') === 0) {
                    $terms[] = 'string_emaildomain:' . substr($f['string'], 1) . '*';
                } else {
                    $terms[] = $solrfields[$f['field']] . ':' . strtolower($f['string'])
                        . ($f['type'] != 'equals' ? '*' : '');
                }
            }
            $q .= '(' . join(' OR ', $terms) . ')';
        }
        if (!empty($constraints)) {
            if (!empty($q)) {
                $q .= ' AND ';
            }
            $terms = array();
            foreach ($constraints as $f) {
                $terms[] = $solrfields[$f['field']] . ':' . strtolower($f['string'])
                    . ($f['type'] != 'equals' ? '*' : '');
            }
            $q .= join(' AND ', $terms);
        }

        $sort = $solrfields[$sortby] . ' ' . $sortdir;

        $results = self::send_query($q, $limit, $offset, array('type' => 'user'), '*', false, $sort);
        self::remove_key_prefix(&$results);
        return $results;
    }


    /**
     * Given a query string and limits, return an array of matching objects
     * owned by the current user.  Possible return types are ...
     *   - artefact
     *   - view
     *
     * Implementations of this search should search across tags for artefacts
     * and views at a minimum. Ideally the search would also index
     * title/description and other metadata for these objects.
     *
     * @param string  The query string
     * @param integer How many results to return
     * @param integer What result to start at (0 == first result)
     * @param string  Type to search for (either 'all' or one of the types above).
     * 
     */
    public static function self_search($query_string, $limit, $offset, $type = 'all') {
        global $USER;

        if ($type != 'artefact' && $type != 'view') {
            $type = 'artefact OR view';
        }

        $results = self::send_query($query_string, $limit, $offset, array('type' => $type, 'owner' => $USER->get('id')), '*', true);

        if (is_array($results['data'])) {
            foreach ($results['data'] as &$result) {
                $new_result = array();
                foreach ($result as $key => &$value) {
                    if ($key == 'id' || $key == 'title' ||$key == 'type' || $key == 'description' || $key == 'summary' || $key == 'tags') {
                        $new_result[$key] = $value;
                    }
                    else if ($key == 'ref_artefacttype') {
                        $new_result['artefacttype'] = $value;
                    }
                }
                $result = $new_result;
            }
        }

        self::self_search_make_links($results);

        return $results;
    }

    // This function will rebuild the solr indexes
    public static function rebuild_all() {
        if (!self::config_is_sane()) {
            return;
        }
        self::rebuild_users();
        self::rebuild_artefacts();
        self::rebuild_views();
        self::commit();
        self::optimize();
    }

    public static function rebuild_views() {
        log_debug('Starting rebuild_views()');

        self::delete_bytype('view');

        $views = get_recordset('view', '', '', '', '*,' . db_format_tsfield('ctime') . ',' . db_format_tsfield('mtime'));

        while ($view = $views->FetchRow()) {
            $doc = array(
                'id'                 => $view['id'],
                'owner'              => $view['owner'],
                'type'               => 'view',
                'title'              => $view['title'],
                'description'        => strip_tags($view['description']),
                'tags'               => get_column('view_tag', 'tag', 'view', $view['id']),
                'ctime'              => $view['ctime'],
                'mtime'              => $view['mtime'],
            );

            self::add_document($doc);
        }

        log_debug('Completed rebuild_views()');
    }

    public static function rebuild_artefacts() {
        log_debug('Starting rebuild_artefacts()');

        self::delete_bytype('artefact');

        $artefacts = get_recordset('artefact', '', '', '', '*,' . db_format_tsfield('ctime') . ',' . db_format_tsfield('mtime'));

        while ($artefact = $artefacts->FetchRow()) {
            $doc = array(
                'id'                 => $artefact['id'],
                'owner'              => $artefact['owner'],
                'ref_artefacttype'   => $artefact['artefacttype'],
                'type'               => 'artefact',
                'title'              => $artefact['title'],
                'description'        => strip_tags($artefact['description']),
                'tags'               => get_column('artefact_tag', 'tag', 'artefact', $artefact['id']),
                'ctime'              => $artefact['ctime'],
                'mtime'              => $artefact['mtime'],
            );

            self::add_document($doc);
        }

        log_debug('Completed rebuild_artefacts()');
    }

    public static function rebuild_users() {
        log_debug('Starting rebuild_users()');

        self::delete_bytype('user');

        $users = get_recordset('usr', 'deleted', '0');
        safe_require('artefact', 'internal');
        $publicfields = array_keys(ArtefactTypeProfile::get_public_fields());

        while ($user = $users->FetchRow()) {
            if ($user['id'] == 0) {
                continue;
            }
            self::index_user($user);
        }

        $users->close();

        log_debug('Completed rebuild_users()');
    }

    private static function index_artefact($artefact) {
        if (!($artefact instanceof ArtefactType)) {
            log_warn('artefact event received without ArtefactType object');
            return;
        }

        $doc = array(
            'id'                  => $artefact->get('id'),
            'owner'               => $artefact->get('owner'),
            'ref_artefacttype'    => $artefact->get('artefacttype'),
            'type'                => 'artefact',
            'title'               => $artefact->get('title'),
            'description'         => $artefact->get('description'),
            'tags'                => $artefact->get('tags'),
            'ctime'               => $artefact->get('ctime'),
            'mtime'               => $artefact->get('mtime'),
        );

        self::add_document($doc);
    }

    private static function index_view($view) {
        $view = (array)get_record('view', 'id', $view['id'], null, null, null, null, '*,' . db_format_tsfield('ctime') . ',' . db_format_tsfield('mtime'));

        $doc = array(
            'id'                 => $view['id'],
            'owner'              => $view['owner'],
            'type'               => 'view',
            'title'              => $view['title'],
            'description'        => strip_tags($view['description']),
            'tags'               => get_column('view_tag', 'tag', 'view', $view['id']),
            'ctime'              => $view['ctime'],
            'mtime'              => $view['mtime'],
        );

        self::add_document($doc);
    }

    private static function index_user($user) {
        if (!isset($user['id'])) {
            throw new InvalidArgumentException('Trying to index user with no id');
        }
        if (
            !isset($user['preferredname'])
            || !isset($user['institution'])
            || !isset($user['email'])
            || !isset($user['username'])
            || !isset($user['preferredname'])
            || !isset($user['firstname'])
            || !isset($user['lastname'])
        ) {
            $user = get_record('usr', 'id', $user['id']);
            if ($user) {
                $user = (array)$user;
            }
        }

        if ($user['deleted']) {
            self::delete_byidtype($user['id'], 'user');
            return;
        }
        
        // @todo: need to index public profile fields
        $doc = array(
            'id'                  => $user['id'],
            'owner'               => $user['id'],
            'type'                => 'user',
            'index_name'          => $user['preferredname'],
            'ref_institution'     => $user['institution'],
            'string_email'        => $user['email'],
            'text_username'       => $user['username'],
            'store_preferredname' => $user['preferredname'],
            'text_firstname'      => $user['firstname'],
            'text_lastname'       => $user['lastname'],
            'index_active'        => $user['active'],
            'store_suspended'     => (int)!empty($user['suspendedcusr']),
        );
        if (empty($doc['index_name'])) {
            $doc['index_name'] = $user['firstname'] . ' ' . $user['lastname'];
        }
        if ($emailparts = split('@', $user['email'])
            and !empty($emailparts[1])) {
            $doc['string_emaildomain'] = $emailparts[1];
        } else {
            $doc['string_emaildomain'] = $user['email'];
        }

        self::add_document($doc);
    }

    public static function commit() {
        self::send_update('<commit />');
    }

    function optimize() {
        self::send_update('<optimize />');
    }


    public static function delete_bytype($type) {
        self::send_update('<delete><query>type:' . htmlentities($type) . '</query></delete>');
    }

    public static function delete_byidtype($id, $type) {
        self::send_update('<delete><query>id:' . htmlentities($id) . ' AND type:' . htmlentities($type) . '</query></delete>');
    }

    /**
     * Takes an XML message and sends it to Solr's update handler
     * 
     * @param  string  The message to send
     */
    private static function send_update($message) {
        require_once('snoopy/Snoopy.class.php');
        $snoopy = new Snoopy;
        $snoopy->rawheaders = array(
            'Content-type' => 'text/xml'
        );

        $url = get_config_plugin('search', 'solr', 'solrurl') . 'update';

        if (!$snoopy->submit($url, $message)) {
            throw new RemoteServerException('Request to solr failed');
        }

        $dom = new DOMDocument;
        if (!@$dom->loadXML($snoopy->results)) {
            log_warn('PluginSearchSolr::send_update (Failed to parse response)' . $snoopy->results);
            throw new RemoteServerException('Parsing Solr response failed');
        }

        $root = $dom->getElementsByTagName('response'); // get root node
        $root = $root->item(0);
        if (is_null($root) || $root->getAttribute('status') != 0) {
            log_warn('PluginSearchSolr::send_update (Got non-zero return status)' . $snoopy->results);
            throw new RemoteServerException('Solr update failed');
        }
    }

    private static function send_query($query, $limit, $offset, $constraints = array(), $fields = '*', $highlight = false, $sort = null) {
        $q = array();

        foreach ( $constraints as $key => $value ) {
            if(empty($value)) {
                continue;
            }
            if(is_array($value)) {
                $value = implode('" OR "', $value);
            }
            array_push($q,$key . ':(' . $value . ')');
        }

        require_once('snoopy/Snoopy.class.php');
        $client = new Snoopy;

        if(!empty($query)) {
            array_push($q, '('.$query.')');
        }

        $data = array(
            'q'      => join(' AND ',$q),
            'fl'     => $fields,
            'start'  => $offset,
            'rows'   => $limit,
            //'indent' => 1,
        );
        if (!empty($sort)) {
            $data['sort'] = $sort;
        }

        if ($highlight) {
            $data['hl']          = 'true';
            $data['hl.fl']       = 'title,description,tags';
            $data['hl.snippets'] = '3';
        }

        $url = get_config_plugin('search', 'solr', 'solrurl') . 'select';

        if (!$client->submit($url, $data)) {
            throw new Exception('Request to solr failed');
        }

        if( $client->status != 200 ) {
            log_warn('solr_send_query(Solr Error)', true, false);
            log_warn($client->results);
            $result = array(
                'error'   => 'Bad repsponse from Solr (HTTP ' . $client->status . ')',
                'data' => array()
            );
            return $result;
        }

        $dom = new DOMDocument;
        if (!$dom->loadXML($client->results)) {
            log_warn('solr_send_query(Solr Error)', true, false);
            log_warn($client->results);
            $result = array(
                'error'   => 'Query parse error',
                'data' => array()
            );
            return $result;
        }

        $summary_info = array();

        if ($highlight) {
            $hlroot = $dom->getElementsByTagName('lst');
            foreach ( $hlroot as $node ) {
                if ($node->getAttribute('name') == 'highlighting') {
                    $hlroot = $node;
                    break;
                }
            }
            foreach ( $hlroot->childNodes as $node ) {
                if( $node->nodeType != XML_ELEMENT_NODE || $node->nodeName != 'lst' ) {
                    continue;
                }
                $idtype = $node->getAttribute('name');
                $summary_info[$idtype] = '';
                foreach ( $node->getElementsByTagName('str') as $text ) {
                    $summary_info[$idtype] .= $text->textContent;
                }
            }
        }

        $root = $dom->getElementsByTagName('result'); // get root node
        $root = $root->item(0);

        $results = array(
            'count'   => $root->getAttribute('numFound'),
            'offset'  => $offset,
            'limit'   => $limit,
            'data'    => array()
        );

        // loop over results
        foreach ( $root->childNodes as $node ) {
            if( $node->nodeType != XML_ELEMENT_NODE || $node->nodeName != 'doc' ) {
                log_debug('bad node: ' . $node->nodeName);
                continue;
            }
            $result = array();
            // loop over fields
            foreach ( $node->childNodes as $field ) {
                if ($field->nodeType != XML_ELEMENT_NODE || ( $field->nodeName != 'str' && $field->nodeName != 'int' )) {
                    continue;
                }

                $value = $field->firstChild;
                if (empty($value)) {
                    continue;
                }
                $result[$field->getAttribute('name')] = $value->wholeText;
            }

            if (isset($summary_info[$result['idtype']])) {
                $result['summary'] = $summary_info[$result['idtype']];
            }

            if (empty($result['summary']) && isset($result['description'])) {
                $result['summary'] = $result['description'];
            }

            $results['data'][] = $result;
        }

        return $results;
    }

    private static function calculate_idtype(&$doc) {
        if(empty($doc['type']) || empty($doc['id'])) {
            throw new InvalidArgumentException('Solr object missing id or type while trying to calculate idtype field');
            return;
        }

        $doc['idtype'] = $doc['type'].'.'.$doc['id'];
    }

    private static function add_document($data) {
        self::calculate_idtype($data);

        $dom = new DOMDocument;

        $node_add = $dom->createElement('add');
        $dom->appendChild($node_add);

        $node_doc = $dom->createElement('doc');
        $node_add->appendChild($node_doc);

        foreach ( $data as $key => $value )
        {
            $value = (array)$value;
            foreach ($value as $v) {
                $node_field = $dom->createElement('field');
                $text = $dom->createTextNode($v);
                $node_field->appendChild($text);
                $node_field->setAttribute('name', $key);
                $node_doc->appendChild($node_field);
            }
        }

        self::send_update($dom->saveXML());
    }

    private static function config_is_sane() {
        if (get_config('searchplugin') != 'solr') {
            return false;
        }

        return true;
    }
}

?>
