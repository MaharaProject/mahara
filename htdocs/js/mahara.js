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

// Appends a status message to the end of elemid
function displayMessage(m, /* optional */ elemid) {
    var color = 'red';
    if (m.type == 'ok') {
        color = 'green';
    }
    else if (m.type == 'info') {
        //color = '#aa6;';
        logDebug(m.message);
        return;
    }

    if (typeof(elemid) == 'undefined') {
        elemid = 'messages';
    }
    var message = DIV({'style':'color:'+color+';'},m.message);
    appendChildNodes(elemid, message);
    callLater(2, function() {
        removeElement(message);
        //fade(message);
    });
}

