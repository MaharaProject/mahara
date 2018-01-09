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
define('CLI', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . 'auth/lib.php');
require(get_config('libroot') . 'cli.php');

$cli = get_cli();

$options = array();
$options['username'] = (object) array(
        'shortoptions' => array('u'),
        'description' => get_string('username'),
        'required' => true,
        'examplevalue' => 'user1',
);
$options['password'] = (object) array(
        'shortoptions' => array('p'),
        'description' => get_string('cli_pwreset_password', 'admin'),
        'required' => false,
        'defaultvalue' => false,
        'examplevalue' => 'Eepha8eeBa',
);
$options['makeinternal'] = (object) array(
        'shortoptions' => array('i'),
        'description' => get_string('cli_pwreset_makeinternal', 'admin'),
        'required' => false,
        'defaultvalue' => false,
);
define('CLI_PWRESET_FORCEPASSWORDCHANGE_DEFAULT', -1);
$options['forcepasswordchange'] = (object) array(
        'shortoptions' => array('f'),
        'description' => get_string('cli_pwreset_forcepasswordchange', 'admin'),
        'required' => false,
        'defaultvalue' => CLI_PWRESET_FORCEPASSWORDCHANGE_DEFAULT,
);

$settings = (object) array(
        'info' => get_string('cli_pwreset_info', 'admin'),
        'options' => $options,
);
$cli->setup($settings);

// Retrieve & validate the username
$username = $cli->get_cli_param('username');
$user = get_record('usr', 'username', $username);
if (!$user) {
    $cli->cli_exit(get_string('cli_pwreset_nosuchuser', 'admin', $username), true);
}

// Retrieve or prompt for password
// (No validation. This is an admin tool!)
$password = $cli->get_cli_param('password');
if (!$password) {
    $password1 = $cli->cli_prompt(get_string('cli_pwreset_prompt1', 'admin'), true);
    $password2 = $cli->cli_prompt(get_string('cli_pwreset_prompt2', 'admin'), true);
    if ($password1 === $password2) {
        $password = $password1;
    }
    else {
        $cli->cli_exit(get_string('cli_pwreset_typo', 'admin'));
    }
}
$user->password = $password;

$makeinternal = $cli->get_cli_param_boolean('makeinternal');
if ($makeinternal) {
    // Change them to the "internal" auth method for "No institution".
    // This one should be permanent because it's the auth method for the "root" user.
    $internalauth = get_field('auth_instance', 'id', 'institution', 'mahara', 'authname', 'internal');
    if (!$internalauth) {
        // If there is no such auth for some reason, then quit.
        $cli->cli_exit(get_string('cli_pwreset_nointernalauth'), true);
    }
    set_field('usr', 'authinstance', $internalauth, 'id', $user->id);
    $user->authinstance = $internalauth;
    $cli->cli_print(get_string('cli_pwreset_authupdated', 'admin'));
}

// Determine whether or not to reset the user's password.
if ($cli->get_cli_param('forcepasswordchange') === CLI_PWRESET_FORCEPASSWORDCHANGE_DEFAULT) {
    // The default behavior, is that we force a reset if they provided the password via the --password flag
    $forcepasswordchange = ($cli->get_cli_param('password') !== false);
}
else {
    // If they specified a forcepasswordchange param, we respect that
    $forcepasswordchange = $cli->get_cli_param_boolean('forcepasswordchange');
}

// Attempt to reset the password.
$success = reset_password($user);

if ($success) {
    // Delete the existing usr_session rows to force usr to log back in
    execute_sql("DELETE FROM {usr_session} WHERE usr = ?", array($user->id));
    $exitstring = get_string('cli_pwreset_success', 'admin', $username);
    if ($forcepasswordchange) {
        set_field('usr', 'passwordchange', 1, 'username', $username);
        $exitstring .= "\n" . get_string('cli_pwreset_success_forcepasswordchange', 'admin');
    }
    $cli->cli_exit($exitstring);
}
else {
    // If it failed because their auth instance doesn't allow password resets,
    // then suggest the -i option.
    $userobj = new User();
    $userobj->find_by_id($user->id);
    $authobj = AuthFactory::create($user->authinstance);
    if (!method_exists($authobj, 'change_password')) {
        $cli->cli_exit(get_string('cli_pwreset_notsupported', 'admin', $username), true);
    }
    else {
        $cli->cli_exit(get_string('cli_pwreset_failure', 'admin', $username), true);
    }
}
