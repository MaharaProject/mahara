// @todo: Pack it down.

// Expects strings array
function get_string(s) {
    var args = flattenArguments(arguments).slice(1);
    if (typeof(strings) == 'undefined' || typeof(strings[s]) == 'undefined') {
        return '[[[' + s + ((args.length > 0) ? ('(' + args.join(',') + ')') : '') + ']]]';
    }
    var str = strings[s];
    // @todo Need to sprintf these strings properly.
    for (var i = 0; i < args.length; i++) {
        str = str.replace('%s',args[i]);
    }
    return str;
}

// Expects an image/css path to fetch url for (requires config.theme[] to be
// set)
function get_themeurl(s) {
    // log('get_themeurl(' + s + ')');
    if (!config || !config.theme || !config.theme[s]) {
        logError('Location of ' + s + ' is unknown, ensure config.theme is set correctly');
    }

    return config.theme[s];
}

function globalErrorHandler(data) {
    if (data.returnCode == 3) {
        // Logged out!
    }
    else {
        displayMessage(data.message, 'error');
    }
}

// Form related functions
var oldValue = null;
function formStartProcessing(form, btn) {
    processingStart();
    var button = $(btn);
    if (button) {
        oldValue = button.value;
        button.value = get_string('processing') + ' ...';

        // we add a hidden input field so the "disabled" button still gets to
        // pass its value through
        var node = INPUT({
            'type': 'hidden',
            'value': button.value,
            'name': button.name
        });
        insertSiblingNodesAfter(button, node);

        button.proxyContainer = node;
        button.disabled = "disabled";
        button.blur();
    }
}
function formStopProcessing(form, btn) {
    processingStop();
    var button = $(btn);
    if (button) {
        button.value = oldValue;
        if(button.proxyContainer) {
            removeElement(button.proxyContainer);
            button.proxyContainer = null;
        }
        button.disabled = null;
    }
}
function formError(form, data) {
    var errMsg = DIV({'id': 'messages'}, makeMessage(data.message.message, 'error'));
    swapDOM('messages', errMsg);
    scrollTo(0, 0);
}
function formSuccess(form, data) {
    var yayMsg = DIV({'id': 'messages'}, makeMessage(data.message, 'ok'));
    swapDOM('messages', yayMsg);
    scrollTo(0, 0);
}

function formGlobalError(form, data) {
    globalErrorHandler(data);
}
// End form related functions

// Message related functions
function makeMessage(message, type) {
    var a = A({'href': ''}, IMG({'src': get_themeurl('images/icon_close.gif'), 'alt': '[X]'}));
    connect(a, 'onclick', function(e) {
        removeElement(a.parentNode.parentNode);
        e.stop();
    });
    return DIV({'class': type}, DIV({'class': 'fr'}, a), message);
}

/* Appends a status message to the end of elemid */
function displayMessage(message, type) {
    // ensure we have type 'ok', 'error', or 'info' (the default)
    if (!type || (type != 'ok' && type != 'error')) {
        type = 'info';
    }

    var message = makeMessage(message, type);
    appendChildNodes('messages', message);

    // callLater(2, function() {
    //     removeElement(message);
    //     //fade(message);
    // });
}

/* Display a nice little loading notification */
function processingStart(msg) {
    if (!msg) {
        msg = get_string('loading');
    }

    replaceChildNodes(
        $('loading_box'),
        DIV(msg)
    );
    showElement('loading_box');
}

/* Hide the loading notification */
function processingStop() {
    hideElement('loading_box');
}
// End message related functions

// Function to post a data object to a json script.
function sendjsonrequest(script, data, rtype, successcallback, errorcallback, quiet) {
    //log('sendjsonrequest(script=', script, ', data=', data, ', rtype=', rtype, ', success=', successcallback, ', error=', errorcallback, ', quiet=', quiet, ')');
    donothing = function () { return; };
    if (typeof(successcallback) != 'function') {
        successcallback = donothing;
    }
    if (typeof(errorcallback) != 'function') {
        errorcallback = donothing;
    }
    processingStart();
    data.sesskey = config.sesskey;

    rtype = rtype.toLowerCase();

    var xhrOptions = { 'method': rtype };

    switch (rtype) {
        case 'post':
            xhrOptions.headers = { 'Content-type': 'application/x-www-form-urlencoded' };
            xhrOptions.sendContent = MochiKit.Base.queryString(data);
            break;
        default:
            xhrOptions.queryString = data;
            break;
    }

    var d = doXHR(script, xhrOptions);

    d.addCallbacks(function (result) {
        var data = evalJSONRequest(result);
        var errtype = false;
        if (!data.error) { 
            errtype = 'ok';
        }
        else if (data.error == 'local') {
            errtype = 'error';
        }
        else {
            logWarning('invoking globalErrorHandler(', data, ')');
            globalErrorHandler(data);
        }
        if (errtype) {
            if (typeof(data.message) == 'string') {
                if (!quiet) {
                    displayMessage(data.message, errtype);
                }
                try { successcallback(data); } catch (e) { logError('sendjsonrequest() callback failed: ', e, data); }
            }
            else if (data.message && typeof(data.message == 'object')) {
                if (data.message.message && typeof(data.message.message == 'string') && !quiet) {
                    displayMessage(data.message.message, errtype);
                }
                try { successcallback(data.message); } catch (e) { logError('sendjsonrequest() callback failed: ', e, data); }
            }
            else {
                try { successcallback(data); } catch (e) { logError('sendjsonrequest() callback failed: ', e, data); }
            }
            processingStop();
        }
        else {
            processingStop();
        }
    },
    function () {
        displayMessage(get_string('unknownerror'), 'error');
        errorcallback();
        processingStop();
    });
}

// Rename a file by appending numbers
function newfilename(oldname, fileexistsfunc) {
    var dotpos = oldname.indexOf('.');
    if (dotpos == -1) {
        var begin = oldname;
        var end = '';
    }
    else {
        var begin = oldname.substring(0, dotpos);
        var end = oldname.substring(dotpos, oldname.length);
    }
    var i = 1;
    var newname = begin + i + end;
    while (fileexistsfunc(newname)) {
        i++;
        newname = begin + i + end;
    }
    return newname;
}

// Return the filename part of a full path
function basename(path) {
    if (path.indexOf('/') > -1) { 
        var separator = '/';
    }
    else {
        var separator = '\\';
    }
    return path.substring(path.lastIndexOf(separator)+1, path.length);
}


// Autofocus the first element with a class of 'autofocus' on page load (@todo: move this to pieforms.js)
// Also, connect input elements with the 'emptyonfocus' class to work properly
addLoadEvent(function() {
    var element = getFirstElementByTagAndClassName(null, 'autofocus', document.body)

    if ( element && typeof(element.focus) == 'function' ) {
        element.focus();
    }

    forEach(getElementsByTagAndClassName('input', 'emptyonfocus'), function(elem) {
        elem.emptyonfocusSignal = connect(elem, 'onfocus', function(e) { elem.value = ''; e.stop(); if (elem.emptyonfocusSignal) { disconnect(elem.emptyonfocusSignal); } if (elem.emptyonfocusSignalForm) { disconnect(elem.emptyonfocusSignalForm); }  });
        if (elem.form) {
            elem.emptyonfocusSignalForm = connect(elem.form, 'onsubmit', function(e) { elem.value = ''; if (elem.emptyonfocusSignal) { disconnect(elem.emptyonfocusSignal); } if (elem.emptyonfocusSignalForm) { disconnect(elem.emptyonfocusSignalForm); } });
        }
    });
});

// Contextual Help
contextualHelpCache       = new Object();
contextualHelpSelected    = null;
contextualHelpContainer   = null;
contextualHelpDeferrable  = null;
contextualHelpOpened      = false;

function contextualHelpIcon(formName, helpName, pluginType, pluginName, page, section) {
    var link = A(
        {'href': null},
        IMG({'alt': '?', 'src': get_themeurl('images/icon_help.gif')})
    );
    connect(link, 'onclick', function (e) {
        e.stop();
        contextualHelp(formName, helpName, pluginType, pluginName, page, section, link);
    });

    return link;
}

function contextualHelp(formName, helpName, pluginType, pluginName, page, section, ref) {
    var key;
    var target = $(formName + '_' + helpName + '_container');
    var url = config.wwwroot + 'json/help.php';
    var url_params = {
        'plugintype': pluginType,
        'pluginname': pluginName
    };

    // deduce the key
    if (page) {
        key = pluginType + '/' + pluginName + '/' + page;
        url_params.page = page;
    }
    else if (section) {
        key = pluginType + '/' + pluginName + '/' + section;
        url_params.section = section;
    } 
    else {
        key = pluginType + '/' + pluginName + '/' + formName + '/' + helpName;
        url_params.form = formName;
        url_params.element = helpName;
    }

    // close existing contextual help
    if (contextualHelpSelected) {
        removeElement(contextualHelpContainer);

        contextualHelpContainer = null;
        if (key == contextualHelpSelected) {
            // we're closing an already open one by clicking on the ? again
            contextualHelpSelected = null;
            contextualHelpOpened = false;
            return;
        } else {
            // we're closing a DIFFERENT one that's already open (we want to
            // continue and open the new one)
            contextualHelpSelected = null;
            contextualHelpOpened = false;
        }
    }

    // create and display the container
    contextualHelpContainer = DIV({
            'style': 'position: absolute; visibility: hidden; z-index: 1000;',
            'class': 'contextualHelp'
        },
        IMG({'src': config.theme['images/loading.gif']})
    );
    appendChildNodes($('header'), contextualHelpContainer);

    var position = getElementPosition(ref);
    var dimensions = getElementDimensions(contextualHelpContainer);

    // Adjust the position. The element is moved towards the centre of the
    // screen, based on which quadrant of the screen the help icon is in
    screenDimensions = getViewportDimensions();
    if (position.x + dimensions.w < screenDimensions.w) {
        // Left of the screen - there's enough room for it
        position.x += 10;
    }
    else {
        position.x -= dimensions.w;
    }
    position.y -= 10;

    // Once it has been positioned, make it visible
    setElementPosition(contextualHelpContainer, position);
    setStyle(contextualHelpContainer, {'visibility': 'visible'});

    contextualHelpSelected = key;

    // load the content
    if (contextualHelpCache[key]) {
        contextualHelpContainer.innerHTML = contextualHelpCache[key];
        callLater(0, function() { contextualHelpOpened = true; });
    }
    else {
        if (contextualHelpDeferrable && contextualHelpDeferrable.cancel) {
            contextualHelpDeferrable.cancel();
        }

        sendjsonrequest(url, url_params, 'GET', function (data) {
            if (data.error) {
                contextualHelpCache[key] = data.message;
                replaceChildNodes(contextualHelpContainer, data.message);
            }
            else {
                contextualHelpCache[key] = data.content;
                contextualHelpContainer.innerHTML = contextualHelpCache[key];
            }
            contextualHelpOpened = true;
            processingStop();
        },
        function (error) {
            contextualHelpCache[key] = get_string('couldnotgethelp');
            contextualHelpContainer.innerHTML = contextualHelpCache[key];
            processingStop();
            contextualHelpOpened = true;
        },
        true);
    }
}

/* Only works in non-ie at the moment. Using 'document' as the element
   makes IE detect the event, but then makes it so you need to click on
   the help twice before it opens. */
connect(window, 'onclick', function(e) {
    if (contextualHelpOpened) {
        removeElement(contextualHelpContainer);
        contextualHelpContainer = null;
        contextualHelpSelected = null;
        contextualHelpOpened = false;
    }
});

// Cookie related functions
/* this function gets the cookie, if it exists */
function getCookie(name) {
    var start = document.cookie.indexOf( name + "=" );
    var len = start + name.length + 1;

    if (
        (!start) &&
        (name != document.cookie.substring(0, name.length))
    ) {
        return null;
    }

    if (start == -1) {
        return null;
    }

    var end = document.cookie.indexOf( ";", len );

    if (end == -1) {
        end = document.cookie.length;
    }

    return unescape(document.cookie.substring( len, end ));
}

function clearCookie( name ) {
    setCookie(name, '', -1);
}

// expires is in seconds
function setCookie( name, value, expires, path, domain, secure ) 
{
    // set time, it's in milliseconds
    var today = new Date();
    today.setTime( today.getTime() );

    /*
    if the expires variable is set, make the correct 
    expires time, the current script below will set 
    it for x number of days, to make it for hours, 
    delete * 24, for minutes, delete * 60 * 24
    */
    if (expires) {
        expires = expires * 1000;
    }

    var expires_date = new Date( today.getTime() + (expires) );

    document.cookie = name + "=" + escape( value ) +
    ( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) + 
    ( ( path ) ? ";path=" + path : "" ) + 
    ( ( domain ) ? ";domain=" + domain : "" ) +
    ( ( secure ) ? ";secure" : "" );
}
// End cookie related functions

function toggleChecked(c) {
    var e = getElementsByTagAndClassName(null, c);
    if (e) {
        for (cb in e) {
	    if (e[cb].checked == true) {
                e[cb].checked = '';
            } 
            else {
                e[cb].checked = 'checked';
            }
        }
    }
    return false;

}

function expandDownToViewport(element, width) {
    var viewport = getViewportDimensions();
    var position = getElementPosition(element);
    var newheight = new Dimensions(width, viewport.h - position.y - 2);

    if ($('footer')) {
        newheight.h -= getElementDimensions('footer').h + 40;
    }

    setElementDimensions(element, newheight);
}

function countKeys(x) {
    n = 0;
    for ( i in x ) n++;
    return n;
}

function keepElementInViewport(element) {
    var pixels = getViewportPosition().y + getViewportDimensions().h 
        - getElementPosition(element).y - getElementDimensions(element).h;
    if (pixels < 0) {
        window.scrollBy(0,-pixels);
    }
}

// this function creates a pre-augmented tags control
function create_tags_control(name, value, options) {
    var elements = [];

    options = update({
            'size': 40
        },
        options
    );

    if (typeof(value) == 'object') {
        value = value.join(', ');
    }
    if (!value) {
        value = '';
    }

    var tagControl = INPUT({'name': name, 'size': options.size, 'value': value});
    elements.push(augment_tags_control(tagControl, true));
    elements.push(tagControl);

    return DIV(null, elements);
}

// this function takes an existing input element and augments it
function augment_tags_control(elem, returnContainer) {
    elem = getElement(elem);
    log('augment_tags_control(', elem, ')');

    var tagContainer = DIV();
    // setElementDimensions(tagContainer, {'w': getElementDimensions(elem).w});
    var showLink = A({'href':''},get_string('showtags'));
    appendChildNodes(tagContainer, showLink);

    connect(showLink, 'onclick', function (e) {
        e.stop();
        replaceChildNodes(tagContainer, get_string('loading') + ' ', IMG({'src':get_themeurl('images/loading.gif')}));
        sendjsonrequest(config.wwwroot + 'json/taglist.php', {}, 'GET', function (data) {
            replaceChildNodes(tagContainer);
            forEach(data, function(tag) {
                var tagLink = A({'href':''}, tag.tag);
                connect(tagLink, 'onclick', function(e) {
                    e.stop();

                    if (some(elem.value.split(/ *, */), function(t) { return t == tag.tag; })) {
                        return;
                    }

                    if (elem.value.match(/^ *$/) || elem.value.match(/, *$/)) {
                        elem.value += tag.tag;
                    }
                    else {
                        elem.value += ', ' + tag.tag;
                    }
                });
                appendChildNodes(tagContainer, tagLink, '\u00A0(', tag.count, '), ');
            });
        });
    });
    
    if (typeof(returnContainer) == 'boolean' && returnContainer) {
        return tagContainer;
    }

    var newNode = DIV();
    swapDOM(elem, newNode);
    appendChildNodes(newNode, tagContainer, elem);
};

function quotaUpdate(quotaused, quota) {
    if (! $('quota_percentage') ) {
        logWarning('quotaUpdate(', quotaused, quota, ') called but no id="quota_percentage" on page');
        return;
    }

    var update = function(data) {
        if ( data.quota >= 1048576 ) {
            data.quota_display = roundToFixed(data.quota / 1048576, 1) + 'MB';
            data.quotaused_display = roundToFixed(data.quotaused / 1048576, 1) + 'MB';
        }
        else if (data.quota >= 1024 ) {
            data.quota_display = roundToFixed(data.quota / 1024, 1) + 'KB';
            data.quotaused_display = roundToFixed(data.quotaused / 1024, 1) + 'KB';
        }
        else {
            data.quota_display = data.quota + ' bytes';
            data.quotaused_display = data.quotaused + ' bytes';
        }

        var percentage = roundToFixed(data.quotaused / data.quota * 100, 0);
        var ref = $('quota_bar_100') || $('quota_bar');

        if (percentage < 100) {
            $('quota_fill').style.display = 'block';
            if (ref.id != 'quota_bar') {
                swapDOM(ref, P({'id': 'quota_bar'}, SPAN({'id': 'quota_percentage'})));
            }
        }
        else {
            $('quota_fill').style.display = 'none';
            if (ref.id != 'quota_bar_100') {
                swapDOM(ref, P({'id': 'quota_bar_100'}, SPAN({'id': 'quota_percentage'})));
            }
        }

        $('quota_used').innerHTML = data.quotaused_display;
        $('quota_total').innerHTML = data.quota_display;
        $('quota_percentage').innerHTML = percentage + '%';
        $('quota_fill').style.width = (percentage * 2) + 'px';
    }

    if ((typeof(quotaused) == 'number' || typeof(quotaused) == 'string') && quota) {
        var data = { 'quotaused': quotaused, 'quota': quota };
        update(data);
    }
    else {
        sendjsonrequest(config.wwwroot + 'json/quota.php', {}, 'POST', function (data) {
            update(data);
        }, null, true);
    }
}
