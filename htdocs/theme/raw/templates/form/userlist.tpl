<script>
    var {{$name}}_d;

    var {{$name}}_searchparams;

    var {{$name}}_searchfunc = function (params) {

        jQuery("#{{$name}}_messages").empty();

        {{if $group}}
        {{$name}}_searchparams['group'] = {{$group}};
        {{$name}}_searchparams['includeadmins'] = {{$includeadmins}};
        {{/if}}

        for (var p in params) {
            {{$name}}_searchparams[p] = params[p];
        }

        sendjsonrequest('{{$WWWROOT}}{{$searchscript}}', {{$name}}_searchparams, 'GET',
            function (users) {
                var members = {};
                var counter = 0;
                jQuery('#{{$name}}_members').children().each(function(id, node) {
                    if (node.nodeName == 'OPTION') {
                        members[node.value] = 1;
                        counter++;
                    }
                });

                var results = [];

                if (users.data) {
                    jQuery.each(users.data, function(id, user) {
                        if (members[user.id]) {
                            return;
                        }
                        //appendChildNodes('{{$name}}_potential',OPTION({'value':user.id},user.name));
                        results.push(jQuery('<option>', {'value':user.id, 'text': user.name}));
                    });
                }

                jQuery('#{{$name}}_potential').empty().append(results);
                if (typeof params.query != 'undefined') {
                    jQuery( jQuery('#{{$name}}_potential')[0]).trigger('focus');
                }

                if(users.count > users.limit) {
                    jQuery('#{{$name}}_messages').empty().append(
                        jQuery('<div>').append(
                          {{$onlyshowingfirst|safe}}, ' ',
                          jQuery('<span>', {'id': '{{$name}}_userlimit', 'text': users.limit }),
                          ' ', {{$resultsof|safe}}, ' ',
                          jQuery('<span>', {'id': '{{$name}}_usercount', 'text': users.count - counter })
                        )
                    );
                }
            });
    }

    jQuery(function ($) {
        $('#{{$name}}_potential :first').remove();
        $('#{{$name}}_members :first').remove();

        {{$name}}_searchparams = {{$searchparams|safe}};

        {{$name}}_searchfunc({});

        $('#{{$name}}_search').on('keypress', function(k) {
            if (k.keyCode == 13) {
                {{$name}}_searchfunc({'query': $('#{{$name}}_search').val()});
                k.preventDefault();
            }
        });

        $('#{{$name}}_search_btn').on("click", function(e) {
            {{$name}}_searchfunc({'query': $('#{{$name}}_search').val()});
            e.preventDefault();
        });
    });

    function {{$name}}_moveopts(from,to) {
        var from = jQuery('#{{$name}}_' + from);
        var to   = jQuery('#{{$name}}_' + to);
        var list = new Array();

        from.children().each(function() {
            if (!this.selected) {
                return;
            }
            list.push(this);
        });

        if (list.length === 0) {
            return;
        }

        jQuery.each(list, function() {
            to.append(this);
            jQuery(this).prop('selected', false);
        });

        // Update the counters if they are present
        if (jQuery('#{{$name}}_userlimit').length) {
            if (from.id === '{{$name}}_potential') {
                jQuery('#{{$name}}_userlimit').empty().append(parseInt(jQuery('#{{$name}}_userlimit').html(), 10) - list.length);
                jQuery('#{{$name}}_usercount').empty().append(parseInt(jQuery('#{{$name}}_usercount').html(), 10) - list.length);
            }
            else {
                jQuery('#{{$name}}_userlimit').empty().append(parseInt(jQuery('#{{$name}}_userlimit').html(), 10) + list.length);
                jQuery('#{{$name}}_usercount').empty().append(parseInt(jQuery('#{{$name}}_usercount').html(), 10) + list.length);
            }
        }
        var members = new Array();
        jQuery('#{{$name}}_members').children().each(function(i, node) {
            if (typeof(node) == 'object' && typeof(node.value) == 'string') {
                members.push(node.value);
            }
        });

        jQuery('#{{$name}}').val(members.join(','));
        if (typeof formchangemanager !== 'undefined') {
            var form = jQuery('select#{{$name}}_members').closest('form')[0];
            formchangemanager.setFormState(form, FORM_CHANGED);
        }

        to.trigger('focus');
    };

    jQuery(function ($) {
        $('#{{$name}}_potential').on('dblclick', function (event) { {{$name}}_moveopts('potential','members') });
        $('#{{$name}}_members', 'ondblclick', function (event) { {{$name}}_moveopts('members','potential') });
    });
</script>
<table class="userlisttable fullwidth">
     <tr>
        <td colspan="3" class="form-group last">
            <label for="{{$name}}_search" class="plain sr-only">{{str tag='filter'}}</label>
            <div class="input-group">
                <input id="{{$name}}_search" type="text" class="text form-control">
                <span class="input-group-append">
                    <button id="{{$name}}_search_btn" type="button" class="btn btn-primary">
                    {{str tag='search'}}
                    </button>
                </span>
            </div>
        </td>
    </tr>
    <tr>
        <td class="lrfieldlists form-group last pt0">
            {{if $lefttitle}}<label class="h3" for="{{$name}}_potential">{{$lefttitle}}</label>{{/if}}
            <select class="form-control" id="{{$name}}_potential" size="10" multiple="true" style="width: 100%;"><option></option></select>
        </td>
        <td class="lrbuttons form-group last select-col pt0">
            <div class="btn-group btn-group-vertical">
                <button type="button" name="rightarrow" onClick="{{$name}}_moveopts('potential','members')" class="rightarrow btn btn-primary btn-lg">
                    <span class="icon icon-long-arrow-right" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">{{$rightarrowlabel}}</span>
                </button>
                <button type="button" name="leftarrow" onClick="{{$name}}_moveopts('members','potential')" class="leftarrow btn btn-primary btn-lg">
                     <span class="icon icon-long-arrow-left" role="presentation" aria-hidden="true"></span>
                     <span class="sr-only">{{$leftarrowlabel}}</span>
                </button>
            </div>
        </td>
        <td class="lrfieldlists form-group pt0">
            {{if $righttitle}}<label class="h3" for="{{$name}}_members">{{$righttitle}}</label>{{/if}}
            <select class="form-control" size="10" multiple="true" id="{{$name}}_members" style="width: 100%;"><option></option>
            {{foreach from=$options key=id item=user}}
                <option value="{{$id}}">{{$user}}</option>
            {{/foreach}}
            </select>
        </td>
    </tr>
</table>
<input type="hidden" id="{{$name}}" name="{{$name}}" value="{{$value}}">
<p id="{{$name}}_messages" class="description"></p>
