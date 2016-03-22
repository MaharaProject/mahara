/**
 *
 * @package    mahara
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
/**
 * Form change checker
 * displays a warning message if a user navigates away without saving
 *
 * - formchangechecker: an object which associated to each pieform
 *   and update the state when a user make changes
 * - formchangemanager: a JS object to manage formchangecheckers
 *   and handle the event when the user begins to leave away (onbeforeunload)
 */

var FORM_INIT      = 0;
var FORM_CHANGED   = 1;
var FORM_SUBMITTED = 2;
var FORM_CANCELLED  = 3;

function FormChangeManager() {
    var self = this;
    this.formcheckers = new Array();

    this.init = function() {
        window.onbeforeunload = self.showWarningMessage;
    }

    this.add = function(formid) {
        var alreadyfound = self.find(formid);
        if (alreadyfound === null) {
            newform = new FormChangeChecker(formid);
            self.formcheckers.push(newform);
        }
        else {
            alreadyfound.unbind();
            alreadyfound.reset();
            alreadyfound.bind();
        }
    }

    this.checkDirtyChanges = function() {
        if (typeof tinyMCE !== 'undefined') {
            for (editor in tinyMCE.editors) {
                if (tinyMCE.editors[editor].isDirty()) {
                    return true;
                }
            }
        }
        for (checker in self.formcheckers) {
            if (self.formcheckers[checker].isDirty()) {
                return true;
            }
        }
        return false;
    }

    this.showWarningMessage = function (e) {
        var warningmessage = get_string('wanttoleavewithoutsaving?');
        if (self.checkDirtyChanges()) {
            if (e) {
                e.returnValue = warningmessage;
            }
            return warningmessage;
        }
    }

    this.confirmLeavingForm = function () {
        if (self.checkDirtyChanges()) {
            if (confirm(get_string('wanttoleavewithoutsaving?'))) {
                self.reset();
                return true;
            }
            else {
                return false;
            }
        }
        return true;
    }

    this.find = function(formid) {
        for (checker in self.formcheckers) {
            if (self.formcheckers[checker].id == formid) {
                return self.formcheckers[checker];
            }
        }
        return null;
    }

    this.unbindForm = function(formid) {
        if (formid && (checker = self.find(formid))) {
            checker.unbind();
        }
    }

    this.rebindForm = function(formid) {
        if (formid && (checker = self.find(formid))) {
            checker.bind();
        }
    }

    /**
     * Set the new state for a form
     * @param form   The form object
     */
    this.setFormState = function(form, newstate) {
        if (form && (checker = self.find(jQuery(form).attr('id')))) {
            checker.set(newstate);
        }
    }

    /**
     * Set the new state for a form
     * @param formid   The form ID
     */
    this.setFormStateById = function(formid, newstate) {
        if (formid && (checker = self.find(formid))) {
            checker.set(newstate);
        }
    }

    this.reset = function () {
        for (checker in self.formcheckers) {
            self.formcheckers[checker].reset();
        }
    }

    this.init();
}

var formchangemanager = new FormChangeManager();

function FormChangeChecker(formid) {
    var self = this;

    this.state = FORM_INIT;
    this.id = formid;

    this.init = function() {
        self.bind();
    }

    this.bind = function() {
        if (jQuery('form#' + self.id)) {
            jQuery('form#' + self.id + ' :input').bind('change.changechecker', function() {
                // Only update the state if there are changes of any form input, EXCEPT for
                // - search input
                // - upload file input
                if (this.id && (this.id.search('search') !== -1)
                    || (this.type && this.type === 'file')) {
                    return;
                }
                self.state = FORM_CHANGED;
            });
            jQuery('form#' + self.id + ' :input[type="radio"]').bind('click.changechecker', function() {
                self.state = FORM_CHANGED;
            });
            jQuery('form#' + self.id + ' :input.cancel').bind('click.changechecker', function() {
                self.reset();
            });
            jQuery('form#' + self.id + ' :input.submit').bind('click.changechecker', function() {
                self.state = FORM_SUBMITTED;
            });
            jQuery('form#' + self.id + ' :input[type="file"]').bind('change.changechecker', function() {
                self.state = FORM_SUBMITTED;
            });
            jQuery('form#' + self.id).bind('submit.changechecker', function() {
                self.state = FORM_SUBMITTED;
            });
        }
    }

    this.unbind = function() {
        if (jQuery('form#' + self.id)) {
            jQuery('form#' + self.id + ' :input').unbind('change.changechecker');
            jQuery('form#' + self.id + ' :input[type="radio"]').unbind('click.changechecker');
            jQuery('form#' + self.id + ' :input.cancel').unbind('click.changechecker');
            jQuery('form#' + self.id).unbind('submit.changechecker');
        }
    }

    this.isDirty = function() {
        return (self.state == FORM_CHANGED);
    }

    this.set = function(newstate) {
        self.state = newstate;
    }

    this.reset = function () {
        self.state = FORM_INIT;
    }

    this.init();
}
