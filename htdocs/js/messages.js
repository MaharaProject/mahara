function displayMessage(m) {
    var color = 'red';
    if (m.type == 'ok') {
        color = 'green';
    }
    else if (m.type == 'info') {
        color = '#aa6;';
    }
    var elemid = 'messages';
    if (arguments.length > 1 && typeof(arguments[1]) == 'string') {
        elemid = arguments[1];
    }
    $(elemid).appendChild(DIV({'style':'color:'+color+';'},m.message));
}
