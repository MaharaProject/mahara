<?php
/**
 * Handler for module requests.
 *
 * This web page receives requests for web-pages hosted by modules, and directs them to
 * the RequestHandler in the module.
 *
 * @author Olav Morken, UNINETT AS.
 * @package SimpleSAMLphp
 */

require_once('../extlib/simplesamlphp/www/_include.php');

$moduleDir = '../';

try {

    if (empty($_SERVER['PATH_INFO'])) {
        throw new SimpleSAML_Error_NotFound('No PATH_INFO to module.php');
    }

    $url = $_SERVER['PATH_INFO'];
    assert('substr($url, 0, 1) === "/"');

    /* clear the PATH_INFO option, so that a script can detect whether it is called with anything following the
     *'.php'-ending.
     */
    unset($_SERVER['PATH_INFO']);

    $modEnd = strpos($url, '/', 1);
    if ($modEnd === false) {
        // the path must always be on the form /module/
        throw new SimpleSAML_Error_NotFound('The URL must at least contain a module name followed by a slash.');
    }

    $module = substr($url, 1, $modEnd - 1);
    $url = substr($url, $modEnd + 1);
    if ($url === false) {
        $url = '';
    }

    if (!SimpleSAML_Module::isModuleEnabled($module)) {
        throw new SimpleSAML_Error_NotFound('The module \''.$module.'\' was either not found, or wasn\'t enabled.');
    }

    /* Make sure that the request isn't suspicious (contains references to current directory or parent directory or
     * anything like that. Searching for './' in the URL will detect both '../' and './'. Searching for '\' will detect
     * attempts to use Windows-style paths.
     */
    if (strpos($url, '\\') !== false) {
        throw new SimpleSAML_Error_BadRequest('Requested URL contained a backslash.');
    }
    else if (strpos($url, './') !== false) {
        throw new SimpleSAML_Error_BadRequest('Requested URL contained \'./\'.');
    }

    // check for '.php/' in the path, the presence of which indicates that another php-script should handle the request
    for ($phpPos = strpos($url, '.php/'); $phpPos !== false; $phpPos = strpos($url, '.php/', $phpPos + 1)) {

        $newURL = substr($url, 0, $phpPos + 4);
        $param = substr($url, $phpPos + 4);

        if (is_file($moduleDir.$newURL)) {
            /* $newPath points to a normal file. Point execution to that file, and
             * save the remainder of the path in PATH_INFO.
             */
            $url = $newURL;
            $_SERVER['PATH_INFO'] = $param;
            break;
        }
    }

    $path = $moduleDir.$url;

    if (is_dir($path)) {
        /* Path is a directory - maybe no index file was found in the previous step, or maybe the path didn't end with
         * a slash. Either way, we don't do directory listings.
         */
        throw new SimpleSAML_Error_NotFound('Directory listing not available.');
    }

    if (!file_exists($path)) {
        // file not found
        SimpleSAML_Logger::info('Could not find file \''.$path.'\'.');
        throw new SimpleSAML_Error_NotFound('The URL wasn\'t found in the module.');
    }

    if (preg_match('#\.php$#D', $path)) {
        // PHP file - attempt to run it
        $_SERVER['SCRIPT_NAME'] .= '/'.$module.'/'.$url;
        require($path);
        exit();
    }

    throw new SimpleSAML_Error_NotFound('The URL wasn\'t found in the module.');
}
catch (SimpleSAML_Error_Error $e) {

    $e->show();
}
catch (Exception $e) {

    $e = new SimpleSAML_Error_Error('UNHANDLEDEXCEPTION', $e);
    $e->show();
}
