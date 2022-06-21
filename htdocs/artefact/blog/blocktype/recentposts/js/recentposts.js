function recentPostsAddNewPostShortcut(blockid, viewid) {
    var addEntryAnchor = jQuery('#blockinstance_' + blockid + ' .blockinstance-content').find('a').first();
    // Add support for seeing drag and drop.
    addEntryAnchor.mousedown(function(){
        var p = $(this).closest('div.block');
        p.data('dragging', false);
    })
    .mousemove(function(){
        var p = $(this).closest('div.block');
        p.data('dragging', true);
    })
    .mouseup(function(){
        setTimeout(function(p) {
            target = $('#blockinstance_' + blockid + ' .blockinstance-content');
            var p = target.find('a').first().closest('div.block');
            p.data('dragging', false);
        }, 1);
    });

    // React to the click action
    addEntryAnchor.on("click", function(e) {
        var p = $(this).closest('div.block');
        if (p.data('dragging')) {
            // We are dragging by the anchor, not clicking it.
            return;
        }
        e.preventDefault();
        var blogselect = addEntryAnchor.find('select').first().val();
        if (!blogselect) {
            blogselect = jQuery(this).find('span').first().attr('id').match( /\d+/);
        }
        window.open(config.wwwroot + 'artefact/blog/post.php?blog=' + blogselect + '&shortcutnewentryviewid=' + viewid, '_self');
    });
}