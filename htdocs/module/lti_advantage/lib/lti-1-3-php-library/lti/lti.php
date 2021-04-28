<?php
// Import files in lti-1-3-php-library
foreach (glob(__DIR__ . "/*.php") as $filename) {
    require_once $filename;
}

// include files in php-jwt library
foreach (glob(dirname(dirname(dirname(__FILE__))) . "/php-jwt/src/*.php") as $filename) {
    require_once $filename;
}

// include files in phpseclib
spl_autoload_register(
    function($classname) {
        $classpath = explode('_', $classname);
        if ($classpath[0] != 'phpseclib') {
            $classpath = explode('\\', $classname);
            if ($classpath[0] != 'phpseclib') {
                return;
            }
        }
        $filepath = dirname(dirname(dirname(__FILE__))) . "/phpseclib/" . implode('/', $classpath) . '.php';
        if (file_exists($filepath)) {
            require_once($filepath);
        }
    }
);

// check what this means
// need to be this way so the cron and the browser can use oauth
if (isset($_SERVER['REQUEST_SCHEME'])) {
    define("TOOL_HOST", (getenv('HTTP_X_FORWARDED_PROTO') ?: $_SERVER['REQUEST_SCHEME']) . '://' . $_SERVER['HTTP_HOST']);
}
else {
    define("TOOL_HOST", get_config('wwwroot'));
}
Firebase\JWT\JWT::$leeway = 5;
?>