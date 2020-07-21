/**
 * Javascript for the authlist Pieform element
 *
 * @package    mahara
 * @subpackage pieform.authlist
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

(function (PieformAuthlist) {
    "use strict";

    // Public methods
    /**
     * Handler for the user clicking the "edit" button next to an auth instance
     * @param id
     * @param plugin
     */
    PieformAuthlist.edit_auth = function(id, plugin) {
        if (requiresConfig(plugin)) {
            sendjsonrequest(
                config.wwwroot + 'admin/users/addauthority.php',
                {
                    'id': id,
                    'edit': 1,
                    'i': get_institution(),
                    'p': plugin,
                    'sesskey': config.sesskey,
                },
                'GET',
                open_modal
            );
            dock.show(jQuery('#configureauthinstance-modal'), true, true);
        }
        else {
            alert(get_string('noauthpluginconfigoptions'));
        }
    };

    /**
     * Move the auth instance up in the list
     *
     * @param id
     */
    PieformAuthlist.move_up = function(id) {
        var instanceArray = document.getElementById('instancePriority').value.split(',');
        var outputArray = new Array();
        for (var i = instanceArray.length - 1; i >= 0; i--) {
            if (instanceArray[i] == id) {
                outputArray[i] = instanceArray[i-1];
                outputArray[i-1] = instanceArray[i];
                --i;
            }
            else {
                outputArray[i] = instanceArray[i];
            }
        }
        rebuildInstanceList(outputArray);
        if (typeof formchangemanager !== 'undefined') {
            var form = jQuery('div#instanceList').closest('form')[0];
            formchangemanager.setFormState(form, FORM_CHANGED);
        }
    };

    /**
     * Move the auth instance down in the list
     * @param id
     */
    PieformAuthlist.move_down = function(id) {
        var instanceArray = document.getElementById('instancePriority').value.split(',');
        var outputArray = new Array();

        for (var i = 0; i < instanceArray.length; i++) {
            if (instanceArray[i] == id) {
                outputArray[i+1] = instanceArray[i];
                outputArray[i] = instanceArray[i+1];
                ++i;
            }
            else {
                outputArray[i] = instanceArray[i];
            }
        }
        rebuildInstanceList(outputArray);
        if (typeof formchangemanager !== 'undefined') {
            var form = jQuery('div#instanceList').closest('form')[0];
            formchangemanager.setFormState(form, FORM_CHANGED);
        }
    };

    /**
     * Remove auth instance from the list
     *
     * @param id
     * @returns {Boolean}
     */
    PieformAuthlist.remove_auth = function(id) {
        var instanceArray = arrayIze('instancePriority');
        var deleteArray   = arrayIze('deleteList');
        var inuseArray   = arrayIze('institution_inuse');

        if (instanceArray.length == 1) {
            alert(get_string('cannotremove'));
            return false;
        }

        for (var i = 0; i < inuseArray.length; i++) {
            if (id == inuseArray[i]) {
                alert(get_string('cannotremoveinuse'));
                return false;
            }
        }

        for (var i = 0; i < instanceArray.length; i++) {
            if (instanceArray[i] == id) {
                instanceArray.splice(i, 1);
                deleteArray.push(id);
                jQuery('#instanceList div#instanceDiv' + id).remove();
            }
        }

        document.getElementById('deleteList').value = deleteArray.join(',');
        rebuildInstanceList(instanceArray);
        if (typeof formchangemanager !== 'undefined') {
            var form = jQuery('div#instanceList').closest('form')[0];
            formchangemanager.setFormState(form, FORM_CHANGED);
        }
    };

    /**
     * Display a newly created auth instance, in the auth instance list
     *
     * @param id
     * @param name
     * @param authname
     */
    PieformAuthlist.insert_auth = function(id, name, authname) {
        var instanceArray;
        var newDiv = '<div class="authInstance" id="instanceDiv'+id+'"> ' +
            '<span class="authitem">' +
            '<label class="authLabel"><a href="" onclick="PieformAuthlist.edit_auth('+id+',\''+authname+'\'); return false;">'+name+'</a></label> ' +
            '<span class="authIcons" id="arrows'+id+'"></span></span></div>';
        document.getElementById('instanceList').innerHTML += newDiv;
        if (document.getElementById('instancePriority').value.length) {
            instanceArray = document.getElementById('instancePriority').value.split(',');
        }
        else {
            instanceArray = new Array();
        }
        instanceArray.push(id);
        rebuildInstanceList(instanceArray);
        jQuery('#messages').empty();
    }


    /**
     * Handler for the user clicking the "add" button
     * @returns {Boolean}
     */
    PieformAuthlist.create_auth = function() {
        var authlistDummySelect = jQuery('#authlistDummySelect');
        var selectedPlugin = authlistDummySelect.val();
        var institution = get_institution();
        if (institution.length == 0) {
            alert('saveinstitutiondetailsfirst');
            return false;
        }

        if (requiresConfig(selectedPlugin) == 1) {
            sendjsonrequest(
                    config.wwwroot + 'admin/users/addauthority.php',
                    {
                        'i': institution,
                        'p': selectedPlugin,
                        'add': 1,
                        'j': 1,
                        'sesskey': config.sesskey
                    },
                    'GET',
                    open_modal
            );
            dock.show(jQuery('#configureauthinstance-modal'), true, true);
            return;
        }

        var authSelect = document.getElementById('authlistDummySelect');
        for (var i=0; i < authSelect.length; i++) {
            if (authSelect.options[i].value == selectedPlugin) {
                authSelect.remove(i);
            }
        }

        sendjsonrequest(
                config.wwwroot + 'admin/users/addauthority.php',
                {
                    'i': institution,
                    'p': selectedPlugin,
                    'add': 1,
                    'j': 1,
                    'sesskey': config.sesskey
                },
                'GET',
                function (data) {
                    PieformAuthlist.insert_auth(data.id, data.name, data.authname);
                }
        );
        return false;
    }

    /**
     * Pieform JS callback. Invoked after the form is successfully submitted
     * @param form
     * @param data
     */
    PieformAuthlist.pieform_success = function(form, data) {
        if (data.new) {
            PieformAuthlist.insert_auth(data.id, data.name, data.authname);
        }
        dock.hide();
    }

    /**
     * Pieform JS callback. Invoked after the form fails validation and is
     * reloaded.
     *
     * @param form
     * @param data
     */
    PieformAuthlist.pieform_error = function(form, data) {
        rewire_pieform();
    }



    // Private methods

    /**
     * Re-render the auth instance list
     *
     * @param outputArray
     */
    function rebuildInstanceList(outputArray) {
        var displayArray = new Array();
        var instanceListDiv = document.getElementById('instanceList');

        // Take each auth instance div, remove its span tag (containing arrow links) and clone it
        // adding the clone to the displayArray list
        for (var i = 0; i < outputArray.length; i++) {
            var myDiv =  document.getElementById('instanceDiv' + outputArray[i]);
            jQuery(myDiv).find('span.authIcons').empty();
            displayArray.push(myDiv.cloneNode(true));
        }

        emptyThisNode(instanceListDiv);

        for (var i = 0; i < displayArray.length; i++) {
            if (displayArray.length > 1) {
                if (i + 1 != displayArray.length) {
                    jQuery(displayArray[i]).find('span.authIcons').first()
                        .append('<a class="btn text-default order-sort-control arrow-down text-midtone" href="" onclick="PieformAuthlist.move_down('+outputArray[i]+'); ' +
                            'return false;"><span class="icon icon-long-arrow-alt-down" role="presentation" aria-hidden="true">' +
                            '</span><span class="sr-only">'+get_string('moveitemdown')+'</span></a>'+"\n");
                }
                if (i != 0) {
                    jQuery(displayArray[i]).find('span.authIcons').first()
                        .append('<a class="btn text-default order-sort-control arrow-up text-midtone" href="" onclick="PieformAuthlist.move_up('+outputArray[i]+'); ' +
                            'return false;"><span class="icon icon-long-arrow-alt-up" role="presentation" aria-hidden="true">' +
                            '</span><span class="sr-only">'+get_string('moveitemup')+'</span></a>'+"\n");
                }
            }

            jQuery(displayArray[i]).find('span.authIcons').first()
                .append('<a class="btn btn-sm" href="" onclick="PieformAuthlist.remove_auth('+outputArray[i]+'); ' +
                    'return false;"><span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true">' +
                    '</span><span class="sr-only">'+get_string('deleteitem')+'</span></a>'+"\n");


            instanceListDiv.appendChild(displayArray[i]);
        }
        document.getElementById('instancePriority').value = outputArray.join(',');
    }

    /**
     *
     * @param id
     * @returns
     */
    function arrayIze(id) {
        var thing = document.getElementById(id).value;
        if (thing == '') {
            return new Array();
        }
        return thing.split(',');
    }

    /**
     * Remove the nodes under this one
     * @param node
     */
    function emptyThisNode(node) {
        while (node.hasChildNodes()) {
            node.removeChild(node.childNodes[0]);
        }
    }

    /**
     * Check whether this authname requires configuration
     * Relies on a data attribute populated into the dummySelect via the authlist.tpl
     * @param authname
     * @returns
     */
    function requiresConfig(authname) {
        return Boolean(jQuery('#authlistDummySelect option[value=' + authname + ']').data('requires_config'));
    }

    /**
     * Finds out the institution we're setting up the auth list for
     *
     * @returns
     */
    function get_institution() {
        return jQuery('#authlistDummySelect').closest('form').find('input[type=hidden][name=i]').val();
    }

    function open_modal(data) {
        var authlistDummySelect = jQuery('#authlistDummySelect');

        jQuery('.authinstance-header').html(authlistDummySelect.find("option[value='" + data.pluginname + "']").text());
        jQuery('.authinstance-content').html(data['html']);
        (function() {
            eval(data.javascript);
        })();

        rewire_pieform();

        PieformManager.signal('onload', 'auth_config');

    }

    function close_modal() {
        dock.hide();
        authlistDummyButton.focus();

        PieformManager.signal('onreply', 'auth_config');

        // Clear the content of the dock
        jQuery('.authinstance-header').html('');
        jQuery('.authinstance-content').html('');
    }

    /**
     * Rewrites the "cancel" button in the Pieform displayed in the modal window,
     * so that it closes the modal instead of the default Pieforms action of
     * reloading the page.
     */
    function rewrite_modal_cancel_button() {
        jQuery('#cancel_auth_config_submit,#authlist_modal_closer').off('click').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            close_modal();
        });
    }

    /**
     * These are the things we need to do to rewrite parts of the returned pieform,
     * whether we're displaying it new, or whether the form has been reloaded
     * after a validation failure.
     */
    function rewire_pieform() {
        rewrite_modal_cancel_button();
        if ($('#auth_config_parent')) {
            $('#auth_config_parent').on('change', authloginmsgVisibility);
            authloginmsgVisibility();
        }
        if ($('#auth_config_ssodirection')) {
            $('#auth_config_ssodirection').on('change', updateSsoOptions);
            updateSsoOptions();
        }
    }

    /**
     * Toggles a message about the visibility of the parent auth
     */
    function authloginmsgVisibility() {
        // If Parent authority is 'None'
        if ($('#auth_config_parent').length && $('#auth_config_parent').val() != 0) {
            jQuery('#auth_config_authloginmsg_container').addClass('hidden');
        }
        else {
            jQuery('#auth_config_authloginmsg_container').removeClass('hidden');
            tinyMCE.execCommand('mceRemoveEditor', false, "auth_config_authloginmsg");
            tinyMCE.execCommand('mceAddEditor', false, "auth_config_authloginmsg");
        }
    }

    var ssoAllOptions = {
        'updateuserinfoonlogin': 'theyssoin',
        'weautocreateusers': 'theyssoin',
        'theyautocreateusers': 'wessoout',
        'weimportcontent': 'theyssoin'
    };

    function updateSsoOptions() {
        var current = $('#auth_config_ssodirection').val();
        if (typeof current !== 'undefined') {
            for (var opt in ssoAllOptions) {
                if (ssoAllOptions[opt] == current) {
                    jQuery('#auth_config_' + opt + '_container').removeClass('hidden');
                }
                else {
                    jQuery('#auth_config_' + opt + '_container').addClass('hidden');
                }
            }
        }
    }
}( window.PieformAuthlist = window.PieformAuthlist || {}));

/**
 * Placeholders for Pieform callback methods. Can't call PieformAuthlist
 * methods directly, because Pieforms doesn't like periods in JS
 * callback method names.
 */
function authlist_success(form, data) {
    return PieformAuthlist.pieform_success(form, data);
}
function authlist_error(form, data) {
    return PieformAuthlist.pieform_error(form, data);
}
