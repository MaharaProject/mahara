<script type="application/javascript">

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

    function rebuildInstanceList(outputArray) {
        var displayArray = new Array();
        var instanceListDiv = document.getElementById('instanceList');

        // Take each auth instance div, remove its span tag (containing arrow links) and clone it
        // adding the clone to the displayArray list
        for (i = 0; i < outputArray.length; i++) {
            var myDiv =  document.getElementById('instanceDiv' + outputArray[i]);
            replaceChildNodes(getFirstElementByTagAndClassName('span', 'authIcons', 'instanceDiv' + outputArray[i]));
            displayArray.push(myDiv.cloneNode(true));
        }

        emptyThisNode(instanceListDiv);

        for(i = 0; i < displayArray.length; i++) {
            if(displayArray.length > 1) {
                if (i + 1 != displayArray.length) {
                    getFirstElementByTagAndClassName('span', 'authIcons', displayArray[i]).innerHTML += '<a class="btn btn-link text-midtone" href="" onclick="move_down('+outputArray[i]+'); return false;"><span class="icon icon-long-arrow-down" role="presentation" aria-hidden="true"></span><span class="sr-only">'+get_string('moveitemdown')+'</span></a>'+"\n";
                }
                if(i != 0) {
                    getFirstElementByTagAndClassName('span', 'authIcons', displayArray[i]).innerHTML += '<a class="btn btn-link text-midtone" href="" onclick="move_up('+outputArray[i]+'); return false;"><span class="icon icon-long-arrow-up" role="presentation" aria-hidden="true"></span><span class="sr-only">'+get_string('moveitemup')+'</span></a>'+"\n";
                }
            }

            getFirstElementByTagAndClassName('span', 'authIcons', displayArray[i]).innerHTML += '<a class="btn btn-default btn-sm" href="" onclick="removeConnection('+outputArray[i]+'); return false;"><span class="icon icon-trash icon-lg text-danger" role="presentation" aria-hidden="true"></span><span class="sr-only">'+get_string('deleteitem')+'</span></a>'+"\n";

            instanceListDiv.appendChild(displayArray[i]);
        }
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

    function emptyThisNode(node) {
        while(node.hasChildNodes()) {
            node.removeChild(node.childNodes[0]);
        }
    }

    function addinstance() {
        var selectedPlugin = document.getElementById('dummySelect').value;
        var institution = '{{$institution}}';
        window.open('{{$WWWROOT}}webservice/admin/addconnection.php?add=1&i={{$institution}}&p=' + selectedPlugin, 'addinstance', 'height=600,width=800,screenx=250,screenY=200,scrollbars=1');
        return;
    }

    function editinstance(id, plugin) {
        // if (requiresConfig(plugin)) {
        window.open('addconnection.php?id='+id+'&edit=1&i={{$institution}}&p=' + plugin, 'editinstance', 'height=520,width=550,screenx=250,screenY=200,scrollbars=1');
        return;
    }

    function addConnection(id, name, connectionname) {
        var newDiv = '<div class="authInstance" id="instanceDiv'+id+'"> '+
            '<label class="authLabel"><a href="" onclick="editinstance('+id+',\''+connectionname+'\'); return false;">'+name+'</a></label> '+
            '<span class="authIcons" id="arrows'+id+'"></span> </div>';
        document.getElementById('instanceList').innerHTML += newDiv;
        if(document.getElementById('instancePriority').value.length) {
            instanceArray = document.getElementById('instancePriority').value.split(',');
        } else {
            instanceArray = new Array();
        }
        instanceArray.push(id);
        rebuildInstanceList(instanceArray);
        if (typeof formchangemanager !== 'undefined') {
            var form = jQuery('div#instanceList').closest('form')[0];
            formchangemanager.setFormState(form, FORM_CHANGED);
        }
        replaceChildNodes('messages');
    }

</script>
{{*

IMPORTANT: do not introduce any new whitespace into the instanceList div.

*}}
<div id="instanceList">
    {{foreach $instancelist instance}}
    <div class="authInstance" id="instanceDiv{{$instance->id}}">
        <label class="authLabel">
            <a href="" onclick="editinstance({{$instance->id}},'{{$instance->name}}'); return false;">
            {{$instance->name}}</a>
        </label>
        <span class="authIcons" id="arrows{{$instance->id}}">
            {{if $instance->index + 1 < $instance->total}}
            <a class="btn btn-link text-midtone" href="" onclick="move_down({{$instance->id}}); return false;">
                <span class="icon icon-long-arrow-down" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{{str tag=moveitemdown}}</span>
            </a>
            {{/if}}
            {{if $instance->index != 0 }}
            <a class="btn btn-link text-midtone" href="" onclick="move_up({{$instance->id}}); return false;">
                <span class="icon icon-long-arrow-up" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{{str tag=moveitemup}}</span>
            </a>
            {{/if}}
            <a href="" class="btn btn-default btn-sm" onclick="removeConnection({{$instance->id}}); return false;">
                <span class="icon icon-trash icon-lg text-danger" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{{str tag=deleteitem}}</span>
            </a>
        </span>
    </div>
    {{/foreach}}
</div>
<p/>
<div class="select">
    <span class="picker">
        <select class="select form-control" name="dummy" id="dummySelect">
        {{foreach $connections connection}}
            <option value="{{$connection->id}}">{{$connection->shortname}} - {{$connection->name}}</option>
        {{/foreach}}
        </select>
    </span>
    <button class="btn btn-primary" type="button" onclick="addinstance(); return false;" name="button" value="foo">{{str tag=Add section=admin}}</button>
</div>

<input type="hidden" id="instancePriority" name="instancePriority" value="{{$instancestring}}" />
<input type="hidden" id="deleteList" name="deleteList" value="" />
