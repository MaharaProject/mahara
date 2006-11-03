// Expects strings array
function get_string(s) {
    // @todo Still need to sprintf these strings.
    var flatargs = flattenArguments(arguments);
    if (arguments.length > 1) {
        argstr =  '(' + flatargs.slice(1).join(',') + ')';
    } else {
        argstr = '';
    }
    if (typeof(strings) == 'undefined' || typeof(strings[s]) == 'undefined') {
        return '[[[' + s + argstr + ']]]';
    }
    return strings[s] + argstr;  
}

// Appends a status message to the end of elemid
function displayMessage(m, /* optional */ elemid) {
    var color = 'red';
    if (m.type == 'ok') {
        color = 'green';
    }
    else if (m.type == 'info') {
        color = '#aa6;';
    }
    if (typeof(elemid) == 'undefined') {
        elemid = 'messages';
    }
    $(elemid).appendChild(DIV({'style':'color:'+color+';'},m.message));
}


// Tests if elements with the 'required' class have content and
// displays the appropriate message.

// Uses the html output from form.php to find the title of required
// fields: <label for="elementid">Element title</label>
function testRequired(e,formid) {
    if (hasElementClass(e,'required') && e.value == '') {
        var labels = getElementsByTagAndClassName('label',null,formid);
        for (var j = 0; j < labels.length; j++) {
            if (getNodeAttribute(labels[j],'for') == e.name) {
                displayMessage({'message':get_string('requiredfieldempty',scrapeText(labels[j])),
                                    'type':'error'});
                return false;
            }
        }
        displayMessage({'message':get_string('requiredfieldempty'),'type':'error'});
        return false;
    }
    return true;
}

// Gets form elements, submits them to a url via post, and waits for a
// JSON response containing the result of the submission.
function submitForm(formid,url) {
    if (typeof(tinyMCE) != 'undefined') {
        tinyMCE.triggerSave();
    }
    var formelements = getElementsByTagAndClassName(null,formid,formid);
    var data = {};
    for (var i = 0; i < formelements.length; i++) {
        if (testRequired(formelements[i])) {
            data[formelements[i].name] = formelements[i].value;
        }
        else {
            return false;
        }
    }
    var req = getXMLHttpRequest();
    req.open('POST',url);
    req.setRequestHeader('Content-type','application/x-www-form-urlencoded'); 
    var d = sendXMLHttpRequest(req,queryString(data));
    d.addCallback(function (result) {
        var data = evalJSONRequest(result);
        displayMessage({'message':data.message,'type':data.success});
    });
    d.addErrback(function() { displayMessage(get_string('unknownerror'),'error'); });
    displayMessage({'message':get_string('processingform'),'type':'info'});
    return false;
}
