function addNewPostShortcut(blockid) {
    forEach(
        getElementsByTagAndClassName('a', 'btnshortcut', 'blockinstance_' + blockid),
        function(a) {
            disconnectAll(a);
            connect(a, 'onclick', function(e) {
                e.stop();
                var p = getFirstParentByTagAndClassName(a, 'div', 'shortcut');
                var blogselect = getFirstElementByTagAndClassName('select','select', p);
                window.open(config.wwwroot + 'artefact/blog/post.php?blog=' + blogselect.value,'_blank');
            });
        }
    );
}
