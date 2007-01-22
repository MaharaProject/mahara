<script type="text/javascript">
    var {{$name}}_d;

    var {{$name}}_searchfunc = function (q) {
        processingStart();
        replaceChildNodes('{{$name}}_messages');
        {{$name}}_d = loadJSONDoc('{{$WWWROOT}}json/usersearch.php', {'query':q, 'limit': 100});

        {{$name}}_d.addCallbacks(
            function (users) {
                processingStop();

                if(users.error) {
                    displayMessage(users.message,'error');
                    return;
                }

                var members = new Array();
                forEach($('{{$name}}_members').childNodes, function(node) {
                    members[node.value] = 1;
                });

                replaceChildNodes('{{$name}}_potential');
                forEach(users.data, function(user) {
                    if (members[user.id]) {
                        return;
                    }
                    appendChildNodes('{{$name}}_potential',OPTION({'value':user.id},user.name));
                });

                if(users.count > users.limit) {
                    replaceChildNodes('{{$name}}_messages', DIV(null, 'Only showing first ' + users.limit + ' results of ' + users.count));
                }
            },
            function (err) {
                processingStop();
                displayMessage(get_string('errorloadingusers'),'error');
            }
        );
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

        var members = new Array();
        forEach($('{{$name}}_members').childNodes, function(node) {
            if (typeof(node) == 'object' && typeof(node.value) == 'string') {
                members.push(node.value);
            }
        });

        $('{{$name}}').value=members.join(',');
    };
</script>
<table cellspacing="0">
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
        <td style="width: 15em;">
            <select id="{{$name}}_potential" size="10" multiple="true" style="width: 100%;"><option></option></select>
        </td>
        <td>
            <button type="button" onClick="{{$name}}_moveopts('potential','members')">--&gt;</button>
            <button type="button" onClick="{{$name}}_moveopts('members','potential')">&lt;--</button>
        </td>
        <td style="width: 15em;">
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
