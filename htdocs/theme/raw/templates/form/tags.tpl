<script type="text/javascript">
    var tags_changed = false;
addLoadEvent(partial(augment_tags_control,'{{$id}}'))
</script>
<input type="text" size="{{$size}}" id="{{$id}}" name="{{$name}}" value="{{$value}}" {{if $describedby}}aria-describedby="{{$describedby}}"{{/if}}>
