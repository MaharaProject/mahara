<script>

    function move(id, direction) {
        sendjsonrequest('{{$WWWROOT}}webservice/admin/connections.php', {'i': '{{$institution}}', 'row': id, 'reorder': 1, 'direction': direction}, 'POST', function (data) {
            if (data.success) {
                location.reload();
            }
        });
        return false;
    }

    function removeConnection(id) {
        var r = confirm(get_string('deleteconnection', 'auth.webservice'));
        if (r == true) {
            sendjsonrequest('{{$WWWROOT}}webservice/admin/addconnection.php', {'i': '{{$institution}}', 'id': id, 'delete': 1}, 'GET', function (data) {
                if (data.rc == 'succeeded') {
                    location.reload();
                }
            });
        }
        return false;
    }

    function editinstance(id, plugin) {
        window.location = '{{$WWWROOT}}webservice/admin/addconnection.php?id=' + id + '&edit=1&i={{$institution}}&p=' + plugin;
        return;
    }

</script>
{{*

IMPORTANT: do not introduce any new whitespace into the instanceList div.

*}}
{{if $instancelist}}
<div id="instanceList">
    <div class="table-responsive">
        <table class="fullwidth table table-striped">
        <thead>
        <tr>
            <th>{{str tag="name" section="mahara"}}</th>
            <th>{{str tag="active" section="mahara"}}</th>
            <th>{{str tag="servicetype" section="auth.webservice"}}</th>
            <th>{{str tag="authtype" section="auth.webservice"}}</th>
            <th>{{str tag="jsonenabled" section="auth.webservice"}}</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {{foreach $instancelist instance}}
        <tr id="instanceDiv{{$instance->id}}">
            <td>
                {{$instance->name}}
            </td>
            <td>
                {{if $instance->enable}}
                <span class="icon icon-lg icon-check text-success" title="{{str tag='enabled'}}"></span>
                {{else}}
                <span class="icon icon-lg icon-times text-danger" title="{{str tag='disabled'}}"></span>
                {{/if}}
            </td>
            <td>{{$instance->type}}</td>
            <td>{{$instance->authtype}}</td>
            <td>
                {{if $instance->json}}
                <span class="icon icon-lg icon-check text-success" title="{{str tag='enabled'}}"></span>
                {{else}}
                <span class="icon icon-lg icon-times text-danger" title="{{str tag='disabled'}}"></span>
                {{/if}}
            </td>
            <td class="text-end">
            <span class="authIcons" id="arrows{{$instance->id}}">
            {{if $instance->index + 1 < $instance->total}}
            <button class="btn text-default order-sort-control arrow-down text-midtone" onclick="move({{$instance->id}}, 'down'); return false;">
                <span class="icon icon-long-arrow-alt-down" role="presentation" aria-hidden="true"></span>
                <span class="visually-hidden">{{str tag=moveitemdown}}</span>
            </button>
            {{else}}
                <span class="emptybtnspace"></span>
            {{/if}}
            {{if $instance->index != 0 }}
            <button class="btn text-default order-sort-control arrow-up text-midtone" onclick="move({{$instance->id}}, 'up'); return false;">
                <span class="icon icon-long-arrow-alt-up" role="presentation" aria-hidden="true"></span>
                <span class="visually-hidden">{{str tag=moveitemup}}</span>
            </button>
            {{else}}
                <span class="emptybtnspace"></span>
            {{/if}}
            <div class="btn-group btn-tasks">
            <button class="btn btn-secondary btn-sm" onclick="editinstance({{$instance->id}},'{{$instance->name}}'); return false;" title="{{str tag=edit}}">
                <span class="icon icon-pencil-alt" role="presentation" aria-hidden="true"></span>
                <span class="visually-hidden">{{str tag=editspecific section=mahara arg1="$instance->name"}}</span>
            </button>
            <button class="btn btn-secondary btn-sm" onclick="removeConnection({{$instance->id}}); return false;" title="{{str tag=delete}}">
                <span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true"></span>
                <span class="visually-hidden">{{str tag=deletespecific section=mahara arg1="$instance->name"}}</span>
            </button>
            </div>
            </td>
        </tr>
        {{/foreach}}
        </tbody>
        </table>
    </div>
</div>
{{else}}
<div>{{str tag='instancelistempty' section='auth.webservice'}}</div>
{{/if}}

<input type="hidden" id="instancePriority" name="instancePriority" value="{{$instancestring}}" />
<input type="hidden" id="deleteList" name="deleteList" value="" />
