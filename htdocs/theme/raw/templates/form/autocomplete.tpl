<input type="hidden" id="{{$id}}" name="{{$name}}" {{if $describedby}}aria-describedby="{{$describedby}}"{{/if}} value="{{$value}}"/>

<script type="text/javascript">
addLoadEvent(function () {
    jQuery("#{{$id}}").select2({
        initSelection : function(element, callback) {
            callback({{$initvalue|safe}});
        },
        multiple: {{$multiple}},
        width: "{{$width}}",
        allowClear: {{$allowclear}},
        {{if $hint}}placeholder: "{{$hint}}",{{/if}}
        minimumInputLength: {{$mininputlength}},
        ajax: {
            url: "{{$ajaxurl}}",
            dataType: 'json',
            data: function(term, page) {
                return {
                    q: term,
                    page: page,
                    sesskey: "{{$sesskey}}"
                }
            },
            results: function(data, page) {
                return {
                    results: data.results,
                    more: data.more
                };
            }
        },
        {{$extraparams|safe}}
    });
});
jQuery("#{{$id}}").prop('disabled', {{$disabled}});
</script>
