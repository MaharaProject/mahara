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
