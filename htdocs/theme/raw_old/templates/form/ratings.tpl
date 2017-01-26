<div id="{{$id}}-container" data-rating="{{$value}}">{{if !$readonly}}<input type=hidden name="rating" id="{{$id}}">{{/if}}</div>

<script type="application/javascript">
jQuery(document).ready(function() {
    jQuery("#{{$id}}-container").rating('create', {
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
