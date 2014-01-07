{if $images}
<div class="slideshow" id="slideshow{$instanceid}">
    <table class="images fullwidth">
    <tr>
    <td class="control">
        <span class="prev disabled">&lsaquo;</span>
        <span class="first disabled">&laquo;</span>
    </td>
    <td>
    {foreach from=$images item=image key=k name=images}
        <a href="{$image.link}" target="_blank"><img src="{$image.source}" alt="{$image.title}" title="{$image.title}" style="max-width: {$width}px;{if !$dwoo.foreach.images.first} display:none;{/if}"></a>
        {if $showdescription && $image.title}<div class="caption" id="description_{$instanceid}_{$k}" style="{if !$dwoo.foreach.images.first} display:none;{/if}">{$image.title}</div>{/if}
    {/foreach}
    </td>
    <td class="control">
        <span class="next disabled">&rsaquo;</span>
        <span class="last disabled">&raquo;</span>
    </td>
    </tr>
    </table>
</div>
<script type="text/javascript">
var slideshow{$instanceid} = new Slideshow({$instanceid}, {$count});
$j(function() {
    if (($j('#slideshow{$instanceid}').width() - 60) < {$width}) {
        // adjust max-width of images to fit within slider
        $j('#slideshow{$instanceid} img').each(function() {
            $j(this).css('max-width',$j('#slideshow{$instanceid}').width() - 60);
        });
    }
});
</script>
{else}
  {str tag=noimagesfound section=artefact.file}
{/if}