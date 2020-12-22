function addNewPostShortcut(blockid, viewid) {
    var addentry = jQuery('#blockinstance_' + blockid + ' .blockinstance-content');
    addentry.find('a').first().on("click", function(e) {
        e.preventDefault();
        var blogselect = addentry.find('select').first().val();
        if (!blogselect) {
            blogselect = jQuery(this).find('span').first().attr('id').match( /\d+/);
        }
        window.open(config.wwwroot + 'artefact/blog/post.php?blog=' + blogselect + '&shortcutnewentryviewid=' + viewid, '_self');
    });
}