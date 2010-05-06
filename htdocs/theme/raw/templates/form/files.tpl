{{auto_escape off}}
<script type="text/javascript">
    var {{$name}}_current = 0;
    var {{$name}}_newrefinput;
    var {{$name}}_newref;

    function {{$name}}_new() {
        {{$name}}_current++;
        {{$name}}_newrefinput = INPUT({'type': 'file', 'name': '{{$name}}_files_' + {{$name}}_current});
        var {{$name}}_newref = DIV(null,{{$name}}_newrefinput);

        appendChildNodes('{{$name}}_list', {{$name}}_newref);

        {{$name}}_newrefinput.focus();
    }
</script>
<div id="{{$name}}_list">
    <input type="file" name="{{$name}}_files_0"/>
</div>
<a href="" onclick="{{$name}}_new(); return false;">[+]</a>
{{/auto_escape}}
