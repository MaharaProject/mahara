<script type="application/javascript">
    var {{$name}}_current = 0;
    var {{$name}}_newrefinput;
    var {{$name}}_newref;

    function {{$name}}_new() {
        {{$name}}_current++;
        var id = '{{$name}}_files_' + {{$name}}_current;
        {{$name}}_newlabel = LABEL({'for': id, 'class': 'accessible-hidden'}, $('{{$name}}_files_label').innerHTML);
        {{$name}}_newrefinput = INPUT({'type': 'file', 'id': id, 'name': id});
        var {{$name}}_newref = DIV(null,{{$name}}_newlabel, {{$name}}_newrefinput);

        appendChildNodes('{{$name}}_list', {{$name}}_newref);

        {{$name}}_newrefinput.focus();
    }
</script>
<div id="{{$name}}_list">
    {{if $maxfilesize}}
    <input type="hidden" name="MAX_FILE_SIZE" value="{{$maxfilesize}}">
    {{/if}}
    <label id="{{$name}}_files_label" class="accessible-hidden" for="{{$name}}_files_0">{{$title}}</label>
    <input type="file" id="{{$name}}_files_0" name="{{$name}}_files_0">
</div>
<a class="btn-addfile" href="" onclick="{{$name}}_new(); return false;">
    <img src="{{theme_image_url btn_add}}" alt="{{str tag=element.files.addattachment section=pieforms}}">
</a>
