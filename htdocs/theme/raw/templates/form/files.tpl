<script type="application/javascript">
    var {{$name}}_current = 0;
    var {{$name}}_newrefinput;
    var {{$name}}_newref;

    function {{$name}}_new() {
        {{$name}}_current++;
        var id = '{{$name}}_files_' + {{$name}}_current;
        {{$name}}_newlabel = jQuery('<label>', {'for': id, 'class': 'sr-only'}).append(jQuery('#{{$name}}_files_label').html());
        {{$name}}_newrefinput = jQuery('<input>', {'type': 'file', 'id': id, 'name': id, 'class': 'file'});
        var {{$name}}_newref = jQuery('<div>', {'class': 'file-wrapper'}).append({{$name}}_newlabel, {{$name}}_newrefinput);

        jQuery('#{{$name}}_list').append({{$name}}_newref);

        {{$name}}_newrefinput.focus();
    }
</script>
<div id="{{$name}}_list">
    {{if $maxfilesize}}
    <input type="hidden" name="MAX_FILE_SIZE" value="{{$maxfilesize}}">
    {{/if}}
    <div class="file-wrapper">
        <label id="{{$name}}_files_label" class="accessible-hidden sr-only" for="{{$name}}_files_0">{{$title}}</label>
        <input type="file" id="{{$name}}_files_0" name="{{$name}}_files_0">
    </div>
</div>
<a class="btn btn-default btn-xs" href="" onclick="{{$name}}_new(); return false;">
    <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
    <span class="">{{str tag=element.files.addattachment section=pieforms}}</span>
</a>
