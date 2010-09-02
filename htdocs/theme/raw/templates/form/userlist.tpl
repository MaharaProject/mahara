<script type="text/javascript">
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

                // work around IE7's magical shrinking select box. Only
                // Internet Explorer has the "brilliance" to slowly shrink the
                // select box every time you put a new option into it :(
                // It turns out by altering the contents of the container
                // object, IE decides it might be a good time to recalculate
                // the width of other children.
                var td = $('{{$name}}_potential').parentNode;
                appendChildNodes(td, ' ');
                removeElement(td.lastChild);
                // </rant>


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

        connect('{{$name}}_search', 'onkeypress', function (k) {
            if (keypressKeyCode(k) == 13) {
                {{$name}}_searchfunc({'query': $('{{$name}}_search').value});
                k.stop();
            }
        });

        connect('{{$name}}_search_btn', 'onclick', function(e) {
            {{$name}}_searchfunc({'query': $('{{$name}}_search').value});
            e.stop();
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

        forEach(list, function(node) {
            to.appendChild(node);
            node.selected = false;
        });

        // Update the counters if they are present
        if ($('{{$name}}_userlimit')) {
            if (from.id == '{{$name}}_potential') {
                replaceChildNodes('{{$name}}_userlimit', parseInt($('{{$name}}_userlimit').innerHTML) - list.length);
                replaceChildNodes('{{$name}}_usercount', parseInt($('{{$name}}_usercount').innerHTML) - list.length);
            }
            else {
                replaceChildNodes('{{$name}}_userlimit', parseInt($('{{$name}}_userlimit').innerHTML) + list.length);
                replaceChildNodes('{{$name}}_usercount', parseInt($('{{$name}}_usercount').innerHTML) + list.length);
            }
        }

        var members = new Array();
        forEach($('{{$name}}_members').childNodes, function(node) {
            if (typeof(node) == 'object' && typeof(node.value) == 'string') {
                members.push(node.value);
            }
        });

        $('{{$name}}').value=members.join(',');
    };
</script>
<table class="userlisttable fullwidth">
    <tr>
        {{if $filter}}
        <td colspan="3">
            <select id="{{$name}}_groups">
                <option value="all">All Users</option>
                <option value="all">Test Community</option>
            </select>
        </td>
        {{/if}}
    </tr>
    {{if $lefttitle || $righttitle}}
    <tr>
        <th>{{$lefttitle}}</th>
        <th></th>
        <th>{{$righttitle}}</th>
    </tr>
    {{/if}}
    <tr>
        <td colspan="3" id="{{$name}}_messages">
        </td>
    </tr>
    <tr>
        <td class="lrfieldlists">
            <select id="{{$name}}_potential" size="10" multiple="true" style="width: 100%;"><option></option></select>
        </td>
        <td class="lrbuttons">
            <button type="button" onClick="{{$name}}_moveopts('potential','members')" class="rightarrow">&gt;</button><br>
            <button type="button" onClick="{{$name}}_moveopts('members','potential')" class="leftarrow">&lt;</button>
        </td>
        <td class="lrfieldlists">
            <select size="10" multiple="true" id="{{$name}}_members" style="width: 100%;"><option></option>
{{foreach from=$options key=id item=user}}
                <option value="{{$id}}">{{$user}}</option>
{{/foreach}}
            </select>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <label for="{{$name}}_search" class="plain">{{str tag='search'}}:</label> <input id="{{$name}}_search" type="text" class="text"> <button id="{{$name}}_search_btn" type="button" class="btn btn-search">{{str tag="go"}}</button>
        </td>
    </tr>
</table>
<input type="hidden" id="{{$name}}" name="{{$name}}" value="{{$value}}">
