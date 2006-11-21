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

function global_error_handler(data) {}

// Appends a status message to the end of elemid
function displayMessage(message, type) {
    // ensure we have type 'ok', 'error', or 'info' (the default)
    if (!type || (type != 'ok' && type != 'error')) {
        type = 'info';
    }

    var message = DIV({'class':type},message, ' ', A({'style': 'cursor: pointer;', 'onclick':'removeElement(this.parentNode)'},'[X]'));

    appendChildNodes('messages', message);

    // callLater(2, function() {
    //     removeElement(message);
    //     //fade(message);
    // });
}

// display a nice little loading notification
function processingStart(msg) {
    if (!msg) {
        msg = get_string('loading');
    }

    replaceChildNodes(
        $('loading_box'),
        DIV(msg)
    );
    $('loading_box').style.display = 'block';
}

// hide the loading notification
function processingStop() {
    $('loading_box').style.display = 'none';
}

// Autofocus the first element with a class of 'autofocus' on page load
addLoadEvent(function() {
    var element = getFirstElementByTagAndClassName(null, 'autofocus', document.body)

    if ( element && typeof(element.focus) == 'function' ) {
        element.focus();
    }
});

// @todo remove this when we migrate to mochikit 1.4
//if (typeof(getFirstElementByTagAndClassName) == 'undefined') {
//    function getFirstElementByTagAndClassName(tag, className, parentElement) {
//        return getElementsByTagAndClassName(tag, className, parentElement)[0];
//    }
//}

var ctxHelp = new Array();
var ctxHelp_selected;
var container;

function contextualHelp(formName, helpName, pluginType, pluginName, page) {
    var tooltip = $('tooltip');
    log(tooltip);
    log(ctxHelp_selected);

    var key = pluginType + '/' + pluginName + '/' + helpName;
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
        contextualHelpOpen(helpName, ctxHelp[key].content);
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

    contextualHelpOpen(helpName, 'spinner');
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

// this function gets the cookie, if it exists
function getCookie( name ) {
    var start = document.cookie.indexOf( name + "=" );
    var len = start + name.length + 1;

    if (
        ( !start ) &&
        ( name != document.cookie.substring( 0, name.length ) )
    ) {
        return null;
    }

    if ( start == -1 ) {
        return null;
    }

    var end = document.cookie.indexOf( ";", len );

    if ( end == -1 ) {
        end = document.cookie.length;
    }

    return unescape( document.cookie.substring( len, end ) );
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
    if ( expires )
    {
        expires = expires * 1000;
    }

    var expires_date = new Date( today.getTime() + (expires) );

    document.cookie = name + "=" + escape( value ) +
    ( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) + 
    ( ( path ) ? ";path=" + path : "" ) + 
    ( ( domain ) ? ";domain=" + domain : "" ) +
    ( ( secure ) ? ";secure" : "" );
}

function toggleChecked(c) {
    var e = getElementsByTagAndClassName(null,c);
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
