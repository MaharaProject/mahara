/**
 * Pieforms: Advanced web forms made easy
 * @source: http://gitorious.org/mahara/mahara
 *
 * @licstart
 * Copyright (C) 2006-2010  Catalyst IT Ltd
 *
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
 *
 * @package    pieform
 * @subpackage static
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 */

window.pieformHandlers = {};

/**
 * Handles things that work the same across all pieforms, such as plugin
 * management and events
 */
function PieformManager() {//{{{
    var self = this;

    this.init = function() {//{{{
        self.connect('onload', null, self.setFocus);
        self.signal('onload', null);
    }//}}}

    /**
     * When called, ensures the focus is set correctly for all pieforms on the
     * page
     */
    this.setFocus = function() {//{{{
        var check = getElementsByTagAndClassName('form', 'pieform');
        var formsWithError = filter(function(i) { return hasElementClass(i, 'error'); }, check);
        if (formsWithError.length > 0) {
            check = formsWithError;
        }
        forEach(check, function(form) {
            var element = getFirstElementByTagAndClassName(null, 'autofocus', form);
            if (element && (typeof(element.focus) == 'function' || (element.focus && element.focus.call))) {
                var type = getNodeAttribute(element, 'type');
                if (type && type == 'hidden') {
                    return;
                }
                try { // If element is invisible, IE will throw an error
                    element.focus();
                    if (hasElementClass(element, 'autoselect')
                        && (typeof(element.select) == 'function' || (element.focus && element.select.call))) {
                        element.select();
                    }
                }
                catch (e) {}
                throw MochiKit.Iter.StopIteration;
            }
        });
    }//}}}

    /**
     * Loads a javascript plugin file
     */
    this.loadPlugin = function(type, name) {//{{{
        if (type != 'element' && type != 'renderer' && type != 'rule') {
            throw 'Plugin type ' + type + ' is not valid';
        }
        if (typeof(self.loadCache[type][name]) != 'undefined') {
            return;
        }

        var script = createDOM('script', {
            'type': 'text/javascript',
            'src' : self.pieformPath + type + 's/' + name + '.js'
        });

        appendChildNodes(self.head, script);
        self.loadCache[type][name] = 1;
    }//}}}

    /**
     * Registers an observer for a given event type
     */
    this.connect = function(slot, form, callback) {//{{{
        if (typeof(self.observers[slot]) == 'undefined') {
            throw 'Slot ' + slot + ' does not exist';
        }
        self.observers[slot].push({'form': form, 'callback': callback});
    }//}}}

    this.signal = function(slot, form) {//{{{
        forEach(self.observers[slot], function(observer) {
            if (form == null || observer.form == null || form == observer['form']) {
                observer.callback(form);
            }
        });
    }//}}}

    this.head = getFirstElementByTagAndClassName('head');

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

    addLoadEvent(self.init);
}//}}}

PieformManager = new PieformManager();


/**
 * Handles the javascript side of pieforms - submitting the form via a hidden
 * iframe and dealing with the result
 */
function Pieform(data) {//{{{
    var self = this;

    this.init = function() {//{{{
        connect(self.data.name, 'onsubmit', self.processForm);

        self.connectSubmitButtons();
    }//}}}

    this.processForm = function(e) {//{{{
        PieformManager.signal('onsubmit', self.data.name);

        // Call the presubmit callback, if there is one
        if (typeof(self.data.preSubmitCallback) == 'string'
            && self.data.preSubmitCallback != "") {
            window[self.data.preSubmitCallback]($(self.data.name), self.clickedButton, e);
        }

        // If the form actually isn't a jsform - i.e. only a presubmithandler
        // was defined - we stop here
        if (!self.data.jsForm) {
            return;
        }

        // Ensure the iframe exists and make sure the form targets it
        // self.data.newIframes = true;
        var iframeName = self.setupIframe();
        $(self.data.name).target = iframeName;

        appendChildNodes(self.data.name,
            INPUT({
                'type': 'hidden',
                'name': 'pieform_jssubmission',
                'value': 1
            })
        );

        window.pieformHandlers[self.data.name] = function(data) {
            // If canceling the form, redirect away
            if (data.returnCode == -2) {
                window.location = data.location;
                return;
            }

            if (typeof(data.replaceHTML) == 'string') {
                PieformManager.signal('onreply', self.data.name);

                var tmp = DIV();
                tmp.innerHTML = data.replaceHTML;

                // Work out whether the new form tag has the error class on it, for
                // updating the form in the document
                if (hasElementClass(tmp.childNodes[0], 'error')) {
                    addElementClass(self.data.name, 'error');
                }
                else {
                    removeElementClass(self.data.name, 'error');
                }
                
                // The first child node is the form tag. We replace the children of
                // the current form tag with the new children. This prevents
                // javascript references being lost
                replaceChildNodes($(self.data.name), tmp.childNodes[0].childNodes);

                self.connectSubmitButtons();
                self.clickedButton = null;
                PieformManager.signal('onload', self.data.name);
            }

            if (data.returnCode == 0) {
                // Call the defined success callback, if there is one
                if (typeof(self.data.jsSuccessCallback) == 'string'
                    && self.data.jsSuccessCallback != "") {
                    window[self.data.jsSuccessCallback]($(self.data.name), data);
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
                    window[self.data.jsErrorCallback]($(self.data.name), data);
                }
            }
            else if (typeof(self.data.globalJsErrorCallback) == 'string'
                && self.data.globalJsErrorCallback != '') {
                window[self.data.globalJsErrorCallback]($(self.data.name), data);
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
                window[self.data.postSubmitCallback]($(self.data.name), self.clickedButton, e);
            }
        }
    }//}}}

    this.setupIframe = function() {//{{{
        var iframeName = self.data.name + '_iframe';
        if (self.data.newIframeOnSubmit) {
            if (!self.data.nextIframe) {
                self.data.nextIframe = 0;
            }
            iframeName += '_' + self.data.nextIframe;
            self.data.nextIframe++;
        }
        if ($(iframeName)) {
            self.iframe = $(iframeName);
        }
        else {
            self.iframe = createDOM('iframe', {
                'name': iframeName,
                'id'  : iframeName,
                'style': 'position: absolute; visibility: hidden;'
            });
            insertSiblingNodesAfter(self.data.name, self.iframe);
        }
        return iframeName;
    }//}}}

    this.connectSubmitButtons = function() {//{{{
        forEach(self.data.submitButtons, function(buttonName) {
            var btn = $(self.data.name + '_' + buttonName);
            if (btn) {
                connect(btn, 'onclick', function() { self.clickedButton = this; });
            }
        });
    }//}}}

    // A reference to the iframe that submissions are made through
    this.iframe = null;

    // The button that was clicked to trigger the form submission
    this.clickedButton = null;

    // Form configuration data passed from PHP
    this.data = data;

    addLoadEvent(self.init);
}//}}}

