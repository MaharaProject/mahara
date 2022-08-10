/**
 * plugin.js
 *
 * Copyright 2013, Daniel Thies
 * Released under LGPL License.
 *
 * Updated for Tinymce 5, Catalyst IT Limited
 * LGPL 2019
 */

tinymce.PluginManager.add('mathslate', function(editor,url) {

	function showDialog() {

		var slateurl = location.protocol == 'https:' ? '/mathslate-s.html' : '/mathslate.html';

		win = editor.windowManager.openUrl({
			title: "Math Editor",
			url: url + slateurl,
			width: 520,
			height: 550,
			body: {
				type: 'panel',
				items: [{
					type: 'iframe',
					name: 'mathslate',
					content : url + slateurl,
					html: url + slateurl,
					type: 'object',
				}]
			},
			buttons: [
				{type: 'custom',
				text: "Insert Inline",
				name: "inline",
				primary: true,
				},
				{type: 'custom',
				text: "Insert Display",
				name: "display",
				primary: false
				},
				{type: 'cancel',
				text: "Cancel",
				name: "cancel",
				primary: false
				}
			],
			onAction: (win, details) => {
				if (details.name === 'inline') {
                    var output = jQuery('.tox-navobj iframe').contents().find('.mathslate-preview').text();
					editor.selection.setContent('\\\(' + output + '\\\)');
					win.close();
				}
				if (details.name === 'display') {
                    var output = jQuery('.tox-navobj iframe').contents().find('.mathslate-preview').text();
					editor.selection.setContent('\\\[' + output + '\\\]');
					win.close();
				}
				if (details.name === 'cancel') {
					win.close();
				}
			},
		});
	}

	editor.ui.registry.addButton('mathslate', {
                path : url + '/img/mathslate.png',
		tooltip: 'Insert Math',
		onAction: showDialog,
		icon: 'mathslate'
	});

	editor.ui.registry.addMenuItem('mathslate', {
		text: 'Insert Math',
		onAction: showDialog,
		context: 'insert'
	});
});
