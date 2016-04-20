<select class="js-data-ajax" multiple="multiple" id="{{$id}}" name="{{$name}}[]" {{if $describedby}}aria-describedby="{{$describedby}}"{{/if}}>
{{if $initvalues}}
    {{foreach from=$initvalues item=value}}
    <option selected value="{{$value->id}}">{{$value->text}}</option>
    {{/foreach}}
{{/if}}
</select>

<script type="application/javascript">
{{if !$inblockconfig}}
    addLoadEvent(function () {
{{/if}}
    jQuery("#{{$id}}").select2({
        ajax: {
            url: "{{$ajaxurl}}",
            dataType: 'json',
            type: 'POST',
            delay: 250,
            data: function(params) {
                return {
                    'q': params.term,
                    'page': params.page || 0,
                    'sesskey': "{{$sesskey}}",
                    'offset': 0,
                    'limit': 10,
                }
            },
            processResults: function(data, page) {
                return {
                    results: data.results,
                    pagination: {
                        more: data.more
                    }
                };
            }
        },
        language: "{{$language}}",
        multiple: {{$multiple}},
        width: "{{$width}}",
        allowClear: {{$allowclear}},
        {{if $hint}}placeholder: "{{$hint}}",{{/if}}
        minimumInputLength: {{$mininputlength}},
        {{$extraparams|safe}}
    });
{{if !$inblockconfig}}
    });
{{/if}}
jQuery("#{{$id}}").prop('disabled', {{$disabled}});
</script>
