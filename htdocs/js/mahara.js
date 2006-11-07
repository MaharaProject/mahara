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

// The javascript form validating function should be available from
// the server as formname_validate().

// Gets form elements, submits them to a url via post, and waits for a
// JSON response containing the result of the submission.
function submitForm(formid,url,callback) {
    if (typeof(tinyMCE) != 'undefined') {
        tinyMCE.triggerSave();
    }
    if (!eval(formid + '_validate()')) {
        return false;
    }
    var formelements = getElementsByTagAndClassName(null,formid,formid);
    var data = {};
    for (var i = 0; i < formelements.length; i++) {
        data[formelements[i].name] = formelements[i].value;
    }
    var req = getXMLHttpRequest();
    req.open('POST',url);
    req.setRequestHeader('Content-type','application/x-www-form-urlencoded'); 
    var d = sendXMLHttpRequest(req,queryString(data));
    d.addCallback(function (result) {
        var data = evalJSONRequest(result);
        displayMessage({'message':data.message,'type':data.success});
        callback();
    });
    d.addErrback(function() { displayMessage(get_string('unknownerror'),'error'); });
    displayMessage({'message':get_string('processingform'),'type':'info'});
    return false;
}
