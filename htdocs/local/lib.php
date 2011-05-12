<?php
/**
 * Library file for miscellaneous local customisations.
 *
 * For simple customisation of a Mahara site, the core code will call some local_* functions
 * which may be defined in this file.
 *
 * Functions that will be called by core:
 *  - local_main_nav_update(&$menu):        modify the main navigation menu in the header
 *  - local_xmlrpc_services():              add custom xmlrpc functions
 *  - local_can_remove_viewtype($viewtype): stop users from deleting views of a particular type
 */
