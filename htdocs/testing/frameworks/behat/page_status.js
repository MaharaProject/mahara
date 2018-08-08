/**
 *
 * @package    mahara
 * @subpackage behat
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) portions from Moodle, (C) Martin Dougiamas http://dougiamas.com
 */

/**
 * This variable determines the completeness of a request
 * = true if the request is still in progress
 */
window.isRequestProcessing = false;

/**
 * This variable determines if pieforms are rendering by their js handlers
 * = true if the rendering is still in progress
 */
window.isPieformRendering = false;

/**
 * This variable determines if tinyMCE is initializing
 * = true if it is in progress
 */
window.isEditorInitializing = false;

/**
 * This variable determines if modal window is rendering by js
 * = true if the rendering is still in progress
 */
window.isModalRendering = false;

function isMaharaPageReady() {
    return (window.isRequestProcessing === false)
        && (window.isPieformRendering === false)
        && (window.isEditorInitializing === false)
        && (window.isModalRendering === false)
        && (document.readyState === "complete");
}

jQuery(function() {
    /**
     * Listening for ajax events
     */
    jQuery(document).ajaxStart(function() {
        window.isRequestProcessing = true;
    });
    jQuery(document).ajaxStop(function() {
        window.isRequestProcessing = false;
    });

    /**
     * Remove boostrap modal animation
     */
    jQuery('.modal').removeClass('fade');

    /**
     * Update the page status while showing the Bootstrap's modal pop-up
     */
    jQuery('.modal').on('show.bs.modal', function (event) {
        window.isModalRendering = true;
    });
    jQuery('.modal').on('shown.bs.modal', function (event) {
        window.isModalRendering = false;
    });

    /**
     * Disable all jQuery animations e.g. fadeIn/Out(), toogle(), animation()
     */
    jQuery.fx.off = true;

    /**
     * Disable bootstrap transitions
     */
    jQuery.support.transition = false;

});
