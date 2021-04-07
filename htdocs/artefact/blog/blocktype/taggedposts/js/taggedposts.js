var taggedPostsAddNewPostShortcut = (function($) {
    return function (blockid, viewid) {
      $('#blockinstance_' + blockid + ' a.btnshortcut').each(function() {
            $(this).off();
            // Prevent the dragging of a block by the anchor from acting on
            // the click event.
            $(this)
            .mousedown(function(){
                var p = $(this).closest('div.shortcut');
                p.data('dragging', false);
            })
            .mousemove(function(){
                var p = $(this).closest('div.shortcut');
                p.data('dragging', true);
            })
            .mouseup(function(){
                setTimeout(function(p) {
                    var p = $(this).closest('div.shortcut');
                    p.data('dragging', false);
                }, 1);
            });
            $(this).on('click', function(e) {
                  e.preventDefault();
                  var p = $(this).closest('div.shortcut');
                  if (p.data('dragging')) {
                      // We are dragging by the anchor, not clicking it.
                      return;
                  }
                  var selectedBlog = p.find('select.select').first();
                  var currentTag = p.find('input.select').first();
                  var BlogIDInput = $('<input>', {'name': 'blog', 'type': 'text', 'value': selectedBlog[0].value});
                  var TagInput = $('<input>', {'name': 'tagselect', 'type': 'text', 'value': currentTag[0].value});
                  var myForm = $('<form>', {'action': config.wwwroot + 'artefact/blog/post.php?shortcutnewentryviewid='+ viewid, 'method': 'POST'});
                  myForm.append(BlogIDInput[0], TagInput);
                  document.body.appendChild(myForm[0]);
                  myForm.trigger('submit');
              });
          }
      );
    };
}(jQuery));
