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

function globalErrorHandler(data) {
    if (data.returnCode == 3) {
        // Logged out!
    }
}

// Form related functions
var oldValue = null;
function formStartProcessing(form, btn) {
    processingStart();
    var button = $(btn);
    if (button) {
        oldValue = button.value;
        button.value = get_string('processingform') + ' ...';
        button.disabled = "disabled";
        button.style.borderWidth = '1px';
        button.blur();
    }
}
function formStopProcessing(form, btn) {
    processingStop();
    var button = $(btn);
    if (button) {
        button.value = oldValue;
        button.disabled = null;
        button.style.borderWidth = '2px';
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
    var a = A({'href': ''}, IMG({'src': config.themeurl + 'icon_close.gif', 'alt': '[X]'}));
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
function sendjsonrequest(script, data, successcallback, errorcallback) {
    donothing = function () { return; };
    if (typeof(successcallback) != 'function') {
        successcallback = donothing;
    }
    if (typeof(errorcallback) != 'function') {
        errorcallback = donothing;
    }
    processingStart();
    var req = getXMLHttpRequest();
    req.open('POST', script);
    req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
    var d = sendXMLHttpRequest(req,queryString(data));
    d.addCallbacks(function (result) {
        var data = evalJSONRequest(result);
        var errtype = false;
        if (!data.error) { 
            errtype = 'info';
        }
        else if (data.error == 'local') {
            errtype = 'error';
        }
        else {
            globalErrorHandler(data);
        }
        if (errtype) {
            displayMessage(data.message,errtype);
            successcallback();
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

// Autofocus the first element with a class of 'autofocus' on page load (@todo: move this to pieforms.js)
// Also, connect input elements with the 'emptyonfocus' class to work properly
addLoadEvent(function() {
    var element = getFirstElementByTagAndClassName(null, 'autofocus', document.body)

    if ( element && typeof(element.focus) == 'function' ) {
        element.focus();
    }

    forEach(getElementsByTagAndClassName('input', 'emptyonfocus'), function(elem) {
        connect(elem, 'onfocus', function(e) { elem.value = ''; e.stop(); });
        if (elem.form) {
            connect(elem.form, 'onsubmit', function(e) { elem.value = ''; });
        }
    });
});

// Contextual help
var ctxHelp = new Array();
var ctxHelp_selected;
var container;

function contextualHelp(formName, helpName, pluginType, pluginName, page) {
    var tooltip = $('tooltip');
    log(tooltip);
    log(ctxHelp_selected);

    var key;
    if (page) {
	key = pluginType + '/' + pluginName + '/' + page;
    }
    else {
	key = pluginType + '/' + pluginName + '/' + helpName;
    }
    if (typeof(ctxHelp_selected) != 'undefined' && ctxHelp_selected && ctxHelp_selected != key) {
        log('chose another help when one was open');
        contextualHelpClose();
    }

    if (ctxHelp[key] && tooltip) {
        log('have help for this key, and it is already open');
        contextualHelpClose();
        return;
    }
    else if (ctxHelp[key]) {
        log('reloaded help from cache');
        ctxHelp_selected = key;
	if (page) {
	    contextualHelpOpen(page, ctxHelp[key].content);
	} 
	else{
	    contextualHelpOpen(formName + '_' + helpName, ctxHelp[key].content);
	}
        return;
    }
    else {
        log('no help for this key yet, getting...');
        ctxHelp[key] = new Object();
        processingStart();
	var url = '../json/help.php?plugintype=' + pluginType + '&pluginname=' + pluginName;
	if (page) {
	    url += '&page=' + page;
	}
	else {
	    url += '&form=' + formName + '&element=' + helpName;
	}
        var d = loadJSONDoc(url);
        d.addCallbacks(
        function (data) {
	    if (data.error) {
		ctxHelp[key].content = data.message;
		container.innerHTML = ctxHelp[key].content;
		processingStop();
	    } 
	    else {
		ctxHelp[key].content = data.content;
		container.innerHTML = ctxHelp[key].content;
		processingStop();
	    }
        },
        function () {
            ctxHelp[key].content = '<p>Sorry, no help for this element could be found</p>';
            container.innerHTML = ctxHelp[key].content;
            processingStop();
        });
    }

    if (page) {
	contextualHelpOpen(page, 'spinner');
    }
    else {
	contextualHelpOpen(formName + '_' + helpName, 'spinner');
    }
    ctxHelp[key] = {
        'content': ''
    };
    ctxHelp_selected = key;
}

function contextualHelpClose() {
    var tooltip = $('tooltip');
    if ( ctxHelp_selected && tooltip) {
        tooltip.style.display = 'none';
        removeElement(tooltip);
        //ctxHelp[ctxHelp_selected].visible = 0;
        ctxHelp_selected = null;
    }
}

function contextualHelpOpen(helpName, content) {
    var help = DIV({'id': 'tooltip'});
    container = DIV({'class':'content'});
    container.innerHTML = content;

    appendChildNodes(help, container);
    appendChildNodes($(helpName + '_container'), help);
}
// End contextual help

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
    if (typeof(width) == 'undefined') {
        width = getElementDimensions(element).w;
    }

    var viewport = getViewportDimensions();
    var position = getElementPosition(element);
    var newheight = new Dimensions(width, viewport.h - position.y - 2);

    if ($('footer')) {
        newheight.h -= getElementDimensions('footer').h + 8;
    }

    setElementDimensions(element, newheight);
}

function countKeys(x) {
    n = 0;
    for ( i in x ) n++;
    return n;
}
