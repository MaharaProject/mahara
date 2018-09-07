{{if $connections}}
<script>

    function addinstance() {
        var selectedPlugin = document.getElementById('dummySelect').value;
        var institution = '{{$institution}}';
        window.location = '{{$WWWROOT}}webservice/admin/addconnection.php?add=1&i={{$institution}}&p=' + selectedPlugin;
        return;
    }

</script>

<div class="select connections lead">
    <span class="picker">
        <select class="select form-control" name="dummy" id="dummySelect">
        {{foreach $connections connection}}
        <option value="{{$connection->id}}">{{$connection->shortname}} - {{$connection->name}}</option>
        {{/foreach}}
        </select>
    </span>
    <div>
        <button class="btn btn-primary" type="button" onclick="addinstance(); return false;" name="button" value="foo">{{str tag=Add section=admin}}</button>
    </div>
</div>
{{else}}
<div class="alert alert-info">
{{str tag="nodefinedconnections" section="auth.webservice"}}
</div>
{{/if}}