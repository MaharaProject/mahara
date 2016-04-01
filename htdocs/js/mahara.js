/**
 * General javascript routines for Mahara
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// Expects strings array
function get_string(s) {
    var args = flattenArguments(arguments).slice(1);
    if (typeof(strings) == 'undefined' || typeof(strings[s]) == 'undefined') {
        return '[[[' + s + ((args.length > 0) ? ('(' + args.join(',') + ')') : '') + ']]]';
    }

    var str = strings[s];
    if (typeof(str) == 'object') {
        var index = 0;
        if (args.length > 0 && typeof(plural) == 'function') {
            index = plural(parseInt(args[0], 10));
            if (typeof(index) == 'boolean') {
                index = index ? 1 : 0;
            }
        }
        if (typeof(str[index]) != 'string') {
            return '[[[' + s + ((args.length > 0) ? ('(' + args.join(',') + ')') : '') + ']]]';
        }
        str = str[index];
    }
    var i = 0;
    return str.replace(/%((%)|s)/g, function (m) { return m[2] || args[i++]; });
}

/**
 * Getting the string via ajax as deferred object
 */
function get_string_ajax(str, section) {
    // need to pass all the arguments except 'section' in case there are %s variables
    var getstringargs = [str].concat([].slice.call(arguments, 2));

    // If string already exists in strings object
    if (typeof(strings[str]) !== 'undefined') {
        return get_string.apply(this, getstringargs);
    }

    var rnd = randString(10);
    var placeholder = '<span id="str_' + rnd + '"></span>';
    get_string_ajax_call.apply(this, arguments).done(function(r) {
        // need to find the random string in the text and replace it with our lang string
        jQuery('#str_' + rnd).replaceWith(get_string.apply(this, getstringargs));
    });
    return placeholder;
}

/**
 * Allow for the fetching of a string after the page has loaded.
 * Adds the string to the stings array so we don't have to keep
 * re-fetching it.
 *
 * Useful for page builder blocks that fetch things via ajax
 * This runs asynchronously so broken string may display for a split before
 * being fetched here.
 * This hooks into get_string() so it can return 'missing string' string like normal
 *
 * @param  str     string  The string to fetch
 * @param  section string  The lang file to find the string
 *
 * @return string The output from get_string()
 */
function get_string_ajax_call(str, section) {
    // Try fetching the string and adding it to the strings object
    return jQuery.ajax({
        url: config.wwwroot + 'lang/get_string.php',
        data: {'string': str, 'section': section},
        type: 'GET',
        success: function(data) {
            // on success
            if (data.message.data.string) {
                strings[str] = data.message.data.string;
            }
            return get_string.apply(this, arguments);
        },
        error: function() {
            // on error
            return get_string.apply(this, arguments);
        }
    });
}

/**
 * Return a random alphanumeric string
 * @param x int Length of returned string
 */
function randString(x) {
    var s = "";
    while (s.length < x && x > 0) {
        var r = Math.random();
        s += (r < 0.1 ? Math.floor (r * 100) : String.fromCharCode(Math.floor(r * 26) + ( r > 0.5 ? 97 : 65)));
    }
    return s;
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

var save_orig_data = true;
var orig_caller;
var orig_arguments;
var real_sesskey = '';

function globalErrorHandler(data) {
    if (data.returnCode == 1) {
        // Logged out!
        show_login_form('ajaxlogin');
    }
    else {
        displayMessage(data.message, 'error');
    }
}

function show_login_form(submit) {
    if($('ajax-login-form') == null) {
        var loginForm = DIV({id: 'ajax-login-form', 'class': 'modal-dialog'});
        loginForm.innerHTML = '<h2>' + get_string('login') + '</h2><a href="/">&laquo; ' + get_string('home') + '<\/a><div id="loginmessage">' + get_string('sessiontimedout') + '</div><form class="pieform" name="login" method="post" action="" id="login" onsubmit="' + submit + '(this, 42); return false;"><table cellspacing="0" border="0" class="maharatable"><tbody><tr id="login_login_username_header" class="required text"><th><label for="login_login_username">' + get_string('username') + ':<\/label><\/th><\/tr><tr id="login_login_username_container"><td><input type="text" class="required text autofocus" id="login_login_username" name="login_username" value=""><\/td><\/tr><tr><td class="description"> <\/td><\/tr><tr id="login_login_password_header" class="required password"><th><label for="login_login_password">' + get_string('password') + ':<\/label><\/th><\/tr><tr id="login_login_password_container"><td><input type="password" class="required password" id="login_login_password" name="login_password" value=""><\/td><\/tr><tr><td class="description"> <\/td><\/tr><tr id="login_submit_container"><td><input type="submit" class="submit btn btn-primary" id="login_submit" name="submit" value="' + get_string('login') + '"><\/td><\/tr><\/tbody><\/table><div id="homepage"><\/div><input type="hidden" name="sesskey" value=""><input type="hidden" name="pieform_login" value=""><\/form><script type="text\/javascript">var login_btn = null;addLoadEvent(function() {    connect($(\'login_submit\'), \'onclick\', function() { login_btn = \'login_submit\'; });});connect(\'login\', \'onsubmit\', function() { formStartProcessing(\'login\', login_btn); });<\/script>';
        appendChildNodes(document.body, DIV({id: 'overlay'}));
        appendChildNodes(document.body, loginForm);
        $('login_login_username').focus();
    }
    else {
        $('loginmessage').innerHTML = get_string('loginfailed');
        $('login_login_username').focus();
    }
}

function ajaxlogin(form, crap) {
    save_orig_data = false;
    sendjsonrequest(
        config.wwwroot + 'minilogin.php',
        {'login_username': form.elements['login_username'].value, 'login_password': form.elements['login_password'].value, 'pieform_login': ''},
        'POST',
        function(data) {
            removeElement('ajax-login-form');
            removeElement('overlay');
            config.sesskey = data.message;
            sendjsonrequest.apply(orig_caller, orig_arguments);
        },
        function() {},
        true
    );
    save_orig_data = true;
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

        button.disabled = "disabled";
        button.blur();

        // Start the progress meter if it is enabled.
        if (typeof(form) !== 'undefined' && typeof(form.elements) !== 'undefined' && typeof(form.elements['progress_meter_token']) !== 'undefined') {
            meter_update_timer(form.elements['progress_meter_token'].value);
        }
    }
}

function meter_update_timer(instance) {
    sendjsonrequest( config.wwwroot + 'json/progress_meter.php', { 'instance' : instance }, 'GET', function(data) {
        if (typeof(data) != 'undefined') {
            if (!data.data.finished || !jQuery('#meter_overlay').is(':visible')) {
                setTimeout(function() { meter_update_timer(instance) }, 1000);
            }
            meter_update(data.data);
        }
    }, false, true, false, true);
}

function formStopProcessing(form, btn) {
    processingStop();
}
function formError(form, data) {
    var errMsg = DIV({'id': 'messages'}, makeMessage(data.message, 'error'));
    swapDOM('messages', errMsg);
    scrollTo(0, 0);
}
function formSuccess(form, data) {
    if (config.mathjax) {
        MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
    }
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
    if (message === undefined) {
        return;
    }
    switch (type) {
        case 'ok':
            return DIV({'class': type +' alert alert-success'}, message);
            break;
        case 'error':
            return DIV({'class': type +' alert alert-danger'}, message);
             break;
        case 'warning':
            return DIV({'class': type +' alert alert-warning'}, message);
            break;
        default:
            return DIV({'class': type +' alert alert-info'}, message);
    }
}

/* Appends a status message to the end of elemid */
function displayMessage(message, type, hideprevmsg) {
    // ensure we have type 'ok', 'error', or 'info' (the default)
    if (!type || (type != 'ok' && type != 'error')) {
        type = 'info';
    }

    var oldmessage = getFirstElementByTagAndClassName('div', null, 'messages');
    var message = makeMessage(message, type);
    appendChildNodes('messages', message);

    if (typeof hideprevmsg === 'undefined' || hideprevmsg == true) {
        if (oldmessage) {
            fade(oldmessage, {afterFinish: partial(removeElement, oldmessage)});
        }
    }
}

/**
 * This variable determines the completeness of a json request
 * = true if the request is still in progress
 */
var isRequestStillProcessing = false;
var isPageRendering = false;

/* Display a nice little loading notification */
function processingStart(msg) {
    if (!msg) {
        msg = get_string('loading');
    }

    replaceChildNodes(
        $('loading-box'),
        DIV({'class': 'loading-inner'},
            SPAN({'class': 'icon-spinner icon-pulse icon icon-lg'}),
            SPAN({'class': 'loading-message'}, msg))
    );

    showElement('loading-box');

    isRequestStillProcessing = true;
}

/* Hide the loading notification */
function processingStop() {
    setTimeout(function(){
        hideElement('loading-box');
        isRequestStillProcessing = false;
    }, 100); //give users enough time to see the loading indicator
}
// End message related functions

// Function to post a data object to a json script.
function sendjsonrequest(script, data, rtype, successcallback, errorcallback, quiet, anon, extraquiet) {
    //log('sendjsonrequest(script=', script, ', data=', data, ', rtype=', rtype, ', success=', successcallback, ', error=', errorcallback, ', quiet=', quiet, ')');
    donothing = function () { return; };
    if (typeof(successcallback) != 'function') {
        successcallback = donothing;
    }
    if (typeof(errorcallback) != 'function') {
        errorcallback = donothing;
    }
    if (typeof(extraquiet) == 'undefined' || !extraquiet) {
        processingStart();
    }
    if (!anon) {
        data.sesskey = config.sesskey;
    }

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

    if (save_orig_data) {
        orig_caller = this;
        orig_arguments = arguments;
    }

    document.documentElement.style.cursor = 'wait';

    if (typeof(fakewwwroot) == 'string') {
        if (script.substring(0, 4) == 'http') {
            script = fakewwwroot + script.substring(config.wwwroot.length);
        }
        else {
            script = fakewwwroot + script;
        }
    }

    var d = doXHR(script, xhrOptions);

    d.addCallbacks(function (result) {
        document.documentElement.style.cursor = '';
        var data;
        try {
            data = jQuery.parseJSON(result.responseText);
        }
        catch (e) {
            logError('sendjsonrequest() received invalid JSON');
            processingStop();
            errorcallback();
            return;
        }

        var errtype = false;
        if (!data.error) {
            errtype = 'ok';
        }
        else if (data.error == 'local') {
            errtype = 'error';
            errorcallback();
        }
        else {
            logWarning('invoking globalErrorHandler(', data, this, arguments, ')');
            // Trying something ninja. The call failed, but in the event that the global error
            // handler can recover, maybe it can be called
            globalErrorHandler(data);
            errorcallback();
        }
        if (errtype) {
            if (typeof(data.message) == 'string') {
                if (!quiet) {
                    displayMessage(data.message, errtype);
                }
                try { successcallback(data); } catch (e) { logError('sendjsonrequest() callback failed: ', e, data); }
            }
            else if (data.message && typeof(data.message) == 'object') {
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
    function (e) {
        document.documentElement.style.cursor = '';
        if (e instanceof MochiKit.Async.XMLHttpRequestError) {
            log(e);
        }
        else {
            displayMessage(get_string('unknownerror'), 'error');
        }
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
    var element = getFirstElementByTagAndClassName(null, 'autofocus', document.body);

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
contextualHelpLink        = null;
badIE = false;

function contextualHelpIcon(formName, helpName, pluginType, pluginName, page, section) {
    var link = A(
        {'href': '#'},
        SPAN({'alt': get_string('Help'), 'class': 'icon icon-info-circle'})
    );
    connect(link, 'onclick', function (e) {
        e.stop();
        contextualHelp(formName, helpName, pluginType, pluginName, page, section, link);
    });

    return SPAN({'class':'help'}, link);
}

function contextualHelp(formName, helpName, pluginType, pluginName, page, section, ref) {
    var key;
    var target = $(formName + '_' + helpName + '_container');
    var url = config.wwwroot + 'json/help.php';
    var url_params = {
        'plugintype': pluginType,
        'pluginname': pluginName
    };

    contextualHelpLink = ref;

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
            'style': 'position: absolute;',
            'class': 'contextualHelp hidden',
            'role' : 'dialog'
        },
        SPAN({'class': 'icon icon-spinner icon-pulse'})
    );
    var parent = ref.parentNode;
    var inserted = false;
    var illegalParents = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'legend'];
    while (parent != null) {
        if (illegalParents.indexOf(parent.nodeName.toLowerCase()) >= 0) {
            insertSiblingNodesAfter(parent, contextualHelpContainer);
            inserted = true;
            break;
        }
        parent = parent.parentNode;
    }
    if (!inserted) {
        insertSiblingNodesAfter(ref.parentNode, contextualHelpContainer);
    }

    var position = contextualHelpPosition(ref, contextualHelpContainer);

    // Once it has been positioned, make it visible
    setElementPosition(contextualHelpContainer, position);
    removeElementClass(contextualHelpContainer, 'hidden');

    contextualHelpSelected = key;

    // load the content
    if (contextualHelpCache[key]) {
        buildContextualHelpBox(contextualHelpCache[key]);
        callLater(0, function() { contextualHelpOpened = true; });
        ensureHelpIsOnScreen(contextualHelpContainer, position);
    }
    else {
        if (contextualHelpDeferrable && contextualHelpDeferrable.cancel) {
            contextualHelpDeferrable.cancel();
        }
        badIE = true;
        sendjsonrequest(url, url_params, 'GET', function (data) {
            if (data.error) {
                contextualHelpCache[key] = data.message;
                replaceChildNodes(contextualHelpContainer, data.message);
            }
            else {
                contextualHelpCache[key] = data.content;
                buildContextualHelpBox(contextualHelpCache[key]);
            }
            contextualHelpOpened = true;
            ensureHelpIsOnScreen(contextualHelpContainer, position);
            processingStop();
        },
        function (error) {
            contextualHelpCache[key] = get_string('couldnotgethelp');
            buildContextualHelpBox(contextualHelpCache[key]);
            processingStop();
            contextualHelpOpened = true;
        },
        true, true);
    }
}

/*
 * Builds the contents of the box with the currently open contextual help in
 * it, including the close button and an overlay div to prevent clicking on the
 * help closing the box
 */
function buildContextualHelpBox(content) {
    var result = '<div class="pull-right pts">';
    result += '<a href="" class="help-dismiss" onclick="return false;">';
    result += '<span class="icon icon-remove"></span>';
    result += '<span class="sr-only">' + get_string('closehelp') + '</span>';
    result += '</a>';
    result += '</div>';
    result += '<div id="helpstop">';
    result += content;
    result += '</div>';
    contextualHelpContainer.innerHTML = result;

    connect('helpstop', 'onclick', function(e) { if (e.target().nodeName != "A") { e.stop(); } });
    getFirstElementByTagAndClassName(null, 'help-dismiss', contextualHelpContainer).focus();
}

/*
 * Positions the box so that it's next to the link that was activated
 */
function contextualHelpPosition(ref, contextualHelpContainer) {
    $j(contextualHelpContainer).css('visibility', 'hidden').removeClass('hidden');
    var position = $j(ref).position();
    var offset = $j(ref).offset();
    var containerwidth = $j(contextualHelpContainer).outerWidth(true);

    // Adjust the position. The element is moved towards the centre of the
    // screen, based on which quadrant of the screen the help icon is in
    var screenwidth = $j(window).width();
    if (offset.left + containerwidth < screenwidth) {
        // Left of the screen - there's enough room for it
        position.left += 15;
    }
    else if (offset.left - containerwidth < 0) {
        var oldoffset = $j(contextualHelpContainer).offset();
        var oldposition = $j(contextualHelpContainer).position();

        if (containerwidth >= screenwidth) {
            // Very small screen, resize the help box to fit
            position.left = oldposition.left - oldoffset.left;
        }
        else {
            // Otherwise center it
            position.left = (screenwidth / 2) - (containerwidth / 2) - oldoffset.left + oldposition.left;
        }
    }
    else {
        position.left -= containerwidth;
    }
    position.top -= 10;

    $j(contextualHelpContainer).css('visibility', 'visible');

    return {x: position.left, y: position.top};
}

/*
 * Ensures that the contextual help box given is fully visible on screen. This
 * will adjust the position of the help vertically if the help has opened right
 * next to the bottom or top of the viewport
 */
function ensureHelpIsOnScreen(container, position) {
    var screenheight = $j(window).height();
    var containerheight = $j(container).height();
    if (position.y + containerheight > screenheight + $j('html').scrollTop()) {
        position.y -= containerheight - 18;
        $j(container).css('top', position.y);
    }
}

/* Only works in non-ie at the moment. Using 'document' as the element
   makes IE detect the event, but then makes it so you need to click on
   the help twice before it opens. */
connect(document, 'onclick', function(e) {
    if (contextualHelpOpened && !badIE) {
        removeElement(contextualHelpContainer);
        contextualHelpContainer = null;
        contextualHelpSelected = null;
        contextualHelpOpened = false;
        if (contextualHelpLink) {
            contextualHelpLink.focus();
            contextualHelpLink = null;
        }
    }
    badIE = false;
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
    var elements = getElementsByTagAndClassName(null, c),
        trigger = document.querySelectorAll('data-'+c),
        i;

    if(trigger) {
        trigger.checked = true;
    }
    if (elements) {
        for (i = 0; i < elements.length; i = i + 1) {

            if (elements[i].checked == true) {
                elements[i].checked = '';
            } else {
                elements[i].checked = 'checked';
            }
        }
    }
    return;
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

function tag_select2_clear(id) {
    var select2 = jQuery('#' + id).data('select2');
    if (select2) {
        jQuery('#' + id).select2();
    }
    jQuery('#' + id).find('option').remove();
}

function tag_select2(id) {
    jQuery('#' + id).select2({
        ajax: {
            url: config.wwwroot + "json/taglist.php",
            dataType: 'json',
            type: 'POST',
            delay: 250,
            data: function(params) {
                return {
                    'q': params.term,
                    'page': params.page || 0,
                    'sesskey': config.sesskey,
                    'offset': 0,
                    'limit': 10,
                }
            },
            processResults: function(data, page) {
                return {
                    results: data.results,
                    pagination: {
                        more: data.more
                    }
                };
            }
        },
        multiple: true,
        width: "300px",
        allowClear: false,
        placeholder: "Type in a search term",
        minimumInputLength: 1,
        tags: true,
    });
}

function progressbarUpdate(artefacttype, remove) {
    if (! $('progressbarwrap')) {
        return;
    }
    // are we adding or deleting?
    var change = 1;
    if (remove) {
        change = -1;
    }

    // if we have the artefacttype and it needs to be updated
    if (typeof artefacttype != 'undefined') {
        if ($('progress_counting_' + artefacttype)) {
            var counting = parseInt($('progress_counting_' + artefacttype).innerHTML, 10);
            var oldcompleted = parseInt($('progress_completed_' + artefacttype).innerHTML, 10);
            var completed = oldcompleted + change;
            $('progress_completed_' + artefacttype).innerHTML = completed;
            var progressitem = $('progress_item_' + artefacttype);
            progressitem.innerHTML = progressitem.innerHTML.replace(/-?\d+/, counting - completed);

            // when progress is met
            if ((counting - completed) <= 0) {
                addElementClass(progressitem.parentNode.parentNode,'hidden');
            }
            else {
                removeElementClass(progressitem.parentNode.parentNode,'hidden');
            }
            // now update the totals if we need to
            if ((oldcompleted > 0 && oldcompleted <= counting && remove ) || (completed <= counting && !remove)) {
                var totalcounting = parseInt($('progress_counting_total').innerHTML, 10);
                var totalcompleted = parseInt($('progress_completed_total').innerHTML, 10) + change;
                $('progress_completed_total').innerHTML = totalcompleted;
                var percentage = roundToFixed((totalcompleted / totalcounting) * 100, 0);
                $('progress_bar_percentage').innerHTML = percentage + '%';
                setStyle($('progress_bar_fill'), {'width': percentage + '%'});
            }
        }
    }
}

function meter_update(data) {
    if (! jQuery('#meter_overlay')) {
        return false;
    }

    if (data.finished) {
        jQuery('#meter_overlay').hide();

        if (typeof(data.redirect) !== 'undefined') {
            window.location.href = data.redirect;
        }
        return true;
    }

    jQuery('#meter_overlay').show();

    if (data.denominator) {
        data.message += ' ... ' + (Math.round(100 * data.numerator / data.denominator)) + '% done';
    }
    jQuery('#meter_message').html(data.message);
    if (data.denominator > 0) {
        new_width = jQuery('#meter_wrap').width() * data.numerator / data.denominator;
    }
    else {
        new_width = 0;
    }
    jQuery('#meter_fill').width(new_width);

    return true;
}

function quotaUpdate(quotaused, quota) {
    if (jQuery('#instconf').length) {
        return;
    }
    if (! jQuery('#quota_fill').length) {
        logWarning('quotaUpdate(', quotaused, quota, ') called but no id="quota_fill" on page');
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
        jQuery('#quota_used').text(data.quotaused_display);
        jQuery('#quota_total').text(data.quota_display);
        jQuery('#quota_fill').css('width', percentage + '%').text(percentage + '%').attr('aria-valuenow', percentage);
    };

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

function updateUnreadCount(data) {
    var inboxmenu = jQuery(".navbar-right .inbox");
    if (!inboxmenu.length) {
        return;
    }

    if (typeof(data.data.newunreadcount) != 'undefined') {
        var countnode = inboxmenu.find('span.unreadmessagecount');
        if (countnode.length) {
            countnode.text(data.data.newunreadcount);
        }
    }
}

// Work around hack for Mochikit Event key function
// (returns 0 for ff and onkeypress)
function keypressKeyCode(e) {
    if (typeof(e._event.charCode) != 'undefined' && e._event.charCode !== 0 &&
        !MochiKit.Signal._specialMacKeys[e._event.charCode]) {
        return e._event.charCode;
    }
    if (e._event.keyCode && (typeof(e._event.charCode) == 'undefined' || e._event.charCode == 0)) {
        return e._event.keyCode;
    }
    return 0;
}

function is_FF() {
    if ( /Firefox|Gecko|Iceweasel/.test(navigator.userAgent) && !/Chromium|Chrome|Safari|AppleWebKit/.test(navigator.userAgent) ) {
        return true;
    }
    return false;
}

// Escapes all special characters for RegExp, code from https://developer.mozilla.org/en/docs/Web/JavaScript/Guide/Regular_Expressions
function escapeRegExp(string) {
  return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
}

// Fix for Chrome and IE, which don't change focus when going to a fragment identifier link
// Manually focuses the main content when the "skip to main content" link is activated
jQuery(document).ready(function() {
    $j('a.skiplink').click(function() {
        var id = $j(this).attr('href');
        $j(id).attr('tabIndex', -1).focus();
    });
});

/**
* Allow the js / no-js toggle on all pages for theme styling
*/
jQuery(document).ready(function() {
    jQuery('body').removeClass('no-js').addClass('js');
});

/**
 * Check if the page is ready in javascript
 */
var is_page_ready = false;
jQuery(document).ready(function() {
    is_page_ready = true;
});

/**
 * Calls statistical data from the db and returns Chart.js structured json
 *
 * @param   object  opts  Any options we need to pass in to get correct data
 *                        Can contain:
 *                        id - the id of the canvas to put the graph in. The legend id should be id + 'legend'
 *                        type - the type of graph we want to display, eg line/bar/pie etc
 *                        graph - the name of the function to fetch the data from, eg 'group_type_graph'
 *                        colours - an array of rgb colours eg "['200,100,37','123,21,103']"
 *
 * @return  object  data  A json encoded object acceptable to Chart.js
 *                        - see Chart.js for json shape.
 */
var chartobject;
var canvascontext;
function fetch_graph_data(opts) {

    if (typeof opts.extradata != 'undefined') {
        opts.extradata = JSON.stringify(opts.extradata);
    }
    if (typeof opts.colours != 'undefined') {
        opts.colours = JSON.stringify(opts.colours);
    }

    if (!document.getElementById(opts.id + 'legend')) {
        // We need to add in the legend container
        var legend = document.createElement('div');
        legend.id = opts.id + 'legend';
        legend.className = 'graphlegend';
        var canvas = document.getElementById(opts.id);
        canvas.parentNode.insertBefore(legend, canvas.nextSibling);
    }

    if (!document.getElementById(opts.id + 'title')) {
        // We need to add in the title container
        var title = document.createElement('strong');
        title.id = opts.id + 'title';
        title.className = 'graphtitle';
        var canvas = document.getElementById(opts.id);
        canvas.parentNode.insertBefore(title, canvas);
    }

    sendjsonrequest(config.wwwroot + 'json/graphdata.php', opts, 'POST', function (json) {
        if (json.data.empty == true) {
            document.getElementById(opts.id).style.display = 'none';
        }
        else {
            if (document.getElementById(opts.id + 'legend').hasChildNodes()) {
                // We already have a chart with this id so we need to clear its data
                chartobject.destroy();
                document.getElementById(opts.id + 'legend').innerHTML = '';
                document.getElementById(opts.id + 'title').innerHTML = '';
            }
            else {
                canvascontext = document.getElementById(opts.id).getContext("2d");
            }
            chartobject = new Chart(canvascontext)[json.data.graph](JSON.parse(json.data.datastr),JSON.parse(json.data.configstr));
            legendtype = (typeof chartobject.options.datasetStroke !== 'undefined' && chartobject.options.datasetStroke == true) ? 'stroke' : 'fill';
            chartobject.options.legendTemplate = "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i]." + legendtype + "Color%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>";

            var legendHolder = document.createElement('div');
            legendHolder.innerHTML = chartobject.generateLegend();
            document.getElementById(opts.id + 'legend').appendChild(legendHolder.firstChild);
            if (json.data.title) {
                jQuery('#' + opts.id + 'title').text(json.data.title);
            }
        }
    });
}
