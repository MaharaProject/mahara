<?php
/**
 *
 * @package    mahara
 * @subpackage auth-saml
 * @author     Piers Harding <piers@catalyst.net.nz>
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

// disable external entities
libxml_disable_entity_loader(true);

// make sure that ssphp directories are looked in first
$path = realpath('../lib');
set_include_path($path . PATH_SEPARATOR . get_include_path());

// calculate the log process name
$LOG_PROCESS = explode('.', $_SERVER['HTTP_HOST']);
$LOG_PROCESS = 'ssphp-' . array_shift($LOG_PROCESS);

$metadata_files = glob(AuthSaml::get_metadata_path() . '*.xml');
$metadata_sources = array();
foreach ($metadata_files as $file) {
    $metadata_sources[]= array('type' => 'xml', 'file' => $file);
}

// Fix up session handling config - to match Mahara
$memcache_config = array();
if (get_config('memcacheservers') || extension_loaded('memcache')) {
    if (empty(get_config('ssphpsessionhandler'))) {
        $sessionhandler = 'memcache';
    }
    else {
        $sessionhandler = get_config('ssphpsessionhandler');
    }
    $servers = get_config('memcacheservers');
    if (empty($servers)) {
        $servers = 'localhost';
    }
    $servers = explode(',', $servers);

    foreach ($servers as $server) {
        $url = parse_url($server);
        $host = !empty($url['host']) ? $url['host'] : $url['path'];
        $port = !empty($url['port']) ? $url['port'] : 11211;
        $memcache_config[] = array('hostname' => $host, 'port'=> $port);
    }
}
else {
    $sessionhandler = 'phpsession';
}

/*
 * The configuration of simpleSAMLphp
 *
 * $Id: config.php 1881 2009-10-20 09:14:47Z olavmrk $
 */

$config = array (

    // Force HTTPS
    // 'forcehttps' => TRUE,
    // 'forceport'  => '',

    /**
     * Setup the following parameters to match the directory of your installation.
     * See the user manual for more details.
     */
    'baseurlpath'           => 'simplesaml/',
    'certdir'               => 'cert/',
    'loggingdir'            => '/tmp/',
    'datadir'               => 'data/',

    /*
     * A directory where simpleSAMLphp can save temporary files.
     *
     * SimpleSAMLphp will attempt to create this directory if it doesn't exist.
     */
    'tempdir'               => '/tmp/simplesaml',

    /**
     * If you set the debug parameter to true, all SAML messages will be visible in the
     * browser, and require the user to click the submit button. If debug is set to false,
     * Browser/POST SAML messages will be automaticly submitted.
     */
    'debug'                 => TRUE,
    'showerrors'            => TRUE,
    // 'errors.show_function'  => array('sspmod_avs_Error_Show', 'show'),

    /**
     * This option allows you to enable validation of XML data against its
     * schemas. A warning will be written to the log if validation fails.
     */
    'debug.validatexml' => FALSE,

    /**
     * This password must be kept secret, and modified from the default value 123.
     * This password will give access to the installation page of simpleSAMLphp with
     * metadata listing and diagnostics pages.
     */
    'auth.adminpassword'        => 'letmein',
    'admin.protectindexpage'    => false,
    'admin.protectmetadata'     => false,

    /*
     * Proxy to use for retrieving URLs.
     *
     * Example:
     *   'proxy' => 'tcp://proxy.example.com:5100'
     */

    /**
     * This is a secret salt used by simpleSAMLphp when it needs to generate a secure hash
     * of a value. It must be changed from its default value to a secret value. The value of
     * 'secretsalt' can be any valid string of any length.
     *
     * A possible way to generate a random salt is by running the following command from a unix shell:
     * tr -c -d '0123456789abcdefghijklmnopqrstuvwxyz' </dev/urandom | dd bs=32 count=1 2>/dev/null;echo
     */
    'secretsalt' => '7d77l9eb0u6vuhvz3jadpn0bhjmxo97e',

    /*
     * Some information about the technical persons running this installation.
     * The email address will be used as the recipient address for error reports, and
     * also as the technical contact in generated metadata.
     */
    'technicalcontact_name'     => 'Administrator',
    'technicalcontact_email'    => 'piers@toad.local.net',

    /*
     * The timezone of the server. This option should be set to the timezone you want
     * simpleSAMLphp to report the time in. The default is to guess the timezone based
     * on your system timezone.
     *
     * See this page for a list of valid timezones: http://php.net/manual/en/timezones.php
     */
    'timezone' => NULL,

    /*
     * Logging.
     *
     * define the minimum log level to log
     * LOG_ERR      No statistics, only errors
     * LOG_WARNING  No statistics, only warnings/errors
     * LOG_NOTICE   Statistics and errors
     * LOG_INFO     Verbose logs
     * LOG_DEBUG    Full debug logs - not reccomended for production
     *
     * Choose logging handler.
     *
     * Options: [syslog,file,errorlog]
     *
     */
    'logging.level'         => LOG_NOTICE,
    'logging.handler'       => 'file',

    /*
     * Choose which facility should be used when logging with syslog.
     *
     * These can be used for filtering the syslog output from simpleSAMLphp into its
     * own file by configuring the syslog daemon.
     *
     * See the documentation for openlog (http://php.net/manual/en/function.openlog.php) for available
     * facilities. Note that only LOG_USER is valid on windows.
     *
     * The default is to use LOG_LOCAL5 if available, and fall back to LOG_USER if not.
     */
    'logging.facility' => defined('LOG_LOCAL6') ? constant('LOG_LOCAL6') : LOG_USER,

    /*
     * The process name that should be used when logging to syslog.
     * The value is also written out by the other logging handlers.
     */
    'logging.processname' => $LOG_PROCESS,

    /* Logging: file - Logfilename in the loggingdir from above.
     */
    'logging.logfile'     => 'simplesamlphp.log',

    /*
     * Enable
     *
     * Which functionality in simpleSAMLphp do you want to enable. Normally you would enable only
     * one of the functionalities below, but in some cases you could run multiple functionalities.
     * In example when you are setting up a federation bridge.
     */
    'enable.saml20-idp'     => false,
    'enable.saml20-sp'      => false,
    'enable.shib13-idp'     => false,
    'enable.wsfed-sp'       => false,
    'enable.authmemcookie'  => false,

    /*
     * This value is the duration of the session in seconds. Make sure that the time duration of
     * cookies both at the SP and the IdP exceeds this duration.
     */
    'session.duration'      =>  8 * (60*60), // 8 hours.
    // 'session.duration'      =>  60,
    'session.requestcache'  =>  4 * (60*60), // 4 hours
    // 'session.requestcache'  =>  60,

    /*
     * Sets the duration, in seconds, data should be stored in the datastore. As the datastore is used for
     * login and logout requests, thid option will control the maximum time these operations can take.
     * The default is 4 hours (4*60*60) seconds, which should be more than enough for these operations.
     */
    // 'session.datastore.timeout' => (4*60*60), // 4 hours

    /*
     * Options to override the default settings for php sessions.
     */
    //'session.phpsession.cookiename'  => null,
    'session.phpsession.cookiename'  => 'SSPHP_SESSION',
    'session.phpsession.limitedpath' => false,
    'session.phpsession.savepath'    => null,
    'session.datastore.timeout' => (4*60*60), // 4 hours
    // 'session.datastore.timeout' => 60,
    'session.cookie.name' => 'SimpleSAMLSessionID',
    'session.cookie.lifetime' => 0,
    'session.cookie.path' => '/',
    // 'session.cookie.domain' => '.local.net',

    /*
     * Languages available and what language is default
     */
    //'language.available'  => array('en', 'no', 'nn', 'se', 'fi', 'da', 'sv', 'de', 'es', 'fr', 'nl', 'lb', 'hr', 'hu', 'pl', 'sl', 'pt', 'pt-BR', 'tr'),
    //'language.available'  => array('en', 'mi'),
    'language.available'    => array('en'),
    'language.default'      => 'en',

    /*
     * Which theme directory should be used?
     */
    'theme.use'         => 'default',

    /*
     * Default IdP for WS-Fed.
     */
    //'default-wsfed-idp'   => 'urn:federation:pingfederate:localhost',
    // 'default-wsfed-idp'  => 'http://identityserver.v2.thinktecture.com/samples',

    /*
     * Whether the discovery service should allow the user to save his choice of IdP.
     */
    'idpdisco.enableremember' => TRUE,
    'idpdisco.rememberchecked' => TRUE,

    // Disco service only accepts entities it knows.
    'idpdisco.validate' => TRUE,

    'idpdisco.extDiscoveryStorage' => NULL,

    /*
     * IdP Discovery service look configuration.
     * Wether to display a list of idp or to display a dropdown box. For many IdP' a dropdown box
     * gives the best use experience.
     *
     * When using dropdown box a cookie is used to highlight the previously chosen IdP in the dropdown.
     * This makes it easier for the user to choose the IdP
     *
     * Options: [links,dropdown]
     *
     */
    'idpdisco.layout' => 'dropdown',

    /*
     * Whether simpleSAMLphp should sign the response or the assertion in SAML 1.1 authentication
     * responses.
     *
     * The default is to sign the assertion element, but that can be overridden by setting this
     * option to TRUE. It can also be overridden on a pr. SP basis by adding an option with the
     * same name to the metadata of the SP.
     */
    'shib13.signresponse' => TRUE,

    /*
     * Authentication processing filters that will be executed for all IdPs
     * Both Shibboleth and SAML 2.0
     */
    'authproc.idp' => array(
        /* Enable the authproc filter below to add URN Prefixces to all attributes
        10 => array(
            'class' => 'core:AttributeMap', 'addurnprefix'
        ), */
        /* Enable the authproc filter below to automatically generated eduPersonTargetedID.
        20 => 'core:TargetedID',
        */

        // Adopts language from attribute to use in UI
        //30 => 'core:LanguageAdaptor',

        /* Add a realm attribute from edupersonprincipalname
        40 => 'core:AttributeRealm',
        */
        45 => array(
            'class' => 'core:StatisticsWithAttribute',
            'attributename' => 'realm',
            'type' => 'saml20-idp-SSO',
        ),

        50 => array(
            'class' => 'core:PHP',
            'code' => 'if (!isset($attributes["uid"]) && isset($attributes["email"])) {$attributes["uid"] = $attributes["email"];};',
        ),

        51 => array(
            'class' => 'core:PHP',
            'code' => 'if (!isset($attributes["UserID"]) && isset($attributes["uid"])) {$attributes["UserID"] = $attributes["uid"];};',
        ),

        // following rules do the bulk of the attribute munging for authgoogle
        // grab the organisation from the email address
        /*
        50 => array(
            'class' => 'core:ScopeFromAttribute',
            //'sourceAttribute' => 'email',
            'sourceAttribute' => 'mail',
            'targetAttribute' => 'mlepOrganisation',
        ),
        */
        // add a FAIL!
        /*
        53 => array(
            'class' => 'core:PHP',
            'code' => 'throw new Exception("big fail");',
        ),
        */
        // 90 => 'core:AttributeLimit',

        // If language is set in Consent module it will be added as an attribute.
        99 => 'core:LanguageAdaptor',
    ),
    /*
     * Authentication processing filters that will be executed for all IdPs
     * Both Shibboleth and SAML 2.0
     */
    'authproc.sp' => array(
    /*
        10 => array(
            'class' => 'core:AttributeMap', 'mappings',
        ),
        */
        45 => array(
            'class' => 'core:StatisticsWithAttribute',
            'attributename' => 'realm',
            'type' => 'saml20-sp-SSO',
        ),

        50 => array(
            'class' => 'core:PHP',
            'code' => 'if (!isset($attributes["uid"]) && isset($attributes["email"])) {$attributes["uid"] = $attributes["email"];};',
        ),

        51 => array(
            'class' => 'core:PHP',
            'code' => 'if (!isset($attributes["UserID"]) && isset($attributes["uid"])) {$attributes["UserID"] = $attributes["uid"];};',
        ),

        /* When called without parameters, it will fallback to filter attributes ‹the old way›
         * by checking the 'attributes' parameter in metadata on SP hosted and IdP remote.
         */
        //50 => 'core:AttributeLimit',
        // translate assertion names for ADFS
        52 => array(
            'class' => 'core:PHP',
            'code' => 'if (isset($attributes["http://schemas.microsoft.com/ws/2008/06/identity/claims/windowsaccountname"][0])
                       && !isset($attributes["uid"])) {
                           $attributes["uid"] = array(strtolower(preg_replace("/^AD\\\\\/", "", $attributes["http://schemas.microsoft.com/ws/2008/06/identity/claims/windowsaccountname"][0])));
                        };',
        ),

        // force an error
        /*
        99 => array(
            'class' => 'core:PHP',
            'code' => '
                        throw new Exception("blah");
                       ',
        ),
        */

        // Adopts language from attribute to use in UI
        90 => 'core:LanguageAdaptor',

    ),

    'metadata.sources' => $metadata_sources,


    /*
     * This configuration option allows you to select which session handler
     * SimpleSAMLPHP should use to store the session information. Currently
     * we have two session handlers:
     * - 'phpsession': The default PHP session handler.
     * - 'memcache': Stores the session information in one or more
     *   memcache servers by using the MemcacheStore class.
     *
     * The default session handler is 'phpsession'.
     */
    'session.handler'       => $sessionhandler,


    /*
     * Configuration for the MemcacheStore class. This allows you to store
     * multiple redudant copies of sessions on different memcache servers.
     *
     * 'memcache_store.servers' is an array of server groups. Every data
     * item will be mirrored in every server group.
     *
     * Each server group is an array of servers. The data items will be
     * load-balanced between all servers in each server group.
     *
     * Each server is an array of parameters for the server. The following
     * options are available:
     *  - 'hostname': This is the hostname or ip address where the
     *    memcache server runs. This is the only required option.
     *  - 'port': This is the port number of the memcache server. If this
     *    option isn't set, then we will use the 'memcache.default_port'
     *    ini setting. This is 11211 by default.
     *  - 'weight': This sets the weight of this server in this server
     *    group. http://php.net/manual/en/function.Memcache-addServer.php
     *    contains more information about the weight option.
     *  - 'timeout': The timeout for this server. By default, the timeout
     *    is 3 seconds.
     *
     * Example of redudant configuration with load balancing:
     * This configuration makes it possible to lose both servers in the
     * a-group or both servers in the b-group without losing any sessions.
     * Note that sessions will be lost if one server is lost from both the
     * a-group and the b-group.
     *
     * 'memcache_store.servers' => array(
     *     array(
     *         array('hostname' => 'mc_a1'),
     *         array('hostname' => 'mc_a2'),
     *     ),
     *     array(
     *         array('hostname' => 'mc_b1'),
     *         array('hostname' => 'mc_b2'),
     *     ),
     * ),
     *
     * Example of simple configuration with only one memcache server,
     * running on the same computer as the web server:
     * Note that all sessions will be lost if the memcache server crashes.
     *
     * 'memcache_store.servers' => array(
     *     array(
     *         array('hostname' => 'localhost'),
     *     ),
     * ),
     *
     */
    'memcache_store.servers' => array(
        $memcache_config,
    ),


    /*
     * This value is the duration data should be stored in memcache. Data
     * will be dropped from the memcache servers when this time expires.
     * The time will be reset every time the data is written to the
     * memcache servers.
     *
     * This value should always be larger than the 'session.duration'
     * option. Not doing this may result in the session being deleted from
     * the memcache servers while it is still in use.
     *
     * Set this value to 0 if you don't want data to expire.
     *
     * Note: The oldest data will always be deleted if the memcache server
     * runs out of storage space.
     */
    // 'memcache_store.expires' =>  36 * (60*60), // 36 hours.
    'memcache_store.expires' =>  60,


    'redis_store.servers' => array(
        array(
            array('hostname' => 'localhost'),
        ),
    ),
    'redis_store.expires' =>  36 * (60*60), // 36 hours.


    /*
     * Should signing of generated metadata be enabled by default.
     *
     * Metadata signing can also be enabled for a individual SP or IdP by setting the
     * same option in the metadata for the SP or IdP.
     */
    'metadata.sign.enable' => FALSE,

    /*
     * The default key & certificate which should be used to sign generated metadata. These
     * are files stored in the cert dir.
     * These values can be overridden by the options with the same names in the SP or
     * IdP metadata.
     *
     * If these aren't specified here or in the metadata for the SP or IdP, then
     * the 'certificate' and 'privatekey' option in the metadata will be used.
     * if those aren't set, signing of metadata will fail.
     */
    'metadata.sign.privatekey' => NULL,
    'metadata.sign.privatekey_pass' => NULL,
    'metadata.sign.certificate' => NULL,

    /*
     * This is the default URL to a MetaShare service where a SAML 2.0 IdP can register its metadata.
     * This is a highly experimentar feature.
     */
    'metashare.publishurl' => NULL,

);
