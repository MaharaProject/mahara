(function () {
var code = (function () {
  'use strict';

  var global = tinymce.util.Tools.resolve('tinymce.PluginManager');

  var global$1 = tinymce.util.Tools.resolve('tinymce.dom.DOMUtils');

  var getMinWidth = function (editor) {
    return editor.getParam('code_dialog_width', 600);
  };
  var getMinHeight = function (editor) {
    return editor.getParam('code_dialog_height', Math.min(global$1.DOM.getViewPort().h - 200, 500));
  };
  var $_9q3l9ma2jkvrikk5 = {
    getMinWidth: getMinWidth,
    getMinHeight: getMinHeight
  };

  var setContent = function (editor, html) {
    editor.focus();
    editor.undoManager.transact(function () {
      editor.setContent(html);
    });
    editor.selection.setCursorLocation();
    editor.nodeChanged();
  };
  var getContent = function (editor) {
    return editor.getContent({ source_view: true });
  };
  var $_2r48lha4jkvrikk7 = {
    setContent: setContent,
    getContent: getContent
  };

  var open = function (editor) {
    var minWidth = $_9q3l9ma2jkvrikk5.getMinWidth(editor);
    var minHeight = $_9q3l9ma2jkvrikk5.getMinHeight(editor);
    var win = editor.windowManager.open({
      title: 'Source code',
      body: {
        type: 'textbox',
        name: 'code',
        multiline: true,
        minWidth: minWidth,
        minHeight: minHeight,
        spellcheck: false,
        style: 'direction: ltr; text-align: left'
      },
      onSubmit: function (e) {
        $_2r48lha4jkvrikk7.setContent(editor, e.data.code);
      }
    });
    win.find('#code').value($_2r48lha4jkvrikk7.getContent(editor));
  };
  var $_2zom2ua1jkvrikk4 = { open: open };

  var register = function (editor) {
    editor.addCommand('mceCodeEditor', function () {
      $_2zom2ua1jkvrikk4.open(editor);
    });
  };
  var $_b5q8dza0jkvrikk3 = { register: register };

  var register$1 = function (editor) {
    editor.addButton('code', {
      icon: 'code',
      tooltip: 'Source code',
      onclick: function () {
        $_2zom2ua1jkvrikk4.open(editor);
      }
    });
    editor.addMenuItem('code', {
      icon: 'code',
      text: 'Source code',
      onclick: function () {
        $_2zom2ua1jkvrikk4.open(editor);
      }
    });
  };
  var $_eju4h7a5jkvrikk8 = { register: register$1 };

  global.add('code', function (editor) {
    $_b5q8dza0jkvrikk3.register(editor);
    $_eju4h7a5jkvrikk8.register(editor);
    return {};
  });
  function Plugin () {
  }

  return Plugin;

}());
})();
