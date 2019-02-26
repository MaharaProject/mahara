/**
 * JS behaviour for the export UI
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

// TODO: i18n

jQuery(function($) {
    $('#whatviewsselection').removeClass('d-none');
    $('#whatcollectionsselection').removeClass('d-none');

    var containers = {
        'views': {'container': $('#whatviews'), 'visible': false},
        'collections': {'container': $('#whatcollections'), 'visible': false}
    };

    var radios = [];

    function toggleRadios(state) {
        $.each(radios, function() {
            $(this).prop('disabled', state);
        });
    }
    var enableRadios  = toggleRadios.bind(null, '');
    var disableRadios = toggleRadios.bind(null, 'disabled');

    // Make the radio buttons show/hide the view selector
    $('#whattoexport-buttons input.radio').each(function(id, radio) {
        radios.push(radio);
        $(radio).on('click', function(e) {
            if ($(radio).prop('checked')) {
                for (var c in containers) {
                    if (c != radio.value && containers[c].visible) {
                        disableRadios();
                        containers[c].visible = false;
                        containers[c].container.slideUp( 500, enableRadios);
                        break;
                    }
                }
                if (radio.value != 'all' && !containers[radio.value].visible) {
                    disableRadios();
                    containers[radio.value].visible = true;
                    containers[radio.value].container.slideDown( 500, function() {
                      containers[radio.value].container.removeClass('js-hidden');
                      enableRadios();
                    });
                }
            }
        });
        // Open the view selector if the views checkbox is select on page load
        if (radio.value != 'all' && radio.checked && !containers[radio.value].visible) {
            containers[radio.value].visible = true;
            containers[radio.value].container.removeClass('js-hidden');
        }
    });

    // Make the export format radio buttons show/hide the includefeedback checkbox
    $('#exportformat-buttons input.radio').each(function() {
        $(this).on('click', function(e) {
            $('#includefeedback').hide();
            if ($(this).prop('checked')) {
                if ($(this).val() === 'html') {
                    $('#includefeedback').show();
                }
            }
        });
    });

    // Hook up 'click to preview' links
    $(containers.views.container).find('a.viewlink').each(function() {
        $(this).off();
        $(this).prop('title', 'Click to preview');
        $(this).on('click', function (event) {
            event.preventDefault();
            var href = $(this).prop('href');
            var params = {
                'id': getUrlParameter('id', href) || '',
                'export': 1
            };
            sendjsonrequest(config['wwwroot'] + 'view/viewcontent.json.php', params, 'POST', showPreview.bind(null, 'big'));
        });
    });
    $(containers.collections.container).find('a.viewlink').each(function() {
       $(this).off();
       $(this).prop('title', 'Click to preview');
       $(this).on('click', function (event) {
           event.preventDefault();
           var href = $(this).prop('href');
           var params = {
               'id': getUrlParameter('id', href) || '',
               'export': 1
           };
           sendjsonrequest(config['wwwroot'] + 'collection/viewcontent.json.php', params, 'POST', showPreview.bind(null, 'big'));
       });
   });

    // Checkbox helpers
    var checkboxes = $('#whatviews input.checkbox');
    var checkboxHelperDiv = $('<div>');

    var checkboxSelectAll = $('#selection_all');
    $(checkboxSelectAll).on('click', function(e) {
        e.preventDefault();
        checkboxes.each(function() {
          $(this).prop('checked', true);
        });
    });

    var checkboxReverseSelection = $('#selection_reverse');
    checkboxReverseSelection.on('click', function(e) {
        e.preventDefault();
          checkboxes.each(function() {
            $(this).prop('checked', !$(this).prop('checked'));
          });
    });

    var checkboxesCollection = $('#whatcollections input.checkbox');
    var checkboxHelperDivCollection = $('<div>');

    var checkboxSelectAllCollection = $('#selection_all_collections');
    $(checkboxSelectAllCollection).on('click', function(e) {
        e.preventDefault();
        checkboxesCollection.each(function() {
          $(this).prop('checked', true);
        });
    });

    var checkboxReverseSelectionCollection = $('#selection_reverse_collections');
    checkboxReverseSelectionCollection.on('click', function(e) {
        e.preventDefault();
          checkboxesCollection.each(function() {
            $(this).prop('checked', !$(this).prop('checked'));
          });
    });

    checkboxHelperDiv.insertBefore($(containers.views.container).find('div:first'));
});
