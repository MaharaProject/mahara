<script>
    var {{$name}}_current = 0;
    var {{$name}}_newrefinput;
    var {{$name}}_newref;

    function {{$name}}_new() {
        {{$name}}_current++;
        var id = '{{$name}}_files_' + {{$name}}_current;
        {{$name}}_newlabel = jQuery('<label>', {'for': id, 'class': 'sr-only'}).append(jQuery('#{{$name}}_files_label').html());
        {{$name}}_newrefinput = jQuery('<input>', {'type': 'file', 'id': id, 'name': id, 'class': 'file'});
        {{$name}}_newmaxsize = jQuery('<span>', {'id': id, 'class': 'file-description'}).append(jQuery('#{{$name}}_files_maxsize').html());

        var {{$name}}_newref = jQuery('<div>', {'class': 'file-wrapper'}).append({{$name}}_newlabel, {{$name}}_newrefinput, {{$name}}_newmaxsize);

        jQuery('#{{$name}}_list').append({{$name}}_newref);

        {{$name}}_newrefinput.trigger("focus");
    }
</script>
<div id="{{$name}}_list">
    {{if $maxfilesize}}
    <input type="hidden" name="MAX_FILE_SIZE" value="{{$maxfilesize}}">
    {{/if}}
    <div class="file-wrapper">
        <label id="{{$name}}_files_label" class="accessible-hidden sr-only" for="{{$name}}_files_0">{{$title}}</label>
        <input type="file" id="{{$name}}_files_0" name="{{$name}}_files_0">
        <span id="{{$name}}_files_maxsize" class="file-description">({{$maxfilesizedesc}})</span>
    </div>
</div>
<a class="btn btn-secondary btn-sm" href="" onclick="{{$name}}_new(); return false;">
    <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
    <span class="">{{str tag=element.files.addattachment section=pieforms}}</span>
</a>
