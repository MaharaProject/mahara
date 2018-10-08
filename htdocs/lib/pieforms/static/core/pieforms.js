/**
 * Pieforms: Advanced web forms made easy
 *
 * @package    mahara
 * @subpackage pieforms
 * @author     Catalyst IT Ltd
 * @author     Drupal (http://www.drupal.org)
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  2006 Drupal (http://www.drupal.org)
 * @licstart
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU
 * General Public License (GNU GPL) as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.  The code is distributed WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.
 *
 * As additional permission under GNU GPL version 3 section 7, you
 * may distribute non-source (e.g., minimized or compacted) forms of
 * that code without the copy of the GNU GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.
 * @licend
 */

window.pieformHandlers = {};

/**
 * Handles things that work the same across all pieforms, such as plugin
 * management and events
 */
var PieformManager = (function($) {
    return function() {
        var self = this;

        this.init = function() {
            self.connect('onload', null, self.setFocus);
            self.signal('onload', null);
        };

        /**
         * When called, ensures the focus is set correctly for all pieforms on the
         * page
         */
        this.setFocus = function() {
            var check = $('form.pieform');
            var formsWithError = check.filter(function() { return $(this).hasClass('error'); });
            if (formsWithError.length > 0) {
                check = formsWithError;
            }
            check.each(function() {
                var element = $(this).find('.autofocus')[0];
                if (element && (typeof(element.focus) == 'function' || (element.focus && element.focus.call))) {
                    var type = $(element).prop('type');
                    if (type && type == 'hidden') {
                        return;
                    }
                    try { // If element is invisible, IE will throw an error
                        $(element).trigger("focus");
                        if ($(element).hasClass('autoselect')
                            && (typeof(element.select) == 'function' || (element.focus && element.select.call))) {
                            $(element).trigger('select');
                        }
                    }
                    catch (e) {}
                    return false;
                }
            });
        };

        /**
         * Loads a javascript plugin file
         */
        this.loadPlugin = function(type, name) {
            if (type != 'element' && type != 'renderer' && type != 'rule') {
                throw 'Plugin type ' + type + ' is not valid';
            }
            if (typeof(self.loadCache[type][name]) != 'undefined') {
                return;
            }

            var script = $('<script>', {
                'type': 'application/javascript',
                'src' : self.pieformPath + type + 's/' + name + '.js'
            });

            script.append(self.head);
            self.loadCache[type][name] = 1;
        };

        /**
         * Registers an observer for a given event type
         */
        this.connect = function(slot, form, callback) {
            if (typeof(self.observers[slot]) == 'undefined') {
                throw 'Slot ' + slot + ' does not exist';
            }
            self.observers[slot].push({'form': form, 'callback': callback});
        };

        this.signal = function(slot, form) {
            $.each(self.observers[slot], function(id, observer) {
                if (form == null || observer.form == null || form == observer['form']) {
                    observer.callback(form);
                }
            });
        };

        this.head = $('head')[0];

        if (typeof(pieformPath) == 'string') {
            this.pieformPath = pieformPath;
            if (pieformPath.substr(pieformPath.length - 1, 1) != '/') {
                this.pieformPath += '/';
            }
        }
        else {
            this.pieformPath = '';
        }

        this.loadCache = {'element': {}, 'renderer': {}, 'rule': {}};

        this.observers = {
            'onload'  : [],  // when elements are loaded
            'onsubmit': [],  // when a form is submitted
            'onreply' : []   // when a response is received
        };

        $(window).on('load', self.init);

    };
}(jQuery));

PieformManager = new PieformManager();

/**
 * Handles the javascript side of pieforms - submitting the form via a hidden
 * iframe and dealing with the result
 */
var Pieform = (function($) {
    return function(data) {
        var self = this;

        this.init = function() {
            if (self.data.checkDirtyChange) {
                formchangemanager.add(self.data.name);
            }
            $('#' + self.data.name).on('submit', self.processForm);

            // this is custom event that is triggered by filebrowser.js on form submit
            $('#' + self.data.name).on('onsubmit', self.processForm);

            self.connectSubmitButtons();

            // Hook for pieform elements that need to execute Javascript
            // *after* the Pieform has been initialized.
            $(document).triggerHandler('pieform_postinit', self);
        };

        this.processForm = function(e) {
            PieformManager.signal('onsubmit', self.data.name);

            // Call the presubmit callback, if there is one
            if (typeof(self.data.preSubmitCallback) == 'string'
                && self.data.preSubmitCallback != "") {
                window[self.data.preSubmitCallback]($('#' + self.data.name)[0], self.clickedButton, e);
            }

            // If the form actually isn't a jsform - i.e. only a presubmithandler
            // was defined - we stop here
            if (!self.data.jsForm) {
                return;
            }

            // Ensure the iframe exists and make sure the form targets it
            // self.data.newIframes = true;
            var iframeName = self.setupIframe();
            $('#' + self.data.name)[0].target = iframeName;

            $('#' + self.data.name).append($('<input>', {
                    'type': 'hidden',
                    'name': 'pieform_jssubmission',
                    'value': 1
                }));

            window.pieformHandlers[self.data.name] = function(data) {
                // If canceling the form, redirect away
                if (data.returnCode == -2) {
                    window.location = data.location;
                    return;
                }
                // The pieform is rendering
                window.isPieformRendering = true;

                if (typeof(data.replaceHTML) == 'string') {
                    PieformManager.signal('onreply', self.data.name);

                    var tmp = $('<div>');
                    tmp.html(data.replaceHTML);

                    // Work out whether the new form tag has the error class on it, for
                    // updating the form in the document
                    if (tmp.children().first().hasClass('error')) {
                        $('#' + self.data.name).addClass('error');
                    }
                    else {
                      $('#' + self.data.name).removeClass('error');
                    }

                    // The first child node is the form tag. We replace the children of
                    // the current form tag with the new children. This prevents
                    // javascript references being lost
                    $('#' + self.data.name).empty().append(tmp.children().first().children());

                    // data.replaceHTML may contain inline javascript code which need to be evaluated
                    // Append any inline js code to data.javascript and evaluate them
                    var temp = $('<div>').append(data.replaceHTML);
                    data.javascript = '';
                    temp.find('*').each(function() {
                        if ($(this).prop('nodeName') === 'SCRIPT' && $(this).prop('src') === '' && $(this).prop('type') !== 'text/x-tmpl') {
                            data.javascript += $(this).prop('innerHTML');
                        }
                    });
                    eval(data.javascript);

                    self.connectSubmitButtons();
                    self.clickedButton = null;
                    if (self.data.checkDirtyChange) {
                        formchangemanager.rebindForm(self.data.name);
                    }
                    PieformManager.signal('onload', self.data.name);
                }

                if (data.returnCode == 0) {
                    // Call the defined success callback, if there is one
                    if (typeof(self.data.jsSuccessCallback) == 'string'
                        && self.data.jsSuccessCallback != "") {
                        window[self.data.jsSuccessCallback]($('#' + self.data.name)[0], data);
                    }
                    else {
                        // TODO: work out what I'm going to do here...
                        if (typeof(data.message) == 'string' && data.message != '') {
                            alert(data.message);
                        }
                    }
                }
                else if (data.returnCode == -1) {
                    if (typeof(self.data.jsErrorCallback) == 'string'
                        && self.data.jsErrorCallback != '') {
                        window[self.data.jsErrorCallback]($('#' + self.data.name)[0], data);
                    }
                }
                else if (typeof(self.data.globalJsErrorCallback) == 'string'
                    && self.data.globalJsErrorCallback != '') {
                    window[self.data.globalJsErrorCallback]($('#' + self.data.name)[0], data);
                }
                else {
                    alert('Developer: got error code ' + data.returnCode
                    + ', either fix your form to not use this code or define '
                    + 'a global js error handler');
                }

                // The post submit callback (for if the form succeeds or fails, but
                // not for if it cancels)
                if (typeof(self.data.postSubmitCallback) == 'string'
                    && self.data.postSubmitCallback != '') {
                    window[self.data.postSubmitCallback]($('#' + self.data.name)[0], self.clickedButton, e);
                }

            // The pieform rendering is done.
            window.isPieformRendering = false;
            }
        };

        this.setupIframe = function() {
            var iframeName = self.data.name + '_iframe';
            if (self.data.newIframeOnSubmit) {
                if (!self.data.nextIframe) {
                    self.data.nextIframe = 0;
                }
                iframeName += '_' + self.data.nextIframe;
                self.data.nextIframe++;
            }
            if ($('#' + iframeName).length) {
                self.iframe = $('#' + iframeName)[0];
            }
            else {
                self.iframe = $('<iframe>', {
                    'name': iframeName,
                    'id'  : iframeName,
                    'style': 'position: absolute; visibility: hidden; height: 0px; width: 0px;'
                })[0];
                $(self.iframe).insertAfter($('#' + self.data.name));
            }
            return iframeName;
        };

        this.connectSubmitButtons = function() {
            $.each(self.data.submitButtons, function(id, buttonName) {
                var btn = $('#' + self.data.name + '_' + buttonName)[0];
                if (btn) {
                    $(btn).on('click', function() { self.clickedButton = this; });
                }
            });
        };

        // A reference to the iframe that submissions are made through
        this.iframe = null;

        // The button that was clicked to trigger the form submission
        this.clickedButton = null;

        // Form configuration data passed from PHP
        this.data = data;

        $(self.init);

    };
}(jQuery));
