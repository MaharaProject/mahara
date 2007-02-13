<script type="text/javascript">
    var {{$name}}_d;

    var {{$name}}_searchfunc = function (q) {

        replaceChildNodes('{{$name}}_messages');

        sendjsonrequest('{{$WWWROOT}}json/usersearch.php', {'query':q, 'limit': 100}, 'GET', 
            function (users) {
                var members = {};
                var counter = 0;
                forEach($('{{$name}}_members').childNodes, function(node) {
                    if (node.nodeName == 'OPTION') {
                        members[node.value] = 1;
                        counter++;
                    }
                });

                replaceChildNodes('{{$name}}_potential');
                forEach(users.data, function(user) {
                    if (members[user.id]) {
                        return;
                    }
                    appendChildNodes('{{$name}}_potential',OPTION({'value':user.id},user.name));
                });

                if(users.count > users.limit) {
                    replaceChildNodes('{{$name}}_messages',
                        DIV(null,
                            'Only showing first ',
                            SPAN({'id': '{{$name}}_userlimit'}, users.limit),
                            ' results of ',
                            SPAN({'id': '{{$name}}_usercount'}, users.count - counter)
                        )
                    );
                }
            });
    }

    addLoadEvent(function () {
        removeElement($('{{$name}}_potential').childNodes[0]);
        removeElement($('{{$name}}_members').childNodes[0]);

        {{$name}}_searchfunc('');

        connect('{{$name}}_search', 'onkeypress', function (k) {
            if (k.key().code == 13) {
                {{$name}}_searchfunc($('{{$name}}_search').value);
                k.stop();
            }
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
<table cellspacing="0" width="100%">
    <tr>
        {{if $filter}}
        <td colspan="3">
            <select id="{{$name}}_groups">
                <option value="all">All Users</option>
                <option value="all">Test Community</option>
                <option value="all">My Group</option>
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
        <td style="width: 50%;">
            <select id="{{$name}}_potential" size="10" multiple="true" style="width: 100%;"><option></option></select>
        </td>
        <td>
            <button type="button" onClick="{{$name}}_moveopts('potential','members')">--&gt;</button><br>
            <button type="button" onClick="{{$name}}_moveopts('members','potential')">&lt;--</button>
        </td>
        <td style="width: 50%;">
            <select size="10" multiple="true" id="{{$name}}_members" style="width: 100%;"><option></option>
{{foreach from=$options key=id item=user}}
                <option value="{{$id|escape}}">{{$user|escape}}</option>
{{/foreach}}
            </select>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <label for="{{$name}}_search">Search:</label><input id="{{$name}}_search" type="text">
        </td>
    </tr>
</table>
<input type="hidden" id="{{$name}}" name="{{$name}}" value="{{$value}}">
