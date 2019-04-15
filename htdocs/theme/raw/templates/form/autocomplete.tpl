<select class="js-data-ajax" multiple="multiple" id="{{$id}}" name="{{$name}}[]" {{if $describedby}}aria-describedby="{{$describedby}}"{{/if}}>
{{if $initvalues}}
    {{foreach from=$initvalues item=value}}
    <option selected value="{{$value->id}}">{{$value->text}}</option>
    {{/foreach}}
{{/if}}
</select>

<script>
{{if !$inblockconfig}}
    jQuery(window).on('load', function () {
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
                    {{if $institution}}
                        'institution': "{{$institution}}",
                    {{/if}}
                }
            },
            processResults: function(data, page) {
                return {
                    {{if $renderresult}}
                    results: jQuery.map(data.results, function(item) {
                        // sometimes text contains html that has to be renderered in the result list (e.g. user profile)
                        // we're assigning text to resultsText variable that get rendered in results, and
                        // leave only text values in text variable. (in selection field will be displayed only text without markup)
                        return jQuery.extend(item, {
                          resultsText: item.text,
                          text: jQuery('<div>').html(item.text).text()
                        })
                    }),
                    {{else}}
                    results: data.results,
                    {{/if}}
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
        templateResult: function(item) {
            return item.resultsText || item.text;
        },
        {{$extraparams|safe}}
    });
    jQuery("#{{$id}}").on('select2:select', function(e) {
        window.pageIsRendering = false;
    });
    jQuery("#{{$id}}").on('select2:unselect', function(e) {
        window.pageIsRendering = false;
    });
{{if !$inblockconfig}}
    });
{{/if}}
jQuery("#{{$id}}").prop('disabled', {{$disabled}});
</script>
