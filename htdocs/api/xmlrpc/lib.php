<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage auth
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmlrpc_exception (Exception $e) {
    if (($e instanceof XmlrpcServerException) && get_class($e) == 'XmlrpcServerException') {
        $e->handle_exception();
        return;
    } elseif (($e instanceof MaharaException) && get_class($e) == 'MaharaException') {
        throw new XmlrpcServerException($e->getMessage(), $e->getCode());
        return;
    }
    xmlrpc_error('An unexpected error has occurred: '.$e->getMessage(), $e->getCode());
    log_message($e->getMessage(), LOG_LEVEL_WARN, true, true, $e->getFile(), $e->getLine(), $e->getTrace());
}

function get_hostname_from_uri($uri = null) {
    static $cache = array();
    if (array_key_exists($uri, $cache)) {
        return $cache[$uri];
    }
    $count = preg_match("@^(?:http[s]?://)?([A-Z0-9\-\.]+).*@i", $uri, $matches);
    $cache[$uri] = $matches[1];
    if ($count > 0) return $matches[1];
    return false;
}

function dropslash($wwwroot) {
    if (substr($wwwroot, -1, 1) == '/') {
        return substr($wwwroot, 0, -1);
    }
    return $wwwroot;
}

function generate_token() {
    return sha1(str_shuffle('' . mt_rand(999999,99999999) . microtime(true)));
}

function start_jump_session($peer, $instanceid, $wantsurl="") {
    global $USER;

    $rpc_negotiation_timeout = 15;
    $providers = get_service_providers($USER->authinstance);

    $approved = false;
    foreach ($providers as $provider) {
        if ($provider['wwwroot'] == $peer->wwwroot) {
            $approved = true;
            break;
        }
    }

    if (false == $approved) {
        // This shouldn't happen: the user shouldn't have been presented with 
        // the link
        throw new AccessTotallyDeniedException('Host not approved for sso');
    }

    // set up the session
    $sso_session = get_record('sso_session',
                              'userid',     $USER->id);
    if ($sso_session == false) {
        $sso_session = new stdClass();
        $sso_session->instanceid = $instanceid;
        $sso_session->userid = $USER->id;
        $sso_session->username = $USER->username;
        $sso_session->useragent = sha1($_SERVER['HTTP_USER_AGENT']);
        $sso_session->token = generate_token();
        $sso_session->confirmtimeout = time() + $rpc_negotiation_timeout;
        $sso_session->expires = time() + (integer)ini_get('session.gc_maxlifetime');
        $sso_session->sessionid = session_id();
        if (! insert_record('sso_session', $sso_session)) {
            throw new SQLException("database error");
        }
    } else {
        $sso_session->useragent = sha1($_SERVER['HTTP_USER_AGENT']);
        $sso_session->token = generate_token();
        $sso_session->instanceid = $instanceid;
        $sso_session->confirmtimeout = time() + $rpc_negotiation_timeout;
        $sso_session->expires = time() + (integer)ini_get('session.gc_maxlifetime');
        $sso_session->useragent = sha1($_SERVER['HTTP_USER_AGENT']);
        $sso_session->sessionid = session_id();
        if (false == update_record('sso_session', $sso_session, array('userid' => $USER->id))) {
            throw new SQLException("database error");
        }
    }

    $wwwroot = dropslash(get_config('wwwroot'));

    // construct the redirection URL
    $url = "{$peer->wwwroot}{$peer->application->ssolandurl}?token={$sso_session->token}&idp={$wwwroot}&wantsurl={$wantsurl}";

    return $url;
}

function api_dummy_method($methodname, $argsarray, $functionname) {
    return call_user_func_array($functionname, $argsarray);
}

function find_remote_user($username, $wwwroot) {
    $authinstances = auth_get_auth_instances_for_wwwroot($wwwroot);

    $candidates = array();

    foreach ($authinstances as $authinstance) {
        if ($authinstance->authname != 'xmlrpc') {
            continue;
        }
        try {
            $user = new User;
            $user->find_by_instanceid_username($authinstance->id, $username, true);
            $candidates[$authinstance->id] = $user;
        } catch (Exception $e) {
            // we don't care
            continue;
        }
    }

    if (count($candidates) != 1) {
        return false;
    }

    safe_require('auth', 'xmlrpc');
    return array(end($candidates), new AuthXmlrpc(key($candidates)));
}

function fetch_user_image($username) {
    global $REMOTEWWWROOT;

    list ($user, $authinstance) = find_remote_user($username, $REMOTEWWWROOT);
    if (!$user) {
        return false;
    }

    $ic = $user->profileicon;
    if (!empty($ic)) {
        $filename = get_config('dataroot') . 'artefact/file/profileicons/' . ($user->profileicon % 256) . '/'.$user->profileicon;
        $return = array();
        try {
            $fi = file_get_contents($filename);
        } catch (Exception $e) {
            // meh
        }

        $return['f1'] = base64_encode($fi);

        require_once('file.php');
        $im = get_dataroot_image_path('artefact/file/profileicons' , $user->profileicon, 100);
        $fi = file_get_contents($im);
        $return['f2'] = base64_encode($fi);
        return $return;
    } else {
        // no icon
    }
}

function user_authorise($token, $useragent) {
    global $USER;

    $sso_session = get_record('sso_session', 'token', $token, 'useragent', $useragent);
    if (empty($sso_session)) {
        throw new XmlrpcServerException('No such session exists');
    }

    // check session confirm timeout
    if ($sso_session->expires < time()) {
        throw new XmlrpcServerException('This session has timed out');
    }

    // session okay, try getting the user
    $user = new User();
    try {
        $user->find_by_id($sso_session->userid);
    } catch (Exception $e) {
        throw new XmlrpcServerException('Unable to get information for the specified user');
    }

    require_once(get_config('docroot') . 'artefact/lib.php');
    require_once(get_config('docroot') . 'artefact/internal/lib.php');

    $element_list = call_static_method('ArtefactTypeProfile', 'get_all_fields');
    $element_required = call_static_method('ArtefactTypeProfile', 'get_mandatory_fields');

    // load existing profile information
    $profilefields = array();
    $profile_data = get_records_select_assoc('artefact', "owner=? AND artefacttype IN (" . join(",",array_map(create_function('$a','return db_quote($a);'),array_keys($element_list))) . ")", array($USER->get('id')), '','artefacttype, title');
    if ($profile_data == false) {
        $profile_data = array();
    }

    $email = get_field('artefact_internal_profile_email', 'email', 'owner', $sso_session->userid, 'principal', 1);
    if (false == $email) {
        throw new XmlrpcServerException("No email adress for user");
    }

    $userdata = array();
    $userdata['username']                = $user->username;
    $userdata['email']                   = $email;
    $userdata['auth']                    = 'mnet';
    $userdata['confirmed']               = 1;
    $userdata['deleted']                 = 0;
    $userdata['firstname']               = $user->firstname;
    $userdata['lastname']                = $user->lastname;
    $userdata['city']                    = array_key_exists('city', $profile_data) ? $profile_data['city']->title : '';
    $userdata['country']                 = array_key_exists('country', $profile_data) ? $profile_data['country']->title : '';

    if (is_numeric($user->profileicon)) {
        $filename = get_config('dataroot') . 'artefact/file/profileicons/' . ($user->profileicon % 256) . '/'.$user->profileicon;
        if (file_exists($filename) && is_readable($filename)) {
            $userdata['imagehash'] = sha1_file($filename);
        }
    }

    get_service_providers($USER->authinstance);

    // Todo: push application name to list of hosts... update Moodle block to display more info, maybe in 'Other' list
    $userdata['myhosts'] = array();

    return $userdata;
}

/**
 * Retrieve a file for a user calling this function
 * The file is encoded in base64
 * @global object $REMOTEWWWROOT
 * @param string $username
 * @param integer $id Artefact to send
 * @return array The file content encoded in base 64 + file name
 */
function get_file($username, $id) {

    global $REMOTEWWWROOT;

    //check that the user exists
    list ($user, $authinstance) = find_remote_user($username, $REMOTEWWWROOT);
    if (!$user) {
        throw new ExportException("Could not find user $username for $REMOTEWWWROOT");
    }

    //check that the user is owner of the requested file
    safe_require('artefact', 'file');
    $file = artefact_instance_from_id($id);
    if (!record_exists('artefact', 'owner', $user->id, 'id', $id)) {
        throw new ExportException("You are not allowed to get this file.");
    }

    //retrieve the content and send the file encoded in base 64
    $filecontent = base64_encode(file_get_contents($file->get_path()));
    return array($filecontent, $file->name);
}


/**
 * Retrieve list of files/folders matching the search
 * @global object $REMOTEWWWROOT
 * @param string $username
 * @param string $search
 * @return array list of files/folders matching the search
 */
function search_folders_and_files($username, $search='') {

    global $REMOTEWWWROOT;

    //check that the user exists
    list ($user, $authinstance) = find_remote_user($username, $REMOTEWWWROOT);
    if (!$user) {
        throw new ExportException("Could not find user $username for $REMOTEWWWROOT");
    }

    $list = array();
    safe_require('artefact', 'file');
    $filetypes = array_diff(PluginArtefactFile::get_artefact_types(), array('profileicon'));
    foreach ($filetypes as $k => $v) {
        if ($v == 'folder') {
            unset($filetypes[$k]);
        }
    }
    $filetypesql = "('" . join("','", $filetypes) . "')";

    $ownersql = artefact_owner_sql($user->id);

    //retrieve folders and files of a specific Mahara folder
    $sql = "SELECT
                *
            FROM
                {artefact} a
            LEFT JOIN {artefact_tag} at ON (at.artefact = a.id)
            WHERE
                $ownersql
                AND
                (a.title like ? OR at.tag like ?)";
    $list =  array(

            'files'   => get_records_sql_array($sql." AND artefacttype IN $filetypesql ORDER BY title", array('%'.$search.'%','%'.$search.'%')),
            'folders' => get_records_sql_array($sql." AND artefacttype = 'folder' ORDER BY title", array('%'.$search.'%','%'.$search.'%'))
    );

    return $list;
}

/**
 * Retrieve file list in a folder
 * @global object $REMOTEWWWROOT
 * @param string $username
 * @param integer $folderid  folder to browse
 * @return array The complete folder path + list of files for a specific Mahara folder
 */
function get_folder_files($username, $folderid) {

    global $REMOTEWWWROOT;

    //check that the user exists
    list ($user, $authinstance) = find_remote_user($username, $REMOTEWWWROOT);
    if (!$user) {
        throw new ExportException("Could not find user $username for $REMOTEWWWROOT");
    }

    $list = array();
    safe_require('artefact', 'file');
    $filetypes = array_diff(PluginArtefactFile::get_artefact_types(), array('profileicon'));
    foreach ($filetypes as $k => $v) {
        if ($v == 'folder') {
            unset($filetypes[$k]);
        }
    }
    $filetypesql = "('" . join("','", $filetypes) . "')";

    $ownersql = artefact_owner_sql($user->id);

    $folderpath = array(); //the complete folder path (some client could need it)
    if (!empty($folderid)) {
        $pathsql = " AND parent = $folderid";

        //build the path
        $parentids = artefact_get_parents_for_cache($folderid); //the closest parent is on the first key
                                                            //the further parent is on the last key
        foreach ($parentids as $id => $dump) {
            $artefact = get_record('artefact', 'id', $id);
            array_unshift($folderpath, array('path' => $artefact->id, 'name' => $artefact->title));
        }

    } else {
        $pathsql = "AND parent IS NULL";
    }
    array_unshift($folderpath, array('path' => null, 'name' => 'Root'));

    //retrieve folders and files of a specific Mahara folder
    $list =  array(
            'files'   => get_records_select_array('artefact', "artefacttype IN $filetypesql AND $ownersql $pathsql", array(),'title'),
            'folders' => get_records_select_array('artefact', "artefacttype = 'folder' AND $ownersql $pathsql", array(),'title')
    );

    return array($folderpath, $list);
}

function send_content_intent($username) {
    global $REMOTEWWWROOT;
    require_once(get_config('docroot') . 'import/lib.php');

    list ($user, $authinstance) = find_remote_user($username, $REMOTEWWWROOT);
    if (!$user) {
        throw new ImportException(null, "Could not find user $username for $REMOTEWWWROOT");
    }

    if (!is_executable(get_config('pathtounzip'))) {
        throw new ImportException(null, "Cannot find unzip executable");
    }

    if (!$authinstance->weimportcontent) {
        $e = new ImportException(null, 'Importing content is disabled');
        $e->set_log_off(); // we don't want these ones.
        throw $e;
    }

    $queue = PluginImport::create_new_queue($user->id, null, $REMOTEWWWROOT, 0);

    return array(
        'sendtype' => (($queue->queue) ? 'queue' : 'immediate'),
        'token' => $queue->token,
    );
}

function send_content_ready($token, $username, $format, $importdata, $fetchnow=false) {
    global $REMOTEWWWROOT;
    require_once(get_config('docroot') . 'import/lib.php');

    list ($user, $authinstance) = find_remote_user($username, $REMOTEWWWROOT);
    if (!$user) {
        throw new ImportException(null, "Could not find user $username for $REMOTEWWWROOT");
    }

    // go verify the token
    if (!$queue = get_record('import_queue', 'token', $token, 'host', $REMOTEWWWROOT)) {
        throw new ImportException(null, "Could not find queue record with given token for username $username for $REMOTEWWWROOT");
    }

    if (strtotime($queue->expirytime) < time()) {
        throw new ImportException(null, "Queue record has expired");
    }

    $class = null;
    try {
        $class = PluginImport::class_from_format($format);
    } catch (Exception $e) {
        throw new ImportException(null, "Invalid format $format");
    }

    $queue->format = $format;
    if ($class == 'PluginImportLeap') {
        // don't import persondata over mnet
        // because it will just silently overwrite stuff
        // which is not really desirable.
        $queue->loglevel = get_config('leapovermnetloglevel');
        $importdata['skippersondata'] = true;
    }
    $queue->data = serialize($importdata);
    update_record('import_queue', $queue);
    $tr = new MnetImporterTransport($queue);
    try {
        $tr->validate_import_data();
    } catch (Exception $e) {
        throw new ImportException(null, 'Invalid importdata: ' . $e->getMessage());
    }



    if (!array_key_exists('totalsize', $importdata)) {
        throw new ImportException(null, 'Invalid importdata: missing totalsize');
    }

    if (!$user->quota_allowed($importdata['totalsize'])) {
        $e = new ImportException(null, 'Exceeded user quota');
        $e->set_log_off();
        throw $e;
    }


    $result = new StdClass;
    if ($fetchnow && PluginImport::import_immediately_allowed()) {
        // either immediately spawn a curl request to go fetch the file
        $importer = PluginImport::create_importer($queue->id, $tr, $queue);
        $importer->prepare();
        try {
            $importer->validate_transported_data($tr);
        } catch (Exception $e) {
            throw new ImportException(null, 'Invalid importdata: ' . $e->getMessage());
        }
        $importer->process();
        $importer->cleanup();
        delete_records('import_queue', 'id', $queue->id);
        $result->status = true;
        $result->type = 'complete';
        $returndata = $importer->get_return_data();
        $result->querystring = '?';
        foreach ($importer->get_return_data() as $k => $v) {
            $result->querystring .= $k . '=' . $v . '&';
        }
        $importer->get('importertransport')->cleanup();
    } else {
        // or set ready to 1 for the next cronjob to go fetch it.
        $result->status = set_field('import_queue', 'ready', 1, 'id', $queue->id);
        $result->type = 'queued';
    }
    return $result;
}

/**
 * If we're an IDP, kill_children will kill the session of the given user here, 
 * as well as at any other children
 *
 * NOTE: well, currently it doesn't call kill_child on any other children, but 
 * it will kill the local sessions for the user
 *
 * @param   string  $username       Username for session to kill
 * @param   string  $useragent      SHA1 hash of user agent to look for
 * @return  string                  A plaintext report of what has happened
 */
function kill_children($username, $useragent) {
    global $REMOTEWWWROOT; // comes from server.php
    //require_once(get_config('docroot') .'api/xmlrpc/client.php');

    // We've received a logout request for user X. In Mahara, usernames are unique. So we check that user X 
    // has an authinstance that would have been able to SSO to the remote site.
    $userid = get_field('usr', 'id', 'username', $username);
    $providers = get_service_providers(get_field('usr', 'authinstance', 'username', $username));

    $approved = false;
    foreach ($providers as $provider) {
        if ($provider['wwwroot'] == $REMOTEWWWROOT) {
            $approved = true;
            break;
        }
    }

    if (false == $approved) {
        return 'This host is not permitted to kill sessions for this username';
    }

    $mnetsessions = get_records_select_array('sso_session', 'userid = ? AND useragent = ?', array($userid, $useragent));

    // Prepare to destroy local sessions associated with the user
    $start = ob_start();
    $uc = ini_get('session.use_cookies');
    ini_set('session.use_cookies', false);
    $sesscache = isset($_SESSION) ? clone($_SESSION) : null;
    $sessidcache = session_id();
    session_write_close();
    unset($_SESSION);

    foreach($mnetsessions as $mnetsession) {
        // Kills all local sessions associated with this user
        // TODO: We should send kill_child requests to the remote servers too
        session_id($mnetsession->sessionid);
        session_start();
        session_unregister("USER");
        session_unregister("SESSION");
        unset($_SESSION);
        $_SESSION = array();
        session_destroy();
        session_write_close();
    }

    // We're done destroying local sessions
    ini_set('session.use_cookies', $uc);
    if ($sessidcache) {
        session_name(get_config('cookieprefix') . 'mahara');
        session_id($sessidcache);
        session_start();
        $_SESSION = ($sesscache) ? clone($sesscache) : null;
        session_write_close();
    }
    $end = ob_end_clean();

    delete_records('sso_session',
                   'useragent', $useragent,
                   'userid',    $userid);

    return true;
}

function xmlrpc_not_implemented() {
    return true;
}

function get_views_for_user($username, $query=null) {
    global $REMOTEWWWROOT, $USER;

    list ($user, $authinstance) = find_remote_user($username, $REMOTEWWWROOT);
    if (!$user) {
        return false;
    }

    $USER->reanimate($user->id, $authinstance->instanceid);
    require_once('view.php');
    $data = View::view_search($query, null, (object) array('owner' => $USER->get('id')));
    $data->displayname = display_name($user);
    if ($data->count) {
        foreach ($data->data as &$v) {
            $v['url'] = '/view/view.php?id=' . $v['id'];
            $v['fullurl'] = get_config('wwwroot') . 'view/view.php?id=' . $v['id'];
        }
    }
    return $data;
}

function submit_view_for_assessment($username, $viewid) {
    global $REMOTEWWWROOT;

    list ($user, $authinstance) = find_remote_user($username, $REMOTEWWWROOT);
    if (!$user) {
        return false;
    }

    $viewid = (int) $viewid;
    if (!$viewid) {
        return false;
    }

    require_once('view.php');
    $view = new View($viewid);

    $view->set('submittedhost', $authinstance->config['wwwroot']);
    $view->set('submittedtime', db_format_timestamp(time()));

    // Create secret key
    $access = View::new_token($view->get('id'), false);

    $data = array(
        'id'          => $view->get('id'),
        'title'       => $view->get('title'),
        'description' => $view->get('description'),
        'fullurl'     => get_config('wwwroot') . 'view/view.php?id=' . $view->get('id') . '&mt=' . $access->token,
        'url'         => '/view/view.php?id=' . $view->get('id') . '&mt=' . $access->token,
        'accesskey'   => $access->token,
    );

    foreach (plugins_installed('artefact') as $plugin) {
        safe_require('artefact', $plugin->name);
        $classname = generate_class_name('artefact', $plugin->name);
        if (is_callable($classname . '::view_submit_external_data')) {
            $data[$plugin->name] = call_static_method($classname, 'view_submit_external_data', $view->get('id'));
        }
    }

    $view->commit();

    // Lock view contents
    require_once(get_config('docroot') . 'artefact/lib.php');
    ArtefactType::update_locked($user->get('id'));

    return $data;
}

function release_submitted_view($viewid, $assessmentdata, $teacherusername) {
    global $REMOTEWWWROOT, $USER;

    require_once('view.php');
    $view = new View($viewid);
    list ($teacher, $authinstance) = find_remote_user($teacherusername, $REMOTEWWWROOT);

    db_begin();
    foreach (plugins_installed('artefact') as $plugin) {
        safe_require('artefact', $plugin->name);
        $classname = generate_class_name('artefact', $plugin->name);
        if (is_callable($classname . '::view_release_external_data')) {
            call_static_method($classname, 'view_release_external_data', $view, $assessmentdata, $teacher ? $teacher->id : 0);
        }
    }

    // Release the view for editing
    $view->set('submittedhost', null);
    $view->set('submittedtime', null);
    $view->commit();
    db_commit();
}

/**
 * Given a USER, get all Service Providers for that User, based on child auth
 * instances of its canonical auth instance
 */
function get_service_providers($instance) {
    static $cache = array();

    if (defined('INSTALLER')) {
        return array();
    }

    if (array_key_exists($instance, $cache)) {
        return $cache[$instance];
    }

    $query = "
        SELECT
            h.name,
            a.ssolandurl,
            h.wwwroot,
            aic.instance
        FROM
            {auth_instance_config} aic,
            {auth_instance_config} aic2,
            {auth_instance_config} aic3,
            {host} h,
            {application} a
        WHERE
          ((aic.value = '1' AND
            aic.field = 'theyautocreateusers' ) OR
           (aic.value = ?  AND
            aic.field = 'parent')) AND

            aic.instance = aic2.instance AND
            aic2.field = 'wwwroot' AND
            aic2.value = h.wwwroot AND

            aic.instance = aic3.instance AND
            aic3.field = 'wessoout' AND
            aic3.value = '1' AND

            a.name = h.appname";
    try {
        $results = get_records_sql_assoc($query, array('value' => $instance));
    } catch (SQLException $e) {
        // Table doesn't exist yet
        return array();
    }

    if (false == $results) {
        $results = array();
    }

    foreach($results as $key => $result) {
        $results[$key] = get_object_vars($result);
    }

    $cache[$instance] = $results;
    return $cache[$instance];
}

function get_public_key($uri, $application=null) {

    static $keyarray = array();
    if (isset($keyarray[$uri])) {
        return $keyarray[$uri];
    }

    $openssl = OpenSslRepo::singleton();

    if (empty($application)) {
        $application = 'moodle';
    }

    $xmlrpcserverurl = get_field('application', 'xmlrpcserverurl', 'name', $application);
    if (empty($xmlrpcserverurl)) {
        throw new XmlrpcClientException('Unknown application');
    } 
    $wwwroot = dropslash(get_config('wwwroot'));

    $rq = xmlrpc_encode_request('system/keyswap', array($wwwroot, $openssl->certificate), array("encoding" => "utf-8"));

    $config = array(
        CURLOPT_URL => $uri . $xmlrpcserverurl,
        CURLOPT_POST => true,
        CURLOPT_USERAGENT => 'Moodle',
        CURLOPT_POSTFIELDS => $rq,
        CURLOPT_HTTPHEADER => array("Content-Type: text/xml charset=UTF-8", 'Expect: '),
    );

    $result = mahara_http_request($config);

    if (!empty($result->errno)) {
        throw new XmlrpcClientException('Curl error: ' . $result->errno . ': ' . $result->error);
    }
    if (empty($result->data)) {
        throw new XmlrpcClientException('CURL connection failed');
    }

    $response_code        = $result->info['http_code'];
    $response_code_prefix = substr($response_code, 0, 1);

    if ('2' != $response_code_prefix) {
        if ('4' == $response_code_prefix) {
            throw new XmlrpcClientException('Client error code: ', $response_code);
        } elseif ('5' == $response_code_prefix) {
            throw new XmlrpcClientException('An error occurred at the remote server. Code: ', $response_code);
        }
    }

    $res = xmlrpc_decode($result->data);

    // XMLRPC error messages are returned as an array
    // We are expecting a string
    if (!is_array($res)) {
        $keyarray[$uri] = $res;
        $credentials=array();
        if (strlen(trim($keyarray[$uri]))) {
            $credentials = openssl_x509_parse($keyarray[$uri]);
            $host = $credentials['subject']['CN'];
            if (strpos($uri, $host) !== false) {
                return $keyarray[$uri];
            }
            throw new XmlrpcClientException('The remote site sent us a key that is valid for ' . $host . ' instead of their hostname (' . $uri . ')', 500);
        }
    } else {
        throw new XmlrpcClientException($res['faultString'], $res['faultCode']);
    }
    return false;
}

/**
 * Output a valid XML-RPC error message.
 *
 * @param  string   $message              The error message
 * @param  int      $code                 Unique identifying integer
 * @return string                         An XMLRPC error doc
 */
function xmlrpc_error($message, $code) {
    echo <<<EOF
<?xml version="1.0"?>
<methodResponse>
   <fault>
      <value>
         <struct>
            <member>
               <name>faultCode</name>
               <value><int>$code</int></value>
            </member>
            <member>
               <name>faultString</name>
               <value><string>$message</string></value>
            </member>
         </struct>
      </value>
   </fault>
</methodResponse>
EOF;
}

function xmlenc_envelope_strip(&$xml, $oldkeyok=false) {
    $openssl           = OpenSslRepo::singleton();
    $payload_encrypted = true;
    $data              = base64_decode($xml->EncryptedData->CipherData->CipherValue);
    $key               = base64_decode($xml->EncryptedKey->CipherData->CipherValue);
    $payload           = '';    // Initialize payload var
    $payload           = $openssl->openssl_open($data, $key, $oldkeyok);
    $xml               = parse_payload($payload);
    return $payload;
}

function parse_payload($payload) {
    try {
        $xml = new SimpleXMLElement($payload);
        return $xml;
    } catch (Exception $e) {
        throw new MaharaException('Encrypted payload is not a valid XML document', 6002);
    }
}

function get_peer($wwwroot, $cache=true) {

    $wwwroot = (string)$wwwroot;
    static $peers = array();
    if ($cache) {
        if (isset($peers[$wwwroot])) return $peers[$wwwroot];
    }

    require_once(get_config('libroot') . 'peer.php');
    $peer = new Peer();

    if (!$peer->findByWwwroot($wwwroot)) {
        // Bootstrap unknown hosts?
        throw new MaharaException("We don't have a record for your webserver ($wwwroot) in our database", 6003);
    }
    $peers[$wwwroot] = $peer;
    return $peers[$wwwroot];
}

function get_peer_from_instanceid($authinstanceid) {
    $sql = 'SELECT
                h.wwwroot, h.name
            FROM
                {auth_instance_config} aic,
                {host} h
            WHERE
                aic.value = h.wwwroot AND
                aic.instance = ? AND aic.field = \'wwwroot\'';
    return get_record_sql($sql, array($authinstanceid));
}

/**
 * Check that the signature has been signed by the remote host.
 */
function xmldsig_envelope_strip(&$xml) {

    $signature      = base64_decode($xml->Signature->SignatureValue);
    $payload        = base64_decode($xml->object);
    $wwwroot        = (string)$xml->wwwroot;
    $timestamp      = $xml->timestamp;
    $peer           = get_peer($wwwroot);


    // Does the signature match the data and the public cert?
    $signature_verified = openssl_verify($payload, $signature, $peer->certificate);

    if ($signature_verified == 0) {
        // Maybe the remote host is using a new key?
        // Make a dummy request so we'll be given a new key
        log_info("Signature verification for message from $wwwroot failed, checking to see if they have a new signature for us");
        require_once(get_config('docroot') . 'api/xmlrpc/client.php');
        $client = new Client();
        $client->set_method('system/listServices')
               ->send($wwwroot);

        // Now use the new key and re-try verification
        $peer = get_peer($wwwroot, false);
        $signature_verified = openssl_verify($payload, $signature, $peer->certificate);
    }

    if ($signature_verified == 1) {
        // Parse the XML
        try {
            $xml = new SimpleXMLElement($payload);
            return $payload;
        } catch (Exception $e) {
            throw new MaharaException('Signed payload is not a valid XML document', 6007);
        }
    }

    throw new MaharaException('An error occurred while trying to verify your message signature', 6004);
}

/**
 * Encrypt a message and return it in an XML-Encrypted document
 *
 * This function can encrypt any content, but it was written to provide a system
 * of encrypting XML-RPC request and response messages. The message does not 
 * need to be text - binary data should work.
 * 
 * Asymmetric keys can encrypt only small chunks of data. Usually 1023 or 2047 
 * characters, depending on the key size. So - we generate a symmetric key and 
 * use the asymmetric key to secure it for transport with the data.
 *
 * We generate a symmetric key
 * We encrypt the symmetric key with the public key of the remote host
 * We encrypt our content with the symmetric key
 * We base64 the key & message data.
 * We identify our wwwroot - this must match our certificate's CN
 *
 * Normally, the XML-RPC document will be parceled inside an XML-SIG envelope.
 * We parcel the XML-SIG document inside an XML-ENC envelope.
 *
 * See the {@Link http://www.w3.org/TR/xmlenc-core/ XML-ENC spec} at the W3c
 * site
 *
 * @param  string   $message              The data you want to sign
 * @param  string   $remote_certificate   Peer's certificate in PEM format
 * @return string                         An XML-ENC document
 */
function xmlenc_envelope($message, $remote_certificate) {

    // Generate a key resource from the remote_certificate text string
    $publickey = openssl_get_publickey($remote_certificate);

    if ( gettype($publickey) != 'resource' ) {
        // Remote certificate is faulty.
        throw new MaharaException('Could not generate public key resource from certificate', 1);
    }

    // Initialize vars
    $wwwroot = dropslash(get_config('wwwroot'));
    $encryptedstring = '';
    $symmetric_keys = array();

    //      passed by ref ->      &$encryptedstring &$symmetric_keys
    $bool = openssl_seal($message, $encryptedstring, $symmetric_keys, array($publickey));
    $message = base64_encode($encryptedstring);
    $symmetrickey = base64_encode(array_pop($symmetric_keys));
    $zed = 'nothing';

    return <<<EOF
<?xml version="1.0" encoding="iso-8859-1"?>
    <encryptedMessage>
        <EncryptedData Id="ED" xmlns="http://www.w3.org/2001/04/xmlenc#">
            <EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#arcfour"/>
            <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
                <ds:RetrievalMethod URI="#EK" Type="http://www.w3.org/2001/04/xmlenc#EncryptedKey"/>
                <ds:KeyName>XMLENC</ds:KeyName>
            </ds:KeyInfo>
            <CipherData>
                <CipherValue>$message</CipherValue>
            </CipherData>
        </EncryptedData>
        <EncryptedKey Id="EK" xmlns="http://www.w3.org/2001/04/xmlenc#">
            <EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-1_5"/>
            <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
                <ds:KeyName>SSLKEY</ds:KeyName>
            </ds:KeyInfo>
            <CipherData>
                <CipherValue>$symmetrickey</CipherValue>
            </CipherData>
            <ReferenceList>
                <DataReference URI="#ED"/>
            </ReferenceList>
            <CarriedKeyName>XMLENC</CarriedKeyName>
        </EncryptedKey>
        <wwwroot>{$wwwroot}</wwwroot>
        <X1>$zed</X1>
    </encryptedMessage>
EOF;
}

/**
 * Sign a message and return it in an XML-Signature document
 *
 * This function can sign any content, but it was written to provide a system of
 * signing XML-RPC request and response messages. The message will be base64
 * encoded, so it does not need to be text.
 *
 * We compute the SHA1 digest of the message.
 * We compute a signature on that digest with our private key.
 * We link to the public key that can be used to verify our signature.
 * We base64 the message data.
 * We identify our wwwroot - this must match our certificate's CN
 *
 * The XML-RPC document will be parceled inside an XML-SIG document, which holds
 * the base64_encoded XML as an object, the SHA1 digest of that document, and a
 * signature of that document using the local private key. This signature will
 * uniquely identify the RPC document as having come from this server.
 *
 * See the {@Link http://www.w3.org/TR/xmldsig-core/ XML-DSig spec} at the W3c
 * site
 *
 * @param  string   $message              The data you want to sign
 * @return string                         An XML-DSig document
 */
function xmldsig_envelope($message) {

    $openssl = OpenSslRepo::singleton();
    $wwwroot = dropslash(get_config('wwwroot'));
    $digest = sha1($message);

    $sig = base64_encode($openssl->sign_message($message));
    $message = base64_encode($message);
    $time = time();
    // TODO: Provide RESTful access to our public key as per KeyInfo element

return <<<EOF
<?xml version="1.0" encoding="iso-8859-1"?>
    <signedMessage>
        <Signature Id="MoodleSignature" xmlns="http://www.w3.org/2000/09/xmldsig#">
            <SignedInfo>
                <CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>
                <SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
                <Reference URI="#XMLRPC-MSG">
                    <DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
                    <DigestValue>$digest</DigestValue>
                </Reference>
            </SignedInfo>
            <SignatureValue>$sig</SignatureValue>
            <KeyInfo>
                <RetrievalMethod URI="{$wwwroot}/api/xmlrpc/publickey.php"/>
            </KeyInfo>
        </Signature>
        <object ID="XMLRPC-MSG">$message</object>
        <wwwroot>{$wwwroot}</wwwroot>
        <timestamp>$time</timestamp>
    </signedMessage>
EOF;

}

/**
 * Good candidate to be a singleton
 */
class OpenSslRepo {

    private $keypair = array();

    /**
     * Sign a message with our private key so that peers can verify that it came
     * from us.
     *
     * @param  string   $message
     * @return string
     * @access public
     */
    public function sign_message($message) {
        $signature = '';
        $bool      = openssl_sign($message, $signature, $this->keypair['privatekey']);
        return $signature;
    }

    /**
     * Decrypt some data using our private key and an auxiliary symmetric key. 
     * The symmetric key encrypted the data, and then was itself encrypted with
     * our public key.
     * This is because asymmetric keys can only safely be used to encrypt 
     * relatively short messages.
     *
     * @param string   $data
     * @param string   $key
     * @param bool     $oldkeyok If true, we will simply return the data rather 
     *                           than complaining about the key being old (if 
     *                           we could decrypt it with an older key)
     * @return string
     * @access public
     */
    public function openssl_open($data, $key, $oldkeyok=false) {
        $payload = '';
        $isOpen = openssl_open($data, $payload, $key, $this->keypair['privatekey']);

        if (!empty($isOpen)) {
            return $payload;
        } else {
            // Decryption failed... let's try our archived keys
            $openssl_history = $this->get_history();
            foreach($openssl_history as $keyset) {
                $keyresource = openssl_pkey_get_private($keyset['keypair_PEM']);
                $isOpen      = openssl_open($data, $payload, $key, $keyresource);
                if ($isOpen) {
                    // It's an older code, sir, but it checks out
                    if ($oldkeyok) {
                        return $payload;
                    }
                    else {
                        // We notify the remote host that the key has changed
                        throw new CryptException($this->keypair['certificate'], 7025);
                    }
                }
            }
        }
        throw new CryptException('We know nothing about the key used to encrypt this message', 7025);
    }

    /**
     * Singleton function keeps us from generating multiple instances of this
     * class
     *
     * @return object   The class instance
     * @access public
     */
    public static function singleton() {
        //single instance
        static $instance;

        //if we don't have the single instance, create one
        if (!isset($instance)) {
            $instance = new OpenSslRepo();
        }
        return($instance);
    }

    /**
     * This is a singleton - don't try to create an instance by doing:
     * $openssl = new OpenSslRepo();
     * Instead, use:
     * $openssl = OpenSslRepo::singleton();
     * 
     */
    private function __construct() {
        if (empty($this->keypair)) {
            $this->get_keypair();
	    $this->calculate_fingerprints();
            $this->keypair['privatekey'] = openssl_pkey_get_private($this->keypair['keypair_PEM']);
            $this->keypair['publickey']  = openssl_pkey_get_public($this->keypair['certificate']);
        }
        return $this;
    }

    /**
     * Utility function to get old SSL keys from the config table, or create a 
     * blank record if none exists.
     *
     * @return array    Array of keypair hashes
     * @access private
     */
    private function get_history() {
        $openssl_history = get_field('config', 'value', 'field', 'openssl_history');
        if (empty($openssl_history)) {
            $openssl_history = array();
            $record = new stdClass();
            $record->field = 'openssl_history';
            $record->value = serialize($openssl_history);
            insert_record('config', $record);
        } else {
            $openssl_history = unserialize($openssl_history);
        }
        return $openssl_history;
    }

    /**
     * Utility function to stash old SSL keys in the config table. It will retain
     * a max of 'openssl_generations' which is itself a value in config.
     *
     * @param  array    Array of keypair hashes
     * @return bool
     * @access private
     */
    private function save_history($openssl_history) {
        $openssl_generations = get_field('config', 'value', 'field', 'openssl_generations');
        if (empty($openssl_generations)) {
            set_config('openssl_generations', 6);
            $openssl_generations = 6;
        }
        if (count($openssl_history) > $openssl_generations) {
            $openssl_history = array_slice($openssl_history, 0, $openssl_generations);
        }
        return set_config('openssl_history', serialize($openssl_history));
    }

    /**
     * The get Overloader will let you pull out the 'certificate' and 'expires'
     * values
     *
     * @param  string    Name of the value you want
     * @return mixed     The value of the thing you asked for or null (if it 
     *                   doesn't exist or is private)
     * @access public
     */
    public function __get($name) {
        if ('certificate' === $name) return $this->keypair['certificate'];
        if ('expires' === $name)     return $this->keypair['expires'];
        if ('sha1_fingerprint' === $name) return $this->keypair['sha1_fingerprint'];
        if ('md5_fingerprint' === $name ) return $this->keypair['md5_fingerprint'];
        return null;
    }

    /**
     * Get the keypair. If it doesn't exist, create it. If it's out of date, 
     * archive it and create a fresh pair.
     *
     * @param  bool      True if you want to force fresh keys to be generated
     * @return bool     
     * @access private
     */
    public function get_keypair($regenerate = null) {
        $this->keypair = array();
        $records       = null;
        
        if ($records = get_records_select_menu('config', "field IN ('openssl_keypair', 'openssl_keypair_expires')", 'field', 'field, value')) {
            list($this->keypair['certificate'], $this->keypair['keypair_PEM']) = explode('@@@@@@@@', $records['openssl_keypair']);
            $this->keypair['expires'] = $records['openssl_keypair_expires'];
            if (empty($regenerate) && $this->keypair['expires'] > time()) {
                return true;
            }
        }

        // Save out the old key
        $openssl_history = $this->get_history();
        array_unshift($openssl_history, $this->keypair);
        $this->save_history($openssl_history);

        // Initialize a new set of SSL keys
        $this->keypair = array();
        $this->generate_keypair();

        // A record for the keys
        $keyrecord = new stdClass();
        $keyrecord->field = 'openssl_keypair';
        $keyrecord->value = implode('@@@@@@@@', $this->keypair);

        // A convenience record for the keys' expire time (UNIX timestamp)
        $expiresrecord        = new stdClass();
        $expiresrecord->field = 'openssl_keypair_expires';

        // Getting the expire timestamp is convoluted, but required:
        $credentials = openssl_x509_parse($this->keypair['certificate']);
        if (is_array($credentials) && isset($credentials['validTo_time_t'])) {
            $expiresrecord->value = $credentials['validTo_time_t'];
            $this->keypair['expires'] = $credentials['validTo_time_t'];
        }

        if (empty($records)) {
            db_begin();
            insert_record('config', $keyrecord);
            insert_record('config', $expiresrecord);
            db_commit();
        }
        else {
            db_begin();
            update_record('config', $keyrecord,     array('field' => 'openssl_keypair'));
            update_record('config', $expiresrecord, array('field' => 'openssl_keypair_expires'));
            db_commit();
        }
        log_info("New public key has been generated. It expires " . date('Y/m/d h:i:s', $credentials['validTo_time_t']));
        return true;
    }

    /**
     * Generate public/private keys and store in the config table
     *
     * Use the distinguished name provided to create a CSR, and then sign that CSR
     * with the same credentials. Store the keypair you create in the config table.
     * If a distinguished name is not provided, create one using the fullname of
     * 'the course with ID 1' as your organization name, and your hostname (as
     * detailed in $CFG->wwwroot).
     *
     * @param   array  $dn  The distinguished name of the server
     * @return  string      The signature over that text
     */
    private function generate_keypair() {
        $host = get_hostname_from_uri(get_config('wwwroot'));

        $organization = get_config('sitename');
        $email        = get_config('noreplyaddress');
        $country      = get_config('country');
        $province     = get_config('province');
        $locality     = get_config('locality');

        //TODO: Create additional fields on site setup and read those from 
        //      config. Then remove the next 3 linez
        if (empty($country))  $country  = 'NZ';
        if (empty($province)) $province = 'Wellington';
        if (empty($locality)) $locality = 'Te Aro';

        $dn = array(
           "countryName" => $country,
           "stateOrProvinceName" => $province,
           "localityName" => $locality,
           "organizationName" => $organization,
           "organizationalUnitName" => 'Mahara',
           "commonName" => get_config('wwwroot'),
           "emailAddress" => $email
        );

        // ensure we remove trailing slashes
        $dn["commonName"] = preg_replace(':/$:', '', $dn["commonName"]);

        $config = array();
        $opensslcnf = get_config('opensslcnf');
        if ($opensslcnf) {
            $config['config'] = $opensslcnf;
        } else {
            $config = null;
        }

        if (!$new_key = openssl_pkey_new($config)) {
            throw new ConfigException(get_string('errorcouldnotgeneratenewsslkey', 'auth'));
        }

        if (!$csr_rsc = openssl_csr_new($dn, $new_key, $config)) {
            // This behaviour has been observed once before, on an ubuntu hardy box. 
            // The php5-openssl package was installed but somehow openssl 
            // wasn't.
            throw new ConfigException(get_string('errorcouldnotgeneratenewsslkey', 'auth'));
        }
        $selfSignedCert = openssl_csr_sign($csr_rsc, null, $new_key, 365 /*days*/, $config);
        unset($csr_rsc); // Free up the resource

        // We export our self-signed certificate to a string.
        openssl_x509_export($selfSignedCert, $this->keypair['certificate']);
        openssl_x509_free($selfSignedCert);

        // Export your public/private key pair as a PEM encoded string. You
        // can protect it with an optional passphrase if you wish.
        $export = openssl_pkey_export($new_key, $this->keypair['keypair_PEM'] , null /*$passphrase */, $config);
        openssl_pkey_free($new_key);
        unset($new_key); // Free up the resource

        // Calculate fingerprints
        $this->calculate_fingerprints();

        return $this;
    }


    /**
     * Calculates the SHA1 and MD5 fingerprints of the certificate in DER format
     * It does the same as the fingerprint commandline option in x509
     * command. For example:
     *
     *        $ openssl x509 -in cert_file -fingerprint -sha1
     *        $ openssl x509 -in cert_file -fingerprint -md5
     */

    private function calculate_fingerprints () {

        // Convert the certificate to DER and calculate the digest

        $pem_cert = $this->keypair['certificate'];

        $from_pos = strpos($pem_cert, "-----BEGIN CERTIFICATE-----");
        if ( $from_pos === false ) {
            throw new CryptException("Certificate not in PEM format");
        }
        $from_pos = $from_pos + 27;

        $to_pos = strpos($pem_cert, "-----END CERTIFICATE-----");
        if ( $to_pos === false ) {
            throw new CryptException("Certificate not in PEM format");
        }

        $der_cert = base64_decode(substr($pem_cert, $from_pos, $to_pos - $from_pos));
        if ( $der_cert === FALSE ) {
            throw new CryptException("Certificate not in PEM format");
        }

        $_sha1_fingerprint = sha1($der_cert);
        if ( $_sha1_fingerprint === FALSE ) {
            throw new CryptException("Error calculating sha1 fingerprint");
        }
        $_md5_fingerprint = md5($der_cert);
        if ( $_md5_fingerprint === FALSE ) {
            throw new CryptException("Error calculating md5 fingerprint");
        }

        unset($der_cert);

        $_sha1_fingerprint = strtoupper($_sha1_fingerprint);
        $_md5_fingerprint  = strtoupper($_md5_fingerprint);

        $sha1_fingerprint = $_sha1_fingerprint[0];
        for ( $i = 1, $to = strlen($_sha1_fingerprint); $i < $to ; $i++ ) {
            if ( $i % 2 == 0 ) {
                $sha1_fingerprint .= ":" . $_sha1_fingerprint[$i];
            } else {
                $sha1_fingerprint .= $_sha1_fingerprint[$i];
            }
        }

        $md5_fingerprint = $_md5_fingerprint[0];
        for ( $i = 1, $to = strlen($_md5_fingerprint); $i < $to ; $i++ ) {
            if ( $i % 2 == 0 ) {
                $md5_fingerprint .= ":" . $_md5_fingerprint[$i];
            } else {
                $md5_fingerprint .= $_md5_fingerprint[$i];
            }
        }

        $this->keypair['sha1_fingerprint'] = $sha1_fingerprint;
        $this->keypair['md5_fingerprint']  = $md5_fingerprint;

    }

}

class PublicKey {

    private   $credentials = array();
    private   $wwwroot     = '';
    private   $certificate = '';

    function __construct($keystring, $wwwroot) {

        $this->credentials = openssl_x509_parse($keystring);
        $this->wwwroot     = dropslash($wwwroot);
        $this->certificate = $keystring;

        if ($this->credentials == false) {
            throw new CryptException(get_string('errornotvalidsslcertificate', 'auth'), 1);
            return false;
        } elseif ($this->credentials['subject']['CN'] != $this->wwwroot) {
            throw new CryptException(get_string('errorcertificateinvalidwwwroot', 'auth', $this->credentials['subject']['CN'], $this->wwwroot), 1);
            return false;
        } else {
            return $this->credentials;
        }
    }

    function __get($name) {
        if ('expires' == $name) return $this->credentials['validTo_time_t'];
        return $this->{$name};
    }
}
