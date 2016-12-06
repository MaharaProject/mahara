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
function get_string(name) {
    // Flatten the arguments in case string parameters were passed as an array
    var args = Array.prototype.concat.apply([], arguments).slice(1);

    if (typeof(strings) == 'undefined' || typeof(strings[name]) == 'undefined') {
        return '[[[' + name + ((args.length > 0) ? ('(' + args.join(',') + ')') : '') + ']]]';
    }

    var str = strings[name];
    if (typeof(str) == 'object') {
        var index = 0;
        if (args.length > 0 && typeof(plural) == 'function') {
            index = plural(parseInt(args[0], 10));
            if (typeof(index) == 'boolean') {
                index = index ? 1 : 0;
            }
        }
        if (typeof(str[index]) != 'string') {
            return '[[[' + name + ((args.length > 0) ? ('(' + args.join(',') + ')') : '') + ']]]';
        }
        str = str[index];
    }
    // Strings should have their 'section' set for easier debugging.
    // So we need to ignore args[1] when replacing '%s' for args
    // @TODO: get the javascript strings to respect 'section' so that
    // strings with same key but different section can be used without clashes.
    var i = 1;
    return str.replace(/%((%)|s|d)/g, function (m) { return m[2] || args[i++]; });
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
    if (!config || !config.theme || !config.theme[s]) {
        console.error('Location of ' + s + ' is unknown, ensure config.theme is set correctly');
    }

    return config.theme[s];
}

var real_sesskey = '';

function globalErrorHandler(data) {
    if (data.returnCode == 1) {
        // Logged out - redirect back to the login page
        window.location.href = config.wwwroot;
    }
    else {
        displayMessage(data.message, 'error');
    }
}

// Form related functions

function formStartProcessing(form, btn) {
    processingStart();
    var button = jQuery(btn);
    if (button.length) {
        button.val(get_string('processing') + ' ...');

        // we add a hidden input field so the "disabled" button still gets to
        // pass its value through
        var node = jQuery('<input type="hidden" />').attr({
            'value': button.val(),
            'name': button.attr('name')
        });
        button.after(node);

        button.attr('disabled', true);
        button.blur();

        // Start the progress meter if it is enabled.
        if (form && form.elements && form.elements['progress_meter_token']) {
            meter_update_timer(form.elements['progress_meter_token'].value);
        }
    }
}

function meter_update_timer(instance) {
    sendjsonrequest(config.wwwroot + 'json/progress_meter.php', { 'instance' : instance }, 'GET', function(data) {
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
    displayMessage(data.message, 'error', true);
    scrollTo(0, 0);
}

function formSuccess(form, data) {
    if (config.mathjax) {
        MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
    }
    var hideprevmsg = true;
    if (data.hasOwnProperty('hideprevmsg')) {
        hideprevmsg = data.hideprevmsg;
    }
    displayMessage(data.message, 'ok', hideprevmsg);
    scrollTo(0, 0);
}

function formGlobalError(form, data) {
    globalErrorHandler(data);
}

// Message related functions

function makeMessage(message, type) {
    if (message === undefined) {
        return;
    }

    var messageContainer = jQuery('<div class="alert"></div>').append(message);
    switch (type) {
        case 'ok':
            return messageContainer.addClass('alert-success').get(0);
        case 'error':
            return messageContainer.addClass('alert-danger').get(0);
        case 'warning':
            return messageContainer.addClass('alert-warning').get(0);
        default:
            return messageContainer.addClass('alert-info').get(0);
    }
}

/**
 * Appends a status message to the end of elemid
 */
function displayMessage(message, type, hideprevmsg) {
    if (message === undefined || message == '') {
        return;
    }
    // ensure we have type 'ok', 'error', or 'info' (the default)
    if (!type || (type != 'ok' && type != 'error')) {
        type = 'info';
    }

    var oldmessage = jQuery('#messages div').first();
    var message = makeMessage(message, type);
    jQuery('#messages').append(message);

    if (hideprevmsg || typeof(hideprevmsg) === 'undefined') {
        isPageRendering = true;
        oldmessage.fadeOut(200, function() {
            $j(this).remove();
            isPageRendering = false;
        });
    }
}

/**
 * This variable determines the completeness of a json request
 * = true if the request is still in progress
 */
var isRequestStillProcessing = false;

/**
 * This variable determines the completeness of a page rendering
 * = true if the rendering is still in progress
 */
var isPageRendering = false;

/**
 * Display a nice little loading notification
 */
function processingStart(msg) {
    if (!msg) {
        msg = get_string('loading');
    }

    jQuery('.loading-box').removeClass('hidden').html(
        '<div class="loading-inner">' +
            '<span class="icon-spinner icon-pulse icon icon-lg"></span>' +
            '<span class="loading-message"></span>' +
        '</div>'
    );
    jQuery('.loading-box .loading-message').text(msg);

    isRequestStillProcessing = true;
}

/**
 * Hide the loading notification
 */
function processingStop() {
    setTimeout(function() {
        jQuery('.loading-box').addClass('hidden');
        isRequestStillProcessing = false;
    }, 100); //give users enough time to see the loading indicator
}

/**
 * Clean null values from data
 */
function cleanData(data) {
    if (data !== null && typeof data === 'object') {
        for (var key in data) {
            if (data[key] === null) {
                delete data[key];
            }
        }
    }
}

/**
 * Post a data object to a json script
 */
function sendjsonrequest(url, data, method, successcallback, errorcallback, quiet, anon, extraquiet) {
    var donothing = function() { };
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

    /* The variable fakewwwroot is set when cleanurlusersubdomains is on*/
    if (typeof(fakewwwroot) == 'string') {
        if (url.substring(0, 4) == 'http') {
            url = fakewwwroot + url.substring(config.wwwroot.length);
        }
        else {
            url = fakewwwroot + url;
        }
    }

    cleanData(data);

    var request = jQuery.ajax({
        url: url,
        dataType: 'json',
        data: data,
        method: method.toUpperCase()
    });

    document.documentElement.style.cursor = 'wait';

    request.always(function() {
        document.documentElement.style.cursor = '';
    });

    request.done(function(data) {
        var error = data.error;

        if (typeof(data.message) === 'object' && data.message !== null) {
            data = data.message;
        }

        if (typeof(data.message) === 'string' && (data.message != '') && !quiet) {
            displayMessage(data.message, error ? 'error' : 'ok');
        }

        if (error) {
            errorcallback();
        }
        else {
            try {
                successcallback(data);
            }
            catch (e) {
                console.error('sendjsonrequest() callback failed: ', e, data);
            }
        }

        processingStop();
    });

    request.fail(function(xhr, status) {
        if (status) {
            console.error('sendjsonrequest() failed: ' + status);
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

jQuery(function($) {
    // Autofocus the first element with a class of 'autofocus' on page load (@todo: move this to pieforms.js)
    $('.autofocus').first().focus();
});

// Contextual Help
var contextualHelpCache = new Object();
var contextualHelpSelected = null;
var contextualHelpContainer = null;
var contextualHelpDeferrable = null;
var contextualHelpOpened = false;
var contextualHelpLink = null;
var badIE = false;

function contextualHelpIcon(formName, helpName, pluginType, pluginName, page, section) {
    var link = jQuery(
        '<a href="#">' +
            '<span class="icon icon-info-circle" alt="' + get_string('Help') + '></span>' +
        '</a>'
    );
    link.click(function(e) {
        contextualHelp(formName, helpName, pluginType, pluginName, page, section, link);
        e.preventDefault();
    });

    return jQuery('<span class="help"></span>').append(link).get(0);
}

function contextualHelp(formName, helpName, pluginType, pluginName, page, section, ref) {
    var key;
    var target = $(formName + '_' + helpName + '_container');
    var url = config.wwwroot + 'json/help.php';
    var url_params = {
        'plugintype': pluginType,
        'pluginname': pluginName
    };

    contextualHelpLink = jQuery(ref);

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
        contextualHelpContainer.remove();

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
    contextualHelpContainer = jQuery(
        '<div style="position: absolute" class="contextualHelp hidden" role="dialog">' +
            '<span class="icon icon-spinner icon-pulse"></span>' +
        '</div>'
    );
    var container = contextualHelpLink.parent();
    var inserted = false;
    var illegalParents = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'legend'];
    while (container.length > 0) {
        if (illegalParents.indexOf(container.get(0).nodeName.toLowerCase()) >= 0) {
            container.after(contextualHelpContainer);
            inserted = true;
            break;
        }
        container = container.parent();
    }
    if (!inserted) {
        container = contextualHelpLink.parent();
        container.after(contextualHelpContainer);
    }

    var position = contextualHelpPosition(container);

    // Once it has been positioned, make it visible
    contextualHelpContainer.offset(position);
    contextualHelpContainer.removeClass('hidden');

    contextualHelpSelected = key;

    // load the content
    if (contextualHelpCache[key]) {
        buildContextualHelpBox(contextualHelpCache[key]);
        setTimeout(function() { contextualHelpOpened = true; }, 0);
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
                contextualHelpContainer.html(data.message);
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
    contextualHelpContainer.html(
        '<div class="pull-right pts">' +
            '<a href="" class="help-dismiss" onclick="return false;">' +
                '<span class="icon icon-remove"></span>' +
                '<span class="sr-only">' + get_string('closehelp') + '</span>' +
            '</a>' +
        '</div>' +
        '<div id="helpstop">' + content +  '</div>'
    );

    jQuery('#helpstop').click(function(e) {
        if (e.target.nodeName != "A") {
            e.preventDefault();
            e.stopPropagation();
        }
    });
    contextualHelpContainer.find('.help-dismiss').focus();
}

/*
 * Positions the box so that it's next to the link that was activated
 */
function contextualHelpPosition(container) {
    contextualHelpContainer.css('visibility', 'hidden').removeClass('hidden');
    var position = contextualHelpLink.offset();
    var containerwidth = contextualHelpContainer.outerWidth(true);

    // Adjust the position. The element is moved towards the centre of the
    // screen, based on which quadrant of the screen the help icon is in
    var screenwidth = $j(window).width();
    if (position.left + containerwidth < screenwidth) {
        // Left of the screen - there's enough room for it
        position.left += 25;
    }
    else if (position.left - containerwidth < 0) {
        var oldoffset = contextualHelpContainer.offset();
        var oldposition = contextualHelpContainer.position();

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

    contextualHelpContainer.css('visibility', 'visible');

    return position;
}

/*
 * Ensures that the contextual help box given is fully visible on screen. This
 * will adjust the position of the help vertically if the help has opened right
 * next to the bottom or top of the viewport
 */
function ensureHelpIsOnScreen(container, position) {
    var screenheight = $j(window).height();
    var containerheight = $j(container).height();
    var scrolltop = $j('html').scrollTop();
    if (position.y + containerheight > screenheight + scrolltop) {
        position.y -= containerheight - 18;
        container.css('top', position.y);
    }
    // If the popup's begin outside the screen, put it at top.
    var offsettop = $j(container).offset().top;
    if (offsettop < 0) {
        position.y += -offsettop + scrolltop;
        $j(container).css('top', position.y);
    }
}

/* Only works in non-ie at the moment. Using 'document' as the element
   makes IE detect the event, but then makes it so you need to click on
   the help twice before it opens. */
jQuery(document).click(function(e) {
    if (contextualHelpOpened && !badIE) {
        contextualHelpContainer.remove();
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

function clearCookie(name) {
    setCookie(name, '', -1);
}

// expires is in seconds
function setCookie(name, value, expires, path, domain, secure) {
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

function progressbarUpdate(artefacttype, remove) {
    if (!jQuery('#progressbarwrap').length) {
        return;
    }
    // are we adding or deleting?
    var change = 1;
    if (remove) {
        change = -1;
    }

    // if we have the artefacttype and it needs to be updated
    if (typeof artefacttype != 'undefined') {
        if (jQuery('#progress_counting_' + artefacttype).length > 0) {
            var counting = parseInt(jQuery('#progress_counting_' + artefacttype).text(), 10);
            var oldcompleted = parseInt(jQuery('#progress_completed_' + artefacttype).text(), 10);
            var completed = oldcompleted + change;
            jQuery('#progress_completed_' + artefacttype).text(completed);
            var progressitem = jQuery('#progress_item_' + artefacttype);
            progressitem.html(progressitem.html().replace(/-?\d+/, counting - completed));

            // when progress is met
            if ((counting - completed) <= 0) {
                progressitem.closest('li').addClass('hidden');
            }
            else {
                progressitem.closest('li').removeClass('hidden');
            }
            // now update the totals if we need to
            if ((oldcompleted > 0 && oldcompleted <= counting && remove ) || (completed <= counting && !remove)) {
                var totalcounting = parseInt(jQuery('#progress_counting_total').text(), 10);
                var totalcompleted = parseInt(jQuery('#progress_completed_total').text(), 10) + change;
                jQuery('#progress_completed_total').text(totalcompleted);
                var percentage = ((totalcompleted / totalcounting) * 100).toFixed(0);
                jQuery('#progress_bar_percentage').text(percentage + '%');
                jQuery('#progress_bar_fill').css('width', percentage + '%');
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
    if (!jQuery('#quota_fill').length) {
        console.warn('quotaUpdate(', quotaused, quota, ') called but no id="quota_fill" on page');
        return;
    }

    var update = function(data) {
        if (data.quota >= 1048576) {
            data.quota_display = (data.quota / 1048576).toFixed(1) + 'MB';
            data.quotaused_display = (data.quotaused / 1048576).toFixed(1) + 'MB';
        }
        else if (data.quota >= 1024 ) {
            data.quota_display = (data.quota / 1024).toFixed(1) + 'KB';
            data.quotaused_display = (data.quotaused / 1024).toFixed(1) + 'KB';
        }
        else {
            data.quota_display = data.quota + ' bytes';
            data.quotaused_display = data.quotaused + ' bytes';
        }

        var percentage = Math.round(data.quotaused / data.quota * 100);
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

// Escapes all special characters for RegExp, code from https://developer.mozilla.org/en/docs/Web/JavaScript/Guide/Regular_Expressions
function escapeRegExp(string) {
  return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
}

jQuery(document).ready(function($) {
    // Allow the js / no-js toggle on all pages for theme styling
    $('body').removeClass('no-js').addClass('js');

    // Fix for Chrome and IE, which don't change focus when going to a fragment identifier link
    // Manually focuses the main content when the "skip to main content" link is activated
    $('a.skiplink').click(function() {
        var id = $j(this).attr('href');
        $(id).attr('tabIndex', -1).focus();
    });
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

/**
 * Allow the finding / changing of a param from a url string
 */
function updateUrlParameter(url, param, value) {
    var found = false;
    var vars = url.split("?");
    varparams = vars[1].split("&");

    for (var i = 0; i < varparams.length; i++) {
        var pair = varparams[i].split("=");
        if (pair[0] == param) {
            pair[1] = value;
            found = true;
        }
        varparams[i] = pair.join("=");
    }
    vars[1] = varparams.join("&");
    url = vars.join("?");
    if (!found) {
        url = url + '&' + param + '=' + value;
    }
    return url;
}
