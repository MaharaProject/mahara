<script>

    function move_up(id) {
        instanceArray = document.getElementById('instancePriority').value.split(',');
        var outputArray = new Array();
        for(i = instanceArray.length - 1; i >= 0; i--) {
            if(instanceArray[i] == id) {
                outputArray[i] = instanceArray[i-1];
                outputArray[i-1] = instanceArray[i];
                --i;
            } else {
                outputArray[i] = instanceArray[i];
            }
        }
        sendjsonrequest('{{$WWWROOT}}webservice/admin/connections.php', {'i': '{{$institution}}', 'ids': outputArray.join(','), 'reorder': 1, 'j': 1}, 'GET', function (data) {
            rebuildInstanceList(outputArray);
        });
        return false;
    }

    function move_down(id) {
        instanceArray = document.getElementById('instancePriority').value.split(',');
        var outputArray = new Array();

        for(i = 0; i < instanceArray.length; i++) {
            if(instanceArray[i] == id) {
                outputArray[i+1] = instanceArray[i];
                outputArray[i] = instanceArray[i+1];
                ++i;
            } else {
                outputArray[i] = instanceArray[i];
            }
        }
        sendjsonrequest('{{$WWWROOT}}webservice/admin/connections.php', {'i': '{{$institution}}', 'ids': outputArray.join(','), 'reorder': 1, 'j': 1}, 'GET', function (data) {
            rebuildInstanceList(outputArray);
        });
        return false;
    }

    function buttonOptions (id, length, i) {
        // Make the buttons a row should have
        var buttons = '';        
        if (length > 1) {
            if (i + 1 != length) {
                buttons += '<a class="btn btn-link text-midtone" href="" onclick="move_down(' + id + '); return false;"><span class="icon icon-long-arrow-down" role="presentation" aria-hidden="true"></span><span class="sr-only">' + get_string('moveitemdown') + '</span></a>'
            }
            else {
                buttons += '<span class="emptybtnspace"></span>';
            }

            if (i != 0) {
                buttons += '<a class="btn btn-link text-midtone" href="" onclick="move_up(' + id + '); return false;"><span class="icon icon-long-arrow-up" role="presentation" aria-hidden="true"></span><span class="sr-only">' + get_string('moveitemup') + '</span></a>';
            }
            else {

                buttons += '<span class="emptybtnspace"></span>';
            }
        }
        buttons += '<a class="btn btn-secondary btn-sm" href="" onclick="removeConnection(' + id + '); return false;"><span class="icon icon-trash icon-lg text-danger" role="presentation" aria-hidden="true"></span><span class="sr-only">' + get_string('deleteitem') + '</span></a>' + "\n";
        return buttons;
    }

    function rebuildInstanceList(outputArray) {
        var displayArray = new Array();
        var instanceList = jQuery('.authInstance');

        // Take each connection row, remove its icons and add back new ones
        for (i = 0; i < outputArray.length; i++) {
            var row = jQuery('#instanceDiv' + outputArray[i]);
            row.data('order', i);
            row.find('.authIcons').html(buttonOptions(outputArray[i], outputArray.length, i));
        }
        // Now re-sort the rows
        instanceList.sort(function(a, b) {
            return jQuery(a).data('order') - jQuery(b).data('order');
        });
        jQuery("#instanceList").html(instanceList);
        document.getElementById('instancePriority').value = outputArray.join(',');
    }

    function arrayIze(id) {
        var thing = document.getElementById(id).value;
        if (thing == '') {
            return new Array();
        }
        return thing.split(',');
    }

    function removeConnection(id) {
        instanceArray = arrayIze('instancePriority');
        deleteArray   = arrayIze('deleteList');
        sendjsonrequest('{{$WWWROOT}}webservice/admin/addconnection.php', {'i': '{{$institution}}', 'id': id, 'delete': 1, 'j': 1}, 'GET', function (data) {
            for(i = 0; i < instanceArray.length; i++) {
                if(instanceArray[i] == id) {
                    instanceArray.splice(i, 1);
                    deleteArray.push(id);
                    var instances = jQuery('#instanceDiv'+id);
                    instances.remove(0);
                }
            }
            rebuildInstanceList(instanceArray);
            document.getElementById('deleteList').value = deleteArray.join(',');
        });
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
    {{foreach $instancelist instance}}
    <div class="authInstance" id="instanceDiv{{$instance->id}}" data-order="1">
        <label class="authLabel">
            {{if $instance->enable}}
                <span class="icon icon-lg icon-check text-success" title="{{str tag='enabled'}}"></span>
            {{else}}
                <span class="icon icon-lg icon-times text-danger" title="{{str tag='disabled'}}"></span>
            {{/if}}
            <a href="" onclick="editinstance({{$instance->id}},'{{$instance->name}}'); return false;">
            {{$instance->name}}</a>
        </label>
        <span class="authIcons" id="arrows{{$instance->id}}">
            {{if $instance->index + 1 < $instance->total}}
            <a class="btn btn-link text-midtone" href="" onclick="move_down({{$instance->id}}); return false;">
                <span class="icon icon-long-arrow-down" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{{str tag=moveitemdown}}</span>
            </a>
            {{else}}
                <span class="emptybtnspace"></span>
            {{/if}}
            {{if $instance->index != 0 }}
            <a class="btn btn-link text-midtone" href="" onclick="move_up({{$instance->id}}); return false;">
                <span class="icon icon-long-arrow-up" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{{str tag=moveitemup}}</span>
            </a>
            {{else}}
                <span class="emptybtnspace"></span>
            {{/if}}
            <a href="" class="btn btn-secondary btn-sm" onclick="removeConnection({{$instance->id}}); return false;">
                <span class="icon icon-trash icon-lg text-danger" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{{str tag=deleteitem}}</span>
            </a>
        </span>
    </div>
    {{/foreach}}
</div>
{{else}}
<div>{{str tag='instancelistempty' section='auth.webservice'}}</div>
{{/if}}

<input type="hidden" id="instancePriority" name="instancePriority" value="{{$instancestring}}" />
<input type="hidden" id="deleteList" name="deleteList" value="" />
