var addNewTaggedPostShortcut = (function($) {
    return function (blockid) {
      $('#blockinstance_' + blockid + ' a.btnshortcut').each(function() {
            $(this).off();
            $(this).on('click', function(e) {
                  e.preventDefault();
                  var p = $(this).closest('div.shortcut');
                  var selectedBlog = p.find('select.select').first();
                  var currentTag = p.find('input.select').first();
                  var BlogIDInput = $('<input>', {'name': 'blog', 'type': 'text', 'value': selectedBlog[0].value});
                  var TagInput = $('<input>', {'name': 'tagselect', 'type': 'text', 'value': currentTag[0].value});
                  var myForm = $('<form>', {'action': config.wwwroot + 'artefact/blog/post.php', 'method': 'POST'});
                  myForm.append(BlogIDInput[0], TagInput);
                  document.body.appendChild(myForm[0]);
                  myForm.trigger('submit');
              });
          }
      );
    };
}(jQuery));
