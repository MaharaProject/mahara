<script>
    var {{$name}}_current = {{$next}};
    var {{$name}}_newrefinput;
    var {{$name}}_newref;

    function {{$name}}_new() {
        {{$name}}_current++;
        {{$name}}_newrefinput = jQuery('<input>', {'type': 'text', 'name': '{{$name}}[' + {{$name}}_current + ']'});
        var {{$name}}_newref = jQuery('<div>').append({{$name}}_newrefinput);

        jQuery('#{{$name}}_list').append({{$name}}_newref);

        {{$name}}_newrefinput.trigger("focus");
    }
</script>
<div id="{{$name}}_list">
{{foreach from=$value key=k item=v}}
  <div>
    <input type="text" name="{{$name}}[{{$k}}]" value="{{$v}}">
    <a href="" onclick="jQuery(this.parentNode).remove(); return false;">[x]</a>
  </div>
{{/foreach}}
  <div>
    <input type="text" name="{{$name}}[{{$next}}]" value="">
    <a href="" onclick="{{$name}}_new(); return false;">[+]</a>
  </div>
</div>
