debugObject = function (obj) {
    // gives a nice, stable string representation for objects,
    // ignoring any methods
    var keyValuePairs = [];
    for (var k in obj) {
        var v = obj[k];
        if (typeof(v) != 'function') {
            keyValuePairs.push([k, v]);
        }
    };
    keyValuePairs.sort(compare);
    log( "{" + map(
        function (pair) {
            return map(repr, pair).join(":");
        },
        keyValuePairs
    ).join(", ") + "}");
};

debugObjectAll = function (obj) {
    // gives a nice, stable string representation for objects
    var keyValuePairs = [];
    for (var k in obj) {
        var v = obj[k];
        keyValuePairs.push([k, v]);
    };
    keyValuePairs.sort(compare);
    log( "{" + map(
        function (pair) {
            return map(repr, pair).join(":");
        },
        keyValuePairs
    ).join(", ") + "}");
};
