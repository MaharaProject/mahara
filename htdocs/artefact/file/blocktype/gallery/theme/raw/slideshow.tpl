<div class="slideshow" id="slideshow{$instanceid}">
    <table class="images">
    <tr>
    <td class="control">
        <span class="first hidden">&laquo;</span>
        <span class="prev disabled">&lsaquo;</span>
    </td>
    <td>
    {foreach from=$images item=image name=images}
        <a href="{$image.link}" target="_blank"><img src="{$image.source}" alt="{$image.title}" title="{$image.title}" {if !$dwoo.foreach.images.first}style="display:none;"{/if}/></a>
    {/foreach}
    </td>
    <td class="control">
        <span class="next disabled">&rsaquo;</span>
        {*<span class="last">&raquo;</span>*}
    </td>
    </tr>
    </table>
</div>
<script>
var slideshow{$instanceid} = new Slideshow({$instanceid}, {$count});
</script>
