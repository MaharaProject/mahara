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
if (typeof(getFirstElementByTagAndClassName) == 'undefined') {
    function getFirstElementByTagAndClassName(tag, className, parentElement) {
        return getElementsByTagAndClassName(tag, className, parentElement)[0];
    }
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

