function addNewTaggedPostShortcut(blockid) {
    forEach(
        getElementsByTagAndClassName('a', 'btnshortcut', 'blockinstance_' + blockid),
        function(a) {
            disconnectAll(a);
            connect(a, 'onclick', function(e) {
                e.stop();
                var p = getFirstParentByTagAndClassName(a, 'div', 'shortcut');
                var selectedBlog = getFirstElementByTagAndClassName('select','select', p);
                var currentTag = getFirstElementByTagAndClassName('input','select', p);
                var BlogIDInput = INPUT({'name': 'blog', 'type': 'text', 'value': selectedBlog.value});
                var TagInput = INPUT({'name': 'tagselect', 'type': 'text', 'value': currentTag.value});
                var myForm = FORM(
                    {'action': config.wwwroot + 'artefact/blog/post.php', 'method': 'POST'},
                    BlogIDInput,
                    TagInput
                );
                document.body.appendChild(myForm);
                myForm.submit();
            });
        }
    );
}
