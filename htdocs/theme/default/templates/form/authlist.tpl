<script type="text/javascript">

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
        rebuildInstanceList(outputArray);
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
        rebuildInstanceList(outputArray);
    }

    function rebuildInstanceList(outputArray) {
        var displayArray = new Array();
        var instanceListDiv = document.getElementById('instanceList');

        // Take each auth instance div, remove its span tag (containing arrow links) and clone it
        // adding the clone to the displayArray list
        for (i = 0; i < outputArray.length; i++) {
            var myDiv =  document.getElementById('instanceDiv' + outputArray[i]);
            emptyThisNode(myDiv.childNodes[3]);
            displayArray.push(myDiv.cloneNode(true));
        }

        emptyThisNode(instanceListDiv);

        for(i = 0; i < displayArray.length; i++) {
            if(displayArray.length > 1) {
                if (i + 1 != displayArray.length) {
                    displayArray[i].childNodes[3].innerHTML += '<a href="" onclick="move_down('+outputArray[i]+'); return false;">[&darr;]</a>'+"\n";
                }
                if(i != 0) {
                	displayArray[i].childNodes[3].innerHTML += '<a href="" onclick="move_up('+outputArray[i]+'); return false;">[&uarr;]</a>'+"\n";
                }
            }
            displayArray[i].childNodes[3].innerHTML += '<a href="" onclick="removeAuth('+outputArray[i]+'); return false;">[x]</a>'+"\n";
            instanceListDiv.appendChild(displayArray[i]);
        }
        document.getElementById('instancePriority').value = outputArray.join(',');
    }

    function arrayIze(id) {
        var thing = document.getElementById(id).value;
        if(thing == '') {
            return new Array();
        }
        return thing.split(',');
    }

    function removeAuth(id) {
        instanceArray = arrayIze('instancePriority');
        deleteArray   = arrayIze('deleteList');

        if (instanceArray.length == 1) {
            alert("{{$cannotremove}}");
            return false;
        }

        for(i = 0; i < instanceArray.length; i++) {
            if(instanceArray[i] == id) {
                instanceArray.splice(i, 1);
                deleteArray.push(id);
                var instanceListDiv = document.getElementById('instanceList');
                instanceListDiv.removeChild(instanceListDiv.childNodes[i]);
            }
        }

        document.getElementById('deleteList').value = deleteArray.join(',');
        rebuildInstanceList(instanceArray);
    }

    function emptyThisNode(node) {
        while(node.hasChildNodes()) {
            node.removeChild(node.childNodes[0]);
        }
    }

    function requiresConfig(authname) {
        var requires_config = new Array();
        {{section name=mysec3 loop=$authtypes}}
            requires_config['{{$authtypes[mysec3]->name}}'] = {{$authtypes[mysec3]->requires_config}};
        {{/section}}
        
        return requires_config[authname];
    }

    function addinstance() {
        var selectedPlugin = document.getElementById('dummySelect').value;
		var institution = '{{$institution}}';
		if (institution.length == 0) {
			alert('Please save the institution details before configuring authentication plugins.');
			return false;
		}

        if (requiresConfig(selectedPlugin) == 1) {
            window.open('addauthority.php?add=1&i={{$institution}}&p=' + selectedPlugin, 'addinstance', 'height=520,width=550,screenx=250,screenY=200,scrollbars=1');
            return;
        }

        var authSelect = document.getElementById('dummySelect');
        for (var i=0; i < authSelect.length; i++) {
            if (authSelect.options[i].value == selectedPlugin) {
                authSelect.remove(i);
            }
        }

        sendjsonrequest('{{$WWWROOT}}admin/users/addauthority.php', {'i': '{{$institution}}', 'p': selectedPlugin, 'add': 1, 'j': 1 }, 'GET', function (data) { addAuthority(data.id, data.name, data.authname); });
        return false;
    }

    function editinstance(id, plugin) {
        if (requiresConfig(plugin)) {
            window.open('addauthority.php?id='+id+'&edit=1&i={{$institution}}&p=' + plugin, 'editinstance', 'height=520,width=550,screenx=250,screenY=200,scrollbars=1');
        } else {
            alert('There are no configuration options associated with this plugin');
        }
    }

    function addAuthority(id, name, authname) {
        var newDiv = '<div class="authInstance" id="instanceDiv'+id+'"> '+
            '<label class="authLabel"><a href="" onclick="editinstance('+id+',\''+authname+'\'); return false;">'+name+'</a></label> '+
            '<span class="authIcons" id="arrows'+id+'"></span> </div>';
        document.getElementById('instanceList').innerHTML += newDiv;
        if(document.getElementById('instancePriority').value.length) {
            instanceArray = document.getElementById('instancePriority').value.split(',');
        } else {
            instanceArray = new Array();
        }
        instanceArray.push(id);
        rebuildInstanceList(instanceArray);
    }

</script>
<!-- TODO: shouldn't have css inline -->
<style type="text/css">
    .authIcons   { float: right; } 
    .authLabel   { float: left;  }
    .authInstance { clear: both; }
    #dummySelect { margin-top: 5px; }
</style>
{{*

IMPORTANT: do not introduce any new whitespace into the instanceList div.

*}}
<div id="instanceList">{{
section name=mysec loop=$instancelist
}}<div class="authInstance" id="instanceDiv{{$instancelist[mysec]->id}}">
        <label class="authLabel">
            <a href="" onclick="editinstance({{$instancelist[mysec]->id}},'{{$instancelist[mysec]->authname}}'); return false;">
            {{$instancelist[mysec]->instancename}}</a>
        </label>
        <span class="authIcons" id="arrows{{$instancelist[mysec]->id}}">
            {{ if $instancelist[mysec]->index + 1 < $instancelist[mysec]->total }}
            <a href="" onclick="move_down({{$instancelist[mysec]->id}}); return false;">[&darr;]</a>
            {{ /if }}
            {{ if $instancelist[mysec]->index != 0 }}
            <a href="" onclick="move_up({{$instancelist[mysec]->id}}); return false;">[&uarr;]</a>
            {{ /if }}
            <a href="" onclick="removeAuth({{$instancelist[mysec]->id}}); return false;">[x]</a>
        </span>
    </div>{{
    /section
}}</div>
<br>
<select name="dummy" id="dummySelect" {{if $institution eq ''}}disabled{{/if}}>
{{section name=mysec2 loop=$authtypes}}
    <option>{{$authtypes[mysec2]->name}}</option>
{{/section}}
</select>
<a href="" onclick="addinstance(); return false;">[+]</a>
<input type="hidden" id="instancePriority" name="instancePriority" value="{{ $instancestring }}" /><br>
<input type="hidden" id="deleteList" name="deleteList" value="" /><br>