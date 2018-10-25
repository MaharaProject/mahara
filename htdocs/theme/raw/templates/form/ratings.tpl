<div id="{{$id}}_wrapper" data-rating="{{$value}}"></div>
{{if !$readonly}}<input type=hidden name="rating" id="{{$id}}">{{/if}}

<script>
jQuery(function() {
    jQuery("#{{$id}}_wrapper").rating('create', {
        coloron:'{{$colouron}}',
        coloroff:'{{$colouroff}}',
        glyph:'icon-{{$icon}}',
        offglyph:'icon-{{$officon}}',
        emptyglyph: {{$iconempty}},
        limit: {{$limit}},
        {{if $onclick}}
            onClick: {{$onclick}},
        {{/if}}
        {{if $readonly}}
            readonly: true,
        {{/if}}
    });
});
</script>
