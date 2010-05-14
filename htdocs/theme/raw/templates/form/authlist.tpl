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
            replaceChildNodes(getFirstElementByTagAndClassName('span', 'authIcons', 'instanceDiv' + outputArray[i]));
            displayArray.push(myDiv.cloneNode(true));
        }

        emptyThisNode(instanceListDiv);

        for(i = 0; i < displayArray.length; i++) {
            if(displayArray.length > 1) {
                if (i + 1 != displayArray.length) {
                    getFirstElementByTagAndClassName('span', 'authIcons', displayArray[i]).innerHTML += '<a href="" onclick="move_down('+outputArray[i]+'); return false;">[&darr;]</a>'+"\n";
                }
                if(i != 0) {
                    getFirstElementByTagAndClassName('span', 'authIcons', displayArray[i]).innerHTML += '<a href="" onclick="move_up('+outputArray[i]+'); return false;">[&uarr;]</a>'+"\n";
                }
            }

            getFirstElementByTagAndClassName('span', 'authIcons', displayArray[i]).innerHTML += '<a href="" onclick="removeAuth('+outputArray[i]+'); return false;">[x]</a>'+"\n";

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
        inuseArray   = arrayIze('institution_inuse');

        if (instanceArray.length == 1) {
            alert({{$cannotremove|safe}});
            return false;
        }

		for(i = 0; i < inuseArray.length; i++) {
			if (id == inuseArray[i]) {
				alert({{$cannotremoveinuse|safe}});
				return false;
			}
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
        {{foreach $authtypes authtype}}
            requires_config['{{$authtype->name}}'] = {{$authtype->requires_config}};
        {{/foreach}}
        
        return requires_config[authname];
    }

    function addinstance() {
        var selectedPlugin = document.getElementById('dummySelect').value;
        var institution = '{{$institution}}';
        if (institution.length == 0) {
            alert({{$saveinstitutiondetailsfirst|safe}});
            return false;
        }

        if (requiresConfig(selectedPlugin) == 1) {
            window.open('addauthority.php?add=1&i={{$institution}}&p=' + selectedPlugin, 'addinstance', 'height=600,width=800,screenx=250,screenY=200,scrollbars=1');
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
            alert({{$noauthpluginconfigoptions|safe}});
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

        replaceChildNodes('messages');
    }

</script>
{{*

IMPORTANT: do not introduce any new whitespace into the instanceList div.

*}}
<div id="instanceList">{{foreach $instancelist instance}}<div class="authInstance" id="instanceDiv{{$instance->id}}">
        <label class="authLabel">
            <a href="" onclick="editinstance({{$instance->id}},'{{$instance->authname}}'); return false;">
            {{str tag="title" section="auth.`$instance->authname`"}}</a>
        </label>
        <span class="authIcons" id="arrows{{$instance->id}}">
            {{if $instance->index + 1 < $instance->total}}
            <a href="" onclick="move_down({{$instance->id}}); return false;">[&darr;]</a>
            {{/if}}
            {{if $instance->index != 0 }}
            <a href="" onclick="move_up({{$instance->id}}); return false;">[&uarr;]</a>
            {{/if}}
            <a href="" onclick="removeAuth({{$instance->id}}); return false;">[x]</a>
        </span>
    </div>{{/foreach}}
</div>
<select name="dummy" id="dummySelect">
{{foreach $authtypes authtype}}
    <option value="{{$authtype->name}}"{{if !$authtype->is_usable}} disabled="disabled"{{/if}}>{{$authtype->title}} - {{$authtype->description}}</option>
{{/foreach}}
</select>
<button type="button" onclick="addinstance(); return false;" name="button" value="foo">{{str tag=Add section=admin}}</button>
<input type="hidden" id="instancePriority" name="instancePriority" value="{{$instancestring}}" />
<input type="hidden" id="deleteList" name="deleteList" value="" />
