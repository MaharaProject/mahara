<script type="application/javascript">
    var {{$name}}_current = {{$next}};
    var {{$name}}_newrefinput;
    var {{$name}}_newref;

    function {{$name}}_new() {
        {{$name}}_current++;
        {{$name}}_newrefinput = INPUT({'type': 'text', 'name': '{{$name}}[' + {{$name}}_current + ']'});
        var {{$name}}_newref = DIV(null, {{$name}}_newrefinput);

        appendChildNodes('{{$name}}_list', {{$name}}_newref);

        {{$name}}_newrefinput.focus();
    }
</script>
<div id="{{$name}}_list">
{{foreach from=$value key=k item=v}}
  <div>
    <input type="text" name="{{$name}}[{{$k}}]" value="{{$v}}">
    <a href="" onclick="removeElement(this.parentNode); return false;">[x]</a>
  </div>
{{/foreach}}
  <div>
    <input type="text" name="{{$name}}[{{$next}}]" value="">
    <a href="" onclick="{{$name}}_new(); return false;">[+]</a>
  </div>
</div>
