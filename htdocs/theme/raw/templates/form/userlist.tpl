<script type="application/javascript">
    var {{$name}}_d;

    var {{$name}}_searchparams;

    var {{$name}}_searchfunc = function (params) {

        replaceChildNodes('{{$name}}_messages');

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
                forEach($('{{$name}}_members').childNodes, function(node) {
                    if (node.nodeName == 'OPTION') {
                        members[node.value] = 1;
                        counter++;
                    }
                });

                var results = [];

                if (users.data) {
                    forEach(users.data, function(user) {
                        if (members[user.id]) {
                            return;
                        }
                        //appendChildNodes('{{$name}}_potential',OPTION({'value':user.id},user.name));
                        results.push(OPTION({'value':user.id},user.name));
                    });
                }

                replaceChildNodes('{{$name}}_potential', results);
                if (typeof params.query != 'undefined') {
                    $('{{$name}}_potential').focus();
                }

                if(users.count > users.limit) {
                    replaceChildNodes('{{$name}}_messages',
                        DIV(null,
                            {{$onlyshowingfirst|safe}}, ' ',
                            SPAN({'id': '{{$name}}_userlimit'}, users.limit),
                            ' ', {{$resultsof|safe}}, ' ',
                            SPAN({'id': '{{$name}}_usercount'}, users.count - counter)
                        )
                    );
                }
            });
    }

    addLoadEvent(function () {
        removeElement($('{{$name}}_potential').childNodes[0]);
        removeElement($('{{$name}}_members').childNodes[0]);

        {{$name}}_searchparams = {{$searchparams|safe}};

        {{$name}}_searchfunc({});

        jQuery('#{{$name}}_search').keypress(function(k) {
            if (k.keyCode == 13) {
                {{$name}}_searchfunc({'query': $('{{$name}}_search').value});
                k.preventDefault();
            }
        });

        jQuery('#{{$name}}_search_btn').click(function(e) {
            {{$name}}_searchfunc({'query': $('{{$name}}_search').value});
            e.preventDefault();
        });
    });

    function {{$name}}_moveopts(from,to) {
        var from = $('{{$name}}_' + from);
        var to   = $('{{$name}}_' + to);
        var list = new Array();

        forEach(from.childNodes, function(opt) {
            if (!opt.selected) {
                return;
            }
            list.push(opt);
        });

        if (list.length === 0) {
            return;
        }

        forEach(list, function(node) {
            to.appendChild(node);
            node.selected = false;
        });

        // Update the counters if they are present
        if ($('{{$name}}_userlimit')) {
            if (from.id == '{{$name}}_potential') {
                replaceChildNodes('{{$name}}_userlimit', parseInt($('{{$name}}_userlimit').innerHTML, 10) - list.length);
                replaceChildNodes('{{$name}}_usercount', parseInt($('{{$name}}_usercount').innerHTML, 10) - list.length);
            }
            else {
                replaceChildNodes('{{$name}}_userlimit', parseInt($('{{$name}}_userlimit').innerHTML, 10) + list.length);
                replaceChildNodes('{{$name}}_usercount', parseInt($('{{$name}}_usercount').innerHTML, 10) + list.length);
            }
        }

        var members = new Array();
        forEach($('{{$name}}_members').childNodes, function(node) {
            if (typeof(node) == 'object' && typeof(node.value) == 'string') {
                members.push(node.value);
            }
        });

        $('{{$name}}').value=members.join(',');
        if (typeof formchangemanager !== 'undefined') {
            var form = jQuery('select#{{$name}}_members').closest('form')[0];
            formchangemanager.setFormState(form, FORM_CHANGED);
        }

        to.focus();
    };

    addLoadEvent(function () {
        connect('{{$name}}_potential', 'ondblclick', function (event) { {{$name}}_moveopts('potential','members') });
        connect('{{$name}}_members', 'ondblclick', function (event) { {{$name}}_moveopts('members','potential') });
    });
</script>
<table class="userlisttable fullwidth">
     <tr>
        <td colspan="3" class="form-group last">
            <label for="{{$name}}_search" class="plain sr-only">{{str tag='filter'}}</label>
            <div class="input-group">
                <input id="{{$name}}_search" type="text" class="text form-control">
                <span class="input-group-btn">
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
