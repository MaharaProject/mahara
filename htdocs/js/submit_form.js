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
    d.addErrback(function() { displayMessage(get_string('badjsonresponse'),'error'); });
    displayMessage({'message':get_string('processingform'),'type':'info'});
    return false;
}
