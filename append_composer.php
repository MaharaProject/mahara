<?php

/**
 * Script for appending accepted plugins to a composer.json file
 *
 * Normally composer will prompt us to accept plugins it doesn't trust but this is
 * no good if we are doing something automatically like installing simplesamlphp where
 * the composer.json file comes supplied so we can pass in information to this file to
 * manipulate the allowed plugins object so we can install things automatically
 *
 * $argv contains
 * [0] name of this script
 * [1] path to the composer.json file, eg htdocs/auth/saml/extlib/simplesamlphp/composer.json
 * [2]+ the name of the plugins to accept, eg simplesamlphp/composer-module-installer
 *
 * So for example to allow the simplesamlphp/composer-module-installer plugin without being prompted
 *  php append_composer.php htdocs/auth/saml/extlib/simplesamlphp/composer.json simplesamlphp/composer-module-installer
 */
$pluginstoallow = array();
$composerfile = null;
if (count($argv) > 1) {
    $composerfile = $argv[1];
    for ($i = 2; $i < count($argv); $i++) {
        $pluginstoallow[] = $argv[$i];
    }
}
if ($composerfile && $file = file_get_contents($composerfile)) {
    $file = json_decode($file);
    if (!isset($file->config)) {
        $file->config = new stdClass();
    }
    if (!isset($file->config->{'allow-plugins'})) {
        $file->config->{'allow-plugins'} = new stdClass();
    }
    foreach ($pluginstoallow as $plugin) {
        $file->config->{'allow-plugins'}->{$plugin} = true;
    }
    $file = json_encode($file, JSON_PRETTY_PRINT);
    file_put_contents($composerfile, $file);
}
else {
    throw new Exception('Unable to find composer.json file');
}