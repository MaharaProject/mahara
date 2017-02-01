<?php
/**
 * Library file for miscellaneous local customisations.
 *
 * For simple customisation of a Mahara site, the core code will call some local_* functions
 * which may be defined in a file called local/lib.php
 *
 * Functions that will be called by core if they are defined:
 *
 *  - local_can_remove_viewtype($viewtype): stop users from deleting views of a particular type
 *
 *  - local_get_allowed_blocktypes($category, $view): Limit which blocktypes are allowed on a view
 *
 *  - local_get_allowed_blocktype_categories($view): Limit which blocktype categories are allowed
 *  on a view
 *
 *  - local_header_top_content(): Returned string (which may contain HTML) is printed near the top
 *  of each page.
 *
 *  - local_init_user(): Called after $USER is initialized on each page load. This is useful for
 *  changing the user's theme before $THEME is initialized.
 *
 *  - local_main_nav_update(&$menu): Modify the main navigation menu by reference
 *
 *  - local_logout(): Hook function called during the user logout process immediately before
 *  $USER->logout()
 *
 *  - local_post_register($registration, $user): Called after a user has successfully been created and
 *  logged in during registration. This is useful when the properties of the user (which may have
 *  been saved to usr_registration.extra [see local_register_submit()]) need to create the related
 *  artefacts.
 *
 *  - local_progressbar_sortorder($options): Change the order of items in the profile completion
 *  progress bar
 *
 *  - local_register_submit(&$values): Called when registration is submitted, but before the values
 *  are saved to usr_registration. This is useful for remembering the properties or preferences of
 *  the logged-out user when the form was submitted. If a $value['extra'] field is added, it will
 *  be stored to usr_registration.extra.
 *
 *  - local_register_form(&$elements): Add profile elements to the registration form. They can be
 *  either core profile fields or custom profile fields defined in local/lib/artefact_internal.php
 *
 *  - local register_create(&$user, $registration): Add extra profile element values saved via
 *  registration form to the user being created after being approved.
 *
 *  - local_right_nav_update(&$menu): Modify the right column navigation menu by reference
 *
 *  - local_sideblocks_update(&$sideblocks): Modify sideblocks column by reference
 *
 *  - local_site_content_pages(): Add to the list of "static pages" types.
 *
 *  - local_xmlrpc_services(): add custom xmlrpc functions
 *
 *  - local_webservice_info(&$response): Add custom information to the public information
 *  provided by htdocs/webservice/info.php
 */
