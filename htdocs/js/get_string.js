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
